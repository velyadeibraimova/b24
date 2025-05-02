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
