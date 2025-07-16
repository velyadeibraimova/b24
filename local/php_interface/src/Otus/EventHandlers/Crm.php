<?php

namespace Otus\EventHandlers;

use Bitrix\Main\Diag\Debug;
use Otus\Models\Requests\RequestsTable;

class Crm
{
    public static function updateRequestsAfterDealChange($arDeal)
    {
        //получить элементы списка Заявок у которых сделка равна сделке-инициатору события
        $arRequests = RequestsTable::getList([
            'filter' => ['=DEAL' => $arDeal['ID']],
        ])->fetchAll();

        if (!$arRequests || !is_array($arRequests)) {
            return false;
        }

        //у всеъ элементов апнуть данные
        foreach ($arRequests as $arRequest) {
            try {
                $result = RequestsTable::update(
                    $arRequest['IBLOCK_ELEMENT_ID'],
                    [
                        'AMOUNT' => sprintf('%s|%s', $arDeal['OPPORTUNITY'], $arDeal['ACCOUNT_CURRENCY_ID']),
                        'RESPONSIBLE' => $arDeal['ASSIGNED_BY_ID'],
                    ]
                );
                if (!$result->isSuccess(true)) {
                    continue;
                }
            } catch (\Exception $e) {
                Debug::writeToFile($e->getMessage());
            }
        }

        return true;
    }

}