<?php

namespace Sysp\UserPropBooking\Controllers;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class BookingController extends Controller
{
    public function configureActions()
    {
        return [
            'create' => [
                'prefilters' => [],
            ],
        ];
    }

    public function createAction($name, $dateString, $doctorId, $procedureId)
    {
        $result = [
            'errors' => [],
            'id' => null
        ];

        $name = htmlspecialcharsbx($name);
        if (empty($name)) {
            throw new \Exception('Name is required');
        }

        if (empty($dateString)) {
            throw new \Exception('DateTime is required');
        }

        if (empty($doctorId)) {
            throw new \Exception('Doctor ID is required');
        } else {
            $doctorId = (int)$doctorId;
            if ($doctorId <= 0) {
                throw new \Exception('Doctor ID must be positive integer');
            }
        }

        if (empty($procedureId)) {
            throw new \Exception('Procedure ID is required');
        } else {
            $procedureId = (int)$procedureId;
            if ($procedureId <= 0) {
                throw new \Exception('Procedure ID must be positive integer');
            }
        }

        $iblockBookingId = \Bitrix\Main\Config\Option::get('sysp.userpropbooking', 'IBLOCK_BOOKING_ID');
        if (empty($iblockBookingId)) {
            throw new \Exception('Unable to get IBLOCK_BOOKING_ID');
        }

        $iblockElement = new \CIBlockElement();
        $fields = [
            'NAME' => $name,
            'IBLOCK_ID' => $iblockBookingId,
            'PROPERTY_VALUES' => [
                'VREMYA_ZAPISI' => $dateString,
                'DOCTOR' => $doctorId,
                'PROCEDURE' => $procedureId,
            ],
        ];

        $elementId = $iblockElement->Add($fields);

        if ($elementId) {
            $result['id'] = $elementId; // Возвращаем ID нового элемента
        } else {
            throw new \Exception($iblockElement->LAST_ERROR);
        }

        return $result;
    }
}