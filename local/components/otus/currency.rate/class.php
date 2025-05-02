<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;

class CurrencyRateComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['CURRENCY'] = isset($arParams['CURRENCY']) ? $arParams['CURRENCY'] : 'USD';
        return $arParams;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('currency')) {
            ShowError('Модуль валют не установлен');
            return;
        }

        // Получаем список доступных валют
        $currencies = CurrencyTable::getList([
            'select' => ['CURRENCY', 'RATE', 'NAME'],
            'filter' => ['BASE' => 'N']
        ])->fetchAll();
        
        $this->arResult['CURRENCIES'] = $currencies;

        // Получаем курс выбранной валюты
        $selectedCurrency = $this->arParams['CURRENCY'];
        $this->arResult['SELECTED_CURRENCY'] = $selectedCurrency;
        
        $currencyRate = CurrencyTable::getList([
            'select' => ['RATE'],
            'filter' => ['CURRENCY' => $selectedCurrency]
        ])->fetch();
        
        $this->arResult['RATE'] = $currencyRate['RATE'];

        $this->includeComponentTemplate();
    }
} 