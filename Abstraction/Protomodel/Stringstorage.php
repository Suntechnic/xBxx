<?
// Stringstorage::getInstance()->getStringVal();
/*
Список полей:
UF_XML_ID	Строка
UF_STRING	Строка
UF_NAME	Строка
Могут быть так же добавлены поля вид UF_STRING__{КОД_ЯЗЫКА} для вывода локализованных значений
*/
namespace Bxx\Abstraction\Protomodel {
    class Stringstorage extends \Bxx\Abstraction\HLBModel {
        
        public static function getInstance(string $Code = 'Stringstorage') {
            return parent::getInstance($Code);
        }
        
        protected $LANGUAGE_UID;
        protected function __construct(string $Code) {
            $this->LANGUAGE_UID = strtoupper(LANGUAGE_ID);
            return parent::__construct($Code);
        }
        
        // возвращает Значение строки по коду
        public function getStringVal (
                $xml_id,
                $search=false,
                $replace=''
            ) {

            $arElement = $this->getElement(['filter'=>['UF_XML_ID'=>$xml_id]]);
            
            if ($arElement['UF_STRING__'.$this->LANGUAGE_UID]) {
                $str = $arElement['UF_STRING__'.$this->LANGUAGE_UID];
            } else {
                $str = $arElement['UF_STRING'];
            }
            
            if ($search) $str = str_replace($search,$replace,$str);
            return $str;
        }
        #
        
    }
}