<?php

namespace Sysp\CrmCustomTab\ORM;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use CIBlockElement;

/**
 * Class AbstractIblockPropertyValueTable
 *
 * @package Models
 */
abstract class AbstractIblockPropertyValuesTable extends DataManager
{
    protected static ?array $properties = null;
    protected static ?CIBlockElement $iblockElement = null;

    abstract public static function getIblockId(): ?int;
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_iblock_element_prop_s' . static::getIblockId();
    }

    /**
     * @return string
     */
    public static function getTableNameMulti(): string
    {
        $iblockId = static::getIblockId();
        return 'b_iblock_element_prop_m' . static::getIblockId();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function getMap(): array
    {
        $iblockId = static::getIblockId();
        $cache = Cache::createInstance();
        $cacheDir = 'iblock_property_map/'.$iblockId;
        $multipleValuesTableClass = static::getMultipleValuesTableClass();
        static::initMultipleValuesTableClass();

        if ($cache->initCache(3600, md5($cacheDir), $cacheDir)) {
            $map = $cache->getVars();

        } else {
            $cache->startDataCache();

            $map['IBLOCK_ELEMENT_ID'] = new IntegerField('IBLOCK_ELEMENT_ID', ['primary' => true]);
            $map['ELEMENT'] = new ReferenceField(
                'ELEMENT',
                ElementTable::class,
                ['=this.IBLOCK_ELEMENT_ID' => 'ref.ID']
            );

            foreach (static::getProperties() as $property) {
                if ($property['MULTIPLE'] === 'Y') {
                    $map[$property['CODE']] = new ExpressionField(
                        $property['CODE'],
                        sprintf('(select group_concat(`VALUE` SEPARATOR "\0") as VALUE from %s as m where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
                            static::getTableNameMulti(),
                            '%s',
                            $property['ID']
                        ),
                        ['IBLOCK_ELEMENT_ID'],
                        ['fetch_data_modification' => [static::class, 'getMultipleFieldValueModifier']]
                    );

                    if ($property['USER_TYPE'] === 'EList') {
                        $map[$property['CODE'].'_ELEMENT_NAME'] = new ExpressionField(
                            $property['CODE'].'_ELEMENT_NAME',
                            sprintf('(select group_concat(e.NAME SEPARATOR "\0") as VALUE from %s as m join b_iblock_element as e on m.VALUE = e.ID where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
                                static::getTableNameMulti(),
                                '%s',
                                $property['ID']
                            ),
                            ['IBLOCK_ELEMENT_ID'],
                            ['fetch_data_modification' => [static::class, 'getMultipleFieldValueModifier']]
                        );
                    }

                    $map[$property['CODE'].'|SINGLE'] = new ReferenceField(
                        $property['CODE'].'|SINGLE',
                        $multipleValuesTableClass,
                        [
                            '=this.IBLOCK_ELEMENT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                            '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?i', $property['ID'])
                        ]
                    );

                    continue;
                }

                if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_NUMBER) {
                    $map[$property['CODE']] = new IntegerField("PROPERTY_{$property['ID']}");
                } elseif ($property['USER_TYPE'] === 'Date') {
                    $map[$property['CODE']] = new DatetimeField("PROPERTY_{$property['ID']}");
                } else {
                    $map[$property['CODE']] = new StringField("PROPERTY_{$property['ID']}");
                }

                if ($property['PROPERTY_TYPE'] === 'E' && ($property['USER_TYPE'] === 'EList' || is_null($property['USER_TYPE']))) {
                    $map[$property['CODE'].'_ELEMENT'] = new ReferenceField(
                        $property['CODE'].'_ELEMENT',
                        ElementTable::class,
                        ["=this.{$property['CODE']}" => 'ref.ID']
                    );
                }
            }

            if (empty($map)) {
                $cache->abortDataCache();
            } else {
                $cache->endDataCache($map);
            }
        }

        return $map;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function add(array $data)
    {
        $iblockId = static::getIblockId();
        static::$iblockElement ?? static::$iblockElement = new CIBlockElement();
        $fields = [
            'NAME'            => $data['NAME'],
            'IBLOCK_ID'       => $iblockId,
            'PROPERTY_VALUES' => $data,
        ];

        return static::$iblockElement->Add($fields);
    }

    /**
     * @param $primary
     *
     * @return DeleteResult
     * @throws NotImplementedException
     */
    public static function delete($primary): DeleteResult
    {
        #TODO Implement function
        throw new NotImplementedException();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    public static function getProperties(): array
    {
        $iblockId = static::getIblockId();
        if (isset(static::$properties[$iblockId])) {
            return static::$properties[$iblockId];
        }

        $dbResult = PropertyTable::query()
            ->setSelect(['ID', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'NAME', 'USER_TYPE'])
            ->where('IBLOCK_ID', $iblockId)
            ->exec();
        while ($row = $dbResult->fetch()) {
            static::$properties[$iblockId][$row['CODE']] = $row;
        }

        return static::$properties[$iblockId] ?? [];
    }

    /**
     * @param  string  $code
     *
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getPropertyId(string $code): int
    {
        return (int) static::getProperties()[$code]['ID'];
    }

    /**
     * @return array
     */
    public static function getMultipleFieldValueModifier(): array
    {
        return [fn ($value) => array_filter(explode("\0", $value))];
    }

    /**
     * @param  int|null  $iblockId
     */
    public static function clearPropertyMapCache(?int $iblockId = null): void
    {
        $iblockId = $iblockId ?: static::getIblockId();
        if (empty($iblockId)) {
            return;
        }

        Cache::clearCache(true, "iblock_property_map/$iblockId");
    }

    /**
     * @param  string  $propertyCode
     * @param  string  $byKey
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getEnumPropertyOptions(string $propertyCode, string $byKey = 'ID'): array
    {
        $iblockId = static::getIblockId();
        $dbResult = PropertyEnumerationTable::getList([
            'select' => ['ID', 'VALUE', 'XML_ID', 'SORT'],
            'filter' => ['=PROPERTY.CODE' => $propertyCode, 'PROPERTY.IBLOCK_ID' => $iblockId],
        ]);
        while ($row = $dbResult->fetch()) {
            $enumPropertyOptions[$row[$byKey]] = $row;
        }

        return $enumPropertyOptions ?? [];
    }

    /**
     * @return string
     */
    private static function getMultipleValuesTableClass(): string
    {
        $className = end(explode('\\', static::class));
        $namespace = str_replace('\\'.$className, '', static::class);
        $className = str_replace('Table', 'MultipleTable', $className);

        return $namespace.'\\'.$className;
    }

    /**
     * @return void
     */
    private static function initMultipleValuesTableClass(): void
    {
        $className = end(explode('\\', static::class));
        $namespace = str_replace('\\'.$className, '', static::class);
        $className = str_replace('Table', 'MultipleTable', $className);

        if (class_exists($namespace.'\\'.$className)) {
            return;
        }

        $iblockId = static::getIblockId();

//         $php = <<<PHP
// namespace $namespace;

// class {$className} extends \Models\AbstractIblockPropertyMultipleValuesTable
// {
//     const IBLOCK_ID = {$iblockId};
// }

// PHP;
//         eval($php);
    }
}
