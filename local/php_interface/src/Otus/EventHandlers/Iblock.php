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
        //получить ID инфоблока Заявок по коду
        $iblock = IblockTable::getList([
            'filter' => ['CODE' => 'requests'],
            'select' => ['ID']
        ])->fetch();

        //если список не тот, прекращаем обработку
        if (!$iblock || $arItem['IBLOCK_ID'] != $iblock['ID']) {
            return false;
        }

        //получить свойства
        $arPropertyList = PropertyTable::getList([
            'filter' => ['=IBLOCK_ID' => $iblock['ID']],
            'select' => ['ID', 'CODE']
        ])->fetchAll();

        $arProperty = [];

        //пересобрать свойства, чтобы было удобнее работать
        foreach ($arPropertyList as $property) {
            $id = $property['ID'];
            $code = $property['CODE'];

            $values = $arItem['PROPERTY_VALUES'][$id] ?? null;

            if (is_array($values)) {
                $first = reset($values);
                $arProperty[$code] = $first['VALUE'] ?? null;
            } elseif (!is_null($values)) {
                $arProperty[$code] = $values;
            }
        }

        //отрезать валюту и форматировать сумму для сравнения
        [$amount, $currency] = array_pad(explode('|', $arProperty['AMOUNT']), 2, null);
        $arProperty['AMOUNT'] = number_format($amount, 2, '.', '');

        $dealId = $arProperty['DEAL'] ?? null;
        if (!$dealId) {
            return false;
        }

        //получить данные сделки
        $deal = DealTable::getList([
            'filter' => ['=ID' => $dealId],
            'select' => ['ID', 'OPPORTUNITY', 'ASSIGNED_BY_ID']
        ])->fetch();

        if (!$deal) {
            return false;
        }

        //если ничего не изменилось, прервать работу
        if (($arProperty['AMOUNT'] === $deal['OPPORTUNITY']) && ($arProperty['RESPONSIBLE'] === $deal['ASSIGNED_BY_ID'])) {
            return false;
        }

        //обновить данные
        try {
            $result = DealTable::update($dealId, [
                'OPPORTUNITY' => $arProperty['AMOUNT'],
                'ASSIGNED_BY_ID' => $arProperty['RESPONSIBLE'],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        if (!$result->isSuccess(true)) {
            return false;
        }

        return true;
    }
}