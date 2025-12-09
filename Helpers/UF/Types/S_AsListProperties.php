<?

namespace Bxx\Helpers\UF\Types;

/**
\RegisterModuleDependences(
        'main',
        'OnUserTypeBuildList',
        '.app',
        '\Bxx\Helpers\UF\Types\S_AsListProperties',
        'GetIBlockPropertyDescription'
    );

\AddEventHandler(
        'main', 
        'OnUserTypeBuildList', 
        ['\Bxx\Helpers\UF\Types\S_AsListProperties', 'GetIBlockPropertyDescription']
    );
 */

class S_AsListProperties 
{
    public static function GetIBlockPropertyDescription ()
    {
        return [
                // уникальный идентификатор
                'USER_TYPE_ID' => 'bxx_s_aslistproperties',
                // имя класса, методы которого формируют поведение типа
                'CLASS_NAME' => __CLASS__,
                // название для показа в списке типов пользовательских свойств
                'DESCRIPTION' => 'Список кодов свойств инфоблока',
                // базовый тип на котором будут основаны операции фильтра
                'BASE_TYPE' => 'int',
            ];
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        
        ob_start();
        \Kint::dump($arUserField,$arHtmlControl);
        $b=ob_get_clean();
        return 'чичара';
   }
}