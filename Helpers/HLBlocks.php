<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    class HLBlocks
    {
        
        private static $_memoizing = [];
        
        /**
         * Возращает класс по коду
         */
        public static function getEntityClassByCode (string $code)
        {
            if (!isset(self::$_memoizing['getEntityClassByCode'][$code])) {
                self::$_memoizing['getEntityClassByCode'][$code] = self::getEntityClass([
                        'NAME' => $code
                    ]);
            }
            return self::$_memoizing['getEntityClassByCode'][$code];
        }
        
        /**
         * Возращает класс по имени таблицы
         */
        public static function getEntityClassByTable (string $table): string
        {
            if (!isset(self::$_memoizing['getEntityClassByCode'][$table])) {
                self::$_memoizing['getEntityClassByCode'][$table] = self::getEntityClass([
                        'TABLE_NAME' => $table
                    ]);
            }
            return self::$_memoizing['getEntityClassByCode'][$table];
        }
        
        /**
         * Возращает класс по фильтру
         * !!! (метод без мемоизации)
         * 
         */
        public static function getEntityClass (array $dctFilter): string
        {
            \Bitrix\Main\Loader::includeModule('highloadblock');
            $res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                    'filter' => $dctFilter
                ]);
            if ($hlBlockData = $res->fetch()) {
                //$hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlBlockData['ID'])->fetch();
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlockData);
                $class = $entity->getDataClass();
                return $class;
            }
    
            throw new HlBlockNotFoundException(print_r($dctFilter, true));
        }
    }
}


