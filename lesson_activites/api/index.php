<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");


\Bitrix\Main\Loader::includeModule('crm');


//id ответственного и уведомителя
$responsible = 1;

// создаем компанию
$arNewCompany = array(
    "TITLE" => 'Тестовая компания из API',
    "OPENED" => "Y",
    "COMPANY_TYPE" => "CUSTOMER",
    "ASSIGNED_BY_ID" => $responsible,
);
$arNewCompany['FM']['PHONE'] = array(
    "n0" => array(
        "VALUE_TYPE" => "WORK",
        "VALUE" => "123456",
    )
);
$arNewCompany['FM']['EMAIL'] = array(
    "n0" => array(
        "VALUE_TYPE" => "WORK",
        "VALUE" => "test@test.ru",
    )
);
$company = new CCrmCompany(false);
$companyID = $company->Add($arNewCompany);

echo $companyID;


// создаем лид
/*
$lead = new CCrmLead; 
$arFields = Array(
    "TITLE" => "Тестовый лид из API",
    "SOURCE_DESCRIPTION" => "",
    "STATUS_DESCRIPTION" => "",
    "OPPORTUNITY" => 123456,
    "CURRENCY_ID" => "RUB",
    "PRODUCT_ID" => "PRODUCT_1",
    "SOURCE_ID" => "SELF",
       "STATUS_ID" => "NEW",
       "ASSIGNED_BY_ID" => $responsible,  
);
 
$leadID = $lead->Add($arFields);
echo $leadID;*/

// создаем сделку
/*$arFields = array(
    "COMPANY_ID" => $companyID,
    "TITLE" => "Тестовая сделка из API" ,
    "STAGE_ID" => "C8:NEW",
    "SOURCE_ID" => "SELF",
    "CURRENCY_ID" => "RUB",
    "ASSIGNED_BY_ID" => $responsible,
    "CATEGORY_ID" => 8,
);
$deal = new CCrmDeal(false);
$dealID = $deal->Add($arFields, $bUpdateSearch = true, $options = array());
echo $dealID;*/