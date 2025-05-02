<?php
include_once 'const.php';
include(__DIR__ . '/redirect.php');

// Регистрируем автозагрузчик для Models namespace
spl_autoload_register(function ($class) {
    $prefix = 'Models\\';
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . '/local/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Загрузка вспомогательного модуля
\Bitrix\Main\Loader::IncludeModule('alkinu_main');
include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/functions.php";
//Скрипты по интеграции с 1с
include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init_includes/1c.php";
//Добавление обработчиков на почтовые события
include_once $_SERVER['DOCUMENT_ROOT'] . "/local/php_interface/init_includes/sendEmailEvents.php";
//[CRON] Отправка писем по агентам битрикс
include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/cron/sendEmailByCron.php";
//[CRON] Анализ заказов для заполнения свойства "С этими товарами покупают"
include_once $_SERVER['DOCUMENT_ROOT'] . "/local/php_interface/cron/updatePurchasedProperty.php";
//Подключение обработчика для доставок
include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init_includes/deliveries.php";
// редиректы
include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init_includes/redirects.php";
//Удаление забаганной куки
setcookie("PHPSESSID", "", 777, "/", ".kansort.ru");