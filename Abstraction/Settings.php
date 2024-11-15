<?
namespace Bxx\Abstraction
{ 
    class Settings
    {
        public const MODULE = '.app';
        // может содержать список опций приложения, описанные здесь опции попадут в ключ options конфигурации
        public const OPTIONS = [/*
                'OptionName' => [
                        'title' => 'Название опции для отображения в админке',
                        'default' => 'ЗначениеПоУмолчанию',
                        'type' => bool|integer|string - тип опции
                    ]
            */]; 
        // может содержать дополнительные ключи для массива конфиг, 
        // где значения - это имена методово реализации этого класса
        protected const KEYS = [/*
                'Key' => 'MethodName'
            */];
        /**
         * Пример метода загрузки путей api
        public static function getApi (): array
        {
            $refApi = [];
            $router = \Bitrix\Main\Application::getInstance()->getRouter();
            foreach ($router->getRoutes() as $route) {
                $options = $route->getOptions();
                if ($options->getFullPrefix() == '/api/v1' // это api
                        && $options->getFullName() // и он именованный (публичный)
                    ) {
                    $refApi[$options->getFullName()] = [
                            'uri' => $route->getUri(),
                            'parameters' => $route->getParameters(),
                            'methods' => $options->getMethods()
                        ];
                }
                
            }
            return $refApi;
        }
         */

        public const CACHE_TTL = 86399;
        public const CACHE_TTL_LIMIT = 86399;
        
        public static function getOptionKey (string $code): string
        {
            return $code;
        }


        /**
         * возвращает массив Опция=>Параметры
         * для опций описанных в константе OPTIONS
         * по сути просто массив OPTIONS
         */
        public static function getOptionsInfo (): array
        {
            return static::OPTIONS;
        }
        /**
         * возвращает описание опцци по коду
         */
        public static function getOptionInfo (string $Code): array
        {
            $dctOption = static::getOptionsInfo()[$Code];
            if (is_array($dctOption)) return $dctOption;
            return [];
        }
        /**
         * возвращает дефолтное значение опции по коду
         */
        public static function getOptionDefault (string $Code)
        {
            $dctOption = static::getOptionInfo($Code);
            return $dctOption['default'];
        }

        /**
         * возвращает массив Опция=>Значение
         * для опций описанных в константе OPTIONS
         */
        public static function getOptions (): array
        {
            $dctOptions = [];
            foreach (static::OPTIONS as $Code=>$_) {
                $dctOptions[$Code] = static::getOption($Code);
            }
            return $dctOptions;
        }
        
        /**
         * возвращает знаение опции приложения по его имени
         * если необходимо дефолтное значение - оно должно быть описано в константе OPTIONS
         */
        public static function getOption (string $Code)
        {

            $dctOption = static::getOptionInfo($Code);
            $Value = \Bitrix\Main\Config\Option::get(
                    static::MODULE,
                    static::getOptionKey($Code),
                    $dctOption['default']
                );

            // преобразование типов
            if ($dctOption['type'] == 'bool') {
                if ($Value == 'Y') {
                    $Value = true;
                } elseif ($Value == 'N') {
                    $Value = false;
                } else {
                    $Value = !!$Value;
                }
            } elseif ($dctOption['type'] == 'integer') {
                $Value = intval($Value);
            } elseif ($dctOption['type'] == 'string') {
                $Value = (string)$Value;
            }
            
            return $Value;
        }
        public static function setOption (string $Code, $Value)
        {
            $dctOption = static::getOptionInfo($Code);

            // преобразование типов
            if ($dctOption['type'] == 'bool') {
                if (!!$Value) {
                    $Value = 'Y';
                } else {
                    $Value = 'N';
                }
            } elseif ($dctOption['type'] == 'integer') {
                $Value = intval($Value);
            } elseif ($dctOption['type'] == 'string') {
                $Value = (string)$Value;
            }

            return \Bitrix\Main\Config\Option::set(
                    static::MODULE,
                    static::getOptionKey($Code),
                    $Value
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
                    'options' => static::getOptions(),
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
                        ],
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

            // дополнительные ключи
            foreach (static::KEYS as $Key=>$Method) {
                $arConfig[$Key] = static::$Method();
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