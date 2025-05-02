<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/** @global $APPLICATION */
$APPLICATION->SetTitle('Врачи');
$APPLICATION->SetAdditionalCSS('/doctors/style.css');

// модели работающие с инфоблоками
use Models\Lists\DoctorsPropertyValuesTable as DoctorsTable;
use Models\Lists\ProcsPropertyValuesTable as ProcsTable;
use Models\Lists\SpecsPropertyValuesTable as SpecsTable;


// у элемента инфоблока Доктора с $docId мы получаем связанные с ним процедуры 
$docId = 69; // идентификатор доктора из инфоблока Доктора
$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([
        'select' => ['ID', 'NAME', 'PROC_IDS_MULTI.ELEMENT'], // PROC_IDS_MULTI - множественное поле инфоблока Доктора 
        'filter' => [
            'ID' => $docId,
            'ACTIVE' => 'Y'
        ],
   ])->fetchCollection();



// затем обходим коллекцию и получаем процедуры
$procedures = []; 
foreach ($doctors as $doctor) {
    foreach($doctor->getProcIdsMulti()->getAll() as $prItem) {
        
        $procedure = \Bitrix\Iblock\Elements\ElementProceduresTable::getList([
            'select' => ['NAME'],
            'filter' => [
                'ID' => $prItem->getElement()->getId(),
                'ACTIVE' => 'Y'
            ],
        ])->fetchObject();

        $procedures[] = [
            'name'=> $procedure->getName(),                
            'id' => $prItem->getElement()->getId()
        ];
    }
}
// pr($procedures);


// query
/*$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::query()
->setSelect([
    'ID',
    'NAME',
    'PROC_IDS_MULTI.ELEMENT'
])
->setFilter(array('ID' => $docId, 'ACTIVE' => 'Y'))

->fetchCollection();

// затем обходим коллекцию и получаем процедуры
$procedures = []; 
foreach ($doctors as $doctor){
    pr($doctor->get('NAME'));

    foreach($doctor->getProcIdsMulti()->getAll() as $prItem) {
        pr($prItem->getId().' - '.$prItem->getElement()->getName());
        $procedures[] = [
            'name'=> $prItem->getElement()->getName(),                
            'id' => $prItem->getElement()->getId()
        ];
    }
}
pr($procedures);*/
  
?>

