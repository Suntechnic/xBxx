<?php

namespace Bxx\Logger;

use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

/**
 * Обёртка для Monolog для логирования сообщений в файл с поддержкой префиксов и автоматического форматирования имени файла.
 *
 * @example
 *   \Bitrix\Main\Loader::includeModule('ihead.logs');
 *
 *   $logger = new \IHead\Logs\FileLogger('auth');
 *   $logger->infoDb('Пользователь %s вошел', $userId); // Уровень: INFO, Префикс: DB
 */
class IHead
{
    /** @var Logger внутренний логгер Monolog */
    private readonly Logger $logger;

    /**
     * Инициализирует логгер в заданном файле.
     *
     * @param string $logName имя лог-файла или путь.
     * @param string $channel название канала логирования (по умолчанию 'default').
     * @param Level  $level   уровень логирования.
     *
     * @throws SystemException
     */
    public function __construct(string $logName, string $channel = 'default', Level $level = Level::Debug)
    {
        $logPath = \IHead\Logs\LogFilename::expand($logName);

        $this->logger = new Logger($channel);
        $this->logger->pushHandler(new StreamHandler($logPath, $level));
    }

    private static function lcfirst(string $string): string
    {
        if ($string === '') {
            return '';
        }
        $firstChar = mb_substr($string, 0, 1);
        $then      = mb_substr($string, 1);
        return mb_strtolower($firstChar) . $then;
    }

    public function pushProcessor(ProcessorInterface|callable $callback): self
    {
        $this->logger->pushProcessor($callback);

        return $this;
    }

    /**
     * Динамически создаёт методы типа уровень+префикс и отправляет их в лог с префиксом в начало.
     *
     * Имена методов генерируются как <уровень><префикс>, например: `infoDb`, `errorAuth`.
     * Префикс будет преобразован в SCREAMING_SNAKE_CASE + ": ".
     *
     * @param string $name      имя метода (передается для перехвата через __call).
     * @param array  $arguments аргументы: [сообщение, контекст логирования].
     *
     * @return void
     */
    public function __call(string $name, array $arguments): void
    {
        // Разбираем строку вида "infoDb" на ["info", "Db"]
        [$levelString, $prefixString] = preg_split('/(?=[A-Z])/', self::lcfirst($name), 2);

        // Получаем enum уровня Monolog
        $levelEnum = Level::fromName(strtolower($levelString));

        // Преобразуем префикс в SCREAMING_SNAKE_CASE + ": "
        if (!empty($prefixString)) {
            $prefixString = strtoupper(StringHelper::camel2snake($prefixString)) . ': ';
        } else {
            $prefixString = '';
        }

        // Собираем и записываем
        $message = $prefixString . ($arguments[0] ?? '');
        $context = is_array($arguments[1] ?? null) ? $arguments[1] : [];

        // Логируем в файл лога одной записью
        $this->logger->log($levelEnum, $message, $context);
    }

    public function setLevel(Level|string $Level): self
    {
        if (is_string($Level)) {
            $Level = Level::fromName(strtolower($Level));
        }
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handler->setLevel($Level);
            }
        }

        return $this;
    }
}