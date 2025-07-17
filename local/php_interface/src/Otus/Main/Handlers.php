<?php
namespace Otus\Main;

use Bitrix\Main\EventResult;
use Bitrix\Main\UI\Extension;

class Handlers
{
    public static function onBeforePrologHandler(): EventResult
    {
        Extension::load('sidepanel.helper');
        return new EventResult(EventResult::SUCCESS);
    }
}
