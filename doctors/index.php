<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Доктора");
$APPLICATION->SetAdditionalCSS('/doctors/style.css');

use Bitrix\Main\Localization\Loc;
use Models\Lists\DoctorsProp4PropertyValuesTable as ProcsTable;
use Models\Lists\DoctorsTable;

// Обработка параметров URL
$path = trim($_GET['path'] ?? '', '/');
$pathParts = !empty($path) ? explode('/', $path) : [];
$action = '';
$doctor = [];
$errors = [];

// Определение действия
if (!empty($path)) {
    $path_parts = explode('/', $path);
    if (sizeof($path_parts) < 3) {
        if (sizeof($path_parts) == 2 && $path_parts[0] == 'edit') {
            $action = 'edit';
            $doctor_name = $path_parts[1];
        } else if (sizeof($path_parts) == 1 && in_array($path_parts[0], ['new', 'newproc'])) {
            $action = $path_parts[0];
        } else {
            $doctor_name = $path_parts[0];
            $action = 'doctor';
        }
    }
}

// Получение данных
try {
    // Обработка действий
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'newproc':
                $result = ProcsTable::add($_POST);

                if ($result) {
                    LocalRedirect('/doctors');
                } else {
                    $errors = 'Ошибка при добавлении процедуры';
                }

                break;
            case 'new':
                if (DoctorsTable::add($_POST)) {
                    header("Location: /doctors");
                    exit();
                }
            case 'edit':
                $ID = $_POST['IBLOCK_ELEMENT_ID'];
                $procs = $_POST['PROP4'];
                unset($_POST['PROP4']);
                CIBLockELement::SetPropertyValues($ID, DoctorsTable::IBLOCK_ID, $procs, "PROP4");
                if (DoctorsTable:: update($ID, $_POST)) {
                    header("Location: /doctors");
                    exit();
                } else {
                    $errors = 'Ошибка при обновлении данных врача';
                }
                break;
        }
    }

    // Получение данных для отображения
    if (!empty($doctor_name)) {
        $doctor = DoctorsTable::query()
            ->setSelect([
                'NAME' => 'ELEMENT.NAME',
                '*',
                'PROP4',
                'ID' => 'ELEMENT.ID'
            ])
            ->where("NAME", $doctor_name)
            ->fetch();
        if (is_array($doctor)) {

            if ($doctor['PROP4']) {
                $procs = ProcsTable::query()
                    ->setSelect(['NAME' => 'ELEMENT.NAME'])
                    ->where("ELEMENT.ID", "in", $doctor['PROP4'])
                    ->fetchAll();
            }
        } else {
            header("Location: /doctors");
            exit();
        }
    }

    // Получение списков
    $doctors = DoctorsTable::query()
        ->setSelect(['*', "NAME" => "ELEMENT.NAME"])
        ->fetchAll();

    $procedures = ProcsTable::query()
        ->setSelect(['*', "NAME" => "ELEMENT.NAME"])
        ->fetchAll();

} catch
(Exception $e) {
    $errors[] = $e->getMessage();
}
echo Loc::getMessage('ADD');
?>

    <section class="doctors">
        <h1><?= Loc::getMessage('MESS1'); ?></h1>

        <!-- Блок ошибок -->
        <? if (!empty($errors)): ?>
            <div class="errors">
                <? foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <? endforeach ?>
            </div>
        <? endif ?>

        <? if (empty($action)): ?>
            <div class="add-buttons">
                <a href="/doctors/new" class="button"><?= Loc::getMessage('MESS2'); ?></a>
                <a href="/doctors/newproc" class="button"><?= Loc::getMessage('MESS3'); ?></a>
            </div>

            <div class="cards-list">
                <? foreach ($doctors as $doc):
                    $name = '';
                    $name = $doc['PROP1'] . " " . $doc['PROP2'] . ' ' . $doc['PROP3']; ?>
                    <div class="card">
                        <h2><?= htmlspecialchars($name) ?></h2>
                        <a href="/doctors/<?= $doc['NAME'] ?>" class="card-link"></a>
                    </div>
                <? endforeach ?>
            </div>

        <? elseif ($action === 'newproc'): ?>
            <form class="doctor-add-form" method="POST">
                <h2><?= Loc::getMessage('MESS3'); ?></h2>
                <input type="text" name="NAME" placeholder="<?= Loc::getMessage('MESS5'); ?>" required>
                <input type="submit" name="submit" value="<?= Loc::getMessage('MESS6'); ?>">
                <a href="/doctors" class="cancel"><?= Loc::getMessage('MESS7'); ?></a>
            </form>
        <? elseif ($action === 'doctor'): ?>
            <div class="add-buttons">
                <a href="/doctors/edit/<?= $doctor['NAME'] ?>" class="button"><?= Loc::getMessage('MESS4'); ?></a>
            </div>
            <div class="doctor-card">
                <div class="doctor-name">
                    <?= $doctor['PROP1'] . " " . $doctor['PROP2'] . ' ' . $doctor['PROP3']; ?>
                </div>
                <ul class="doctor-procedures">
                    <? foreach ($procs as $proc) { ?>
                        <li><?= $proc['NAME']; ?></li>
                    <? } ?>
                </ul>
            </div>
        <? elseif ($action === 'edit' || $action === 'new'): ?>
            <form class="doctor-add-form" method="POST">
                <h2><?= $action === 'edit' ? Loc::getMessage('MESS4') : Loc::getMessage('MESS8') ?></h2>

                <input type="text" name="NAME"
                       value="<?= htmlspecialchars($doctor['NAME'] ?? '') ?>"
                       placeholder="<?= Loc::getMessage('MESS9'); ?>" required>

                <input type="text" name="PROP1"
                       value="<?= htmlspecialchars($doctor['PROP1'] ?? '') ?>"
                       placeholder="<?= Loc::getMessage('MESS10'); ?>" required>

                <input type="text" name="PROP2"
                       value="<?= htmlspecialchars($doctor['PROP2'] ?? '') ?>"
                       placeholder="<?= Loc::getMessage('MESS11'); ?>" required>

                <input type="text" name="PROP3"
                       value="<?= htmlspecialchars($doctor['PROP3'] ?? '') ?>"
                       placeholder="<?= Loc::getMessage('MESS12'); ?>">
                <select name="PROP4[]" multiple>
                    <? foreach ($procedures as $proc): ?>
                        <option value="<?= $proc['IBLOCK_ELEMENT_ID'] ?>"
                            <?= in_array($proc['IBLOCK_ELEMENT_ID'], (array)($doctor['PROP4'] ?? [])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proc['NAME']) ?>
                        </option>
                    <? endforeach ?>
                </select>
                <input hidden type="text" name="IBLOCK_ELEMENT_ID"
                       value="<?= htmlspecialchars($doctor['IBLOCK_ELEMENT_ID'] ?? '') ?>">
                <input type="submit" name="submit"
                       value="<?= $action === 'edit' ? Loc::getMessage('MESS6') : Loc::getMessage('MESS13') ?>">
                <a href="/doctors" class="cancel"><?= Loc::getMessage('MESS7'); ?></a>
            </form>
        <? endif ?>
    </section>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>