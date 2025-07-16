<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$moduleId = htmlspecialcharsbx('' != $request['mid'] ? $request['mid'] : $request['id']);
$availableEntities = ['LEAD', 'DEAL', 'CONTACT', 'COMPANY'];

Loader::includeModule($moduleId);
Loader::includeModule('crm');

if ($request->isPost() && check_bitrix_sessid()) {
    $entityType = $request->getPost('ENTITY_TYPE');
    $doctorsIblockId = intval($request->getPost('DOCTORS_IBLOCK_ID')) ?? 0;
    $proceduresIblockId = intval($request->getPost('PROCEDURES_IBLOCK_ID')) ?? 0;

    $errors = [];

    if (!in_array($entityType, $availableEntities)) {
        $errors[] = Loc::getMessage('ERROR_ENTITY');
    } else {
        Option::set($moduleId, 'entity_type', $entityType);
    }

    if (!IblockTable::getById($doctorsIblockId)->fetch()) {
        $errors[] = Loc::getMessage('ERROR_DOCTORS');
    } else {
        Option::set($moduleId, 'doctors_iblock_id', $doctorsIblockId);
    }

    if (!IblockTable::getById($proceduresIblockId)->fetch()) {
        $errors[] = Loc::getMessage('ERROR_PROCEDURES');
    } else {
        Option::set($moduleId, 'procedures_iblock_id', $proceduresIblockId);
    }

    if (!empty($errors)) {
        CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => Loc::getMessage('SAVE_ERRORS_TITLE'),
            'DETAILS' => implode('<br>', $errors),
            'HTML' => true
        ]);
    } else {
        CAdminMessage::ShowMessage([
            'TYPE' => 'OK',
            'MESSAGE' => Loc::getMessage('SAVE_SUCCESS_TITLE'),
            'DETAILS' => '',
            'HTML' => true
        ]);
    }
}

$currentEntityType = Option::get($moduleId, 'entity_type');
$proceduresIblockId = Option::get($moduleId, 'procedures_iblock_id');
$doctorsIblockId = Option::get($moduleId, 'doctors_iblock_id');

$arTabs = [
    [
        'DIV' => 'settings',
        'TAB' => Loc::getMessage('MODULE_CONFIG_TAB'),
        'TITLE' => Loc::getMessage('MODULE_CONFIG_TITLE'),
        'OPTIONS' => [
            [
                'ENTITY_TYPE',
                Loc::getMessage('ENTITY_TYPE'),
                $currentEntityType,
                [
                    'selectbox',
                    [
                        'LEAD' => Loc::getMessage('LEAD_TITLE'),
                        'DEAL' => Loc::getMessage('DEAL_TITLE'),
                        'CONTACT' => Loc::getMessage('CONTACT_TITLE'),
                        'COMPANY' => Loc::getMessage('COMPANY_TITLE'),
                    ],
                ],
            ],
            [
                'PROCEDURES_IBLOCK_ID',
                Loc::getMessage('PROCEDURES_IBLOCK_ID'),
                $proceduresIblockId,
                ['text', 2],
            ],
            [
                'DOCTORS_IBLOCK_ID',
                Loc::getMessage('DOCTORS_IBLOCK_ID'),
                $doctorsIblockId,
                ['text', 2],
            ],
        ],
    ],
];

$tabControl = new CAdminTabControl("tabControl", $arTabs);
$tabControl->Begin();
?>

<form action="<?php echo($APPLICATION->GetCurPage()); ?>?mid=<?= $moduleId ?>&lang=<?php echo(LANG); ?>" method="post">
    <?php
    $tabControl->BeginNextTab();
    foreach ($arTabs as $tab) {
        if ($tab["OPTIONS"]) {
            __AdmSettingsDrawList($moduleId, $tab["OPTIONS"]);
        }
    }
    $tabControl->Buttons();
    ?>
    <input type='submit' name='apply' value='<?= Loc::getMessage('MAIN_SAVE') ?>' class='adm-btn-save'/>
    <?= bitrix_sessid_post() ?>
</form>

<?php $tabControl->End();
