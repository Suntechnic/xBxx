<?
namespace Bxx 
{
    class Context
    {
        /**
         * Контекст, это набор имен состоиний, являющихся идентификаторами состояния
         * и их значений (кодов вариантов состояния)
         * При этом имя, является именем класса представляющего состояния
         * Для классов можно установить алиасы
         * 
         * Примером имени состояния может являться \Bxx\Context\Language - встроенное состояние
         * В качестве алиаса для него может быть уставнолен алиас lang
         * 
         * Таким образом контекст может выглядеть как:
         * 'lang' => 'ru'
         * 
         * Контекст должен быть наследником \Bxx\Abstraction\Context\State
         * 
         * 
         */

        protected $bxContext;
        protected $Default;


        /**
         * создает контекст
<<<<<<< HEAD
         * на вход может принимать массив имен=>вариантовСостояний
         * 
=======
         * на вход может принимать массив ИмяСостояния=>КодВариантаСостояния
>>>>>>> 6fbd625c3f45a2b47a1e0e671c854f73398a8fb5
         * 
         */
        public function __construct(array $ref=null) 
        {
            if ($ref) {
                $this->import($ref);
                $Default = false;
            } else {
                // дефолтный контекст
                $Default = true;
            }
        }

        /**
         * поднимает список состояний
<<<<<<< HEAD
=======
         * получает на вход сиписок имен состояния и инициализирует его - получается варианты для кадого
         * характерные для текущего контекста
         * 
>>>>>>> 6fbd625c3f45a2b47a1e0e671c854f73398a8fb5
         */
        public function up (array $lst): self
        {
            foreach ($lst as $Name) {
                $this->getState($Name);
            }
            return $this;
        }

        /**
         * Экспортирует текущий контекст
         * возвращает массив ИмяСостояния=>КодВариантаСостояния
         * 
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
         * 
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
         * например setAlias('lang','\Bxx\Context\Language')
         * 
         * 
         */
        public function setAlias (string $Alias, string $Name): void
        {
            $this->refAliases[$Alias] = $Name;
        }

        /**
         * устанавливает сразу много алиасов
         * например setAliases (['lang'=>'\Bxx\Context\Language', 'language'=>'lang'])
         */
        public function setAliases (array $map): void
        {
            foreach ($map as $Alias=>$Name) $this->setAlias($Alias, $Name);
        }

        /**
         * возвращает конечное имя (class состояния по алиасу)
         * 
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
         * наследник \Bxx\Abstraction\Context\State
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
