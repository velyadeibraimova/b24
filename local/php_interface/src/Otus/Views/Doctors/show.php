<?php
use \Bitrix\Main\Localization\Loc;
/** @var array $doctor */
?>

<h1 style="text-align:center;"><a href="/doctors/"><?= Loc::getMessage('DOCTORS') ?></a></h1>
<br/>
<div class="add-buttons">
    <button onclick="window.location.href='/doctors/edit/<?= $doctor['SLUG'] ?>/'"><?= Loc::getMessage('BUTTON_EDIT_DOCTOR') ?></button>
</div>
<br/>
<br/>
<h2><?= $doctor['LASTNAME'] ?> <?= $doctor['FIRSTNAME'] ?> <?= $doctor['PATRONYMIC'] ?></h2>
<br/>

<?php if (count($doctor['PROCS']) > 0): ?>
    <h3><?= Loc::getMessage('PROCEDURES') ?></h3>
    <ul>
        <?php foreach ($doctor['PROCS'] as $procedure): ?>
            <li><?= $procedure ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (isset($doctor['BOOKING']) && $doctor['BOOKING'] <> ''): ?>
    <hr/>
    <h3><?= Loc::getMessage('BOOKING') ?></h3>
    <?= $doctor['BOOKING'] ?>
<?php endif; ?>