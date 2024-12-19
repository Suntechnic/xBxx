<?
namespace Bxx;

class Timer 
{

    private static $instance;
    public static function getInstance ()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private $refBacket = [];
    private $refTimers = [];
    private $Enable = false;
    protected function __construct ()
    {
        // if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'dev') {
        //     $this->enable();
        // }
    }

    public function enable ()
    {
        $this->Enable = true;
    }

    public function disable ()
    {
        $this->Enable = false;
    }

    public function start (string $Name)
    {
        if (!$this->Enable) return;
        if ($this->refTimers[$Name]) throw new \Exception(
                'Double start: '.$Name
            );
        $this->refTimers[$Name] = hrtime(true);
    }

    public function stop (string $Name)
    {
        if (!$this->Enable) return;
        if (!$this->refTimers[$Name]) throw new \Exception(
                'Stop without start: '.$Name
            );

        $Δ = hrtime(true) - $this->refTimers[$Name];
        if ($this->refBacket[$Name]) {
            $this->refBacket[$Name] = $this->refBacket[$Name] + $Δ;
        } else {
            $this->refBacket[$Name] = $Δ;
        }
        $this->refTimers[$Name] = 0;
    }

    public function get (string $Name=''): int
    {
        if ($this->refBacket[$Name]) {
            return $this->refBacket[$Name];
        } else if ($this->refTimers[$Name]) {
            return hrtime(true) - $this->refTimers[$Name];
        } else {
            return 0;
        }
    }

    public function getNames (): array
    {
        return array_keys($this->refBacket);
    }

    public function getAll (): array
    {
        return $this->refBacket;
    }

}
