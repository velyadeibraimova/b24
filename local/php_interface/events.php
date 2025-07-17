<?php
$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('', 'MaterialTypesOnBeforeAdd', [
    '\Otus\Hlblock\Handlers\Element',
    'onBeforeAddHandler',
]);

$eventManager->addEventHandler('', 'MaterialTypesOnBeforeUpdate', [
    '\Otus\Hlblock\Handlers\Element',
    'onBeforeUpdateHandler',
]);

$eventManager->addEventHandler('', 'MaterialTypesOnBeforeDelete', [
    '\Otus\Hlblock\Handlers\Element',
    'onBeforeDeleteHandler',
]);

$eventManager->addEventHandler('main', 'OnBeforeProlog', [
    '\Otus\Main\Handlers',
    'onBeforePrologHandler',
]);
