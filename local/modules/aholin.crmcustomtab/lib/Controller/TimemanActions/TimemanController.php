<?php
namespace Aholin\Crmcustomtab\Controller\TimemanActions;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;

class TimemanController extends Controller
{
    public function configureActions()
    {
        return [
            'startDate' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }

    public function startDateAction(): array
    {
        $userId = CurrentUser::get()->getId();
        // @TODO реализовать запуск рабочего дня, бработку ошибок, возврат ответа в JS
        return [];
    }
}
