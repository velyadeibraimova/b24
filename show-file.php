<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */

$APPLICATION->SetTitle("Просмотр изображений");
CJSCore::Init(['viewer']);
?>
    <div id="db-items" >
		<span class="feed-com-img-wrap" style="width:548px;">
			<img
                onload="this.parentNode.className='feed-com-img-wrap';"
                src="http://www.1c-bitrix.ru.images.1c-bitrix-cdn.ru/upload/iblock/28b/box_cms_125_143x158.jpg"
                data-bx-viewer="image"
                data-bx-title="CMS"
                data-bx-src="http://www.1c-bitrix.ru/images/gr/bus125_2/02.jpg"
                data-bx-download="http://www.1c-bitrix.ru/images/gr/bus125_2/02.jpg"
                data-bx-width="548"
                data-bx-height="346"
            />
		</span>
        <span class="feed-com-img-wrap" style="width:548px;">
			<img
                onload="this.parentNode.className='feed-com-img-wrap';"
                src="http://www.1c-bitrix.ru.images.1c-bitrix-cdn.ru/upload/iblock/d5b/box_cp_125_143x158.jpg"
                data-bx-viewer="image"
                data-bx-title="KP"
                data-bx-src="http://www.1c-bitrix.ru/images/new/cp11/meeting/podg.png"
                data-bx-download="http://www.1c-bitrix.ru/images/new/cp11/meeting/podg.png"
                data-bx-width="548"
                data-bx-height="346"
            />
		</span>
    </div>
    <script>
        BX.ready(function(){
            var obImageView = BX.viewElementBind(
                'db-items',
                {showTitle: true, lockScroll: false},
                function(node){
                    return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
                }
            );
        });
    </script>
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>
