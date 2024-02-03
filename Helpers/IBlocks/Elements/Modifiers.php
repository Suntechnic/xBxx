<?php
declare(strict_types=1);

namespace Bxx\Helpers\IBlocks\Elements
{   /**
    * коллекция модифкаторов для массивов элементов инфоблоков
    */
    class Modifiers
    {
        
        /**
         * примитивно обрабатывает файлы-картинки
         */
        public static function illustrator (array &$dctElement, array $arParams=[]): array 
        {
            /**
             * параметры по умолчания
             */
            if ($dctElement['PREVIEW_PICTURE']) $dctElement['PREVIEW_PICTURE'] = \CFile::GetFileArray($dctElement['PREVIEW_PICTURE']);
            if ($dctElement['DETAIL_PICTURE']) $dctElement['DETAIL_PICTURE'] = \CFile::GetFileArray($dctElement['DETAIL_PICTURE']);
            
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