<?
declare(strict_types=1);

namespace Bxx\Helpers
{
    /*
    * админский сахар
    */
    class Admin
    {
        
        /**
         * возвращает ссылку на элемент ИБ в админке
         */
        public static function getAdminUrlIBlockElement (int $ID, int $IBLOCK_ID=0): string
        {
            if (!$IBLOCK_ID) $IBLOCK_ID = \Bxx\Helpers\IBlocks::getIdByElementId($ID);
            return '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$IBLOCK_ID.'&type=equipment&ID='.$ID;
        }

        /**
         * возвращает true если выполнение идет в PHP-командной строке
         */
        public static function isAdminCommandLine (): bool
        {
            // @phpstan-ignore-next-line
            return (defined("HELP_FILE") && HELP_FILE == "utilities/php_command_line.php");
        }
        
    }
}
