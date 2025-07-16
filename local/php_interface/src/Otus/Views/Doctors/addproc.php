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
    <input type="hidden" name="entity" value="proc">
    <input type="text" name="NAME" value="<?= $doctor['NAME'] ?>" placeholder="<?= Loc::getMessage('NAME') ?>">
    <input type="submit" value="<?= Loc::getMessage('FORM_SUBMIT') ?>">
</form>
