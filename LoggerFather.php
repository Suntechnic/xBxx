<?php

namespace Bxx;

class LoggerFather {

    static $instance;

    private $LogDir;
    private $LogExt;
    private $LogSize;

    private $LevelDefault=false;

    private $LogDirPath;

    private $refLoggers=[];

    private const LOGDIR = '/local/.logs/';
    private const LOGEXT = '.log.txt';
    private const LOGSIZE = 4096;

    // возвращает логгер
    public static function get (string $Name): \Bitrix\Main\Diag\FileLogger
    {

        if (!$this->refLoggers[$Name]) {
            $logger = new \Bitrix\Main\Diag\FileLogger(
                    $this->LogDirPath.$Name.$this->LogExt,
                    $LogSize
                );
            if ($this->LevelDefault) $logger->setLevel($this->LevelDefault);

            $this->refLoggers[$Name] = $logger;
        }
        
        return $this->refLoggers[$Name];
    }

    public static function setLevelDefault ($Level)
    {
        return $this->LevelDefault = $Level;
    }


    public static function getInstance ()
    {
        $uid = $Name;
        if (!isset(static::$instance)) {
            try {
                static::$instance = new static;
            } catch (\Exception $e) {
                if (APPLICATION_ENV == 'dev') {
                    \Kint\Kint::dump($e->getMessage());
                }
            }
        }
        return static::$instance;
    }

    // устанавливает настройки
    protected function __construct ()
    {
        $this->LogDir   = self::LOGDIR;
        $this->LogExt   = self::LOGDIR;
        $this->LogSize  = self::LOGSIZE;

        if (APPLICATION_ENV == 'dev') {
            $this->setLevelDefault(\Psr\Log\LogLevel::DEBUG);
        } else {
            $this->setLevelDefault(\Psr\Log\LogLevel::ERROR);
        }

        // тут считывание параметров

        $this->LogDirPath = \Bitrix\Main\Application::getDocumentRoot().$LogDir;
    }
}