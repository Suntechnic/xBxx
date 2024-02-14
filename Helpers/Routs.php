<?
declare(strict_types=1);

namespace Bxx\Helpers
{
    /*
    * хелпер для работы с маршрутами
    */
    class Routs
    {
        /**
         * Возрващает маршрут по имени
         */
        public static function getRoute (string $Name): \Bitrix\Main\Routing\Route
        {
            $router = \Bitrix\Main\Application::getInstance()->getRouter();
            foreach ($router->getRoutes() as $route) {
                if ($optionsRoute->getFullName() == $Name) {
                    return $route
                }
            }
        }
        

        /**
         * Возрващает справочник маршрутов проекта относительно текущей позиции
         */
        public static function getRelativeMap (): array
        {
            $router = \Bitrix\Main\Application::getInstance()->getRouter();
            $app = \Bitrix\Main\Application::getInstance(); 
            $routeCurrent = $app->getCurrentRoute();
            $UriRouteCurrent = $routeCurrent->getUri();
            $ulUriRouteCurrent = explode('/',$UriRouteCurrent);
            
            $refRouteMap = [];
            
            foreach ($router->getRoutes() as $route) {
                $UriRoute = $route->getUri();
                $ulUriRoute = explode('/',$UriRoute);
                foreach ($ulUriRoute as $TokenI=>$Token) {
                    if (isset($ulUriRouteCurrent[$TokenI])) {
                        if ($Token == $ulUriRouteCurrent[$TokenI]) {
                            $refRouteMap[$TokenI][] = [
                                    'route' => $route,
                                    'Uri' => $UriRoute
                                ];
                        } else break;
                    } else break;
                }
            }
            return $refRouteMap;
        }

        /**
         * Возрващает справочник маршрутов проекта относительно текущей позиции
         */
        public static function isCurrent (\Bitrix\Main\Routing\Route $route): bool
        {
            $optionsRoute = $route->getOptions();
            //if ($optionsRoute->hasName()) {
                $router = \Bitrix\Main\Application::getInstance()->getRouter();
                $app = \Bitrix\Main\Application::getInstance(); 
                $routeCurrent = $app->getCurrentRoute();
                if ($route->getUri() == $routeCurrent->getUri()) {
                    // $optionsRouteCurrent = $routeCurrent->getOptions();
                    // if ($optionsRoute->getFullName() == $optionsRouteCurrent->getFullName()) 
                            return true;
                }
            //}
            return false;
        }
    }
}
