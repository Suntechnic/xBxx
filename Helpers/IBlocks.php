<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    class IBlocks
    {
        public const DEFAULT_PATH = 'Bxx/IBlock';
        
        public static function getIdByCode(string $code): int
        {
            $ref = self::refIdByCode();
            if ($ref[$code]) return $ref[$code];
            throw new IblockNotFoundException($code);
        }
        
        /*
         * возвращает справочник хайлойдблоков
        */
        private static $_memoizing = false;
        public static function refIdByCode (): array
        {
            
            if (!self::$_memoizing) {
                $cache = \Bitrix\Main\Data\Cache::createInstance();
    
                $cacheKey = 'refIdByCode';
        
                $ref = [];
                if ($cache->initCache(\App\Settings::getCacheTTL(), $cacheKey, self::DEFAULT_PATH)) {
                    $ref = $cache->getVars();
                } elseif ($cache->startDataCache()) {
                    
                    $res = \CIBlock::GetList([], []);
                    while ($arIblock = $res->Fetch()) {
                        $ref[$arIblock['CODE']] = (int) $arIblock['ID'];
                    }
                    
                    $cache->endDataCache($ref);
                }
                self::$_memoizing = $ref;
            }
            return self::$_memoizing;
        }
    }
}

