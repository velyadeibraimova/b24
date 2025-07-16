<?php
/** @var CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

// Обработка ошибок
if (!empty($GLOBALS["ERRORS"])) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('ERRORS_TITLE'),
        'DETAILS' => is_array($GLOBALS["ERRORS"]) ? implode('<br>', $GLOBALS["ERRORS"]) : $GLOBALS["ERRORS"],
        'HTML' => true
    ]);
}

// Установим DEAL как значение по умолчанию, если ничего не выбрано
$defaultEntityType = 'LEAD';
$currentEntityType = $GLOBALS["VALID_VALUES"]["ENTITY_TYPE"] ?? $defaultEntityType;
$doctorsIblockId = $GLOBALS["VALID_VALUES"]["DOCTORS_IBLOCK_ID"] ?? '';
$proceduresIblockId = $GLOBALS["VALID_VALUES"]["PROCEDURES_IBLOCK_ID"] ?? '';
$demoDataChecked = ($GLOBALS["VALID_VALUES"]["DEMO_DATA"] ?? '') === 'Y' ? 'checked' : '';
?>

<form action="<?= $APPLICATION->GetCurPage() ?>" method="post" id="module-install-form">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="sysp.crmcustomtab">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">

    <table class="edit-table">
        <tr>
            <td width="40%"><label for="entity_type"><?= Loc::getMessage('LABEL_ENTITY_TYPE')?>:</label></td>
            <td width="60%">
                <select id="entity_type" name="ENTITY_TYPE" class="typeselect">
                    <option value="LEAD" <?= $currentEntityType === 'LEAD' ? 'selected' : '' ?>>
                        <?= Loc::getMessage('LABEL_ENTITY_TYPE_LEAD') ?>
                    </option>
                    <option value="DEAL" <?= $currentEntityType === 'DEAL' ? 'selected' : '' ?>>
                        <?= Loc::getMessage('LABEL_ENTITY_TYPE_DEAL') ?>
                    </option>
                    <option value="CONTACT" <?= $currentEntityType === 'CONTACT' ? 'selected' : '' ?>>
                        <?= Loc::getMessage('LABEL_ENTITY_TYPE_CONTACT') ?>
                    </option>
                    <option value="COMPANY" <?= $currentEntityType === 'COMPANY' ? 'selected' : '' ?>>
                        <?= Loc::getMessage('LABEL_ENTITY_TYPE_COMPANY') ?>
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <td><label for="doctors_iblock_id"><?= Loc::getMessage('LABEL_DOCTORS_IBLOCK_ID')?>:</label></td>
            <td>
                <input type="text" name="DOCTORS_IBLOCK_ID" id="doctors_iblock_id"
                       value="<?= htmlspecialcharsbx($doctorsIblockId) ?>"
                       size="5" required />
            </td>
        </tr>

        <tr>
            <td><label for="procedures_iblock_id"><?= Loc::getMessage('LABEL_PROCEDURES_IBLOCK_ID')?>:</label></td>
            <td>
                <input type="text" name="PROCEDURES_IBLOCK_ID" id="procedures_iblock_id"
                       value="<?= htmlspecialcharsbx($proceduresIblockId) ?>"
                       size="5" required />
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <label>
                    <input type="checkbox" name="DEMO_DATA" id="demo_data" value="Y" <?= $demoDataChecked ?>>
                    <?= Loc::getMessage("DEMO_DATA") ?>
                </label>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding-top: 20px;">
                <input type="submit" name="install" value="<?= Loc::getMessage('SUBMIT_TEXT')?>" class="adm-btn-save">
            </td>
        </tr>
    </table>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Можно добавить валидацию перед отправкой
        document.getElementById('module-install-form').addEventListener('submit', function(e) {
            var doctorsId = document.getElementById('doctors_iblock_id').value;
            var proceduresId = document.getElementById('procedures_iblock_id').value;

            if (!doctorsId || !proceduresId) {
                alert('<?= Loc::getMessage('FILL_REQUIRED_FIELDS') ?>');
                e.preventDefault();
                return false;
            }
        });
    });
</script>