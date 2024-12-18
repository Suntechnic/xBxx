<?
// Stringstorage::getInstance()->getStringVal();
/*
Список полей:
UF_XML_ID	Строка
UF_STRING	Строка
UF_NAME	Строка
Могут быть так же добавлены поля вид UF_STRING__{КОД_ЯЗЫКА} для вывода локализованных значений
*/
namespace Bxx
{
    class HLBlock extends \Bxx\Abstraction\HLBModel
    {
        public static function getInstance (string $Code='')
        {
            if ($Code == '') throw new \Bitrix\Main\SystemException('Не указан код hlb');
            return parent::getInstance($Code);
        }

        public static function getInstanceByTable (string $TableName)
        {
            return self::getInstance(\Bxx\Helpers\HLBlocks::getCodeByTable($TableName));
        }
    }
}