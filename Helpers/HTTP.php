<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    /*
    * работа с HTTP
    */
    class HTTP
    {
        

        /**
         * возвращает 404 ошибку
         */
        public static function process404 (string $Page404='/404.php'): void
        {
            if (!defined('ERROR_404')) define('ERROR_404', 'Y');
            \CHTTP::setStatus('404 Not Found');

            global $APPLICATION;
            if ($APPLICATION->RestartWorkarea()) {
				if (!defined('BX_URLREWRITE')) {
					define('BX_URLREWRITE', true);
				}
				\Main\Composite\Engine::setEnable(false);
				require(\Bitrix\Main\Application::getDocumentRoot().$Page404);
				die();
			}
        }
        
    }
}
