<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
/**
 * @var CMain $APPLICATION
 */

$APPLICATION->SetTitle('Книги');
?>
<script>
    BX.SidePanel.Instance.open("otus:book.grid", {
        contentCallback: function(slider) {
            return new Promise(function(resolve, reject) {
                BX.ajax.runComponentAction(
                    'otus:book.grid',
                    'openComponent',
                    {
                        data: {},
                        mode: 'class',
                    }
                ).then(response => {
                    resolve(response.data.html);
                });
            });
        }
    });
</script>
<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
?>
