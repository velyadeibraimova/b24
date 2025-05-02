<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

    $APPLICATION->SetTitle('Datamanager в Битрикс');

    use Bitrix\Main\Type;

    use Models\Lists\CarsPropertyValuesTable as CarsTable;
    use Models\BookTable as Books;
    use Models\PublisherTable as Publishers;
    use Models\AuthorTable as Authors;
    use Models\WikiprofileTable as Wikiprofiles;
    use Models\HospitalClientsTable as Clients;


    // use Models\BookTable as Books;
    // use Models\PublisherTable as Publishers;
    // use Models\AuthorTable as Authors;
    // use Models\WikiprofileTable as Wikiprofiles;



    // кастомная таблица и стандартная сущность Битрикс
    // получение данных из hospital_clients
    // получаем контакты CRM привязанные к своей таблице
    /*$collection = Clients::getList([
        'select' => [
            'id', 
            'first_name', 
            'contact_id', 
            'CONTACT.*',
            'DOCTOR.*',
            'PROC_IDS_MULTI'=>'DOCTOR.PROC_IDS_MULTI.ELEMENT',
        ], 
        // 'limit'=>3
    ])->fetchCollection();
    // ])->fetchAll();

    foreach ($collection as $key => $record) {
        echo $record->getId().' '.$record->getFirstName().' '.$record->getContactId().' ';

        // echo $record->getContact()->getPost().' ';
        // echo $record->getContact()->getCompanyId();

        echo $record->getDoctor()->getName();
      
        foreach($record->getDoctor()->getProcIdsMulti()->getAll() as $prItem) {
            pr($prItem->getId().' - '.$prItem->getElement()->getName());
        }

        echo '<br/>';
    }*/

    // pr($collection);

    /*
    $doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([
        'select' => [
            'ID', 
            'NAME', 
            'PROC_IDS_MULTI.ELEMENT' // PROC_IDS_MULTI - множественное поле инфоблока Доктора 
        ],     
    ])
    ->fetchCollection(); 

    foreach ($doctors as $doctor) {
        pr($doctor->get('NAME'));
        foreach($doctor->getProcIdsMulti()->getAll() as $prItem) {
            pr($prItem->getId().' - '.$prItem->getElement()->getName());
        }
    }*/


    // создание записи
    /*$record = [
        'first_name'=>'123456',
        'last_name' =>'123456',
        'age' =>22,
    ];
    $res = Clients::add($record);

    if(!$res->isSuccess()){
        var_dump($res->getErrorMessages());
    }
    */

    // получем коллекцию книг
    /*$collection = Books::getList([
        'select' => [
            'id', 
            'name', 
            'publish_date'
        ]
    ])->fetchCollection();
    foreach ($collection as $key => $book) {
        pr('название '.$book->getName(). ' дата выхода:' .$book->getPublishDate()); 
    }*/

    // добавление записи в таблицу books
    /*$record = [
        'name'=>'Зеленая миля',
        'publish_date' => new Type\Date('1988-09-17', 'Y-m-d'),
        'ISBN' =>'12345678901234'
    ];
    $res = Books::add($record);
    if(!$res->isSuccess()){
       var_dump($res->getErrorMessages());
    }*/



    // отношение OneToOne 
    // выборка википрофиля со сороны книги

    /*    
    $book = Books::getByPrimary(3, [
        'select' => [
            '*', 
            'WIKIPROFILE'
        ]
    ])
    ->fetchObject();
    echo $book->getWikiprofile()->getWikiprofileRu();*/
    

    // выборка книги со сороны википрофиля    
/*    $wikiprofile = Wikiprofiles::getByPrimary(3, [
        'select' => [
            '*', 
            'BOOK'
        ]
    ])
    ->fetchObject();
    echo $wikiprofile->getWikiprofileRu();
    echo $wikiprofile->getBook()->getName();
    echo $wikiprofile->getBook()->getPublishDate()->format("Y-m-d");*/



    
// отношение OneToMany 
// выборка автора со сороны книги
/*$collection = Books::getList([
    'select' => [
        'id', 
        'name', 
        'publish_date',
        'publisher_id',
        'AUTHOR'
    ]
])->fetchCollection();
foreach ($collection as $key => $book) {
    pr( 'название '.$book->getName(). ' дата выхода:' .$book->getPublishDate(). ' автор:'.$book->getAuthor()->getName());
}*/

// отношение ManyToMany
// выборка издетельств и связанных с ними книг
/*$collection = Publishers::getList([
    'select' => [
        'id', 
        'name', 
        'BOOKS' 
    ]
])->fetchCollection();

foreach ($collection as $key => $item) {
    foreach ($item->getBooks() as $book){
        echo 'изательство '.$item->getName(). ' книга: '.$book->getName().'<br/>';
    }
}
*/

// выборка книг и связанных с ними издетельств
/*$collection = Books::getList([
    'select' => [
        'id', 
        'name', 
        'PUBLISHERS' 
    ]
])->fetchCollection();

foreach ($collection as $key => $item) {
    foreach ($item->getPublishers() as $publisher){
        echo 'книга '.$item->getName(). ' изательство: '.$publisher->getName().'<br/>';
    }
}  */ 
?>