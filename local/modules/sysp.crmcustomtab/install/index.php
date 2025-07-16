<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Service\Factory;
use Bitrix\Crm\Item;
use Sysp\CrmCustomTab\ORM\ProceduresTable;
use Sysp\CrmCustomTab\ORM\DoctorsTable;
use Sysp\CrmCustomTab\ORM\PriceListTable;

class sysp_crmcustomtab extends CModule
{
    private $availableEntities = ['LEAD', 'DEAL', 'CONTACT', 'COMPANY'];
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        include __DIR__ . '/version.php';

        /** @var array $arModuleVersion array from version.php */
        $this->MODULE_ID = 'sysp.crmcustomtab';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');
    }

    public function DoInstall()
    {
        if (!$this->isVersionD7() || !$this->hasRequiredModules()) {
            throw new SystemException(Loc::getMessage('INSTALL_ERRORS'));
        }

        global $APPLICATION;
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $step = $request->getPost("step");

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('INSTALL_TITLE_STEP_1'),
                __DIR__ . '/step1.php'
            );
        } elseif ($step == 2) {
            $entityType = $request->getPost('ENTITY_TYPE');
            $doctorsIblockId = intval($request->getPost('DOCTORS_IBLOCK_ID'));
            $proceduresIblockId = intval($request->getPost('PROCEDURES_IBLOCK_ID'));
            $demoData = $request->getPost('DEMO_DATA');

            $errors = [];
            $validValues = [];

            if (!in_array($entityType, $this->availableEntities)) {
                $errors[] = Loc::getMessage('INSTALL_ERROR_ENTITY');
            } else {
                $validValues['ENTITY_TYPE'] = $entityType;
            }

            if (!IblockTable::getById($doctorsIblockId)->fetch()) {
                $errors[] = Loc::getMessage('INSTALL_ERROR_DOCTORS');
            } else {
                $validValues['DOCTORS_IBLOCK_ID'] = $doctorsIblockId;
            }

            if (!IblockTable::getById($proceduresIblockId)->fetch()) {
                $errors[] = Loc::getMessage('INSTALL_ERROR_PROCEDURES');
            } else {
                $validValues['PROCEDURES_IBLOCK_ID'] = $proceduresIblockId;
            }

            if (!empty($validValues)) {
                $validValues['DEMO_DATA'] = $demoData;
            }

            if (!empty($errors)) {
                $GLOBALS['ERRORS'] = implode('<br>', $errors);
                $GLOBALS['VALID_VALUES'] = $validValues;
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage('INSTALL_TITLE_STEP_1'),
                    __DIR__ . '/step1.php'
                );
                return;
            }

            ModuleManager::registerModule($this->MODULE_ID);

            Option::set($this->MODULE_ID, 'entity_type', $entityType);
            Option::set($this->MODULE_ID, 'doctors_iblock_id', $doctorsIblockId);
            Option::set($this->MODULE_ID, 'procedures_iblock_id', $proceduresIblockId);

            $this->installFiles();
            $this->installDb();
            if ($demoData == 'Y') {
                $this->installDemoData($entityType);
            }
            $this->installEvents();
        }
    }

    public function DoUninstall()
    {
        $this->unInstallFiles();
        $this->unInstallDb();
        $this->unInstallEvents();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles(array $params = []): void
    {
        $components_distrib_path = __DIR__ . '/components';

        if (Directory::isDirectoryExists($components_distrib_path)) {
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/sysp';

            if (!Directory::isDirectoryExists($targetPath)) {
                $d = new Directory($targetPath);
                $d->create();
                if (!$d->isExists()) {
                    throw new Exception('Error creating directory ' . $targetPath);
                }
            };

            CopyDirFiles($components_distrib_path, $targetPath, true, true);
        } else {
            throw new InvalidPathException($components_distrib_path);
        }
    }

    public function uninstallFiles(): void
    {
        $components_distrib_path = __DIR__ . '/components';
        $namespace_directory = $_SERVER['DOCUMENT_ROOT'] . '/local/components/sysp/';

        if (Directory::isDirectoryExists($components_distrib_path)) {
            $installed_components = new \DirectoryIterator($components_distrib_path);
            foreach ($installed_components as $component) {
                if ($component->isDir() && !$component->isDot()) {
                    $target_path = $namespace_directory . $component->getFilename();
                    if (Directory::isDirectoryExists($target_path)) {
                        Directory::deleteDirectory($target_path);
                    }
                }
            }
            
            $d = new Directory($namespace_directory);
            if ($d->isExists()) {
                $children = $d->getChildren();
                if (empty($children)) {
                    $d->delete();
                }
            }
        } else {
            throw new InvalidPathException($namespace_directory);
        }
    }

    public function installDb(): void
    {
        Loader::includeModule($this->MODULE_ID);

        $connection = Application::getConnection();
        $connection->queryExecute("
            CREATE TABLE IF NOT EXISTS `otus_price_list` (
              `id` int NOT NULL AUTO_INCREMENT,
              `doctor_id` int NOT NULL,
              `procedure_id` int NOT NULL,
              `cost` decimal(10,2) NOT NULL,
              `recommendations` text,
              `entity_id` int DEFAULT NULL,
              PRIMARY KEY (`id`)
            )
        ");
    }

    public function installDemoData(string $entityType = ''): void
    {
        if ($entityType == 'LEAD') {
            $entityId = $this->createCrmEntity('LEAD', [
                'TITLE' => Loc::getMessage('DEMO_LEAD_TITLE'),
                'STATUS_ID' => 'NEW',
            ]);
        } elseif ($entityType == 'DEAL') {
            $entityId = $this->createCrmEntity('DEAL', [
                'TITLE' => Loc::getMessage('DEMO_DEAL_TITLE'),
                'OPPORTUNITY' => 10000,
                'CURRENCY_ID' => 'RUB',
                'STAGE_ID' => 'NEW',
            ]);
        } elseif ($entityType == 'CONTACT') {
            $entityId = $this->createCrmEntity('CONTACT', [
                'NAME' => Loc::getMessage('DEMO_CONTACT_NAME'),
                'LAST_NAME' => Loc::getMessage('DEMO_CONTACT_LASTNAME'),
            ]);
        } elseif ($entityType == 'COMPANY') {
            $entityId = $this->createCrmEntity('COMPANY', [
                'TITLE' => Loc::getMessage('DEMO_COMPANY_NAME'),
            ]);
        } else {
            return;
        }

        $proc1 = ProceduresTable::add(['NAME' => Loc::getMessage('DEMO_PROC1_NAME')]);
        $proc2 = ProceduresTable::add(['NAME' => Loc::getMessage('DEMO_PROC2_NAME')]);
        $proc3 = ProceduresTable::add(['NAME' => Loc::getMessage('DEMO_PROC3_NAME')]);

        $doctor1 = DoctorsTable::add([
            'NAME' => 'slug_' . rand(0, 1000) . '_' . time(),
            'PROP1' => Loc::getMessage('DEMO_DOCTOR1_LASTNAME'),
            'PROP2' => Loc::getMessage('DEMO_DOCTOR1_FIRSTNAME'),
            'PROP3' => Loc::getMessage('DEMO_DOCTOR1_PATRONYMIC'),
        ]);
        $doctor2 = DoctorsTable::add([
            'NAME' => 'slug_' . rand(0, 1000) . '_' . time(),
            'PROP1' => Loc::getMessage('DEMO_DOCTOR2_LASTNAME'),
            'PROP2' => Loc::getMessage('DEMO_DOCTOR2_FIRSTNAME'),
            'PROP3' => Loc::getMessage('DEMO_DOCTOR2_PATRONYMIC'),
        ]);

        PriceListTable::add([
            'DOCTOR_ID' => $doctor1,
            'PROCEDURE_ID' => $proc1,
            'COST' => mt_rand(1, 20) * 100,
            'RECOMMENDATIONS' => Loc::getMessage('DEMO_RECOMMENDATIONS_PROC1'),
            'ENTITY_ID' => $entityId,
        ]);
        PriceListTable::add([
            'DOCTOR_ID' => $doctor1,
            'PROCEDURE_ID' => $proc3,
            'COST' => mt_rand(1, 20) * 100,
            'RECOMMENDATIONS' => Loc::getMessage('DEMO_RECOMMENDATIONS_PROC3'),
            'ENTITY_ID' => $entityId,
        ]);
        PriceListTable::add([
            'DOCTOR_ID' => $doctor2,
            'PROCEDURE_ID' => $proc1,
            'COST' => mt_rand(1, 20) * 100,
            'RECOMMENDATIONS' => Loc::getMessage('DEMO_RECOMMENDATIONS_PROC1'),
            'ENTITY_ID' => $entityId,
        ]);
        PriceListTable::add([
            'DOCTOR_ID' => $doctor2,
            'PROCEDURE_ID' => $proc2,
            'COST' => mt_rand(1, 20) * 100,
            'RECOMMENDATIONS' => Loc::getMessage('DEMO_RECOMMENDATIONS_PROC2'),
            'ENTITY_ID' => $entityId,
        ]);
        PriceListTable::add([
            'DOCTOR_ID' => $doctor2,
            'PROCEDURE_ID' => $proc3,
            'COST' => mt_rand(1, 20) * 100,
            'RECOMMENDATIONS' => Loc::getMessage('DEMO_RECOMMENDATIONS_PROC3'),
            'ENTITY_ID' => $entityId,
        ]);

        //store options for unInstallDb
        $arDemoData = [
            'ENTITY_ID' => $entityId,
            'PROCEDURES' => [$proc1, $proc2, $proc3],
            'DOCTORS' => [$doctor1, $doctor2],
        ];
        Option::set($this->MODULE_ID, 'demo_data', serialize($arDemoData));
    }

    public function unInstallDb(): void
    {
        $connection = Application::getConnection();
        $connection->queryExecute("DROP TABLE IF EXISTS `otus_price_list`");

        $entityType = Option::get($this->MODULE_ID, 'entity_type');
        $arDemoData = unserialize(Option::get($this->MODULE_ID, 'demo_data'));

        if ($arDemoData) {
            $this->deleteCrmEntity($entityType, $arDemoData['ENTITY_ID']);
            foreach ($arDemoData['PROCEDURES'] as $elementId) {
                $element = new CIBlockElement();
                $element->Delete($elementId);
            }
            foreach ($arDemoData['DOCTORS'] as $elementId) {
                $element = new CIBlockElement();
                $element->Delete($elementId);
            }
        }
    }
    public function installEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Sysp\\Crmcustomtab\\EventHandlers\\Crm',
            'updateTabs'
        );
    }

    public function unInstallEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Sysp\\Crmcustomtab\\EventHandlers\\Crm',
            'updateTabs'
        );
    }

    public function isVersionD7(): bool
    {
        return version_compare(
            ModuleManager::getVersion('main'),
            '20.00.00',
            '>='
        );
    }

    public function hasRequiredModules(): bool
    {
        return Loader::includeModule('crm') && Loader::includeModule('iblock');
    }

    public function createCrmEntity(string $entityType, array $arFields): ?int
    {
        if (!$entityType) {
            return null;
        }
        Loader::includeModule('crm');

        $entityMap = [
            'LEAD' => \CCrmOwnerType::Lead,
            'DEAL' => \CCrmOwnerType::Deal,
            'CONTACT' => \CCrmOwnerType::Contact,
            'COMPANY' => \CCrmOwnerType::Company,
        ];

        $entityTypeId = $entityMap[$entityType] ?? null;
        if (!$entityTypeId) {
            echo "Unknown entity type: $entityType";
            return null;
        }

        $factory = Container::getInstance()->getFactory($entityTypeId);
        if (!$factory) {
            echo "Factory not found for CRM Type: $entityType";
            return null;
        }

        $item = $factory->createItem();
        $item->setFromCompatibleData($arFields);
        $addOperation = $factory->getAddOperation($item);
        $result = $addOperation->launch();

        if ($result->isSuccess()) {
            return $item->getId();
        } else {
            return null;
        }
    }

    public function deleteCrmEntity(string $entityType, int $entityId): void
    {
        if (!$entityType || !$entityId) {
            return;
        }
        Loader::includeModule('crm');

        $entityMap = [
            'LEAD' => \CCrmOwnerType::Lead,
            'DEAL' => \CCrmOwnerType::Deal,
            'CONTACT' => \CCrmOwnerType::Contact,
            'COMPANY' => \CCrmOwnerType::Company,
        ];

        $entityTypeId = $entityMap[$entityType] ?? null;
        if (!$entityTypeId) {
            echo "Unknown entity type: $entityType";
            return;
        }

        $factory = Container::getInstance()->getFactory($entityTypeId);
        if (!$factory) {
            echo "Factory not found for CRM Type: $entityType";
            return;
        }
        $item = $factory->getItem($entityId);
        if (!$item) {
            return;
        }
        $deleteOperation = $factory->getDeleteOperation($item);
        $deleteOperation->launch();
    }
}