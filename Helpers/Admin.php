declare(strict_types=1);

namespace Bxx\Helpers
{
    /*
    * админский сахар
    */
    class Admin
    {
        

        /**
         * очищает массив значений битрикс от муссорных ключей
         */
        public static function isAdminCommandLine (): bool
        {
            return (defined("HELP_FILE") && HELP_FILE == "utilities/php_command_line.php");
        }
        
    }
}
