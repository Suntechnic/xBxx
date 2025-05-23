<?php
declare(strict_types=1);

namespace Bxx\Helpers\IBlocks\Elements
{   /**
    * коллекция модифкаторов для массивов элементов инфоблоков
    */
    class Modifiers
    {
        
        /**
         * добавляет наследуюемые свойства
         */
        public static function inheriter (array &$dctElement, string $Key='SEO'): array 
        {
            if ($dctElement['IBLOCK_ID'] && $dctElement['ID']) {
                $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\ElementValues(
                        $dctElement['IBLOCK_ID'], 
                        $dctElement['ID']
                    );
                $dctElement[$Key] = $ipropSectionValues->getValues();
            }
            return $dctElement;
        }

        /**
         * примитивно обрабатывает файлы-картинки
         */
        public static function illustrator (array &$dctElement, array $arParams=[]): array 
        {
            /**
             * параметры по умолчания
             */
            if ($dctElement['PREVIEW_PICTURE'] 
                    && !is_array($dctElement['PREVIEW_PICTURE'])
                ) $dctElement['PREVIEW_PICTURE'] = \CFile::GetFileArray($dctElement['PREVIEW_PICTURE']);
            if ($dctElement['DETAIL_PICTURE']
                    && !is_array($dctElement['DETAIL_PICTURE'])
                ) $dctElement['DETAIL_PICTURE'] = \CFile::GetFileArray($dctElement['DETAIL_PICTURE']);
            
            return $dctElement;
        }
        
        /**
         * датирует элемент на основе данных в массиве
         */
        public static function dater (array &$dctElement, array $arParams=[]): array 
        {
            /**
             * параметры по умолчания
             */
            if (!$arParams['format']) $arParams['format'] = 'j F Y';


            if ($dctElement['DATE_ACTIVE_FROM']) {
                $Date = $dctElement['DATE_ACTIVE_FROM'];
            } elseif ($dctElement['TIMESTAMP_X']) {
                $Date = $dctElement['DATE_ACTIVE_FROM'];
            } else {
                return $dctElement;
            }

            if (is_a($Date,'\Bitrix\Main\Type\DateTime')) {
                $datetime = $Date;
            } else {
                $datetime = new \Bitrix\Main\Type\DateTime($Date);
            }

            $dctElement['X_DATE'] = $datetime;
            $dctElement['X_DATE_FORMATED'] = \FormatDate($arParams['format'],$datetime->getTimestamp());
            
            return $dctElement;
        }

    }

}