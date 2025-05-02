<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>

<div class="currency-rate">
    <h2>Информация о валюте</h2>
    <div class="currency-info">
        <p class="currency-name">Валюта: <strong><?= htmlspecialcharsbx($arResult['SELECTED_CURRENCY']) ?></strong></p>
        <p class="rate-value">Текущий курс: <strong><?= number_format($arResult['RATE'], 4) ?> руб.</strong></p>
    </div>
</div>

<style>
.currency-rate {
    padding: 20px;
    background: #f5f5f5;
    border-radius: 5px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.currency-rate h2 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 24px;
}
.currency-info {
    background: #fff;
    padding: 15px;
    border-radius: 4px;
}
.currency-name, .rate-value {
    margin: 10px 0;
    font-size: 16px;
    color: #666;
}
.currency-name strong, .rate-value strong {
    color: #333;
    font-size: 18px;
}
</style> 