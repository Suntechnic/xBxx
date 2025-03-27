<?
namespace Bxx\Context {

    class Site extends \Bxx\Abstraction\Context\State
    {

        /**
         * название соятояния
         */
        public function getTitleState (): string
        {
            return 'Сайт';
        }

        /**
         * возвращает список возможных вариантов состояния в виде массива
        [
                [
                        'name' => Кодовое имя возможного состояния
                        'title' => ЧП название варианта состояния
                    ]
            ]
         *  
         */
        private $lst;
        public function getList (): array
        {
            if (!$this->lst) {
                $this->lst = [];
                foreach ($this->getReferences() as $dct) {
                    $this->lst[] = [
                            'name' => $dct['LID'],
                            'title' => $dct['NAME'],
                        ];
                }
                
            }
            
            return $this->lst;
        }
        private $ref;
        public function getReferences (): array
        {
            if (!$this->ref) {
                $rdbSites = \CSite::GetList($By='sort', $Order='desc', ['ACTIVE'=>'Y']);
                while ($dctSite = $rdbSites->Fetch()) {
                    $this->ref[$dctSite['LID']] = $dctSite;
                }
            }
            
            return $this->ref;
        }


        /**
         * возвращает имя/код текущего варианта состояния
         *  
         */
        public function set (string $Name=''): self
        {
            if (!$Name) {
                $Name = SITE_ID;
                parent::set($Name);
            }
            if ($Name != SITE_ID) { // изменение сайта
                throw new \Bitrix\Main\SystemException('Изменение сайта не реализовано');
            }
            return $this;
        }

        /**
         * возвращает какие либо данные состояния по имени
         * если имя не указано, то данные текущего состояния
         * 
         */
        public function getData (string $Name=''): array
        {
            if (!$Name) $Name = $this->get();
            return $this->getReferences()[$Name];
        }


    }
}
