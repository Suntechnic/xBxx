<?
namespace Bxx
{ 
    class Settings extends \Bxx\Abstraction\Settings
    {
        public static function getCacheTTL (int $TTL=0): int
        {
            if (class_exists('\App\Settings')) {
                return \App\Settings::getCacheTTL();
            }
            return parent::getCacheTTL();
        }
    }
}