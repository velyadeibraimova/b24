<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Выборка данных клиентов и докторов');

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Models\HospitalClientsTable as Clients;

// Подключаем модули
Loader::includeModule('iblock');

// Создаем запрос к таблице клиентов
$query = new Query(Clients::getEntity());

// Регистрируем связь с процедурой
$query->registerRuntimeField(
    'PROCEDURE',
    array(
        'data_type' => 'Bitrix\Iblock\ElementTable',
        'reference' => array('=this.PROCEDURE_ID' => 'ref.ID'),
        'join_type' => 'INNER'
    )
);

$query->setSelect([
    'ID',
    'FIRST_NAME',
    'CONTACT_ID',
    'CONTACT.NAME',
    'CONTACT.POST',
    'CONTACT.COMPANY_ID',
    'DOCTOR.PROP2',
    'DOCTOR.PROP1',
    'DOCTOR.PROP3',
    'PROCEDURE_ELEMENT_NAME' => 'PROCEDURE.NAME',
    'PROCEDURE_ELEMENT_ID' => 'PROCEDURE.ID'
]);

// Добавляем сортировку
$query->setOrder(['ID' => 'DESC']);

// Выполняем запрос
$result = $query->exec();

// Выводим результаты
echo '<div class="test-results">';
echo '<h2>Список клиентов и их докторов:</h2>';
echo '<table border="1" cellpadding="5">';
echo '<tr>
        <th>ID</th>
        <th>Имя клиента</th>
        <th>ID контакта</th>
        <th>Имя контакта</th>
        <th>Должность</th>
        <th>ID компании</th>
        <th>Фамилия врача</th>
        <th>Имя врача</th>
        <th>Отчество врача</th>
        <th>Процедура</th>
      </tr>';

while ($row = $result->fetch()) {
	//pr($row);
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . $row['first_name'] . '</td>';
    echo '<td>' . $row['contact_id'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_CONTACT_NAME'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_CONTACT_POST'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_CONTACT_COMPANY_ID'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_DOCTOR_PROP1_VALUE'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_DOCTOR_PROP2_VALUE'] . '</td>';
    echo '<td>' . $row['MODELS_HOSPITAL_CLIENTS_DOCTOR_PROP3_VALUE'] . '</td>';
    echo '<td>' . $row['PROCEDURE_ELEMENT_NAME'] . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</div>';

// Добавляем стили для красивого отображения
echo '<style>
    .test-results {
        margin: 20px;
        font-family: Arial, sans-serif;
    }
    .test-results table {
        border-collapse: collapse;
        width: 100%;
    }
    .test-results th {
        background-color: #f5f5f5;
        font-weight: bold;
    }
    .test-results td, .test-results th {
        padding: 10px;
        border: 1px solid #ddd;
    }
    .test-results tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .test-results tr:hover {
        background-color: #f0f0f0;
    }
</style>';

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?> 