<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/** @global $APPLICATION */
$APPLICATION->SetTitle('Врачи');
$APPLICATION->SetAdditionalCSS('/doctors/style.css');

// модели работающие с инфоблоками
use Models\Lists\DoctorsPropertyValuesTable as DoctorsTable;
use Models\Lists\ProcsPropertyValuesTable as ProcsTable;


// массивы для сохранения полученных данных
$doctors = [];
$doctor = [];
$procs = [];

$path = trim($_GET['path'],'/');
$action = '';
$doctor_name = '';



if (!empty($path)) {
    $path_parts = explode('/',$path);
    if (sizeof($path_parts)<3) {
        if (sizeof($path_parts) == 2 && $path_parts[0] == 'edit') {
            $action = 'edit';
            $doctor_name = $path_parts[1];
        } else if (sizeof($path_parts) == 1 && in_array($path_parts[0],['new','newproc'])) {
            $action = $path_parts[0];
        } else $doctor_name = $path_parts[0];
    }
}

if (!empty($doctor_name)) {
        $doctor = DoctorsTable::query()
            ->setSelect([
                '*', 
                'NAME' => 'ELEMENT.NAME', 
                'PROC_IDS_MULTI',
                'ID' => 'ELEMENT.ID'
            ])
            ->where("NAME", $doctor_name)
            ->fetch();

        if (is_array($doctor)) { //выводим одного доктора

            if($doctor['PROC_IDS_MULTI']){
                $procs = ProcsTable::query()
                    ->setSelect(['NAME' => 'ELEMENT.NAME'])
                    ->where("ELEMENT.ID", "in", $doctor['PROC_IDS_MULTI'])
                    ->fetchAll();
            }
    
        }
        else {
            header("Location: /doctors");
            exit();
        }
}

// если не выбран доктор и его
// выводим всех докторов 
if (empty($doctor_name) && empty($action)) { 
    $doctors = DoctorsTable::query()
        ->setSelect(['*', "NAME" => "ELEMENT.NAME", "ID" => "ELEMENT.ID"])
        ->fetchAll();
}

if ($action == 'newproc') { // добавляем процедуру
    if (isset($_POST['proc-submit'])) {
        unset($_POST['proc-submit']);
        if (ProcsTable::add($_POST)) {
            header("Location: /doctors");
            exit();
        } else echo "Произошла ошибка";
    }
}

if ($action == 'new' || $action == 'edit') { // добавляем доктора
    if (isset($_POST['doctor-submit'])) {
        unset($_POST['doctor-submit']);
        if ($action == 'edit' && !empty($_POST['ID'])) {
            $ID = $_POST['ID'];
            unset($_POST['ID']);
            $_POST['IBLOCK_ELEMENT_ID']=$ID;

            $procs = $_POST['PROC_IDS_MULTI'];
            unset($_POST['PROC_IDS_MULTI']);
            CIBlockElement::SetPropertyValues($ID, DoctorsTable::IBLOCK_ID, $procs, "PROC_IDS_MULTI");

            if (DoctorsTable::update($_POST['ID'], $_POST)) {
                header("Location: /doctors");
                exit();
            } else echo "Произошла ошибка";
        }
        if ($action=='new' && DoctorsTable::add($_POST)) {
            header("Location: /doctors");
            exit();
        } else echo "Произошла ошибка";
    }

    $proc_options = ProcsTable::query()->setSelect(["ID"=>"ELEMENT.ID","NAME"=>"ELEMENT.NAME"])->fetchAll();
    if (!empty($doctor_name)) {
        $data = $doctor;
    }
}
?>
<section class="doctors">
    <h1><a href="/doctors">Врачи</a></h1>

    <?php if (empty($action)):?>
    <div class="add-buttons">
        <?php if (empty($doctor_name)):?>
        <a href="/doctors/new"><button>Добавить врача</button></a>
        <a href="/doctors/newproc"><button>Добавить процедуру</button></a>
        <?php else:?>
            <a href="/doctors/edit/<?=$doctor_name?>"><button>Изменить данные врача</button></a>
        <?php endif;?>
    </div>
    <?php endif;?>

    <div class="cards-list">
    <?php foreach ($doctors as $doc) { ?>
        <a class="card" href="/doctors/<?=$doc["NAME"]?>">
            <div class="fio">
                <?=$doc['LAST_NAME']?>
                <?=$doc['FIRST_NAME']?>
                <?=$doc['MIDDLE_NAME']?>
            </div>
        </a>
    <?php } ?>
    </div>

    <?php if (is_array($doctor) && sizeof($doctor)>0 && $action!='edit'):?>
    <div class="doctor-page">
        <h2><?=$doctor['LAST_NAME']." ".$doctor['FIRST_NAME']." ".$doctor['MIDDLE_NAME']?></h2>
        <h3>Процедуры:</h3>
        <ul>
            <?php foreach ($procs as $proc):?>
                <li><?=$proc['NAME']?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($action=='new' || $action=='edit'):?>
    <form method="POST">
        <h2 style="text-align:center;">Данные врача</h2>
        <div class="doctor-add-form">

            <?php if (isset($data['ID'])):?>
                <input type="hidden" name="ID" value="<?=$data['ID']?>" />
            <?php endif;?>

            <input type="text" name="NAME" placeholder="Название страницы врача (фамилия латиницей)" value="<?=$data['NAME']??''?>"/>
            <input type="text" name="LAST_NAME" placeholder="Фамилия врача" value="<?=$data['LAST_NAME']??''?>"/>
            <input type="text" name="FIRST_NAME" placeholder="Имя врача" value="<?=$data['FIRST_NAME']??''?>"/>
            <input type="text" name="MIDDLE_NAME" placeholder="Отчество врача" value="<?=$data['MIDDLE_NAME']??''?>"/>

            <select multiple name="PROC_IDS_MULTI[]">
                <option value="" selected disabled>Процедуры</option>
                <?php foreach ($proc_options as $proc):?>
                    <option value="<?=$proc['ID']?>"
                            <?php if (isset($data['PROC_IDS_MULTI']) && in_array($proc['ID'],$data['PROC_IDS_MULTI'])):?>selected<?php endif;?>>
                        <?=$proc['NAME']?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="doctor-submit" value="Сохранить"/>
    </div>
    </form>
    <?php endif; ?>

    <?php if ($action=='newproc'):?>
        <form method="POST">
            <h2 style="text-align:center;">Добавить процедуру</h2>
            <div class="doctor-add-form">
                <input type="text" name="NAME" placeholder="Название процедуры"/>
                <input type="submit" name="proc-submit" value="Сохранить"/>
            </div>
        </form>
    <?php endif; ?>

</section>