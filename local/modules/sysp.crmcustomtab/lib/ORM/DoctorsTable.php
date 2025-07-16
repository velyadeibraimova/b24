<?php

namespace Sysp\CrmCustomTab\ORM;

use Bitrix\Main\Config\Option;

class DoctorsTable extends AbstractIblockPropertyValuesTable
{
    protected static $IBLOCK_ID = null;

    public static function getIblockId(): ?int
    {
        return static::$IBLOCK_ID ?? Option::get('sysp.crmcustomtab', 'doctors_iblock_id');
    }
}