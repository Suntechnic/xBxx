<?

namespace Bxx\Helpers\IBlocks\Properties\Types;

/**
\RegisterModuleDependences(
        'iblock',
        'OnIBlockPropertyBuildList',
        '.app',
        '\Bxx\Helpers\IBlocks\Properties\Types\E_WithDescription',
        'GetIBlockPropertyDescription'
    );

\AddEventHandler(
        'iblock', 
        'OnIBlockPropertyBuildList', 
        ['\Bxx\Helpers\IBlocks\Properties\Types\E_WithDescription', 'GetIBlockPropertyDescription']
    );
 */

class E_WithDescription 
{
    public static function GetIBlockPropertyDescription ()
    {
        return [
                'PROPERTY_TYPE' => 'E',
                'USER_TYPE' => 'E:WithDescription',
                'DESCRIPTION' => 'Привязка к элементам с описанием',
                'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
                'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
                'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
                'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
                'ConvertToDB' => array(__CLASS__,'ConvertToDB'),
                'ConvertFromDB' => array(__CLASS__,'ConvertFromDB'),
            ];
    }

    /**
     * возвращает справочник элементов
     * по списку ID
     * @param array $lstElementID - список ID элементов
     * @param int $IBlockID - ID инфоблока
     * @return array
     */
    private static function getElementReference (
            array $lstElementID,
            int $IBlockID=0
        ): array
    {
        $dctFilter = ['ID' => $lstElementID];
        if ($IBlockID) {
            $dctFilter['IBLOCK_ID'] = $IBlockID;
        }
        $lstElement = \Bitrix\Iblock\ElementTable::getList([
                'select' => ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID', 'SORT'],
                'filter' => $dctFilter
            ])->fetchAll();
        $refSectionPath = [];
        foreach ($lstElement as $I=>$dctElement) {
            if ($dctElement['IBLOCK_SECTION_ID']) {
                if (!isset($refSectionPath[$dctElement['IBLOCK_SECTION_ID']])) {
                    $refSectionPath[$dctElement['IBLOCK_SECTION_ID']] = \Bxx\Helpers\IBlocks\Sections::getPath($dctElement['IBLOCK_SECTION_ID']);
                }

                $dctElement['IBLOCK_SECTION_PATH'] = $refSectionPath[$dctElement['IBLOCK_SECTION_ID']];
                $dctElement['IBLOCK_SECTION_PATH_VALUE'] = implode('/',array_column($dctElement['IBLOCK_SECTION_PATH'], 'NAME'));
                //$dctElement['IBLOCK_SECTION_NAME'] = end($dctElement['IBLOCK_SECTION_PATH'])['NAME'];

                $lstElement[$I] = $dctElement;
            }
        }


        return \Bxx\Helpers\Arrays::referencer($lstElement, 'ID');
    }

    /**
     * Возвращает HTML-код для полей множественного свойства
     * Этот метод нужен для группировки и изменнения порядка элементов
     * а также сокращения количества запросов к базе
     * @param array $dctProperty - описание свойства
     * @param array $refValue - справочник значений свойства
     * @param array $dctHTMLControlName - массив с именами контролов
     * @return string
     */
    public static function GetPropertyFieldHtmlMulty (
            array $dctProperty, 
            array $refValue, 
            array $dctHTMLControlNamePrefix
        ): string
    {
        $Result = '';
        // $Result.= '<pre>'.print_r($dctProperty, true).'</pre>';
        // $Result.= '<pre>'.print_r($refValue, true).'</pre>';

        if (count($refValue)) {
            $lstElementID = array_column($refValue, 'VALUE');
            $refElement = static::getElementReference($lstElementID, $dctProperty['LINK_IBLOCK_ID']);

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // сортировка и группировка элементов
            if ($dctProperty['USER_TYPE_SETTINGS']['SORTING'] == 'Y' || $dctProperty['USER_TYPE_SETTINGS']['GROUPING'] == 'Y') {
                $lstKeys = array_keys($refValue);

                $Grouping = $dctProperty['USER_TYPE_SETTINGS']['GROUPING'] == 'Y';
                $Sorting = $dctProperty['USER_TYPE_SETTINGS']['SORTING'] == 'Y';

                if ($Grouping) {
                    // добавим к индексу сортировки, еще и сортировк раздела

                    // справочник сортировки разделов
                    $lstSectionID = array_unique(array_column($refElement, 'IBLOCK_SECTION_ID'));
                    $lstSection = \Bitrix\Iblock\SectionTable::getList([
                            'select' => ['ID', 'SORT'],
                            'filter' => ['ID' => $lstSectionID],
                            'cache' => 3599
                        ])->fetchAll();
                    $refSection = \Bxx\Helpers\Arrays::referencer($lstSection, 'ID');
                    array_walk($refSection, function(&$dctSection) { // добавление id необходимо, чтобы каждой секции присвоить свой порядковый номер
                            $dctSection = $dctSection['SORT'].':'.$dctSection['ID'];
                        });

                    foreach ($refElement as $ElementId=>$dctElement) {
                        $SORT = $refSection[$dctElement['IBLOCK_SECTION_ID']] ?? 0;
                        if ($Sorting) $SORT.= ':'.$dctElement['SORT']; // сохраним исходный индекс сортировки, если необходимо сортировать элементы
                        $dctElement['SORT'] = $SORT;

                        $refElement[$ElementId] = $dctElement;
                    }
                }
                // в случае если группировка не нужна, то индекс сортироки точно нужен, а он уже такой как нам надо

                // сортировка элементов
                usort($refValue, function($a, $b) use ($refElement) {
                        if (!isset($refElement[$a['VALUE']]) || !isset($refElement[$b['VALUE']])) return 0;
                        return $refElement[$a['VALUE']]['SORT'] <=> $refElement[$b['VALUE']]['SORT'];
                    });


                $refValue = array_combine($lstKeys, $refValue);

                //$Result.= '<pre>'.print_r($refValue, true).'</pre>';
            }
            // сортировка и группировка элементов
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // вызов метода для единичного поля
        $dctHTMLControlName = $dctHTMLControlNamePrefix;
        // существующие значения
        $PreviuosSection = '';
        foreach ($refValue as $K=>$dctValue) {

        
            $dctHTMLControlName['VALUE'] = $dctHTMLControlNamePrefix['VALUE'].'['.$K.'][VALUE]';
            $dctHTMLControlName['DESCRIPTION'] = $dctHTMLControlNamePrefix['VALUE'].'['.$K.'][DESCRIPTION]';

            $dctElement = $refElement[$dctValue['VALUE']] ?? [];

            // хак вывода заголовка при группировке
            if ($dctProperty['USER_TYPE_SETTINGS']['GROUPING'] == 'Y') {
                if ($PreviuosSection != $dctElement['IBLOCK_SECTION_ID']) {
                    $PreviuosSection = $dctElement['IBLOCK_SECTION_ID'];
                    $Result.= '<br><h2>'.$dctElement['IBLOCK_SECTION_PATH_VALUE'].'</h2>';
                }
            }



            $Result.= static::GetPropertyFieldHtml(
                    $dctProperty, 
                    $dctValue, 
                    $dctHTMLControlName,
                    $dctElement
                );
            $Result.= '<br>';
        }

        // поля под новые значения
        for ($K=1; $K<=$dctProperty['MULTIPLE_CNT']; $K++) {
            $dctHTMLControlName['VALUE'] = $dctHTMLControlNamePrefix['VALUE'].'[n'.$K.'][VALUE]';
            $dctHTMLControlName['DESCRIPTION'] = $dctHTMLControlNamePrefix['VALUE'].'[n'.$K.'][DESCRIPTION]';
            $Result.= static::GetPropertyFieldHtml(
                    $dctProperty, 
                    [], 
                    $dctHTMLControlName
                );
            $Result.= '<br>';
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
        //$Result.= '<pre>'.print_r($dctHTMLControlName, true).'</pre>';

        $dctValue = static::ConvertFromDB($dctProperty, $dctValue);

        $dctProperty['LINK_IBLOCK_ID'] = (int)$dctProperty['LINK_IBLOCK_ID'];
        $FixIBlock = $dctProperty['LINK_IBLOCK_ID'] > 0;
         
        // получение информации по выбранному элементу
        if(intval($dctValue['VALUE']) > 0 && !$dctElement) {
            $dctElement = static::getElementReference([$dctValue['VALUE']], $dctProperty['LINK_IBLOCK_ID'])[$dctValue['VALUE']];
        }

        if ($dctElement) {
            if ($dctProperty['USER_TYPE_SETTINGS']['SHOW_PATH'] == 'Y') {
                $DescriptionTitle = $dctElement['IBLOCK_SECTION_PATH_VALUE'].':'.$dctElement['NAME'];
            } else {
                $DescriptionTitle = $dctElement['NAME'];
            }
            $Value = $dctElement['ID'];
            unset($dctElement);
        } else {
            $Value = '';
            $DescriptionTitle = '';
        }

        
		$WindowTableId = 'iblockprop-'.\Bitrix\Iblock\PropertyTable::TYPE_ELEMENT.'-'.$dctProperty['ID'].'-'.$dctProperty['LINK_IBLOCK_ID'];
            
        $SearchUrl = (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/').'iblock_element_search.php';
        $SearchUrl.= '?lang='.LANGUAGE_ID.
				'&amp;IBLOCK_ID='.$dctProperty['LINK_IBLOCK_ID'].
				'&amp;n='.urlencode($dctHTMLControlName['VALUE']).
				($FixIBlock ? '&amp;iblockfix=y' : '').
				'&amp;tableId='.$WindowTableId;
        
       
        $Result.= '<input type="text" 
                    name="'.htmlspecialcharsbx($dctHTMLControlName['VALUE']).'" 
                    id="'.$dctHTMLControlName['VALUE'].'" 
                    value="'.$Value.'" 
                    size="5"
                >
                <input type="button" 
                        value="..." 
                        onClick="jsUtils.OpenWindow(\''.$SearchUrl.'\', 900, 700);"
                    >&nbsp;<span id="sp_'.$dctHTMLControlName['VALUE'].'" >'.$DescriptionTitle.'</span>';
        
        unset($SearchUrl);

        if ($dctProperty['USER_TYPE_SETTINGS']['KEYS']) {
            $lstKeys = explode(',', $dctProperty['USER_TYPE_SETTINGS']['KEYS']);
            if (is_array($dctValue['DESCRIPTION'])) {
                $dctValue['DESCRIPTION'] = array_intersect_key($dctValue['DESCRIPTION'], array_flip($lstKeys));
            } else {
                $dctValue['DESCRIPTION'] = [];
            }
            $Result.= '<table>';
            if ($dctProperty['USER_TYPE_SETTINGS']['TITLE']) {
                $Result.= '<tr><td colspan="2"><b>'.$dctProperty['USER_TYPE_SETTINGS']['TITLE'].'</b></td></tr>';
            }
            foreach ($lstKeys as $Key) {
                $Result.= '<tr><td><label for="desc_'.$dctHTMLControlName['VALUE'].'">'.$Key.'</label></td><td><input 
                        type="text" 
                        id="desc_'.$dctHTMLControlName['VALUE'].'_'.$Key.'" 
                        name="'.$dctHTMLControlName['DESCRIPTION'].'['.$Key.']" 
                        value="'.htmlspecialcharsex($dctValue['DESCRIPTION'][$Key]).'"
                    ></td></tr>';
            }
            $Result.= '</table>';
        } else {
            if ($dctProperty['USER_TYPE_SETTINGS']['TITLE']) {
                $Result.= '<label for="desc_'.$dctHTMLControlName['VALUE'].'">'.$dctProperty['USER_TYPE_SETTINGS']['TITLE'].'</label>';
            }
            $Result.= '<input 
                    type="text" 
                    id="desc_'.$dctHTMLControlName['VALUE'].'" 
                    name="'.$dctHTMLControlName['DESCRIPTION'].'" 
                    value="'.htmlspecialcharsex($dctValue['DESCRIPTION']).'"
                >';
        }
        


        return  $Result;
    }
     
    //function GetAdminListViewHTML($dctProperty, $dctValue, $dctHTMLControlName)
    //{
    //    return;
    //}
     
    public static function ConvertToDB (
            array $dctProperty, 
            array $dctValue
        ): array
    {
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
            if (CheckSerializedData($dctValue["VALUE"])) {
                $dctValue['DESCRIPTION'] = unserialize($dctValue['DESCRIPTION']);
            }
        } else {
            $dctValue['DESCRIPTION'] = '';
        }
        $dctValue['VALUE'] = (int)$dctValue['VALUE'];
        return $dctValue;
    }


    public static function GetSettingsHTML(
            array $dctProperty, 
            array $dctHTMLControlName, 
            array &$dctPropertyFields
        ): string
	{
		$dctPropertyFields = [
                'SET' => ['WITH_DESCRIPTION' => 'Y'],
                //'USER_TYPE_SETTINGS_TITLE' => 'Отображение полей в форме редактирования'
            ];


        $dctSettings = static::PrepareSettings($dctProperty);
		if (isset($dctSettings['USER_TYPE_SETTINGS'])) {
			$dctSettings = $dctSettings['USER_TYPE_SETTINGS'];
		}

        $Grouping = is_array($dctProperty['USER_TYPE_SETTINGS']) && $dctProperty['USER_TYPE_SETTINGS']['GROUPING'];
        $ShowPath = is_array($dctProperty['USER_TYPE_SETTINGS']) && $dctProperty['USER_TYPE_SETTINGS']['SHOW_PATH'];

        $Result = '';
        $Result.= 
        '<tr>
            <td>Группировать элементы по разделам:</td>
            <td>'.InputType(
                    'checkbox',
                    $dctHTMLControlName['NAME'].'[GROUPING]',
                    'Y',
                    htmlspecialcharsbx($dctSettings['GROUPING'])
                ).'</td>
		</tr>';

        $Result.= 
        '<tr>
            <td>Менять порядок в соотвествии с сортировкой элементов:</td>
            <td>'.InputType(
                    'checkbox',
                    $dctHTMLControlName['NAME'].'[SORTING]',
                    'Y',
                    htmlspecialcharsbx($dctSettings['SORTING'])
                ).'</td>
		</tr>';

        $Result.= 
        '<tr>
            <td>Выводить путь по разделам к элементу:</td>
            <td>'.InputType(
                    'checkbox',
                    $dctHTMLControlName['NAME'].'[SHOW_PATH]',
                    'Y',
                    htmlspecialcharsbx($dctSettings['SHOW_PATH'])
                ).'</td>
        </tr>';

        $Result.= 
        '<tr>
            <td>Список ключей (через запятую):</td>
            <td><input 
                    type="text" 
                    name="'.$dctHTMLControlName["NAME"].'[KEYS]" 
                    value="'.$dctSettings['KEYS'].'"
                ><br> <small>в случае если необходим хранить несколько значений описания</small></td>
        </tr>';

        $Result.= 
        '<tr>
            <td>Заголовок описания:</td>
            <td><input 
                    type="text" 
                    name="'.$dctHTMLControlName["NAME"].'[TITLE]" 
                    value="'.$dctSettings['TITLE'].'"
                ></td>
        </tr>';
        
        //$Result.= '<pre>'.print_r($dctSettings, true).'</pre>';
        return $Result;
	}


    public static function PrepareSettings (array $dctProperty): array
	{
        $Grouping = ($dctProperty['USER_TYPE_SETTINGS']['GROUPING'] ?? 'N');
        $Grouping = ($Grouping == 'Y' ? 'Y' : 'N');

        $Sorting = ($dctProperty['USER_TYPE_SETTINGS']['SORTING'] ?? 'N');
        $Sorting = ($Sorting == 'Y' ? 'Y' : 'N');

        $ShowPath = ($dctProperty['USER_TYPE_SETTINGS']['SHOW_PATH'] ?? 'N');
		$ShowPath = ($ShowPath == 'Y' ? 'Y' : 'N');

        $Keys = ($dctProperty['USER_TYPE_SETTINGS']['KEYS'] ?? '');
        if (strlen($Keys)) $Keys = preg_replace('/\s*,\s*/', ',', $Keys);

        $Title = ($dctProperty['USER_TYPE_SETTINGS']['TITLE'] ?? '');
		
        return [
                'GROUPING' => $Grouping,
                'SORTING' => $Sorting,
                'SHOW_PATH' => $ShowPath,
                'KEYS' => $Keys,
                'TITLE' => $Title
            ];
	}
}