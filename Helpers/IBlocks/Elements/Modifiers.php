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

            if ($arParams['GALLERY']) { // если в параметрах указаны коды свойств-галерей
                $lstGalleryProps = $arParams['GALLERY'];
                if (!is_array($lstGalleryProps)) $lstGalleryProps = [$lstGalleryProps];

                // здесь коды свойств-галерей массив
                foreach ($lstGalleryProps as $PropCode) {
                    // проверяем битрикс-компонентную схему
                    if ($dctElement['PROPERTIES'][$PropCode]['VALUE']) {
                        $dctElement['PROPERTIES'][$PropCode]['FILES'] = [];
                        foreach ($dctElement['PROPERTIES'][$PropCode]['VALUE'] as $FileID) {
                            $dctElement['PROPERTIES'][$PropCode]['FILES'][] = \CFile::GetFileArray($FileID);
                        }
                    } elseif ($dctElement['PROPERTY_'.$PropCode.'_VALUE']) {
                        // проверяем прямую схему
                        $dctElement['PROPERTY_'.$PropCode.'_FILES'] = [];
                        foreach ($dctElement['PROPERTY_'.$PropCode.'_VALUE'] as $FileID) {
                            $dctElement['PROPERTY_'.$PropCode.'_FILES'][] = \CFile::GetFileArray($FileID);
                        }   
                    }
                }
            }

            if ($arParams['MAIN'] && is_string($arParams['MAIN'])) { // если в параметрах указан код в который установить главное фото
                $MainPhotoCode = $arParams['MAIN'];
                if ($dctElement['DETAIL_PICTURE']) {
                    $dctElement[$MainPhotoCode] = $dctElement['DETAIL_PICTURE'];
                } elseif ($dctElement['PREVIEW_PICTURE']) {
                    $dctElement[$MainPhotoCode] = $dctElement['PREVIEW_PICTURE'];
                } elseif ($lstGalleryProps) { // ни одного фото нет, пробуем взять из галерей
                    foreach ($lstGalleryProps as $PropCode) {
                        if ($dctElement['PROPERTIES'][$PropCode]['FILES']
                                && count($dctElement['PROPERTIES'][$PropCode]['FILES'])>0
                            ) {
                            $dctElement[$MainPhotoCode] = $dctElement['PROPERTIES'][$PropCode]['FILES'][0];
                            break;
                        } elseif ($dctElement['PROPERTY_'.$PropCode.'_FILES']
                                && count($dctElement['PROPERTY_'.$PropCode.'_FILES'])>0
                            ) {
                            $dctElement[$MainPhotoCode] = $dctElement['PROPERTY_'.$PropCode.'_FILES'][0];
                            break;
                        }
                    }
                }
                
            }
            
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