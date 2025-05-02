<?php
namespace Models\Lists;

use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Models\AbstractIblockPropertyValuesTable;

class SpecsPropertyValuesTable extends AbstractIblockPropertyValuesTable
{
    public const IBLOCK_ID = 20;
    public static function getMap(): array {
        $map = [
            "DOCTORS" => (new ManyToMany('DOCTORS', DoctorsPropertyValuesTable::class))
             ->configureTableName('b_iblock_element_prop_m22')
            ];
        return parent::getMap()+$map;
    }

}
