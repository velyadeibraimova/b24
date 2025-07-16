<?php
namespace Aholin\Crmcustomtab\Controller\BookActions;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;
use Aholin\Crmcustomtab\Orm\BookTable;

Loader::includeModule('aholin.crmcustomtab');
class BookController extends Controller
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
            'showNewGrid' => [
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
            $this->addError(new Error($e->getMessage()));
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
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    public function showNewGridAction(): Component
    {
        return new Component('bitrix:news.list', '', [
            'IBLOCK_ID' => 3,
        ]);
    }
}
