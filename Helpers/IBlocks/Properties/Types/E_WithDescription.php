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
                //'ConvertToDB' => array(__CLASS__,'ConvertToDB'),
                //'ConvertFromDB' => array(__CLASS__,'ConvertFromDB'),
            ];
    }
    
    
    public static function GetPropertyFieldHtml (
            array $dctProperty, 
            array $dctValue, 
            array $dctHTMLControlName
        ): string
    {
         
        // получение информации по выбранному элементу
        if(intval($dctValue['VALUE']) > 0) {
            $dctElement = \Bitrix\Iblock\ElementTable::getList([
                    'select' => ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'],
                    'filter' => ['ID' => intval($dctValue['VALUE']), 'IBLOCK_ID' => $dctProperty['LINK_IBLOCK_ID']],
                    'limit' => 1
                ])->fetch();
            if ($dctElement['IBLOCK_SECTION_ID']) {
                $dctElement['IBLOCK_SECTION_PATH'] = \Bxx\Helpers\IBlocks\Sections::getPath($dctElement['IBLOCK_SECTION_ID']);
                $dctElement['IBLOCK_SECTION_PATH_VALUE'] = implode('/',array_column($dctElement['IBLOCK_SECTION_PATH'], 'NAME'));
                //$dctElement['IBLOCK_SECTION_NAME'] = end($dctElement['IBLOCK_SECTION_PATH'])['NAME'];
            }
        }
         
        // сама строка с товаром и доп.значениями
        $dctProperty['LINK_IBLOCK_ID'] = (int)$dctProperty['LINK_IBLOCK_ID'];
		$FixIBlock = $dctProperty['LINK_IBLOCK_ID'] > 0;
		$WindowTableId = 'iblockprop-'.\Bitrix\Iblock\PropertyTable::TYPE_ELEMENT.'-'.$dctProperty['ID'].'-'.$dctProperty['LINK_IBLOCK_ID'];
        
        $SearchUrl = (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/').'iblock_element_search.php';
        $SearchUrl.= '?lang='.LANGUAGE_ID.
				'&amp;IBLOCK_ID='.$dctProperty['LINK_IBLOCK_ID'].
				'&amp;n='.urlencode($dctHTMLControlName['VALUE']).
				($FixIBlock ? '&amp;iblockfix=y' : '').
				'&amp;tableId='.$WindowTableId;
                
        if (!is_array($dctElement)){
            $Result = '<input type="text" name="'.htmlspecialcharsbx($dctHTMLControlName["VALUE"]).'" id="'.$dctHTMLControlName["VALUE"].'" value="" size="5">'.
                '<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$SearchUrl.'\', 900, 700);">'.
                '&nbsp;<span id="sp_'.$dctHTMLControlName["VALUE"].'" ></span>';
        } else {
            $Result = '<input type="text" name="'.$dctHTMLControlName["VALUE"].'" id="'.$dctHTMLControlName["VALUE"].'" value="'.$dctValue['VALUE'].'" size="5">'.
                '<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$SearchUrl.'\', 900, 700);">'.
                '&nbsp;<span id="sp_'.$dctHTMLControlName["VALUE"].'" >'.$dctElement['IBLOCK_SECTION_PATH_VALUE'].':'.$dctElement['NAME'].'</span>';
        }
        
        unset($SearchUrl);
    
        $Result.=
        ' : <input type="text" id="desc_'.$dctHTMLControlName["VALUE"].'" name="'.$dctHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialcharsex($dctValue["DESCRIPTION"]).'">';
        return  $Result;
    }
     
    //function GetAdminListViewHTML($dctProperty, $dctValue, $dctHTMLControlName)
    //{
    //    return;
    //}
     
    //function ConvertToDB ($dctProperty, $dctValue)
    //{
    //    return $dctValue; 
    //}
    // 
    //function ConvertFromDB($dctProperty, $dctValue)
    //{
    //    return $dctValue;
    //}
}