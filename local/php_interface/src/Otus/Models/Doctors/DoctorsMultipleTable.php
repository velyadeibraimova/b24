<?php

namespace Otus\Models\Doctors;

use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

class DoctorsMultipleTable extends DataManager
{
    const IBLOCK_ID = 16;

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_iblock_element_prop_m'.static::IBLOCK_ID;
    }

    /**
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        $map = [];
        $map['ID'] = new IntegerField('ID', ['primary' => true]);
        $map['IBLOCK_ELEMENT_ID'] = new IntegerField('IBLOCK_ELEMENT_ID');
        $map['IBLOCK_PROPERTY_ID'] = new IntegerField('IBLOCK_PROPERTY_ID');
        $map['VALUE'] = new StringField('VALUE');
        $map['DESCRIPTION'] = new StringField('DESCRIPTION');

        return $map;
    }
}