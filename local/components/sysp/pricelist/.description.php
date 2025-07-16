<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("NAME"),
    "DESCRIPTION" =>  GetMessage("DESCRIPTION"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "otus",
        "CHILD" => array(
            "ID" => "table2",
            "NAME" => GetMessage("NAME"),
            "SORT" => 10,
            "CHILD" => array(
                "ID" => "views",
            ),
        ),
    ),
);