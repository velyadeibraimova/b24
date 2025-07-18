<?php
require_once (__DIR__.'/crest.php');

if (empty($_REQUEST['event'])) {
    ?>
    <div>Приложение используется как обработчик события</div>
    <?php
}
if ($_REQUEST['event'] === 'onCrmActivityAdd') {
    $activityId = $_REQUEST['data']['fields']['ID'];
    //@ TODO реализовать получение информации о деле CRM
    $result = CRest::call(
        'crm.activity.get',
        [
            'id' => $activityId,
        ],
    );
    // @TODO если дело является звонком или сообщением, то обновить поле "Дата коммуникации"

}
$result = CRest::call('crm.deal.list');
?>
<ul>
    <?php
    foreach ($result['result'] as $deal) {?>
        <li>
            <?=$deal['TITLE']?>
        </li>
    <?php }
    ?>
</ul>
