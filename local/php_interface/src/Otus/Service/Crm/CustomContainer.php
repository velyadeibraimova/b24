<?php
namespace Otus\Service\Crm;

use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Service\Factory;
use Otus\Service\Factory\Dynamic;

class CustomContainer extends Container
{
    public const SUPER_ENTITY_TYPE_ID = 1036;
    public function getFactory(int $entityTypeId): ?Factory
    {
        if ($entityTypeId === self::SUPER_ENTITY_TYPE_ID)
        {
            $type = $this->getTypeByEntityTypeId($entityTypeId);
            return new Dynamic($type);
        }

        return parent::getFactory($entityTypeId);
    }
}
