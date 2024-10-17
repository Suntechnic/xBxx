<?
namespace Bxx;

class HTML 
{

    private static $instance;
    public static function getInstance ()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    
    protected function __construct ()
    {
        
    }

    private $lstStack = [];

    private $refId = [];

    /**
     * открывает в документе тег $Tag
     * с атрибутами $refAttrs
     */
    public function open (string $UID, array $refAttrs=[], string $Tag='div'): void
    {
        
        $dctTag = [
                'UID' => $UID,
                'TAG' => $Tag,
                'COMMENT' => ''
            ];

        $HTML = '<'.$dctTag['TAG'];
        foreach ($refAttrs as $Name=>$Value) {
            // id
            if ($Name == 'id') {
                if ($this->$refId[$Value]) {
                    throw new \Bitrix\Main\SystemException('Не уникальный id: '.$Value);
                }
                $this->$refId[$Value] = true;

                $dctTag['COMMENT'] = '#'.$Value;
            }

            // class
            if ($Name == 'class' && !$dctTag['COMMENT']) {
                $dctTag['COMMENT'] = '.'.implode('.',explode(' ',$Value));
            }

            $HTML.= ' '.$Name.'="'.$Value.'"';
        }
        $HTML.= '>';

        $this->lstStack[] = $dctTag;
        echo $HTML;
    }

    /**
     * закрывает очередной тег
     * UID необходим для контроля вложенности - в строгом режиме будет ошибка
     * в нестрогом будет пропуск закрытия
     * 
     */
    public function close (string $UID, bool $Strict=false): void
    {
        $dctTag = array_pop($this->lstStack);
        if ($dctTag['UID'] != $UID) {
            if ($Strict)
                    throw new \Bitrix\Main\SystemException('Нарушение вложенности тегов на теге '.$UID);
            return;
        }
        $HTML = '</'.$dctTag['TAG'].'>';
        if ($dctTag['COMMENT']) $HTML.= '<!--'.$dctTag['COMMENT'].'-->';

        echo $HTML."\n";
    }

     /**
     * закрывает вообще всё
     */
    public function closeAll (): void
    {
        foreach (array_reverse($this->lstStack) as $dctTag) {
            $this->close($dctTag['UID']);
        }
    }
}
