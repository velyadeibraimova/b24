<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Курсы валют");

 $APPLICATION->IncludeComponent(
	"otus:currency.rate", 
	".default", 
	array(
		"CURRENCY" => "USD",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); 