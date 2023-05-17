<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    class IBlocks
    {
        public const DEFAULT_PATH = 'Bxx/IBlock';

        private static $_memoizing = false;

        
        /**
         * возвращает id инфоблока по его коду
         *
         * @param string $Code - код инфоблока
         * @return int
         * @throws \Bitrix\Main\ObjectNotFoundException
         */
        public static function getIdByCode(string $Code): int
        {
            $ref = self::refIdByCode();
            if ($ref[$Code]) return intval($ref[$Code]);
            
            $ref = self::refIdByCode(true);
            if ($ref[$Code]) return intval($ref[$Code]);

            throw new \Bitrix\Main\ObjectNotFoundException('Инфоблок с кодом '.$Code.' не существует');
        }
        
        /**
         * возвращает class d7 для инфоблока по его коду
         *
         * @param string $Code - код инфоблока
         * @return string
         * @throws \Bitrix\Main\ObjectNotFoundException
         */
        public static function getElementUrlTemplateByCode (string $Code): string
        {
            if (!is_array(self::$_memoizing['getUrlTemplateCode']) 
                    || !array_key_exists($Code,self::$_memoizing['getUrlTemplateCode'])
                ) {
                $ref = array_column(self::getList(),'DETAIL_PAGE_URL','CODE');

                self::$_memoizing['getUrlTemplateCode'][$Code] = $ref[$Code];
            }
            return self::$_memoizing['getUrlTemplateCode'][$Code];
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
                if (!$ref[$Code]) $ref = self::refIdByCode(true);

                if ($ref[$Code]) {
                    self::$_memoizing['getClassByCode'][$Code]
                            = \Bitrix\Iblock\Iblock::wakeUp($ref[$Code])->getEntityDataClass();
                } else {
                    throw new \Bitrix\Main\ObjectNotFoundException('Инфоблок с кодом '.$Code.' не существует');
                }
            }

            return self::$_memoizing['getClassByCode'][$Code];
        }
        
        /**
         * возвращает справочник инфоблоков
         * 
         * @return array - справочинк инфоблоков где ключом является код инфоблока
         */
        public static function refIdByCode (bool $DropCache=false): array
        {
            if (!self::$_memoizing['refIdByCode'] || $DropCache) {
                $ref = array_column(self::getList($DropCache),'ID','CODE');
                self::$_memoizing['refIdByCode'] = $ref;
            }
            return self::$_memoizing['refIdByCode'];
        }
        

        public static function getList (bool $DropCache=false): array
        {
            $cache = \Bitrix\Main\Data\Cache::createInstance();
            $cacheKey = 'getList';

            $lst = [];
            
            if ($cache->initCache(\App\Settings::getCacheTTL(), $cacheKey, self::DEFAULT_PATH) && !$DropCache) {
                $lst = $cache->getVars();
            } elseif ($cache->startDataCache() || $DropCache) {
                \Bitrix\Main\Loader::includeModule('iblock');
                $res = \CIBlock::GetList([], []);
                while ($arIblock = $res->Fetch()) {
                    $lst[] = [
                            'ID' => $arIblock['ID'],
                            'CODE' => $arIblock['CODE'],
                            'DETAIL_PAGE_URL' => $arIblock['DETAIL_PAGE_URL'],
                            'SECTION_PAGE_URL' => $arIblock['SECTION_PAGE_URL'],
                            'VERSION' => $arIblock['VERSION'],
                        ];
                }
                $cache->endDataCache($lst);
            }

            return $lst;
        }
    }
}

