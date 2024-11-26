<?
namespace Bxx 
{
    class Context
    {

        
        private static $instance;
        public static function getInstance(): self
        {
            if (!self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        protected $bxContext;
        protected function __construct() 
        {
            $context = \Bitrix\Main\Application::getInstance()->getContext();
            $this->bxContext = $context;
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



    

        private $dctStates = [];
        /**
         * возвращает состояние
         * имплементирующие интерфейс \Bxx\Abstraction\Context\State
         * 
         */
        public function getState (string $Name): \Bxx\Abstraction\Context\State
        {
            $Name = $this->getRealName($Name);
            if (!$dctStates[$Name]) {
                $state = new $Name($this);
                if (is_subclass_of($state,'\Bxx\Abstraction\Context\State')) {
                    $dctStates[$Name] = $state;
                } else {
                    throw new \Bitrix\Main\SystemException($Name.' не является состоянием');
                }
            }
            return $state;
        }

    }
}
