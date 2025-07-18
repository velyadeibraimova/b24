<?php
require_once (__DIR__.'/crest.php');

$result = CRest::installApp();

$eventBindResult = CRest::call(
    'event.bind',
    [
        'event' => 'onCrmActivityAdd',
        'handler' => 'https://cz75984.tw1.ru/rest_apps/contact_pulse/index.php'
    ]
);

if($result['rest_only'] === false):?>
	<head>
		<script src="//api.bitrix24.com/api/v1/"></script>
		<?php if($result['install'] == true):?>
			<script>
				BX24.init(function(){
					BX24.installFinish();
				});
			</script>
		<?php endif;?>
	</head>
	<body>
		<?php if($result['install'] == true):?>
			installation has been finished
		<?php else:?>
			installation error
		<?php endif;?>
	</body>
<?php endif;