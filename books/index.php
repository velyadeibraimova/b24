<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

/**
 * @var CMain $APPLICATION
 */

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        'POPUP_COMPONENT_NAME' => 'otus:book.grid',
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        'POPUP_COMPONENT_PARAMS' => [],
        'USE_PADDING' => true,
        'USE_UI_TOOLBAR' => 'Y',
    ]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
