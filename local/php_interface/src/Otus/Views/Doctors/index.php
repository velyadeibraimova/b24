<?php
use \Bitrix\Main\Localization\Loc;
/** @var array $doctors */
?>

<div class="add-buttons">
    <button onclick="window.location.href='./add/'"><?= Loc::getMessage('BUTTON_ADD_DOCTOR') ?></button>
    <button onclick="window.location.href='./newproc/'"><?= Loc::getMessage('BUTTON_ADD_PROCEDURE') ?></button>
</div>

<section class="doctors">
    <?php if (!empty($doctors)): ?>
        <div class="cards-list">
    <?php endif; ?>

    <?php foreach ($doctors as $doctor): ?>
        <div class="card">
            <a href="./<?= $doctor['SLUG'] ?>">
                <?= $doctor['LASTNAME'] ?> <?= $doctor['FIRSTNAME'] ?> <?= $doctor['PATRONYMIC'] ?>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($doctors)): ?>
        </div>
    <?php endif; ?>
</section>
