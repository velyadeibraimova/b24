<?php

namespace Otus\Agents\Crm;

use Bitrix\Main\Type\DateTime;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;

class Deal
{
    /**
     * Непериодический агент для архивации старых сделок
     * Вызывается вручную или по событию
     *
     * @param int $days - Сделки старше этого количества дней архивируются
     * @return string - Пустая строка (непериодический агент)
     */
    public static function archiveOldDeals(int $days = 365): string
    {
        Loader::includeModule('crm');
        $date = new DateTime();
        $date->add('-'.$days.' days');

        $filter = [
            '<CLOSEDATE' => $date,
            '!STAGE_ID' => 'WON',
        ];

        $res = DealTable::getList([
            'select' => ['ID'],
            'filter' => $filter,
            'limit' => 100, // Ограничиваем количество за один вызов
        ]);

        $count = 0;
        while ($row = $res->fetch()) {
            DealTable::update($row['ID'], [
                'STAGE_ID' => 'LOSE',
                'COMMENTS' => 'Срок работы со сделкой истёк',
            ]);
            $count++;
        }

        if ($count > 0) {
            \CEventLog::Add([
                'SEVERITY' => 'INFO',
                'AUDIT_TYPE_ID' => 'DEAL_ARCHIVE',
                'MODULE_ID' => 'crm',
                'DESCRIPTION' => 'Заархивировано сделок: '. $count,
            ]);
        }

        return sprintf('\Otus\Agents\Crm\Deal::archiveOldDeals(%s)', $days);
    }
}
