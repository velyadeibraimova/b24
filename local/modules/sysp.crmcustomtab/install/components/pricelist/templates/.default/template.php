<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
    'FILTER_ID' => $arResult['GRID']['GRID_ID'],
    'GRID_ID' => $arResult['GRID']['GRID_ID'],
    'FILTER' => $arResult['FILTER']['FIELDS'],
    'ENABLE_FIELDS_SEARCH' => false,
    'ENABLE_LIVE_SEARCH' => false,
    'ENABLE_LABEL' => true,
]);

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['GRID']['GRID_ID'],
        'COLUMNS' => $arResult['GRID']['GRID_COLUMNS'],
        'ROWS' => $arResult['GRID']['PRICE_LIST'],
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'ALLOW_COLUMNS_SORT' => false,
        'ALLOW_COLUMNS_RESIZE' => true,
        'ALLOW_HORIZONTAL_SCROLL' => true,
        'ALLOW_SORT' => true,
        'ALLOW_PIN_HEADER' => false,
        'SHOW_ROW_CHECKBOXES' => false,
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU' => false,
        'SHOW_GRID_SETTINGS_MENU' => true,
        'SHOW_NAVIGATION_PANEL' => true,
        'SHOW_PAGINATION' => true,
        'SHOW_SELECTED_COUNTER' => false,
        'SHOW_TOTAL_COUNTER' => true,
        'SHOW_PAGESIZE' => true,
        'SHOW_ACTION_PANEL' => true,
        'NAV_OBJECT' => $arResult['GRID']['NAV'],
        'TOTAL_ROWS_COUNT' => $arResult['GRID']['TOTAL_COUNT'],
        'DEFAULT_PAGE_SIZE' => 20,
        'PAGE_SIZES' => [
            ['NAME' => 1, 'VALUE' => 1],
            ['NAME' => 5, 'VALUE' => 5],
            ['NAME' => 10, 'VALUE' => 10],
            ['NAME' => 20, 'VALUE' => 20],
            ['NAME' => 50, 'VALUE' => 50],
        ],
    ],
    $component
);
?>

<?php if (!empty($arParams['AJAX_LOADER'])): ?>
    <script>
        BX.addCustomEvent('Grid::beforeRequest', function (gridData, argse) {
            if (argse.gridId !== '<?=$arResult['GRID']['GRID_ID']?>') {
                return;
            }

            if (argse.url === '') {
                argse.url = "<?=$component->getPath()?>/lazyload.ajax.php?site=<?=\SITE_ID?>&internal=true&grid_id=<?=$arResult['GRID']['GRID_ID']?>&grid_action=filter&"
            }

            argse.method = 'POST'
            argse.data = <?= Json::encode($arParams['AJAX_LOADER']['data']) ?>;
        });
    </script>
<?php endif; ?>

<div class="new-component-container"></div>