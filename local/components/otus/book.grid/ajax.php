<?php
#components/bitrix/example/ajax.php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Aholin\Crmcustomtab\Orm\BookTable;

use Bitrix\Main\Loader;

Loader::includeModule('aholin.crmcustomtab');

class ExampleAjaxController extends \Bitrix\Main\Engine\Controller
{
    public function configureActions(): array
    {
        return [
            'deleteElement' => [
                'prefilters' => [],
            ],
            'createTestElement' => [
                'prefilters' => [],
            ],
        ];
    }

    public function deleteElementAction(int $id): array
    {
        $result = [
            'success' => false,
            'errors' => [],
        ];

        try {
            $deleteResult = BookTable::delete($id);

            if ($deleteResult->isSuccess()) {
                $result['success'] = true;
            } else {
                $result['errors'] = $deleteResult->getErrorMessages();
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    public function createTestElementAction(): array
    {
        $result = [
            'success' => false,
            'errors' => [],
            'id' => null,
        ];

        try {
            $addResult = BookTable::add([
                'TITLE' => 'Тестовая книга ' . rand(1000, 9999),
                'YEAR' => rand(1900, date('Y')),
                'PAGES' => rand(50, 500),
                'PUBLISH_DATE' => new \Bitrix\Main\Type\DateTime(),
            ]);

            if ($addResult->isSuccess()) {
                $result['success'] = true;
                $result['id'] = $addResult->getId();
            } else {
                $result['errors'] = $addResult->getErrorMessages();
            }
        } catch (Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

}