<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Otus\Customtab\Models\OrderTable;

if (!Loader::includeModule("otus.customtab")) {
    die('Module otus.customtab is not installed!');
}

$data = [];

$titles = [
    'Заключение контракта',
    'Покупка актива',
    'Лизинговое соглашение',
    'Слияние компаний',
    'Продажа предприятия',
    'Кредитное соглашение',
    'Инвестиционное соглашение',
    'Партнёрское соглашение',
    'Сделка по аренде',
    'Обмен активами'
];

foreach ($titles as $title) {
    $data[] = [
        'TITLE' => $title,
        'COMPANY_ID' => rand(1, 7),
        'CLIENT_ID' => rand(6, 8),
    ];
}

OrderTable::addMulti($data);