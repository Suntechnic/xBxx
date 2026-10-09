<?

namespace Bxx\Helpers\IBlocks\Properties\Types;

/**
 * 
 * Свойство типа "Список другого инфоблока"
 * Имеет две настройки: ID инфоблока и ID свойства
 * Для пользователя в админке выводится как битриксовский список, 
 * но в качестве значений списка используются элементы списка привязанного свойства другого инфоблока
 * таким образом реализуется разделяемый список значений для разных инфоблоков
 *  
 * 
\RegisterModuleDependences(
        'iblock',
        'OnIBlockPropertyBuildList',
        '.app',
        '\Bxx\Helpers\IBlocks\Properties\Types\L_Alien',
        'GetIBlockPropertyDescription'
    );

\AddEventHandler(
        'iblock', 
        'OnIBlockPropertyBuildList', 
        ['\Bxx\Helpers\IBlocks\Properties\Types\L_Alien', 'GetIBlockPropertyDescription']
    );
 */

class L_Alien 
{
    public static function GetIBlockPropertyDescription ()
    {
        return [
                'PROPERTY_TYPE' => 'N',
                'USER_TYPE' => 'L:Alien',
                'DESCRIPTION' => 'Список другого инфоблока',
                'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
                'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
                'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
                'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
                'ConvertToDB' => array(__CLASS__,'ConvertToDB'),
                'ConvertFromDB' => array(__CLASS__,'ConvertFromDB'),
            ];
    }

    /**
     * Возвращает варианты списка из свойства типа L другого инфоблока
     * @param int $IBlockID - ID инфоблока источника
     * @param int $PropertyID - ID свойства типа L источника
     * @return array
     */
    private static function getListReference (
            int $IBlockID,
            int $PropertyID
        ): array
    {
        if ($IBlockID <= 0 || $PropertyID <= 0) {
            return [];
        }

        $dctProperty = \Bitrix\Iblock\PropertyTable::getRow([
                'select' => ['ID'],
                'filter' => [
                        '=ID' => $PropertyID,
                        '=IBLOCK_ID' => $IBlockID,
                        '=PROPERTY_TYPE' => 'L'
                    ]
            ]);
        if (!$dctProperty) {
            return [];
        }

        $lstEnum = \Bitrix\Iblock\PropertyEnumerationTable::getList([
                'select' => ['ID', 'VALUE', 'SORT'],
                'order' => ['SORT' => 'ASC', 'VALUE' => 'ASC'],
                'filter' => ['=PROPERTY_ID' => $PropertyID]
            ])->fetchAll();

        return \Bxx\Helpers\Arrays::referencer($lstEnum, 'ID');
    }


    /**
     * Возвращает HTML-код для полей множественного свойства
     * Этот метод нужен для группировки и изменнения порядка элементов
     * а также сокращения количества запросов к базе
     * @param array $dctProperty - описание свойства
     * @param array $refValue - справочник значений свойства
     * @param array $dctHTMLControlNamePrefix - массив с именами контролов
     * @return string
     */
    public static function GetPropertyFieldHtmlMulty (
            array $dctProperty, 
            array $refValue, 
            array $dctHTMLControlNamePrefix
        ): string
    {
        $Result = '';

        $dctHTMLControlName = $dctHTMLControlNamePrefix;

        foreach ($refValue as $K => $dctValue) {
            $dctHTMLControlName['VALUE'] = $dctHTMLControlNamePrefix['VALUE'].'['.$K.'][VALUE]';
            $dctHTMLControlName['DESCRIPTION'] = $dctHTMLControlNamePrefix['VALUE'].'['.$K.'][DESCRIPTION]';
            $Result .= static::GetPropertyFieldHtml(
                    $dctProperty,
                    $dctValue,
                    $dctHTMLControlName
                );
            $Result .= '<br>';
        }

        for ($K = 1; $K <= $dctProperty['MULTIPLE_CNT']; $K++) {
            $dctHTMLControlName['VALUE'] = $dctHTMLControlNamePrefix['VALUE'].'[n'.$K.'][VALUE]';
            $dctHTMLControlName['DESCRIPTION'] = $dctHTMLControlNamePrefix['VALUE'].'[n'.$K.'][DESCRIPTION]';
            $Result .= static::GetPropertyFieldHtml(
                    $dctProperty,
                    [],
                    $dctHTMLControlName
                );
            $Result .= '<br>';
        }

        return $Result;
    }

    /**
     * Возвращает HTML-код для поля свойства
     * @param array $dctProperty - описание свойства
     * @param array $dctValue - значения свойства
     * @param array $dctHTMLControlName - массив с именами контролов
     * @return string
     */
    public static function GetPropertyFieldHtml (
            array $dctProperty, 
            array $dctValue, 
            array $dctHTMLControlName,
            array $dctElement=[]
        ): string
    {
        $Result = '';

        $dctValue = static::ConvertFromDB($dctProperty, $dctValue);
        $Value = (int)$dctValue['VALUE'];

        $dctSettings = static::PrepareSettings($dctProperty);
        $refEnum = static::getListReference((int)$dctSettings['IBLOCK_ID'], (int)$dctSettings['PROPERTY_ID']);

        $ControlName = htmlspecialcharsbx($dctHTMLControlName['VALUE']);
        $ControlId = htmlspecialcharsbx($dctHTMLControlName['VALUE']);

        $Result .= '<select name="'.$ControlName.'" id="'.$ControlId.'">';
        $Result .= '<option value="">(не выбрано)</option>';

        foreach ($refEnum as $EnumID => $dctEnum) {
            $Selected = ((int)$EnumID === $Value ? ' selected' : '');
            $Result .= '<option value="'.(int)$EnumID.'"'.$Selected.'>'.htmlspecialcharsex($dctEnum['VALUE']).'</option>';
        }

        if ($Value > 0 && !isset($refEnum[$Value])) {
            $Result .= '<option value="'.$Value.'" selected>[#'.$Value.']</option>';
        }

        $Result .= '</select>';

        if (!$refEnum) {
            $Result .= '<br><small>Не найдены варианты списка. Проверьте настройки свойства: ID инфоблока и ID свойства типа "Список".</small>';
        }

        return $Result;
    }
    
    
    public static function ConvertToDB (
            array $dctProperty, 
            array $dctValue
        ): array
    {
        $Value = (int)$dctValue['VALUE'];
        if ($Value <= 0) {
            $dctValue['VALUE'] = '';
            $dctValue['DESCRIPTION'] = '';
            return $dctValue;
        }

        $dctValue['VALUE'] = $Value;

        if ($dctValue['DESCRIPTION']) {
            $dctValue['DESCRIPTION'] = serialize($dctValue['DESCRIPTION']);
        } else {
            $dctValue['DESCRIPTION'] = '';
        }
        return $dctValue; 
    }
    
    public static function ConvertFromDB (
            array $dctProperty, 
            array $dctValue
        ): array
    {
        if ($dctValue['DESCRIPTION']) {
            if (CheckSerializedData($dctValue['DESCRIPTION'])) {
                $dctValue['DESCRIPTION'] = unserialize($dctValue['DESCRIPTION']);
            }
        } else {
            $dctValue['DESCRIPTION'] = '';
        }

        $dctValue['VALUE'] = (int)$dctValue['VALUE'];
        if ($dctValue['VALUE'] <= 0) {
            $dctValue['VALUE'] = '';
        }
        
        return $dctValue;
    }

    /**
     * Возвращает HTML-код для формы редактирования настроек свойства:
     * ID инфоблока и ID свойства типа "Список" привязанного инфоблока
     * @param array $dctProperty - описание свойства
     * @param array $dctHTMLControlName - массив с именами контролов
     * @param array &$dctPropertyFields - массив с полями свойства
     * @return string
     */
    public static function GetSettingsHTML(
            array $dctProperty, 
            array $dctHTMLControlName, 
            array &$dctPropertyFields
        ): string
    {
        $dctPropertyFields = [
                'SET' => ['WITH_DESCRIPTION' => 'N']
            ];

        $dctSettings = static::PrepareSettings($dctProperty);
        if (isset($dctSettings['USER_TYPE_SETTINGS'])) {
            $dctSettings = $dctSettings['USER_TYPE_SETTINGS'];
        }

        $Result = '';
        $Result .=
        '<tr>
            <td>ID инфоблока-источника:</td>
            <td><input
                    type="text"
                    name="'.$dctHTMLControlName['NAME'].'[IBLOCK_ID]"
                    value="'.(int)$dctSettings['IBLOCK_ID'].'"
                ></td>
        </tr>';

        $Result .=
        '<tr>
            <td>ID свойства типа "Список" (L):</td>
            <td><input
                    type="text"
                    name="'.$dctHTMLControlName['NAME'].'[PROPERTY_ID]"
                    value="'.(int)$dctSettings['PROPERTY_ID'].'"
                ><br><small>Значением этого свойства будет ID элемента списка выбранного свойства.</small></td>
        </tr>';

        return $Result;
    }


    public static function PrepareSettings (array $dctProperty): array
    {
        $IBlockID = (int)($dctProperty['USER_TYPE_SETTINGS']['IBLOCK_ID'] ?? 0);
        if ($IBlockID < 0) {
            $IBlockID = 0;
        }

        $PropertyID = (int)($dctProperty['USER_TYPE_SETTINGS']['PROPERTY_ID'] ?? 0);
        if ($PropertyID < 0) {
            $PropertyID = 0;
        }

        return [
                'IBLOCK_ID' => $IBlockID,
                'PROPERTY_ID' => $PropertyID
            ];
    }
}
