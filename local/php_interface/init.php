<?php

declare(strict_types=1);

use Bitrix\Main\EventManager;

/*
* Автозагрузка composer
* */
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

/*
* Автозагрузка
* */
if (file_exists(__DIR__ . '/autoloader.php')) {
    require_once(__DIR__ . '/autoloader.php');
}

if (file_exists(__DIR__ . '/src/autoloader.php')) {
    require_once(__DIR__ . '/src/autoloader.php');
}

/*
* Автозагрузка (Второй вариант)
* */

include_once __DIR__ . '/../app/autoload.php';


if (file_exists(__DIR__ . '/events.php')) {
    require_once __DIR__ . '/events.php';
}
if (file_exists(__DIR__ . '/classes/Dadata.php')) {
    include_once __DIR__ . '/classes/Dadata.php';

}
function pr($var, $type = false)
{
    echo '<pre style="font-size:10px; border:1px solid #000; background:#FFF; text-align:left; color:#000;">';
    if ($type)
        var_dump($var);
    else
        print_r($var);
    echo '</pre>';
}

\Bitrix\Main\UI\Extension::load([
    //'helper.log_events',
    //'ajax.all_ajax_handler',
    //'homework.begin_date_button'
]);

\Bitrix\Main\UI\Extension::load(['otus.workday_confirm']);

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    ['Otus\EventHandlers\Iblock', 'updateDealAfterRequestChange']
);

EventManager::getInstance()->addEventHandler(
    'crm',
    'OnAfterCrmDealUpdate',
    ['Otus\EventHandlers\Crm', 'updateRequestsAfterDealChange']
);
