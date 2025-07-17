<?php
namespace Otus\Service\Events;

use Bitrix\Crm\Item;
use Bitrix\Main\Type\DateTime;

class ServiceDynamicEvents
{
    public function __construct(private Item $item) {

    }
    public function onBeforeDynamicItemSave()
    {
        $dateTime = new DateTime();
        $this->item->set('UF_CRM_3_BEST_TEST_STRING', $dateTime->format('Y-m-d H:i:s'));
        $this->item->save();
    }
}
