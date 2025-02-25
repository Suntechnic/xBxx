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
                foreach ($this->getLanguagesReferences() as $dctLang) {
                    $this->lst[] = [
                            'name' => $dctLang['LID'],
                            'title' => $dctLang['NAME'],
                        ];
                }
                
            }
            
            return $this->lst;
        }
        private $refLanguages;
        public function getLanguagesReferences (): array
        {
            if (!$this->refLanguages) {
                $this->refLanguages = [];
                $langs = \Bitrix\Main\Localization\LanguageTable::getList([
                        'filter' => [
                            '=ACTIVE' => 'Y'
                        ]
                    ]);
                while ($dctLang = $langs->fetch()) {
                    $this->refLanguages[$dctLang['LID']] = $dctLang;
                }
            }
            
            return $this->refLanguages;
        }


        /**
         * возвращает имя/код текущего варианта состояния
         *  
         */
        public function set (string $Name=''): self
        {
            if (!$Name) $Name = LANGUAGE_ID;
            if ($Name != LANGUAGE_ID) { // изменение языка

                $context = \Bitrix\Main\Application::getInstance()->getContext();
                $request = $context->getRequest();
                $uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
                $uri->addParams(['lang'=>$Name]);

                LocalRedirect($uri->getUri()); 
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
            return $this->getLanguagesReferences()[$Name];
        }


    }
}
