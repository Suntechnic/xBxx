<?php
declare(strict_types=1);

namespace Bxx\Helpers\IBlocks
{
    class Properties
    {
        public const DEFAULT_CACHE_PATH = 'Bxx/IBlocks/Properties';


        private static $_memoizing = [];


        /**
         * Справочник свойств инфоблока Код=>Свойство
         * 
         */
        public static function getReference (int $IBlockId, int|bool $CacheTTL=false): array
        {
            if (static::$_memoizing['getReference'][$IBlockId]) return static::$_memoizing['getReference'][$IBlockId];

            $cache = \Bitrix\Main\Data\Cache::createInstance();
            $CacheKey = 'getReference_'.$IBlockId;

            if ($CacheTTL === false) {
                if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'dev') {
                    $CacheTTL = 0;
                } else $CacheTTL = \Bxx\Settings::getCacheTTL();
            }

            $refProps = [];

            if ($cache->initCache($CacheTTL, $CacheKey, self::DEFAULT_CACHE_PATH)) {
                $refProps = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $rdbProps = \Bitrix\Iblock\PropertyTable::getList([
                        'order' => [
                                'SORT' => 'ASC',
                                'NAME' => 'ASC'
                            ],
                        'filter' => [
                                'IBLOCK_ID' => $IBlockId,
                                '!CODE' => false
                            ],
                        //'cache' => ['ttl' => $CacheTTL]
                    ]);
                while ($dctProp = $rdbProps->fetch()) {
                    $refProps[$dctProp['CODE']] = $dctProp['ID'];
                }

                $cache->endDataCache($refProps);
            }

            static::$_memoizing['getMap'][$IBlockId] = $refProps;

            return $refProps;

        }



        /**
         * справочник свойст Enum
         * 
         */
        public static function getEnumReference (int $IBlockId, int|bool $CacheTTL=false): array
        {
            $cache = \Bitrix\Main\Data\Cache::createInstance();
            $CacheKey = 'getEnumReference_'.$IBlockId;

            if ($CacheTTL === false) {
                if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'dev') {
                    $CacheTTL = 0;
                } else $CacheTTL = \Bxx\Settings::getCacheTTL();
            }

            $refProps = [];

            if ($cache->initCache($CacheTTL, $CacheKey, self::DEFAULT_CACHE_PATH)) {
                $refProps = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $rdbEnumProps = \Bitrix\Iblock\PropertyTable::getList([
                        'select' => [
                                'ID',
                                'CODE',
                                'NAME'
                            ],
                        'order' => [
                                'SORT' => 'ASC',
                                'NAME' => 'ASC'
                            ],
                        'filter' => [
                                'IBLOCK_ID' => $IBlockId,
                                'PROPERTY_TYPE' => 'L',
                                'LIST_TYPE' => 'L',
                                '!CODE' => false
                            ],
                        //'cache' => ['ttl' => $CacheTTL]
                    ]);
                $mapId2Code = [];
                while ($dctProp = $rdbEnumProps->fetch()) {
                    $dctProp['ITEMS'] = [];
                    $refProps[$dctProp['CODE']] = $dctProp;
                    $mapId2Code[$dctProp['ID']] = $dctProp['CODE'];
                }

                $rdbEnum = \Bitrix\Iblock\PropertyEnumerationTable::getList([
                        'select' => [
                                'ID',
                                'PROPERTY_ID',
                                'VALUE',
                                'XML_ID',
                                'SORT'
                            ],
                        'order' => [
                                'SORT' => 'ASC',
                                'VALUE' => 'ASC'
                            ],
                        'filter' => [
                                'PROPERTY_ID' => array_keys($mapId2Code)
                            ],
                        //'cache' => ['ttl' => $CacheTTL]
                    ]);
                while ($dctEnumItem = $rdbEnum->fetch()) {
                    $refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['MAPS']['ID'][$dctEnumItem['ID']] = count($refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['ITEMS']);
                    $refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['MAPS']['XML_ID'][$dctEnumItem['XML_ID']] = count($refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['ITEMS']);
                    $refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['MAPS']['XML_ID2ID'][$dctEnumItem['XML_ID']] = intval($dctEnumItem['ID']);
                    $refProps[$mapId2Code[$dctEnumItem['PROPERTY_ID']]]['ITEMS'][] = $dctEnumItem;
                }

                $cache->endDataCache($refProps);
            }

            return $refProps;
        }
        
    }
}

