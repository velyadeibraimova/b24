<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');?>
<?php
\Bitrix\Main\UI\Extension::load('selector.ui-selector');
?>

<select multiple id="bitrix-select">
    <option value="1">Книга 1</option>
    <option value="2">Книга 2</option>
</select>

<script>
    BX.ready(function () {
        BX.Selector.UiSelector.createTagSelector(BX('bitrix-select'));
    });
</script>
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>
