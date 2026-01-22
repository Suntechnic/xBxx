<?php
declare(strict_types=1);

namespace Bxx\Helpers\IBlocks\Sections
{   /**
    * коллекция модифкаторов для массивов разделов ИБ
    */
    class Modifiers
    {
        
        /**
         * создаем карту
         */
        public static function maper (array $lstSections, array $arParams=[]): array 
        {
            return \Bxx\Helpers\Arrays::maper($lstSections);
        }
        
        /**
         * примитивно обрабатывает файлы-картинки
         */
        public static function illustrator (array &$dctElement): array 
        {
            /**
             * параметры по умолчания
             */
            if ($dctElement['PICTURE'] 
                    && !is_array($dctElement['PICTURE'])
                ) $dctElement['PICTURE'] = \CFile::GetFileArray($dctElement['PICTURE']);
            if ($dctElement['DETAIL_PICTURE']
                    && !is_array($dctElement['DETAIL_PICTURE'])
                ) $dctElement['DETAIL_PICTURE'] = \CFile::GetFileArray($dctElement['DETAIL_PICTURE']);

            
            return $dctElement;
        }

    }

}