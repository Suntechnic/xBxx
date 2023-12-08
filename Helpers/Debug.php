<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    class Debug
    {
        /**
         * Возвращает true если есть ошибки рантайма о которых знает Debug
         */
        public static function hasError (): boolval
        {
            if (
                    defined('APPLICATION_ENV') 
                    && APPLICATION_ENV == 'dev'
                    && $_SERVER['RUNTIME_ERRORS']
                ) return true;
            return false;
        }

        /**
         * устанавливает ошибку рантайма
         * возрващает количество ошибок
         */
        public static function setError ($mixError): int
        {
            if (
                    defined('APPLICATION_ENV') 
                    && APPLICATION_ENV == 'dev'
                ) {
                if (!isset($_SERVER['RUNTIME_ERRORS'])) {
                    $_SERVER['RUNTIME_ERRORS'] = [$mixError];
                } else {
                    $_SERVER['RUNTIME_ERRORS'] = [$mixError];
                }
            }
            return count($_SERVER['RUNTIME_ERRORS']);
        }
    }
}


