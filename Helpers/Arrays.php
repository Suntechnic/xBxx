<?php
declare(strict_types=1);

namespace Bxx\Helpers
{
    /*
    * массивный саха
    */
    class Arrays
    {
        

        /**
         * очищает массив значений битрикс от муссорных ключей
         */
        public static function clean (array &$ar): array
        {
            $ar = array_filter($ar,function ($Key) {
                    return substr($Key,0,1) != '~';
                }, ARRAY_FILTER_USE_KEY);
            $ar = array_filter($ar,function ($Key) {
                    return substr($Key,-9) != '_VALUE_ID';
                }, ARRAY_FILTER_USE_KEY);
            return $ar;
        }


        /**
         * создает карту масиива массивов
         */
        public static function maper (array $ar, string $Key='ID'): array
        {
            return array_combine(array_column($ar,$Key),array_keys($ar));
        }

        /**
         * переносит значения из ключей массива $ar
         * которые есть как ключи в массиве $map
         * в ключи которые являеются значениями $map
         * Например для создания справочника по XML_ID
         * 
         \Bxx\Helpers\Arrays::transformator(
                $lst,
                array_column($lst,'XML_ID')
            );
         * 
         */
        public static function transformator (array $ar, array $map): array
        {
            $newAr = [];
            foreach ($map as $KeySource=>$KeyTarget) {
                $newAr[$KeyTarget] = $ar[$KeySource];
            }
            return $newAr;
        }

        /**
         * то же что и transformator, но для списка
         * 
         */
        public static function listTransformator (array $ar, array $map): array
        {
            $newAr = [];
            foreach ($ar as $I=>$arItem) {
                $newAr[$I] = self::transformator($arItem,$map);
            }
            return $newAr;
        }
        
    }
}
