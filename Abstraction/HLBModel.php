<?

namespace Bxx\Abstraction {
    abstract class HLBModel extends Model {
        
        const MODEL = 'hlblock';
        
        public static function getInstance(string $Code='') {
            if ($Code) {
                $Code = $Code;
            } else {
                $Code = static::IDHLB;
            }
            
            return parent::getInstance($Code);
        }
        
        protected function __construct(string $Code) {
            if (!$Code) die('Invalid HLBlock Id: '.$Code);
            $this->Code = $Code;
            \Bitrix\Main\Loader::includeModule('highloadblock');
            
            $this->EntityClass = \Bxx\Helpers\HLBlocks::getEntityClassByCode($Code);
            
            return parent::__construct($Code);
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $EntityClass;

        // возвращает класс
        public function getEntityClass()
        {
            return $this->EntityClass;
        }
        #
        
        // возвращает ID инфоблока
        public function getId(): int
        {
            return intval($this->ID);
        }
        #
        
        
        // возвращает один первый элемент
        public function getElement (array $arParams=[])
        {
            // параметры метода
            // если в $arParams нет filter, select или order
            // то будут подставлены текущие
			$arParams = $this->getParams($arParams);
            
            $res = $this->EntityClass::getList($arParams);
            
            $lst = [];
            if ($dct = $res->fetch()) return $dct;
            
			return false;
        }
        #
        
        
        // возвращает список элементов
        public function getList (array $arParams=[]): array
        {
            $arParams = $this->getParams($arParams);
            $res = $this->EntityClass::getList($arParams);
            
            $lst = [];
            while ($dct = $res->fetch()) $lst[] = $dct;
            
            $cacheKey = false;
            //\XDebug::log(
            //        array(
            //                'options'=>$arParams
            //            ),
            //        'call lst for '.$this->EntityClass.($cacheKey?' (from cache)':'')
            //    );
            
			return $lst;
        }
        #
        
        
        /**
         * возвращает справочник
         *
         */
        public function getReference (string $key='', $arParams=[]): array
        {
            
            if ($key === '') $key = 'ID';
            
            $arParams = $this->getParams($arParams);
            if (is_array($arParams['select']) // если селект установлен
                    && count($arParams['select']) // и не пуст
                    && !in_array($key,$arParams['select']) // но в нем нет ключа
                ) $arParams['select'][] = $key; // необходимо его добавить
            
			$res = $this->EntityClass::getList($arParams);
            
            $ref = [];
            while ($dct = $res->fetch()) $ref[$dct[$key]] = $dct;
            
            $cacheKey = false;
            //\XDebug::log(
            //        array(
            //                'options'=>$arParams
            //            ),
            //        'call lst for '.$this->EntityClass.($cacheKey?' (from cache)':'')
            //    );
            
			return $ref;
		}
        
    }
}
