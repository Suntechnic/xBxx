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
         * Состояние должно быть наследником \Bxx\Abstraction\Context\State
         * Не все существующие в приложении состояния обязаны быть определенными в каждом контексте
         */

        protected $bxContext;

        /**
         * Контекст может быть дефолтным, т.е. созданным из нулевого состояния
         * Это контекст где все значения всех состояний определены исходя из окружения
         * Например код языка, будет являться текущим языком сайта
         * Дефолтный контекст необходим для получения текущих состояний приложения
         */
        protected $Default;
        /**
         * Недефолтные контексты нужны для иммитации различных состояний
         */


        /**
         * создает контекст
         * на вход может принимать массив ИмяСостояния=>КодВариантаСостояния
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
         * получает на вход сиписок имен состояния и инициализирует его - получается варианты для каждого состояния
         * характерные для дефолтного контекста
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
         * например 
         setAliases ([
                'lang'=>'\Bxx\Context\Language', 
                'language'=>'lang',
                'site'=>'\Bxx\Context\Site'
            ])
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
