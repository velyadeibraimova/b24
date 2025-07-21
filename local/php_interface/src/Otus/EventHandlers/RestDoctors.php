<?php

namespace Otus\EventHandlers;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\RestException;
use Bitrix\Main\Context;
use Otus\Models\Doctors\Model;

class RestDoctors //extends \IRestService
{
    const LIST_LIMIT = 5;

    /**
     * @return array[]
     */
    public static function OnRestServiceBuildDescriptionHandler()
    {
        Loc::getMessage('REST_SCOPE_SYSP.DOCTORS');
        return [
            'sysp.doctors' => [
                'sysp.doctors.add' => [__CLASS__, 'add'],
                'sysp.doctors.list' => [__CLASS__, 'getList'],
                'sysp.doctors.get' => [__CLASS__, 'get'],
                'sysp.doctors.update' => [__CLASS__, 'update'],
                'sysp.doctors.delete' => [__CLASS__, 'delete'],
            ]
        ];
    }

    /**
     * @param $arParams
     * @param $navStart
     * @param \CRestServer $server
     * @return string|null
     * @throws RestException
     */
    public static function add($arParams, $navStart, \CRestServer $server)
    {
        try {
            $model = new Model();
            // Логируем входящие параметры
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_add.log', "INPUT: " . print_r($arParams, true) . "\n", FILE_APPEND);
            $result = $model->add($arParams);
            // Логируем результат
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_add.log', "RESULT: " . print_r($result, true) . "\n", FILE_APPEND);
            return $result;
        } catch (\Exception $e) {
            // Логируем ошибку
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_add.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new RestException($e->getMessage());
        }
    }

    /**
     * @param $arParams
     * @param $navStart
     * @param \CRestServer $server
     * @return array|null
     * @throws RestException
     */
    public static function getList($arParams, $navStart, \CRestServer $server)
    {
        try {
            $model = new Model();
            // Обработка пагинации
            $limit = isset($arParams['limit']) ? (int)$arParams['limit'] : 50;
            $offset = isset($arParams['start']) ? (int)$arParams['start'] : 0;
            $getListParams = [
                'filter' => $arParams['filter'] ?? [],
                'limit' => $limit,
                'offset' => $offset,
            ];
            return $model->getList($getListParams);
        } catch (\Exception $e) {
            throw new RestException($e->getMessage());
        }
    }

    /**
     * @param $arParams
     * @param $navStart
     * @param \CRestServer $server
     * @return array|false|null
     * @throws RestException
     */
    public static function get($arParams, $navStart, \CRestServer $server)
    {
        try {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_get.txt', "INPUT: " . print_r($arParams, true) . "\n", FILE_APPEND);
            $model = new Model();
            if (!isset($arParams['ID']) || empty($arParams['ID'])) {
                throw new RestException('Parameter slug is required');
            }
            $result = $model->getDoctorById($arParams['ID']);
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_get.txt', "RESULT: " . print_r($result, true) . "\n", FILE_APPEND);
            return $result;
        } catch (\Exception $e) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_get.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new RestException($e->getMessage());
        }
    }

    /**
     * @param $arParams
     * @param $navStart
     * @param \CRestServer $server
     * @return mixed|null
     * @throws RestException
     */
    public static function update($arParams, $navStart, \CRestServer $server)
    {
        try {
            $model = new Model();
            // Логируем входящие параметры
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_update.txt', "INPUT: " . print_r($arParams, true) . "\n", FILE_APPEND);
            if (!isset($arParams['ID']) || (int)$arParams['ID'] <= 0) {
                throw new RestException('Unknown doctor');
            }
            $oldData = $model->getDoctorById($arParams['ID']);
            if (!$oldData) {
                throw new RestException('Doctor not found');
            }
            $data = array_merge($oldData, $arParams);
            $data['SLUG'] = $data['SLUG'] ?? $oldData['NAME'] ?? 'test-name';
            $data['PROP1'] = $data['PROP1'] ?? $oldData['PROP1'] ?? '';
            $data['PROP2'] = $data['PROP2'] ?? $oldData['PROP2'] ?? '';
            $data['PROP3'] = $data['PROP3'] ?? $oldData['PROP3'] ?? '';
            $data['PROP4'] = $data['PROP4'] ?? $oldData['PROP4'] ?? [];
            file_put_contents($_SERVER["DOCUMENT_ROOT"]  . '/rest_doctors_update.txt', "DATA TO UPDATE: " . print_r($data, true) . "\n", FILE_APPEND);
            $result = $model->update($data);
            $result = $model->update($data);
            // Логируем результат
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_update.txt', "RESULT: " . print_r($result, true) . "\n", FILE_APPEND);
            return $result;
        } catch (\Exception $e) {
            // Логируем ошибку
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_update.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new RestException($e->getMessage());
        }
    }


    /**
     * @param $arParams
     * @param $navStart
     * @param \CRestServer $server
     * @return mixed
     * @throws RestException
     */
    public static function delete($arParams, $navStart, \CRestServer $server)
    {
        try {
            $model = new Model();
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/rest_doctors_delete.txt', "INPUT: " . print_r($arParams, true) . "\n", FILE_APPEND);
            if (!isset($arParams['ID']) || (int)$arParams['ID'] <= 0) {
                throw new RestException('Unknown doctor');
            }
            return $model->delete($arParams['ID']);
        } catch (\Exception $e) {
            throw new RestException($e->getMessage());
        }
    }
}