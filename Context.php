<?
namespace Bxx 
{
    class Context
    {

        protected $bxContext;
        /**
         * создает контекст
         * на вход может принимать массив имен=>вариантовСостояний
         */
        public function __construct(array $ref=null) 
        {
            if ($ref) $this->import($ref);
        }

        /**
         * поднимае список состояний
         */
        public function up (array $lst): self
        {
            foreach ($lst as $Name) {
                $this->getState($Name);
            }
            return $this;
        }

        /**
         * возвращает массив ИмяСостояния=>КодВариантаСостояния
         */
        public function export (): array
        {
            $ref = [];
            foreach ($this->refStates as $Name=>$state) {
                $ref[$Name] = $state->get();
            }
            return $ref;
        }

        /**
         * импортирует состояния из массива ИмяСостояния=>КодВариантаСостояния
         */
        public function import (array $ref): self
        {
            foreach ($ref as $Name=>$NameVariant) {
                $state = $this->getState($Name);
                $state->set($NameVariant);
            }
            return $this;
        }



        private $refAliases = [];
        /**
         * устанавливает название алиас для состояния
         * например setAlias('lang','\App\Context\Language')
         */
        public function setAlias (string $Alias, string $Name): void
        {
            $this->refAliases[$Alias] = $Name;
        }
        /**
         * устанавливает сразу много алиасов
         * например setAliases (['lang'=>'\App\Context\Language', 'language'=>'lang'])
         */
        public function setAliases (array $map): void
        {
            foreach ($map as $Alias=>$Name) $this->setAlias($Alias, $Name);
        }
        /**
         * возвращает конечное имя
         */
        public function getRealName (string $Name): string
        {
            while ($this->refAliases[$Name]) {
                $Name = $this->refAliases[$Name];
            }
            return $Name;
        }

    

        private $refStates = [];
        /**
         * возвращает состояние
         * имплементирующие интерфейс \Bxx\Abstraction\Context\State
         * 
         */
        public function getState (string $Name): \Bxx\Abstraction\Context\State
        {
            $Name = $this->getRealName($Name);
            if (!$this->refStates[$Name]) {
                $state = new $Name($this);
                if (is_subclass_of($state,'\Bxx\Abstraction\Context\State')) {
                    $this->refStates[$Name] = $state;
                } else {
                    throw new \Bitrix\Main\SystemException($Name.' не является состоянием');
                }
            }
            return $this->refStates[$Name];
        }

        /**
         * Возвращает массив-список с описанием всех текущих загруженных состояний
         * 
         */
        public function getDescription (): array
        {
            $lst = [];
            foreach ($this->refStates as $state) {
                $lst[] = $state->getDescription();
            }
            return $lst;
        }




    }
}