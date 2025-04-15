<?
namespace Bxx
{
    class Core
    {

        private static $instance = false;
        /**
         * загружает ядро битрикс
         * \Bxx\Core::init();
         */
        public static function init (array $lstModules=[])
        {
            if (!self::$instance) {
                self::$instance = new self();
            }

            foreach ($lstModules as $ModuleName) {
                if (!\Bitrix\Main\Loader::includeModule($ModuleName)) {
                    throw new \Bitrix\Main\SystemException('не найден модуль '.$ModuleName);
                }
            }
            
            return self::$instance;
        }
        
        public static function destroy ()
        {
            if (!self::$instance) {
                throw new \Bitrix\Main\SystemException('ядро не инициализировано');
            }

            require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
            self::$instance = false;
            return false;
        }

        //
        protected function __construct ()
        {
            // загружено ли ядро Bitrix
            if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { // @phpstan-ignore-line
                if (!$_SERVER['DOCUMENT_ROOT']) { // нет документ рут - запуск в коносоле?
                    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../..');

                    define('NO_KEEP_STATISTIC', true);
                    define('NOT_CHECK_PERMISSIONS', true);
                    define('BX_BUFFER_USED', true);
                }
                require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
            }
        }
    }
}