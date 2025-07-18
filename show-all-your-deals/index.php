<?php
require_once (__DIR__.'/crest_ext.php');
CRest_Ext::setCurrentBitrix24($_REQUEST['member_id']);
$result = CRest_Ext::call('crm.lead.list');
?>
<ul>
<?php
foreach ($result['result'] as $lead) { ?>
    <li><?=$lead['TITLE']?></li>
<?php
}
?>
</ul>
