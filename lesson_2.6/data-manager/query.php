<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Query');

use Bitrix\Main\Type;
use Bitrix\Main\Entity\Query;

use Models\BookTable as Books;
use Models\PublisherTable as Publishers;
use Models\AuthorTable as Authors;
use Models\WikiprofileTable as Wikiprofiles;
use Models\HospitalClientsTable as Clients;



// обьект Query 
// запрос к кастомной таблице BookTable
// получем коллекцию книг
/*$q = new Query(Books::getEntity());
$q->setSelect(array('id', 'name', 'publish_date','text', 'ISBN'));
$q->setFilter(array('=id' => 1));
$result = $q->exec(); // выполняем запрос

// выводим результат
while ($row = $result->fetch()) {
    pr($row['name']);
}*/


// обьект Query 
// запрос к кастомной таблице HospitalClientsTable
// получение коллекции
/*$q = new Query(Clients::getEntity());
$q->setSelect(array('id', 'first_name','contact_id', 'CONTACT.*'));
$result = $q->exec(); // выполняем запрос

$collection = $result->fetchCollection();

foreach ($collection as $key => $record) {
    pr('имя: '.$record->getFirstName().' id контакта: '.$record->getContactId().' должность: '.$record->getContact()->getPost().' id компании: '.$record->getContact()->getCompanyId());
}*/


// обьект Query 
// отношение OneToMany указано на стороне класса модели ORM Books в поле PUBLISHER
// получем коллекцию книг
// выводим издателей
/*$q = new Query(Books::getEntity());
$q->setSelect(array('id','name','publish_date','WIKIPROFILE'));
$result = $q->exec(); // выполняем запрос
$collection = $result->fetchCollection();
foreach ($collection as $key => $book) {
    pr('название: '.$book->getName(). ' дата выхода: ' .$book->getPublishDate(). ' профиль в wikipedia: '.$book->getWikiprofile()->getWikiprofileRu());
}*/


/*$q = new Query(Books::getEntity());
// регистрируем новое временное поле для исходной сущности
$q->registerRuntimeField(
    // поле element как ссылка на таблицу b_iblock_element
    'WIKIPROFILE',
    array(
        // тип — сущность ElementTable
        'data_type' => 'Models\WikiprofileTable',
        // this.ID относится к таблице, относительно которой строится запрос, т.е. b_iblock.ID = b_iblock_element.IBLOCK_ID
        'reference' => array('=this.wikiprofile_id' => 'ref.id'),
        // тип соединения INNER JOIN
        'join_type' => 'INNER'
    )
);

// выбираем название инфоблока, символьный код инфоблока, название элемента, символьный код элемента и идентификатор типа инфоблока
$q->setSelect(array('id', 'name', 'publish_date', 'WIKIPROFILE.wikiprofile_ru'));

// выполняем запрос
$result = $q->exec();
while ($row = $result->fetch()) {
    pr($row);
}*/

/*
$records = Books::query()
->setSelect(
    array(
        'id', 
        'name', 
        'publish_date', 
        'publisher_id', 
        'WIKIPROFILE.wikiprofile_ru'
    )
)
->registerRuntimeField(
    // поле element как ссылка на таблицу b_iblock_element
    'WIKIPROFILE',
    array(
        // тип — сущность ElementTable
        'data_type' => 'Models\WikiprofileTable',
        // this.ID относится к таблице, относительно которой строится запрос, т.е. b_iblock.ID = b_iblock_element.IBLOCK_ID
        'reference' => array('=this.wikiprofile_id' => 'ref.id'),
        // тип соединения INNER JOIN
        'join_type' => 'INNER'
    )
)
->fetchAll();
pr($records);
*/


?>