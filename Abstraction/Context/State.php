<?
namespace Bxx\Abstraction\Context {

    abstract class State {

        /**
         * ЧеловекаПонятное (ЧП) название соятояния
         */
        abstract public function getTitleState (): string;

        /**
         * ЧеловекаПонятное (ЧП) название варианта соятояния
         */
        public function getTitle (string $Name=''): string
        {
            if (!$Name) $Name = $this->get();
            $lst = $this->getList();
            foreach ($lst as $dct) {
                if ($dct['name'] == $Name) return $dct['title'];
            }
        }
        
        /**
         * ЧеловекаПонятное (ЧП) описание вариант состояния
         * Если не передано имя, то текущее
         * Должен включать заголовок состояния и заголовак варианта состояния
         */
        public function getDescription (string $Name=''): string
        {
            return $this->getTitleState().': '.$this->getTitle($Name);
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
        abstract public function getList (): array;


        /**
         * возвращает имя/код текущего варианта состояния
         *  
         */
        private $CurrentName;
        public function get (): string
        {
            if (!$this->CurrentName) {
                $this->set();
            }
            return $this->CurrentName;
        }

        /**
         * меняет текущее вариант состояниe
         * вызванная без параметров должна автоматически устанавливать текущее
         * состояние сохраняя его в $this->CurrentName
         * метод должен проверять является ли устанавливаемый вариант состояния допустимым
         * 
         */
        public function set (string $Name): self
        {
            $lst = $this->getList();
            foreach ($lst as $dctStateVariant) {
                if ($dctStateVariant['name'] == $Name) {
                    $this->CurrentName = $Name;
                    return $this;
                }
            }
            
            throw new \Bitrix\Main\SystemException(
                    $Name.' не является допустимым вариантом состояния '
                    .$this->getTitle()
                );
        }
        

        /**
         * возвращает какие либо данные состояния по имени
         * если имя не указано, то данные текущего состояния
         * 
         */
        public function getData (string $Name=''): array
        {
            return [];
        }

    }
}
