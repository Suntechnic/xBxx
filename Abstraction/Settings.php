<?
namespace Bxx\Abstraction
{ 
    class Settings
    {
        public const MODULE = '.app';
        public const OPTIONS = [];
        public const CACHE_TTL = 86399;
        public const CACHE_TTL_LIMIT = 86399;
        
        public function getOptionKey (string $code): string
        {
            return $code;
        }
        
        public static function getOption (string $code)
        {
            if (static::OPTIONS[$code]) $default = static::OPTIONS[$code]['default'];
            return \Bitrix\Main\Config\Option::get(
                    static::MODULE,
                    static::getOptionKey($code),
                    $default
                );
        }
        
        public static function setOption (string $code, $value)
        {
            return \Bitrix\Main\Config\Option::set(
                    static::MODULE,
                    static::getOptionKey($code),
                    $value
                );
        }
        
        public static function getCacheTTL (int $TTL=0): int
        {
            if ($TTL < 1) $TTL = static::CACHE_TTL;
            return $TTL>static::CACHE_TTL_LIMIT?static::CACHE_TTL_LIMIT:$TTL;
        }
        
    }
}