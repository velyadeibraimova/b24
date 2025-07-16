<?php

namespace Otus\Models\Doctors;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Iblock\PropertyTable;
use Sysp\UserPropBooking\UserTypes\CIblockPropertyBooking;

class Model
{
    /**
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAll(): ?array
    {
        return DoctorsTable::getList([
                'select' => [
                    'LASTNAME',
                    'FIRSTNAME',
                    'PATRONYMIC',
                    'SLUG' => 'ELEMENT.NAME',
                ],
                'filter' => [
                    'ELEMENT.ACTIVE' => 'Y',
                ],
        ])->fetchAll();
    }

    /**
     * @param array $arParams
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getList(array $arParams): ?array
    {
        $result = DoctorsTable::getList($arParams)->fetchAll();

        $total = DoctorsTable::getCount($arParams['filter'] ?? []);
        $result['total'] = $total;

        if ($arParams['offset'] + $arParams['limit'] < $total)
        {
            $result['next'] = $arParams['offset'] + $arParams['limit'];
        }

        return $result;
    }

    /**
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAllProcedures(): ?array
    {
        return ProceduresTable::getList([
            'select' => [
                'ID' => 'IBLOCK_ELEMENT_ID',
                'NAME' => 'ELEMENT.NAME',
            ],
            'filter' => [
                'ELEMENT.ACTIVE' => 'Y',
            ],
            'order' => [
                'NAME' => 'ASC',
            ]
        ])->fetchAll();
    }

    /**
     * @param string $code
     * @return array|false
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getDoctor(string $code): ?array
    {
        $arSelect = [
            'IBLOCK_ELEMENT_ID',
            'LASTNAME',
            'FIRSTNAME',
            'PATRONYMIC',
            'SLUG' => 'ELEMENT.NAME',
            'PROCS_IDS' => 'PROP4',
            'PROCS' => 'PROP4_ELEMENT_NAME',
        ];

        // если есть поле онлайн-записи и модуль установлен, то добираем поле онлайн-записи
        $isBooking = false;

        $arPropertyBooking = PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => DoctorsTable::IBLOCK_ID, 'CODE' => 'BOOKING'],
        ])->fetch();

        if ($arPropertyBooking && Loader::includeModule('sysp.userpropbooking')) {
            $isBooking = true;
            $arSelect[] = 'BOOKING';
        }

        // читаем данные
        $doctor = DoctorsTable::getList([
            'select' => $arSelect,
            'filter' => [
                'ELEMENT.ACTIVE' => 'Y',
                '=SLUG' => $code,
            ],
        ])->fetch();

        if ($isBooking) {
            $arPropertyBooking['ELEMENT_ID'] = $doctor['IBLOCK_ELEMENT_ID'];

            $doctor['BOOKING'] = CIblockPropertyBooking::GetPublicViewHTML(
                $arPropertyBooking,
                ['VALUE' => $doctor['BOOKING']],
                null
            );
        }

        return $doctor ?: null;
    }

    /**
     * @param int $id
     * @return array|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getDoctorById(int $id): ?array
    {
        $arSelect = [
            'IBLOCK_ELEMENT_ID',
            'LASTNAME',
            'FIRSTNAME',
            'PATRONYMIC',
            'SLUG' => 'ELEMENT.NAME',
            'PROCS_IDS' => 'PROP4',
            'PROCS' => 'PROP4_ELEMENT_NAME',
        ];

        // если есть поле онлайн-записи и модуль установлен, то добираем поле онлайн-записи
        $isBooking = false;

        $arPropertyBooking = PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => DoctorsTable::IBLOCK_ID, 'CODE' => 'BOOKING'],
        ])->fetch();

        if ($arPropertyBooking && Loader::includeModule('sysp.userpropbooking')) {
            $isBooking = true;
            $arSelect[] = 'BOOKING';
        }

        // читаем данные
        $doctor = DoctorsTable::getList([
            'select' => $arSelect,
            'filter' => [
                '=IBLOCK_ELEMENT_ID' => $id,
            ],
        ])->fetch();

        if ($isBooking) {
            $arPropertyBooking['ELEMENT_ID'] = $doctor['IBLOCK_ELEMENT_ID'];

            $doctor['BOOKING'] = CIblockPropertyBooking::GetPublicViewHTML(
                $arPropertyBooking,
                ['VALUE' => $doctor['BOOKING']],
                null
            );
        }

        return $doctor ?: null;
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function add(array $data): ?string
    {
        $data['LASTNAME'] = htmlspecialchars($data['LASTNAME'] ?? '');
        $data['FIRSTNAME'] = htmlspecialchars($data['FIRSTNAME'] ?? '');
        $data['PATRONYMIC'] = htmlspecialchars($data['PATRONYMIC'] ?? '');
        $data['PROCS'] = $data['PROCS'] ?? [];

        $slug = ($this->translit_sef($data['LASTNAME']) ?? 'unnamed') . time();

        $result = DoctorsTable::add([
            'NAME' => $slug,
            'LASTNAME' => $data['LASTNAME'],
            'FIRSTNAME' => $data['FIRSTNAME'],
            'PATRONYMIC' => $data['PATRONYMIC'],
            'PROCEDURES' => $data['PROCS'],
        ]);

        return ($result) ? $slug : null;
    }

    /**
     * @param array $data
     * @return void
     * @throws LoaderException
     */
    public function update(array $data): mixed
    {
        if (!Loader::includeModule('iblock')) {
            throw new \Exception('IBLOCK_MODULE_NOT_INSTALLED');
        }

        if (isset($data['SLUG'])) {
            $data['SLUG'] = htmlspecialchars($data['SLUG']);
        }
        if (isset($data['LASTNAME'])) {
            $data['LASTNAME'] = htmlspecialchars($data['LASTNAME']);
        }
        if (isset($data['FIRSTNAME'])) {
            $data['FIRSTNAME'] = htmlspecialchars($data['FIRSTNAME']);
        }
        if (isset($data['PATRONYMIC'])) {
            $data['PATRONYMIC'] = htmlspecialchars($data['PATRONYMIC']);
        }

        $iblockElement = new \CIBlockElement();
        $result = $iblockElement->Update($data['ID'], [
            'NAME' => $data['SLUG'],
            'PROPERTY_VALUES' => [
                'LASTNAME' => $data['LASTNAME'],
                'FIRSTNAME' => $data['FIRSTNAME'],
                'PATRONYMIC' => $data['PATRONYMIC'],
                'PROCEDURES' => $data['PROCS'],
            ]
        ]);

        return $result;
    }

    /**
     * @param int $id
     * @return mixed
     * @throws LoaderException
     */
    public function delete(int $id): mixed
    {
        if (!Loader::includeModule('iblock')) {
            throw new \Exception('IBLOCK_MODULE_NOT_INSTALLED');
        }

        return \CIBlockElement::Delete($id);
    }

    /**
     * @param array $data
     * @return bool|null
     */
    public function addProc(array $data)
    {
        $result = ProceduresTable::add([
            'NAME' => $data['NAME'],
        ]);

        return $result ?: null;
    }

    /**
     * @param $value
     * @return string
     */
    private function translit_sef($value)
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
        );

        $value = mb_strtolower($value);
        $value = strtr($value, $converter);
        $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
        $value = mb_ereg_replace('[-]+', '-', $value);
        $value = trim($value, '-');

        return $value;
    }
}