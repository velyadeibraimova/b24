<?php
require_once (__DIR__.'/crest.php');
final class CRest_Ext extends CRest
{
    private static string $currentBitrix24MemberId = '';

    public static function setCurrentBitrix24(string $bitrix24MemberId): void
    {
        self::$currentBitrix24MemberId = $bitrix24MemberId;
    }

    public static function getCurrentBitrix24(): string
    {
        return self::$currentBitrix24MemberId;
    }

    protected static function setSettingData($arSettings): bool
    {
        return (boolean)file_put_contents(__DIR__ . '/' . self::$currentBitrix24MemberId . '.json', static::wrapData($arSettings));
    }

    protected static function getSettingData()
    {
        $return = [];
        $filePath = __DIR__ . '/' . self::getCurrentBitrix24() . '.json';
        if(file_exists($filePath))
        {
            $return = static::expandData(file_get_contents($filePath));
            if(defined('C_REST_CLIENT_ID') && !empty(C_REST_CLIENT_ID) && empty($return['C_REST_CLIENT_ID']))
            {
                $return['C_REST_CLIENT_ID'] = C_REST_CLIENT_ID;
            }
            if(defined('C_REST_CLIENT_SECRET') && !empty(C_REST_CLIENT_SECRET) && empty($return['C_REST_CLIENT_SECRET']))
            {
                $return['C_REST_CLIENT_SECRET'] = C_REST_CLIENT_SECRET;
            }
        }
        return $return;
    }
}
