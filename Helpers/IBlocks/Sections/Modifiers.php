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

    }

}