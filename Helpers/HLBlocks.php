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
        public static function getEntityClassByCode (string $Code)
        {
            if (!isset(self::$_memoizing['getEntityClassByCode'][$Code])) {
                self::$_memoizing['getEntityClassByCode'][$Code] = self::getEntityClass([
                        'NAME' => $Code
                    ]);
            }
            return self::$_memoizing['getEntityClassByCode'][$Code];
        }
        
        /**
         * Возращает класс по имени таблицы
         */
        public static function getEntityClassByTable (string $Table): string
        {
            if (!isset(self::$_memoizing['getEntityClassByCode'][$Table])) {
                self::$_memoizing['getEntityClassByCode'][$Table] = self::getEntityClass([
                        'TABLE_NAME' => $Table
                    ]);
            }
            return self::$_memoizing['getEntityClassByCode'][$Table];
        }


        /**
         * Возращает код по имени таблицы
         */
        public static function getCodeByTable (string $TableName): string
        {
            if (!isset(self::$_memoizing['getCodeByTable'][$TableName])) {
                $dctHLBlockData = self::getHLBlockData([
                        'TABLE_NAME' => $TableName
                    ]);
                self::$_memoizing['getCodeByTable'][$TableName] = $dctHLBlockData['NAME'];
            }
            return self::$_memoizing['getCodeByTable'][$TableName];
        }


        /**
         * Возращает id по коду
         */
        public static function getIdByCode (string $Code): string
        {
            if (!isset(self::$_memoizing['getIdByCode'][$Code])) {
                \Bitrix\Main\Loader::includeModule('highloadblock');
                $res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                        'filter' => ['NAME' => $Code]
                    ]);
                if ($dctHLBlockData = $res->fetch()) {
                    self::$_memoizing['getIdByCode'][$Code] = $dctHLBlockData['ID'];
                } else {
                    throw new \Bitrix\Main\ObjectNotFoundException('Не найден hl-блок с кодом '.$Code);
                }
            }
            return self::$_memoizing['getIdByCode'][$Code];
        }
        
        /**
         * Возращает класс по фильтру
         * !!! (метод без мемоизации)
         * 
         */
        public static function getEntityClass (array $dctFilter): string
        {
            \Bitrix\Main\Loader::includeModule('highloadblock');
            $dctHLBlockData = self::getHLBlockData($dctFilter);

            //$hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($dctHLBlockData['ID'])->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($dctHLBlockData);
            $class = $entity->getDataClass();
            return $class;
        }

        /**
         * Возращает класс по фильтру
         * !!! (метод без мемоизации)
         * 
         */
        public static function getHLBlockData (array $dctFilter): array
        {
            \Bitrix\Main\Loader::includeModule('highloadblock');
            $res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                    'filter' => $dctFilter
                ]);
            if ($dctHLBlockData = $res->fetch()) return $dctHLBlockData;
            throw new \Bitrix\Main\ObjectNotFoundException(print_r($dctFilter, true));
        }
    }
}


