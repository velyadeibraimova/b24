<?php

namespace Sysp\UserPropBooking\UserTypes;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Otus\Models\Doctors\DoctorsTable;
use Otus\Models\Doctors\ProceduresTable;

class CIblockPropertyBooking
{
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
            'USER_TYPE' => 'otus_booking',
            'DESCRIPTION' => Loc::getMessage('PROPERTY_DESCRIPTION'),
            'CLASS_NAME' => __CLASS__,
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
        ];
    }

    public static function GetPublicViewHTML($arProperty, $value, $arHtmlControl)
    {
        if (!$arProperty["ELEMENT_ID"]) {
            return '';
        }

        $arDoctorData = DoctorsTable::getList([
            'select' => [
                'PROCS_IDS' => 'PROP4',
                'PROCS_NAMES' => 'PROP4_ELEMENT_NAME',
            ],
            'filter' => [
                'ELEMENT.ACTIVE' => 'Y',
                '=IBLOCK_ELEMENT_ID' => $arProperty["ELEMENT_ID"],
            ],
        ])->fetch();

        $arProcedures = array_combine($arDoctorData['PROCS_IDS'], $arDoctorData['PROCS_NAMES']);

        $resultHTML = '';
        foreach ($arProcedures as $procedureId => $procedureName) {
            $resultHTML .= sprintf(
                '<a href="javascript:;" data-type="userpropbooking" data-doctor-id="%d" data-proc-id="%d">%s</a><br/>',
                $arProperty["ELEMENT_ID"],
                $procedureId,
                htmlspecialchars($procedureName)
            );
        }

        return $resultHTML;
    }
    public static function GetPublicEditHTML($arProperty, $value, $arHtmlControl)
    {
        return '<span>' . Loc::getMessage('EDITING_IS_UNMEAN') . '</span>';
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return '<span>' . Loc::getMessage('FIELD_IS_UNAVAILABLE') . '</span>';
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl)
    {
        return '<span class="main-grid-cell-content">' . Loc::getMessage('EDITING_IS_UNMEAN') . '</span>';
    }
}
