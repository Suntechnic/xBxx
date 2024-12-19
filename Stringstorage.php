<?
namespace Bxx
{
    class Stringstorage extends \Bxx\Abstraction\Protomodel\Stringstorage
    {
        /**
         * возвращает строку только если значение есть
         */
        public function getStringDisplay (
                string $XmlId, string $Tmpl='#VALUE#', string $ValMark='#VALUE#',
                string | bool $Search=false, string $Replace=''
            ): string
        {
            $Value = $this->getStringVal($XmlId,$Search,$Replace);
            if ($Value) {
                return str_replace($ValMark,$Value,$Tmpl);
            } else return '';
        }
    }
}