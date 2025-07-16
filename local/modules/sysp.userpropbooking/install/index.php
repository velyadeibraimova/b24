<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;

class sysp_userpropbooking extends CModule
{
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
        $this->MODULE_ID = 'sysp.userpropbooking';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('SYSP_USERPROPBOOKING_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SYSP_USERPROPBOOKING_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('SYSP_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('SYSP_PARTNER_URI');
    }

    public function DoInstall()
    {
        if (!$this->isVersionD7() || !$this->hasRequiredModules()) {
            throw new SystemException(Loc::getMessage('INSTALL_ERRORS'));
        }

        global $APPLICATION;
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        ModuleManager::registerModule($this->MODULE_ID);

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/local/modules/sysp.userpropbooking/install/js",
            $_SERVER["DOCUMENT_ROOT"]."/local/js",
            true,
            true
        );

        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
			'Sysp\UserPropBooking\UserTypes\CIblockPropertyBooking',
            'GetUserTypeDescription'
        );
    }

    public function DoUninstall()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/local/js/sysp/userpropbooking");

		EventManager::getInstance()->unRegisterEventHandler(
           'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
			'Sysp\UserPropBooking\UserTypes\CIblockPropertyBooking',
            'GetUserTypeDescription'
        );
		
        ModuleManager::unRegisterModule($this->MODULE_ID);
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
        return Loader::includeModule('iblock');
    }
}