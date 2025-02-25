<?
// Filestorage::getInstance()->getFileSrc();

/*
Filestorage
b_hlbd_filestorage
Список полей:
UF_NAME	Строка
UF_FILE	Файл
UF_URL	Строка
UF_XML_ID	Строка
*/

namespace Bxx\Abstraction\Protomodel {
    class Filestorage extends \Bxx\Abstraction\HLBModel {
        
        public static function getInstance(string $Code = 'Filestorage') {
            return parent::getInstance($Code);
        }
        
        // возвращает src файла по xml_id
        public function getFileSrc ($xml_id) {
            $arElement = $this->getElement(['filter'=>['UF_XML_ID'=>$xml_id]]);
            return $arElement['SRC'];
        }
        #
        
        // возвращает запись по xml_id
        public function getFile ($xml_id) {
            $arElement = $this->getElement(['filter'=>['UF_XML_ID'=>$xml_id]]);
            return $arElement;
        }
        #
        
        // возвращает один первый элеметм
        public function getElement ($arParams=[]) {
            $arElament = parent::getElement($arParams);
            return $this->__procElement($arElament);
        }
        #
        
        //
        private function __procElement ($arElement) {
            if (!$arElement) return false;
            $arElement['NAME'] = $arElement['UF_NAME'];
            // файл и ссылка
            if ($arElement['UF_URL']) {
                $arElement['SRC'] = $arElement['UF_URL'];
            } else if ($arElement['UF_FILE']) {
                $arElement['UF_FILE'] = \CFile::GetFileArray($arElement['UF_FILE']);
                $arElement['SRC'] = $arElement['UF_FILE']['SRC'];
                $arElement['S_SRC'] = $_SERVER['DOCUMENT_ROOT'].$arElement['SRC'];
                $size = intval($arElement['UF_FILE']['FILE_SIZE']);
                
                $arElement['SIZE'] = [
                        'B' => $size,
                        'KiB' => $size/1024,
                        'MiB' => ($size/1024)/1024,
                        'GiB' => (($size/1024)/1024)/1024
                    ];
                foreach ($arElement['SIZE'] as $unit=>$size) {
                    if ($size > 1) {
                        $arElement['SIZE']['ACTUAL_SIZE'] = $size;
                        $arElement['SIZE']['ACTUAL_UNIT'] = $unit;
                    } else break;
                }
                
                if ($arElement['SIZE']['ACTUAL_SIZE'] > 10) {
                    $arElement['SIZE']['ACTUAL_SIZE'] = round($arElement['SIZE']['ACTUAL_SIZE']);
                } else $arElement['SIZE']['ACTUAL_SIZE'] = round($arElement['SIZE']['ACTUAL_SIZE'],1);
                
            }
            return $arElement;
        }
        #
        
    }
}