<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Aholin\Crmcustomtab\Orm\BookTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;

Loader::includeModule('aholin.crmcustomtab');
class ModifiedBookGrid extends \CBitrixComponent implements Controllerable
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
            'openComponent' => [
                'prefilters' => [],
            ],
        ];
    }

    private function getElementActions(int $id): array
    {
        return [
            [
                'text' => Loc::getMessage('BOOK_GRID_ACTION_DELETE'),
                'onclick' => "BX.BookGrid.deleteElement('". $this->arResult['FILTER_ID'] ."', $id)",
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
            throw new \Exception('Empty exception');
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
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    private function getHeaders(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'sort' => 'ID',
                'default' => true,
            ],
            [
                'id' => 'TITLE',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_TITLE_LABEL'),
                'sort' => 'TITLE',
                'default' => true,
            ],
            [
                'id' => 'YEAR',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_PUBLISHING_YEAR_LABEL'),
                'sort' => 'YEAR',
                'default' => true,
            ],
            [
                'id' => 'PAGES',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_PAGES_LABEL'),
                'sort' => 'PAGES',
                'default' => true,
            ],
            [
                'id' => 'AUTHORS',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_AUTHORS_LABEL'),
                'default' => true,
            ],
            [
                'id' => 'PUBLISH_DATE',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_PUBLISHING_DATE_LABEL'),
                'sort' => 'PUBLISH_DATE',
                'default' => true,
            ],
        ];
    }

    public function executeComponent(): void
    {
        if ($this->request->get('EXPORT_MODE') == 'Y') {
            $this->setTemplateName('excel');
        }

        if ($this->request->get('EXPORT_PDF_MODE') == 'Y') {
            $this->setTemplateName('pdf');
        }

        $this->prepareGridData();
        $this->includeComponentTemplate();
    }

    private function prepareGridData(): void
    {
        $this->arResult['HEADERS'] = $this->getHeaders();
        $this->arResult['FILTER_ID'] = 'BOOK_GRID';

        $gridOptions = new GridOptions($this->arResult['FILTER_ID']);
        $navParams = $gridOptions->getNavParams();

        $nav = new PageNavigation($this->arResult['FILTER_ID']);
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        $filterOption = new FilterOptions($this->arResult['FILTER_ID']);
        $filterData = $filterOption->getFilter([]);
        $filter = $this->prepareFilter($filterData);


        $sort = $gridOptions->getSorting([
            'sort' => [
                'ID' => 'DESC',
            ],
            'vars' => [
                'by' => 'by',
                'order' => 'order',
            ],
        ]);

        $bookIdsQuery = BookTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset())
            ->setOrder($sort['sort'])
        ;

        $countQuery = BookTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
        ;
        $nav->setRecordCount($countQuery->queryCountTotal());

        $bookIds = array_column($bookIdsQuery->exec()->fetchAll(), 'ID');

        if (!empty($bookIds)) {
            $books = BookTable::getList([
                'filter' => ['ID' => $bookIds] + $filter,
                'select' => [
                    'ID',
                    'TITLE',
                    'YEAR',
                    'PAGES',
                    'PUBLISH_DATE',
                    'AUTHOR_ID' => 'AUTHORS.ID',
                    'AUTHOR_FIRST_NAME' => 'AUTHORS.FIRST_NAME',
                    'AUTHOR_LAST_NAME' => 'AUTHORS.LAST_NAME',
                    'AUTHOR_SECOND_NAME' => 'AUTHORS.SECOND_NAME',
                ],
                'order' => $sort['sort'],
            ]);

            $this->arResult['GRID_LIST'] = $this->prepareGridList($books);
        } else {
            $this->arResult['GRID_LIST'] = [];
        }

        $this->arResult['NAV'] = $nav;
        $this->arResult['UI_FILTER'] = $this->getFilterFields();
    }

    private function prepareFilter(array $filterData): array
    {
        $filter = [];

        if (!empty($filterData['FIND'])) {
            $filter['%TITLE'] = $filterData['FIND'];
        }

        if (!empty($filterData['TITLE'])) {
            $filter['%TITLE'] = $filterData['TITLE'];
        }

        if (!empty($filterData['YEAR_from'])) {
            $filter['>=YEAR'] = $filterData['YEAR_from'];
        }

        if (!empty($filterData['YEAR_to'])) {
            $filter['<=YEAR'] = $filterData['YEAR_to'];
        }

        foreach ($filterData as $field => $value) {
            if (
                str_contains($field, '_from')
            ) {
                $fieldName = substr($field, 0, strlen($field) - 5);
                $filter['>=' . $fieldName] = $value;
            } elseif (str_contains($field, '_to')) {
                $fieldName = substr($field, 0, strlen($field) - 3);
                $filter['<=' . $fieldName] = $value;
            }
        }

        return $filter;
    }

    private function prepareGridList(Result $books): array
    {
        $gridList = [];
        $groupedBooks = [];

        while ($book = $books->fetch()) {
            $bookId = $book['ID'];

            if (!isset($groupedBooks[$bookId])) {
                $groupedBooks[$bookId] = [
                    'ID' => $book['ID'],
                    'TITLE' => $book['TITLE'],
                    'YEAR' => $book['YEAR'],
                    'PAGES' => $book['PAGES'],
                    'PUBLISH_DATE' => $book['PUBLISH_DATE'],
                    'AUTHORS' => []
                ];
            }

            if ($book['AUTHOR_ID']) {
                $groupedBooks[$bookId]['AUTHORS'][] = implode(' ', array_filter([
                    $book['AUTHOR_LAST_NAME'],
                    $book['AUTHOR_FIRST_NAME'],
                    $book['AUTHOR_SECOND_NAME']
                ]));
            }
        }

        foreach ($groupedBooks as $book) {
            $element = [
                'data' => [
                    'ID' => $book['ID'],
                    'TITLE' => $book['TITLE'],
                    'YEAR' => $book['YEAR'],
                    'PAGES' => $book['PAGES'],
                    'AUTHORS' => implode(', ', $book['AUTHORS']),
                    'PUBLISH_DATE' => $book['PUBLISH_DATE']->format('d.m.Y'),
                ],
                'actions' => $this->getElementActions($book['ID']),
                'id' => $book['ID'],
                'attrs' => [
                    'is-section' => true,
                ],
            ];
            $prevElement = $gridList[array_key_last($gridList)];
            if ($prevElement['attrs']['is-section']) {
                $element['attrs'] = [
                    'is-section' => false,
                    'parent' => $prevElement['id'],
                ];
            }

            $gridList[] = $element;
        }

        return $gridList;
    }

    private function getFilterFields(): array
    {
        return [
            [
                'id' => 'TITLE',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_TITLE_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'YEAR',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_PUBLISHING_YEAR_LABEL'),
                'type' => 'number',
                'default' => true,
            ],
            [
                'id' => 'PUBLISH_DATE',
                'name' => Loc::getMessage('BOOK_GRID_BOOK_PUBLISHING_DATE_LABEL'),
                'type' => 'date',
                'default' => true,
            ],
        ];
    }

    public function openComponentAction(): \Bitrix\Main\Engine\Response\Component
    {
        return  new \Bitrix\Main\Engine\Response\Component('aholin.crmcustomtab:book.grid', '', []);
    }
}