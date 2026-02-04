<?
namespace Bxx\Abstraction
{ 
    class Settings
    {
        public const MODULE = '.app';




        // может содержать список опций приложения, описанные здесь опции попадут в ключ options конфигурации
        #[\Deprecated(message: 'используйте файлы в папке /local/options')]
        public const OPTIONS = [];

        // может содержать дополнительные ключи для массива конфиг, 
        // где значения - это имена методов реализации этого класса
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
         * для опций описанных в константе options
         * по сути просто массив OPTIONS
         * 
         */
        private static $_optionsinfo = false;
        public static function getOptionsInfo (): array
        {
            if (static::$_optionsinfo === false) { // @phpstan-ignore-line
                static::$_optionsinfo = static::OPTIONS; // @phpstan-ignore-line

                // подключаем файлы опций
                $Path = \Bitrix\Main\Application::getDocumentRoot().'/local/options';
                if (is_dir($Path)) {
                    $dir = new \DirectoryIterator($Path);
                    foreach ($dir as $fileinfo) {
                        if ($fileinfo->isDot()) continue;
                        if ($fileinfo->getExtension() != 'php') continue;
                        $refNextOptions = include($fileinfo->getPathname());
                        if (is_array($refNextOptions)) {
                            static::$_optionsinfo = array_merge(static::$_optionsinfo, $refNextOptions); // @phpstan-ignore-line
                        }
                    }
                }
            }
            

            return static::$_optionsinfo; // @phpstan-ignore-line
        }

        /**
         * возвращает hash выбранных опций
         * для разделения компонентного хэша
         */
        public static function getOptionsHash (array $lstOptions=[]): string
        {
            if (!$lstOptions) $lstOptions = array_keys(static::getOptionsInfo());
            $strValues = [];

            // для единообразия массив должен быть отсортирован
            sort($lstOptions);

            $refOptions = [];
            foreach ($lstOptions as $CodeOption) {
                $refOptions[$CodeOption] = self::getOption($CodeOption);
            }

            return md5(serialize($refOptions));
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
         * @param array $Filter - массив кодов опций или префикс кода опций
         * @return array - массив Опция=>Значение
         * @example
         * $refOptions = Settings::getOptions(['option1','option2']);
         * $refOptions = Settings::getOptions(); // все опции определенные в конфиге
         */
        public static function getOptions (array|string $Filter=null): array
        {
            if (is_string($Filter) && $Filter) {
                $lstCode = array_filter(array_keys(static::getOptionsInfo()), function ($Code) use ($Filter) {
                    return strpos($Code, $Filter) === 0; // фильтруем по префиксу
                });
            } elseif (is_array($Filter)) {
                $lstCode = $Filter;
            }
            if (!$lstCode) $lstCode = array_keys(static::getOptionsInfo());

            $dctOptions = [];
            foreach ($lstCode as $Code) {
                $dctOptions[$Code] = static::getOption($Code);
            }
            return $dctOptions;
        }

        /**
         * устанавливает массив Опция=>Значение
         * @param array $refOptions - массив Опция=>Значение
         * @return array - массив Опция=>Значение
         */
        public static function setOptions (array $refOptions): array
        {
            $lstCode = array_keys($refOptions);
            foreach ($lstCode as $Code) {
                static::setOption($Code,$refOptions[$Code]);
            }
            return static::getOptions($lstCode);
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
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // поддержка enum
            if ($Value) {
                $refEnum = static::getEnum($Code);
                if ($refEnum && !array_key_exists($Value, $refEnum)) {
                    $Value = $dctOption['default'];
                }
            }
            // поддержка enum
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
                if (isset($dctOption['max']) && $Value > $dctOption['max']) $Value = $dctOption['max'];
                if (isset($dctOption['min']) && $Value < $dctOption['min']) $Value = $dctOption['min'];
            } elseif ($dctOption['type'] == 'float') {
                $Value = floatval($Value);
            } elseif ($dctOption['type'] == 'string' || $dctOption['type'] == 'text') {
                $Value = (string)$Value;
            }
            // преобразование типов
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            
            return $Value;
        }



        /**
         * возвращает список вариантов enum опции
         */
        public static function getEnum (string $Code): array
        {
            $dctOption = static::getOptionInfo($Code);
            $refEnum = [];
            if (isset($dctOption['enum']) && $dctOption['enum']) {
                if (is_array($dctOption['enum'])) {
                    $refEnum = $dctOption['enum'];
                } elseif (is_string($dctOption['enum']) && class_exists($dctOption['enum'])) {
                    if (method_exists($dctOption['enum'], 'getListOptions')) {
                        $refEnum = $dctOption['enum']::getListOptions();
                    }
                }
            }
            return $refEnum;
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
            } elseif ($dctOption['type'] == 'float') {
                $Value = floatval($Value);
            } elseif ($dctOption['type'] == 'string' || $dctOption['type'] == 'text') {
                $Value = (string)$Value;
            }

            return \Bitrix\Main\Config\Option::set(
                    static::MODULE,
                    static::getOptionKey($Code),
                    $Value
                );
        }
        public static function set (string $Code, $Value)
        {
            return static::setOption($Code, $Value);
        }
        public static function get (string $Code, $Default=null)
        {
            $Value = static::getOption($Code);
            if (is_null($Value)) $Value = $Default;
            return $Value;
        }

        /**
         * удаляет опцию приложения по коду
         * @param string $Code - код опции
         */
        public static function delete (string $Code)
        {
            return \Bitrix\Main\Config\Option::delete(
                    static::MODULE,
                    ['name' => static::getOptionKey($Code)]
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
            $refOptins = static::getOptions();
            // оставим только публичные опции
            foreach ($refOptins as $Code=>$Value) {
                $dctOption = static::getOptionInfo($Code);
                if (isset($dctOption['public'])) {
                    if (is_string($dctOption['public'])) { // это ключ 
                        $refOptins[$dctOption['public']] = $Value;
                        unset($refOptins[$Code]);
                    }
                } else unset($refOptins[$Code]);
            }
            $arConfig = [
                    'options' => $refOptins,
                    'env' => [
                            'stage' => defined('APPLICATION_ENV')?APPLICATION_ENV:'production',
                            'timestamp' => time(),
                            'timestamp_request' => $_SERVER['REQUEST_TIME'],
                            'timestamp_client' => round($timestamp),
                            'timestamp_delta' => floor($timestamp-$_SERVER['REQUEST_TIME']), // насколько время клиента, опережает серверное
                            //                                                                              // в секундах
                        ],
                    'bitrix' => [
                            'SITE_TEMPLATE_PATH' => defined('SITE_TEMPLATE_PATH')?SITE_TEMPLATE_PATH:'',
                            'START_EXEC_TIME' => defined('START_EXEC_TIME')?START_EXEC_TIME:'',
                            'SITE_ID' => defined('SITE_ID')?SITE_ID:'',
                        ],
                    'context' => [
                        'siteid' => \Bitrix\Main\Context::getCurrent()->getSite()
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
