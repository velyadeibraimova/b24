<?php
namespace Otus\Hlblock\Handlers;

use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;

class Element
{
    public static function onBeforeAddHandler(Event $event): EventResult
    {
        $entity = $event->getEntity();
        $fields = $event->getParameter('fields');

        if (!str_contains($fields['UF_NAME'], 'Материал') && $entity->getName() === 'MaterialTypes') {
            $fields['UF_NAME'] = 'Материал ' . $fields['UF_NAME'];
        }

        $result = new EventResult();

        $result->modifyFields($fields);

        $event->getEntity()->cleanCache();

        return $result;
    }

    public static function onBeforeUpdateHandler(Event $event): EventResult
    {
        $result = new EventResult();
        $event->getEntity()->cleanCache();
        return $result;
    }

    public static function onBeforeDeleteHandler(Event $event): EventResult
    {
        $result = new EventResult();
        $event->getEntity()->cleanCache();
        return $result;
    }
}
