<?
namespace Bxx\Context {

    class Language extends \Bxx\Abstraction\Context\State
    {

        /**
         * название соятояния
         */
        public function getTitleState (): string
        {
            return 'Язык';
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
                $this->ref = [];
                $langs = \Bitrix\Main\Localization\LanguageTable::getList([
                        'filter' => [
                            '=ACTIVE' => 'Y'
                        ]
                    ]);
                while ($dctLang = $langs->fetch()) {
                    $this->ref[$dctLang['LID']] = $dctLang;
                }
            }
            
            return $this->ref;
        }


        /**
         * изменяет состояние на указанное
         *  
         */
        public function set (string $Name=''): self
        {
            if (!$Name) {
                $Name = LANGUAGE_ID;
                parent::set($Name);
            }
            if ($Name != LANGUAGE_ID) { // изменение языка
                throw new \Bitrix\Main\SystemException('Изменение языка не реализовано');

                
                // $context = \Bitrix\Main\Application::getInstance()->getContext();
                // $request = $context->getRequest();
                // $uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
                // $uri->addParams(['lang'=>$Name]);

                // LocalRedirect($uri->getUri()); 
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
