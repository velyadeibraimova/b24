<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Test");

echo count($arResult['ITEMS']);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>