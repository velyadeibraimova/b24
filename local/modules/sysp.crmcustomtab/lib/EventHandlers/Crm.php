<?php

namespace Sysp\CrmCustomTab\EventHandlers;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Crm
{
    public static function updateTabs(Event $event): EventResult
    {
        $arEntities = [
            'LEAD' => 1,
            'DEAL' => 2,
            'CONTACT' => 3,
            'COMPANY' => 4,
        ];
        $entityTypeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');
        $tabs = $event->getParameter('tabs');
        $targetEntityType = Option::get('sysp.crmcustomtab', 'entity_type');

        if ($arEntities[$targetEntityType] == $entityTypeId) {
            $tabs[] = [
                'id' => 'pricelist_tab_' . $entityTypeId . '_' . $entityId,
                'name' => Loc::getMessage('TAB_NAME'),
                'enabled' => true,
                'loader' => [
                    'serviceUrl' => '/local/components/sysp/pricelist/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                    'componentData' => [
                        'template' => '',
                        'params' => [
                            'ENTITY_ID' => $entityId,
                        ],
                    ]
                ]
            ];
        }

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs,]);
    }
}