<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Вызов бизнес процесс из кода');


CModule::IncludeModule("workflow"); // модуль документооборот
CModule::IncludeModule("bizproc"); // модуль бизнес процессы 

global $USER;

$dealID = 8;
$bp_id = 13;
$arErrorsTmp = array();

// CBPDocument - вспомогательный класс, содержащий статические методы-обертки для удобного использования API модуля бизнес-процессов.
$wfId = \CBPDocument::StartWorkflow( // метод запускает рабочий поток по коду его шаблона
    $bp_id, // id бизнес процесса
    array("crm", "CCrmDocumentDeal", 'DEAL_'.$dealID),
    array("TargetUser" => "user_".$USER->GetID()),
    $arErrorsTmp
);
echo "ID рабочего потока ".$wfId;


if (count($arErrorsTmp) > 0)
{
    foreach ($arErrorsTmp as $e) {
        $errorMessage .= "[" . $e["code"] . "] " . $e["message"];
    }
    echo $dealID,'Произошла ошибка при старте БП сделки'.$dealID.':'.$errorMessage,'ERROR';
}









