<?php

namespace Otus\EventHandlers;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\RestException;
use Bitrix\Main\Context;
use Otus\Models\Doctors\Model;

class RestDoctors extends \IRestService
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
            $request = Context::getCurrent()->getRequest();
            $data = $request->getQueryList()->toArray();
            return $model->add($data);

        } catch (\Exception $e) {
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
            $getListParams = array_merge($arParams, self::getNavData($navStart, true));
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
            $model = new Model();
            return $model->getDoctor($arParams['slug']);

        } catch (\Exception $e) {
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
            $request = Context::getCurrent()->getRequest();
            $data = $request->getQueryList()->toArray();

            if (!isset($data['ID']) || (int)$data['ID'] <= 0) {
                throw new RestException('Unknown doctor');
            }

            $oldData = $model->getDoctorById($data['ID']);
            $data = array_merge($oldData, $data);

            return $model->update($data);
        } catch (\Exception $e) {
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
            $request = Context::getCurrent()->getRequest();
            $data = $request->getQueryList()->toArray();

            if (!isset($data['ID']) || (int)$data['ID'] <= 0) {
                throw new RestException('Unknown doctor');
            }

            return $model->delete($data['ID']);
        } catch (\Exception $e) {
            throw new RestException($e->getMessage());
        }
    }
}