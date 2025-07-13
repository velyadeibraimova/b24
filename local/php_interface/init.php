<?php

declare(strict_types=1);

/*
* Автозагрузка composer
* */
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

/*
* Автозагрузка
* */
if (file_exists(__DIR__ . '/autoloader.php')) {
    require_once(__DIR__ . '/autoloader.php');
}

/*
* Автозагрузка (Второй вариант)
* */

include_once __DIR__ . '/../app/autoload.php';


function pr($var, $type = false)
{
    echo '<pre style="font-size:10px; border:1px solid #000; background:#FFF; text-align:left; color:#000;">';
    if ($type)
        var_dump($var);
    else
        print_r($var);
    echo '</pre>';
}
