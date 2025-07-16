<?php

namespace Sysp\CrmCustomTab\ORM;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Entity\Query\Join;

class PriceListTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'otus_price_list';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('LIST_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'DOCTOR_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('LIST_ENTITY_DOCTOR_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'PROCEDURE_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('LIST_ENTITY_PROCEDURE_ID_FIELD'),
                ]
            ),
            new FloatField(
                'COST',
                [
                    'required' => true,
                    'title' => Loc::getMessage('LIST_ENTITY_COST_FIELD'),
                ]
            ),
            new TextField(
                'RECOMMENDATIONS',
                [
                    'title' => Loc::getMessage('LIST_ENTITY_RECOMMENDATIONS_FIELD'),
                ]
            ),
            new IntegerField(
                'ENTITY_ID',
                [
                    'title' => Loc::getMessage('LIST_ENTITY_ENTITY_ID_FIELD'),
                ]
            ),
            (new Reference('DOCTOR', DoctorsTable::class, Join::on('this.DOCTOR_ID', 'ref.IBLOCK_ELEMENT_ID')))
                ->configureJoinType('inner'),
            (new Reference('PROCEDURE', ProceduresTable::class, Join::on('this.PROCEDURE_ID', 'ref.IBLOCK_ELEMENT_ID')))
                ->configureJoinType('inner'),
        ];
    }
}