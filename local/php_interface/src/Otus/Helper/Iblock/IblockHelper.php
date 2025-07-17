<?php
namespace Otus\Helper\Iblock;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\SystemException;

class IblockHelper
{
    /**
     * @throws SystemException
     */
    public static function getIblockIdByCode(string $iblockCode): int
    {
        $foundIblocks = IblockTable::getList([
            'filter' => [
                'CODE' => $iblockCode,
            ],
            'cache' => [
                'ttl' => 360000,
            ],
        ])->fetchAll();
        if (count($foundIblocks) > 1) {
            throw new SystemException('Найдено больше одного инфоблока');
        } elseif (count($foundIblocks) < 1) {
            throw new SystemException('Инфоблоки с кодом ' . $iblockCode . ' не найдены');
        }
        return $foundIblocks[0]['ID'];
    }
}
