<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Вывод связанных полей');

use Models\Lists\CarsPropertyValuesTable as CarsTable;

// вывод данных по списку записей из инфоблока Автомобили
/*$cars = CarsTable::getList([       
		'select'=>[
          'ID'=>'IBLOCK_ELEMENT_ID',
          'NAME'=>'ELEMENT.NAME',
 		  'MANUFACTURER_ID'=>'MANUFACTURER_ID'
      ]
  ])->fetchAll();

 pr($cars);*/

/*$cars = CarsTable::query()
    ->setSelect([
        '*',
        'NAME' => 'ELEMENT.NAME',
        'MANUFACTURER_NAME' => 'MANUFACTURER.ELEMENT.NAME',
        'CITY_NAME' => 'CITY.ELEMENT.NAME',
        'COUNTRY' => 'MANUFACTURER.COUNTRY', 
    ])
    ->setOrder(['COUNTRY' => 'desc'])
    ->registerRuntimeField(
        null,
        new \Bitrix\Main\Entity\ReferenceField(
            'MANUFACTURER',
            \Models\Lists\CarManufacturerPropertyValuesTable::getEntity(),
            ['=this.MANUFACTURER_ID' => 'ref.IBLOCK_ELEMENT_ID']
        )
    )
    ->fetchAll();

pr($cars);
*/

// добавление данных  записей в инфоблок Автомобили
/*$dbResult = CarsTable::add([
        'NAME'=>'TEST',
        'MANUFACTURER_ID'=>130,
        'CITY_ID'=>126,
        'MODEL'=>'X5',
        'ENGINE_VOLUME'=>'4',
        'PRODUCTION_DATE'=>date('d.m.Y'),
]);
pr($dbResult);*/

// удаление записи из БД
// $res = \Bitrix\Iblock\Elements\ElementcarTable::delete(137);
// pr($res);


// редактирование записей в БД
/*\Bitrix\Main\Loader::IncludeModule("iblock");
// делаем запрос на тзменение поля NAME в записи с ID 138
$res = \Bitrix\Iblock\Elements\ElementcarTable::update(138, array(
    'NAME' => 'TEST 777',
));
pr($res);*/

/*$cars = \Bitrix\Iblock\Elements\ElementcarTable::query() 
    ->addSelect('NAME')
    ->addSelect('MODEL') // имя свойства 
    ->addSelect('ID')
    ->setFilter(array('=ID' => 138))
->fetchCollection(); 

foreach ($cars as $car) {
        $car->setModel('X5 TEST'); // изменение значения свойства MODEL
        $car->save(); // сохранение данных
}*/