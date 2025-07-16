<?php

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Sysp\CrmCustomTab\ORM\PriceListTable;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

class DoctorsComponent extends CBitrixComponent
{
    private $id;

    public function executeComponent()
    {

        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/ttеt.txt", print_r($this->arResult, true), FILE_APPEND);

        $this->_checkModules();
        $this->id = 'PRICE_LIST';

        try {
            $filter = $this->prepareFilter();
            $this->prepartFilterFields();
            $this->prepareGrid($filter);
            $this->IncludeComponentTemplate();
        } catch (Exception $e) {
            ShowError('System error: ' . $e->getMessage());
        }
    }

    private function _checkModules()
    {
        if(!Loader::includeModule('sysp.crmcustomtab')) {
            throw new \Exception("The modules necessary for the component's operation are not loaded");
        }

        return true;
    }

    private function prepareFilter()
    {
        $filterOption = new FilterOptions($this->id);
        $filterData = $filterOption->getFilter([]);
        $filter = [
            '=ENTITY_ID' => $this->arParams['ENTITY_ID']
        ];

        if (!empty($filterData['PROCEDURE_NAME'])) {
            $filter['%PROCEDURE.ELEMENT.NAME'] = $filterData['PROCEDURE_NAME'];
        }
        if (!empty($filterData['COST_from'])) {
            $filter['>COST'] = $filterData['COST_from'];
        }
        if (!empty($filterData['COST_to'])) {
            $filter['<COST'] = $filterData['COST_to'];
        }

        return $filter;
    }

    private function prepartFilterFields()
    {
        $this->arResult['FILTER']['FIELDS'] = [
            [
                'id' => 'PROCEDURE_NAME',
                'name' => Loc::getMessage("PROCEDURE_NAME"),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'COST',
                'name' => Loc::getMessage("COST"),
                'type' => 'number',
                'default' => true,
            ]
        ];
    }

    private function prepareGrid($filter = [])
    {
            $this->arResult['GRID']['GRID_ID'] = $this->id;
            $this->prepareGridColumns();
            $this->prepareGridRows($filter);
    }

    private function prepareGridColumns()
    {
        $this->arResult['GRID']['GRID_COLUMNS'] = [
            ['id' => 'ID', 'name' => Loc::getMessage('ID'), 'sort' => 'ID', 'default' => true],
            [
                'id' => 'PROCEDURE_NAME',
                'name' => Loc::getMessage('PROCEDURE_NAME'),
                'sort' => 'PROCEDURE_NAME',
                'default' => true,
            ],
            ['id' => 'DOCTOR', 'name' => Loc::getMessage('DOCTOR'), 'sort' => 'DOCTOR_LASTNAME', 'default' => true],
            ['id' => 'COST', 'name' => Loc::getMessage('COST'), 'sort' => 'COST', 'default' => true],
            [
                'id' => 'RECOMMENDATIONS',
                'name' => Loc::getMessage('RECOMMENDATIONS'),
                'sort' => 'RECOMMENDATIONS',
                'default' => true,
            ],
        ];
    }

    private function prepareGridRows($filter = [])
    {
        //options
        $gridOptions = new GridOptions($this->arResult['GRID']['GRID_ID']);
        $navParams = $gridOptions->getNavParams();
        $nav = new PageNavigation($this->arResult['GRID']['GRID_ID']);
        $nav->allowAllRecords(false)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();
        $sort = $gridOptions->getSorting([
            'sort' => ['ID' => 'ASC'],
            'vars' => ['by' => 'by', 'order' => 'order'],
        ]);

        //rows
        $this->arResult['GRID']['PRICE_LIST'] = [];
        $query = new Query(PriceListTable::getEntity());
        $query->setSelect([
            '*',
            'DOCTOR_' => 'DOCTOR',
            'PROCEDURE_' => 'PROCEDURE',
            'PROCEDURE_NAME' => 'PROCEDURE.ELEMENT.NAME',
        ])
            ->setFilter($filter)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset())
            ->setOrder($sort['sort']);
        $res = $query->exec();
        $result = $res->fetchAll();

        foreach ($result as $row) {
            $this->arResult['GRID']['PRICE_LIST'][] = [
                'data' => [
                    'ID' => $row['ID'],
                    'PROCEDURE_NAME' => $row['PROCEDURE_NAME'],
                    'DOCTOR' => implode(' ', [
                        $row['DOCTOR_LASTNAME'],
                        $row['DOCTOR_FIRSTNAME'],
                        $row['DOCTOR_PATRONYMIC'],
                    ]),
                    'COST' => $row['COST'],
                    'RECOMMENDATIONS' => $row['RECOMMENDATIONS'],
                ],
            ];
        }

        //total count
        $query = new Query(PriceListTable::getEntity());
        $query->setFilter($filter);
        $query->countTotal(true);
        $this->arResult['GRID']['TOTAL_COUNT'] = $query->exec()->getCount();
        $nav->setRecordCount($this->arResult['GRID']['TOTAL_COUNT']);
        $this->arResult['GRID']['NAV'] = $nav;
    }

}