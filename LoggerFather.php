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
    private const LOGSIZE = 8388608;

    // возвращает логгер
    public function get (string $Name): \Bitrix\Main\Diag\FileLogger
    {
        if (!$this->refLoggers[$Name]) {
            $LogPath = $this->getLogDirPath().$Name.$this->LogExt;
            $LogDirPath = dirname($LogPath);
            if (!\Bitrix\Main\IO\Directory::isDirectoryExists($LogDirPath)) {
                \Bitrix\Main\IO\Directory::createDirectory($LogDirPath);
            }
            $logger = new \Bitrix\Main\Diag\FileLogger(
                    $LogPath,
                    $this->LogSize
                );
            if ($this->LevelDefault) $logger->setLevel($this->LevelDefault);

            $this->refLoggers[$Name] = $logger;
        }
        
        return $this->refLoggers[$Name];
    }

    public function setLevelDefault ($Level)
    {
        return $this->LevelDefault = $Level;
    }


    public function getLogDirPath (): string
    {
        return $this->LogDirPath;
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
        $this->LogExt   = self::LOGEXT;
        $this->LogSize  = self::LOGSIZE;

        if (APPLICATION_ENV == 'dev') {
            $this->setLevelDefault(\Psr\Log\LogLevel::DEBUG);
        } else {
            $this->setLevelDefault(\Psr\Log\LogLevel::ERROR);
        }

        // тут считывание параметров
        $this->LogDirPath = \Bitrix\Main\Application::getDocumentRoot().$this->LogDir;
    }
}
