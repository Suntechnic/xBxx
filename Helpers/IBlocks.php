<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    class IBlocks
    {
        public const DEFAULT_PATH = 'Bxx/IBlock';
        
<<<<<<< HEAD
        /**
         * возвращает id инфоблока по его коду
         *
         * @param string $code - код инфоблока
         * @return int
         * @throws \Bitrix\Main\ObjectNotFoundException
         */
        public static function getIdByCode(string $code): int
=======
        public static function getIdByCode (string $code): int
>>>>>>> ef1041caf7b968c7ef507f6f293a2e8a0e6c6b3b
        {
            $ref = self::refIdByCode();
            if ($ref[$code]) return $ref[$code];

            throw new \Bitrix\Main\ObjectNotFoundException('Инфоблок с кодом '.$code.' не существует');
        }
        



        /**
         * возвращает class d7 для инфоблока по его коду
         *
         * @param string $Code - код инфоблока
         * @return string
         * @throws \Bitrix\Main\ObjectNotFoundException
         */
        public static function getClassByCode (string $Code): string
        {
            if (!isset(self::$_memoizing['getClassByCode'][$Code])) {
                $ref = self::refIdByCode();
                if ($ref[$Code]) {
                    self::$_memoizing['getClassByCode'][$Code]
                             = \Bitrix\Iblock\Iblock::wakeUp($ref[$Code])->getEntityDataClass();
                    return self::$_memoizing['getClassByCode'][$Code];
                }
            }


            

            throw new \Bitrix\Main\ObjectNotFoundException('Инфоблок с кодом '.$Code.' не существует');
        }
        
        /**
         * возвращает справочник инфоблоков
         * 
         * @return array - справочинк инфоблоков где ключом является код инфоблока
         */

        private static $_memoizing = false;
        public static function refIdByCode (): array
        {
            
            if (!self::$_memoizing['refIdByCode']) {
                $cache = \Bitrix\Main\Data\Cache::createInstance();
    
                $cacheKey = 'refIdByCode';
        
                $ref = [];
                if ($cache->initCache(\App\Settings::getCacheTTL(), $cacheKey, self::DEFAULT_PATH)) {
                    $ref = $cache->getVars();
                } elseif ($cache->startDataCache()) {
                    \Bitrix\Main\Loader::includeModule('iblock');
                    $res = \CIBlock::GetList([], []);
                    while ($arIblock = $res->Fetch()) {
                        $ref[$arIblock['CODE']] = (int) $arIblock['ID'];
                    }
                    
                    $cache->endDataCache($ref);
                }
                self::$_memoizing['refIdByCode'] = $ref;
            }
            return self::$_memoizing['refIdByCode'];
        }
    }
}

