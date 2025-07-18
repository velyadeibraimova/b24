<?php
require_once(__DIR__ . '/crest_ext.php');

if ($_REQUEST['event'] == 'ONAPPINSTALL' && !empty($_REQUEST['auth'])) {
    // Установка текущего Bitrix24 по member_id
    if (!empty($_REQUEST['auth']['member_id'])) {
        CRest_Ext::setCurrentBitrix24($_REQUEST['auth']['member_id']);
    }
}

$result = CRest_Ext::installApp();

if ($result['rest_only'] === false):
    ?>
    <head>
        <script src="//api.bitrix24.com/api/v1/"></script>
        <?php if ($result['install'] == true): ?>
            <script>
                BX24.init(function () {
                    BX24.installFinish();
                });
            </script>
        <?php endif; ?>
    </head>
    <body>
    <?php if ($result['install'] == true): ?>
        installation has been finished
    <?php else: ?>
        installation error
    <?php endif; ?>
    </body>
<?php endif;
