<?
namespace Bxx\Abstraction {
    abstract class Model {
    
        static $instances = [];
        
        public static function getInstance (string $uid)
        {
            if (!isset(static::$instances[$uid])) {
                static::$instances[$uid] = new static($uid);
            }
            return static::$instances[$uid];
        }
        
        /* Пример установки кэша
         * по умолчанию
        protected $cTime = 14400; // 4 часа
         * для отдельного метода
        protected $cTimes = [
                'getCnt' => 18000 // 5 часов
            ];
        */
        
        protected function __construct (string $uid)
        {
            
            // дефолты
            //if ($this->Select) $this->_Select = $this->Select;
            
            // предустановка свойств
            $this->cDir = '/Bxx/Abstraction/Model/'.$this::MODEL.'_'.$uid;
            if (!$this->cTime) {
                $shiftCache = strlen($uid)*10 + intval($uid);
                // время кэширования по умолчанию
                $this->cTime = 86399;
                if (class_exists('\App\Settings'))
                        $this->cTime = \App\Settings::getCacheTTL() + $shiftCache;
            }
        if (!$this->cTimes) $this->cTimes = []; // расчитанное время кэширования по методам
        $this->cTimes['*'] = $this->cTime;
        if (!$this->cMultiplex) $this->cMultiplex = 11;
        if (!$this->cTimes['long']) $this->cTimes['long'] = $this->cTime*$this->cMultiplex;
        
        return $this;
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
        
        
        /* возвращает время кэширования для метода
         * или устанавливает для каждого метода отдельно
         * или устанавливает базовое время если метод указан как *
         * $method - имя метода для которого надо установить/вернуть время кэширования
         * $time - время кэширования в секундах, если false - просто возвращает текущее значение
         */
        public function cacheTime (string $method='*',$time=false): int
        {
            
            if ($time) {
                $time = intval($time);
                if ($time > 0) {
                    $this->cTimes[$method] = $time;
                }
            }
            
            if ($this->cTimes[$method]) return $this->cTimes[$method];
            
            return $this->cTime;
        }
        
        
        /* сбрасывает тегированный кэш
         */
        //public function cacheReset ($arSub=false) { // сбрасывает тегированный кэш
        //    $arInvalida = [];
        //    global $CACHE_MANAGER;
        //    if (!$arSub) { // если теги не переданые - скидываем все
        //        $arInvalida[] = 'x_iblock_id_'.$this::IDIB;
        //        $CACHE_MANAGER->ClearByTag('x_iblock_id_'.$this::IDIB);
        //    } else {
        //        foreach ($arSub as $sub) {
        //            $arInvalida[] = 'x_iblock_id_'.$this::IDIB.'_'.$sub;
        //            $CACHE_MANAGER->ClearByTag('x_iblock_id_'.$this::IDIB.'_'.$sub);
        //        }
        //    }
        //}
        
        /**
         * возвращает ключ кэширования
         */
        public function cacheKey ($params) {
            $key = md5(serialize($params));
            return $key;
        }
        
        
        /**
         *
         */
        public function getParams (array &$arParams): array
        {
            return $arParams;
        }
        
        
        
        /*
         * Возращает фильтр для getList
         * и сбрасывает одноразовый фильтр
         */
        public function getFilter () {
            if (isset($this->disposableParams['filter'])) {
                $arFilter = $this->disposableParams['filter'];
                unset($this->disposableParams['filter']);
            } else $arFilter = $this->Filter;
            
            if (!is_array($arFilter)) $arFilter=array();
            
            return $arFilter;
        }
        protected function __getFilter () {
            $arFilter = $this->getFilter();
            //$this->lastFilter = $arFilter;
            return $arFilter;
        }
        
        /*
         * Возращает селект для getList
         * и сбрасывает одноразовый селект
         */
        public function getSelect () {
            if (isset($this->disposableParams['select'])) {
                $arSelect = $this->disposableParams['select'];
                unset($this->disposableParams['select']);
            } else $arSelect = $this->Select;
            
            if (!is_array($arSelect)) $arSelect=array();
            return $arSelect;
        }
        protected function __getSelect () {
            $arSelect = $this->getSelect();
            //$this->lastSelect = $arSelect;
            return $arSelect;
        }
        
        /*
         * Возращает сортировку для getList
         * и сбрасывает одноразовую сортировку
         */
        public function getOrder () {
            if (isset($this->disposableParams['order'])) {
                $arOrder = $this->disposableParams['order'];
                unset($this->disposableParams['order']);
            } else $arOrder = $this->Order;

            if (!is_array($arOrder)) $arOrder=array();
            return $arOrder;
        }
        protected function __getOrder () {
            $arOrder = $this->getOrder();
            //$this->lastOrder = $arOrder;
            return $arOrder;
        }
        
        public function resetSelect () {
            if ($this->_Select) $this->Select=$this->_Select;
            unset($this->disposableParams['select']);
            return $this;
        }
        
        /*
        * Функции изменяют текущие фильтр, селект и ордер модели
        */
        public function setFilter ($arFilter) {$this->Filter=$arFilter; return $this;}
        public function setSelect ($arSelect) {$this->Select=$arSelect; return $this;}
        public function setOrder ($arOrder) {$this->Order=$arOrder; return $this;}
        
        /*
         * функции расширяют текущие фильтр, селект и ордер модели
        */
        public function add2Filter ($arFilter) {$this->Filter=array_merge($this->Filter,$arFilter); return $this;}
        public function add2Select ($arSelect) {$this->Select=array_merge($this->Select,$arSelect); return $this;}
        public function add2Order ($arOrder) {$this->Order=array_merge($this->Order,$arOrder); return $this;}
        
        
        /*
         * Устанавливает одноразове параметры сортировки фильтрации и выбора
         * которые будут сброшены после одного использования
         */
        public function setParams ($arParams) {
            $this->disposableParams = array();
            if ($arParams['order']) $this->disposableParams['order']=$arParams['order'];
            if ($arParams['filter']) $this->disposableParams['filter']=$arParams['filter'];
            if ($arParams['select']) $this->disposableParams['select']=$arParams['select'];
            return $this;
        }
        
        
    }
}
