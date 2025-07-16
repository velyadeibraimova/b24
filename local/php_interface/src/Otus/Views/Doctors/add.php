<?php
use \Bitrix\Main\Localization\Loc;
/** @var array $doctor */
/** @var array $procedures */
?>

<h1 style="text-align:center;"><a href="/doctors/"><?= Loc::getMessage('DOCTORS') ?></a></h1>
<br/>
<br/>
<form method="post" class="doctor-add-form" action="/doctors/">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="entity" value="doctor">
    <input type="text" name="LASTNAME" value="<?= $doctor['LASTNAME'] ?>" placeholder="<?= Loc::getMessage('LASTNAME') ?>">
    <input type="text" name="FIRSTNAME" value="<?= $doctor['FIRSTNAME'] ?>" placeholder="<?= Loc::getMessage('FIRSTNAME') ?>">
    <input type="text" name="PATRONYMIC" value="<?= $doctor['PATRONYMIC'] ?>" placeholder="<?= Loc::getMessage('PATRONYMIC') ?>">
    <select name="PROCS[]" multiple size="10">
        <?php foreach ($procedures as $procedure): ?>
            <option value="<?= $procedure['ID'] ?>"><?= $procedure['NAME'] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="<?= Loc::getMessage('FORM_SUBMIT') ?>">
</form>