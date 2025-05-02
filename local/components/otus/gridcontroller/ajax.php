<?php

if ( ! defined( "B_PROLOG_INCLUDED" ) || B_PROLOG_INCLUDED !== true ) {
	die();
}

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Models\ClientsTable;
use Bitrix\Main\Engine\Contract\Controllerable;


/**
 *
 */
class AjaxGridcontrollerComponent extends Controller implements Controllerable

{
 	public function configureActions()
    {
        return [
            'deleteRecords' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function deleteRecordsAction($ids)
    {
        foreach ($ids as $id) {
            $result = ClientsTable::delete($id);
            if (!$result->isSuccess()) {
                return ['status' => 'error', 'message' => 'Ошибка при удалении записи с ID: ' . $id];
            }
        }

        return ['status' => 'success'];
    }

}

