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
        

        /*
        * возвращает конфигурацию сайта
        */
        public static function getConfig ($timestamp=0): array
        {
            if (!$timestamp) $timestamp = $_SERVER['REQUEST_TIME']; // если время не передано
                                                                    // наивно предположим что оно такое же как на сервере
            $arConfig = [
                    'options' => [
                            
                        ],
                    'env' => [
                            'stage' => defined('APPLICATION_ENV')?APPLICATION_ENV:'production',
                            'timestamp' => time(),
                            'timestamp_request' => $_SERVER['REQUEST_TIME'],
                            'timestamp_client' => round($timestamp),
                            'timestamp_delta' => floor($timestamp-$_SERVER['REQUEST_TIME']), // насколько время клиента, опережает серверное
                            //                                                                              // в секундах
                        ],
                    'bitrix' => [
                            'SITE_TEMPLATE_PATH' => SITE_TEMPLATE_PATH,
                            'START_EXEC_TIME' => START_EXEC_TIME,
                        ]
                ];
            
            global $USER;
            if ($USER->isAuthorized()) {
                $rsUser = \CUser::GetByID($USER->GetID());
                $arUser = $rsUser->Fetch();

                $arConfig['user'] = [
                        'id' => $arUser['ID'],
                        'email_hash' => md5($arUser['EMAIL'])
                    ];
            } else {
                $arConfig['user'] = [
                        'id' => 0,
                        'email_hash' => ''
                    ];
            }
            
            return $arConfig;
        }

        
        /*
        * возвращает значение по пути
        */
        private static $_configpathesmemo = [];
        public static function getConfigValue (string $Path)
        {
            if (!self::$_configpathesmemo[$Path]) {
                $Val = static::getConfig(); 
                foreach (explode('/',$Path) as $Token) { if (!$Token) continue;
                    $Val = $Val[$Token];
                }
                self::$_configpathesmemo[$Path] = $Val;
            } else {
                $Val = self::$_configpathesmemo[$Path];
            }
            
            return $Val;
        }
        #
    }
}