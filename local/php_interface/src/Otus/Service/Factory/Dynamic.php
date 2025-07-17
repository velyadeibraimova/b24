<?php
namespace Otus\Service\Factory;

use Bitrix\Crm\Service\Factory\Dynamic as StandardDynamic;
use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Operation;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Result;
use Otus\Service\Events\ServiceDynamicEvents;

class Dynamic extends StandardDynamic
{
    public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
    {
        $operation = parent::getUpdateOperation($item, $context);
        $operation->addAction(Operation::ACTION_BEFORE_SAVE,
            new class extends Action {
                public function process(Item $item): Result
                {
                    $eventService = new ServiceDynamicEvents($item);
                    $eventService->onBeforeDynamicItemSave();

                    return new Result();
                }
            }
        );
        return $operation;
    }
}
