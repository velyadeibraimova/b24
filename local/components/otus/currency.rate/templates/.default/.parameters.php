<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arTemplateParameters = array(
    "CURRENCY" => array(
        "NAME" => GetMessage("CURRENCY_SELECT"),
        "TYPE" => "LIST",
        "VALUES" => array(),
        "DEFAULT" => GetMessage("CURRENCY_DEFAULT"),
        "REFRESH" => "Y"
    )
);

// Получаем список валют из базы данных
if (CModule::IncludeModule('currency')) {
    $currencies = CCurrency::GetList(($by = "SORT"), ($order = "ASC"));
    while ($currency = $currencies->Fetch()) {
        $arTemplateParameters["CURRENCY"]["VALUES"][$currency["CURRENCY"]] = $currency["CURRENCY"] . " (" . $currency["FULL_NAME"] . ")";
    }
} 