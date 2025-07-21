<?php

namespace Otus\EventHandlers;

use Bitrix\Main\Diag\Debug;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Crm\DealTable;

class Iblock
{
    public static function updateDealAfterRequestChange(&$arItem)
    {
        // 1. Получаем ID инфоблока
        $iblock = IblockTable::getList([
            'filter' => ['CODE' => 'requests'],
            'select' => ['ID']
        ])->fetch();

        if (!$iblock || $arItem['IBLOCK_ID'] != $iblock['ID']) {
            return false;
        }

        // 2. Обработка свойств
        $arProperty = [];
        foreach ($arItem['PROPERTY_VALUES'] as $propId => $propValues) {
            $property = PropertyTable::getList([
                'filter' => ['=ID' => $propId],
                'select' => ['CODE']
            ])->fetch();

            if ($property) {
                $code = $property['CODE'];
                foreach ($propValues as $valueData) {
                    if (is_array($valueData) && array_key_exists('VALUE', $valueData)) {
                        $value = $valueData['VALUE'];
                    } else {
                        $value = $valueData;
                    }
                    if ($value !== null && $value !== '') {
                        $arProperty[$code] = ($code === 'RESPONSIBLE') ? (int)$value : $value;
                        break;
                    }
                }
            }
        }

        // 3. Проверка обязательных полей
        if (empty($arProperty['DEAL'])) {
            return false;
        }

        // 4. Форматирование суммы
        if (isset($arProperty['AMOUNT'])) {
            $arProperty['AMOUNT'] = number_format((float)$arProperty['AMOUNT'], 2, '.', '');
        }

        // 5. Получение данных сделки
        $deal = DealTable::getList([
            'filter' => ['=ID' => $arProperty['DEAL']],
            'select' => ['ID', 'OPPORTUNITY', 'ASSIGNED_BY_ID']
        ])->fetch();

        if (!$deal) {
            return false;
        }

        // 6. Подготовка обновления
        $updateData = [];
        if (isset($arProperty['AMOUNT']) && $arProperty['AMOUNT'] != $deal['OPPORTUNITY']) {
            $updateData['OPPORTUNITY'] = $arProperty['AMOUNT'];
        }
        if (isset($arProperty['RESPONSIBLE']) && $arProperty['RESPONSIBLE'] != $deal['ASSIGNED_BY_ID']) {
            $updateData['ASSIGNED_BY_ID'] = $arProperty['RESPONSIBLE'];
        }

        if (empty($updateData)) {
            return true;
        }

        // 7. Выполнение обновления
        try {
            $result = DealTable::update($arProperty['DEAL'], $updateData);
            return $result->isSuccess();
        } catch (\Exception $e) {
            return false;
        }
    }
}