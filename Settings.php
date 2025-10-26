<?
namespace Bxx
{ 
    class Settings extends \Bxx\Abstraction\Settings
    {
        public static function getCacheTTL (int $TTL=0): int
        {
            // Проверяем, что метод переопределен в App\Settings
            if (class_exists('\App\Settings')) {
                $reflection = new \ReflectionMethod('\App\Settings', 'getCacheTTL');
                // Если метод определен именно в App\Settings (не унаследован)
                if ($reflection->getDeclaringClass()->getName() === 'App\Settings') {
                    return \App\Settings::getCacheTTL($TTL);
                }
            }
            return parent::getCacheTTL($TTL);
        }
    }
}