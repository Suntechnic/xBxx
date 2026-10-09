<?
namespace Bxx\im;


class Telegram
{


    private $Token;
    /**
     * @param string $From - токен бота который может быть в следующем виде:
     * [bot]{Token}[@telegram.org]
     */
    public function __construct(string $From)
    {
        $Token = trim($From);

        $Token = preg_replace('/^\[bot\]/i', '', $Token);
        $Token = preg_replace('/@telegram\.org$/i', '', $Token);

        if ($Token === '' || !str_contains($Token, ':')) {
            throw new \InvalidArgumentException('Некорректный формат токена');
        }

        $this->Token = $Token;
    }

    /**
     * @var string $To Список адресов {ChatId}[@telegram.org], разделённых запятой и/или пробелом
     * @var string $Subject Не используется
     * @var string $Message Сообщение; префикс HTML:, MarkdownV2: или Text: задаёт parse_mode
     */
    public function send(string $To, string $Subject, string $Message): bool
    {
        $Url = 'https://api.telegram.org/bot' . $this->Token . '/sendMessage';

        
        if (str_starts_with($Message, 'Text:')) {
            $parseMode = 'MarkdownV2';
            $Message = substr($Message, strlen('Text:'));
            $Message = static::escape($Message);
        } elseif (str_starts_with($Message, 'MarkdownV2:')) {
            $parseMode = 'MarkdownV2';
            $Message = substr($Message, strlen('MarkdownV2:'));
        } elseif (str_starts_with($Message, 'HTML:')) {
            $parseMode = 'HTML';
            $Message = substr($Message, strlen('HTML:'));
            $Message = static::escapeHtml($Message);
        } else {
            $parseMode = 'HTML';
        }

        $lstChatIds = preg_split('/[\s,]+/', trim($To), -1, PREG_SPLIT_NO_EMPTY);
        
        $logger = $this->getLogger();
        
        if (empty($lstChatIds)) {
            if ($logger) {
                $logger->error("\n[{date}] ERROR: некуда отправлять {chanel}",[
                        'chanel' => $lstChatIds
                    ]);
            }
            return false;
        }

        $httpClient = $this->getClient();
        
        $Success = true;

        foreach ($lstChatIds as $ChatId) {
            $ChatId = preg_replace('/@telegram\.org$/i', '', $ChatId);

            if ($ChatId === '') {
                $Success = false;
                continue;
            }
            
            $dctSendData = [
                    'chat_id'    => $ChatId,
                    'text'       => $Message,
                    'parse_mode' => $parseMode,
                ];

            $response = $httpClient->post($Url, $dctSendData);

            $dctResponse = is_string($response) ? json_decode($response, true) : null;

            if (is_array($dctResponse) && !empty($dctResponse['ok'])) {
                if ($logger) {
                    $logger->debug("\n[{date}] Сообщение отправлено в канал {chanel}",[
                            'chanel' => $ChatId
                        ]);
                }
            } else {
                if ($logger) {
                    $logger->error("\n[{date}] ERROR Ошибка отправки {chanel} \n{responce}",[
                            'chanel' => $ChatId,
                            'responce' => $response
                        ]);
                }
                $Success = false;
            }
        }

        return $Success;
    }

    private $client;
    public function getClient (): \Bitrix\Main\Web\HttpClient
    {
        if (!$this->client) {
            $httpClient = new \Bitrix\Main\Web\HttpClient([
                    'socketTimeout' => 5,  // максимум 5 сек. на подключение
                    'streamTimeout' => 10, // максимум 10 сек. ожидания ответа
                ]);
                
            // проверяем нет ли фирменного прокси
            $dctProxyInit = parse_ini_file('/etc/oceansites/proxy.ini');
            
            if ($dctProxyInit['host'] && $dctProxyInit['port']) {
                $httpClient->setProxy(
                        $dctProxyInit['host'],
                        (int)$dctProxyInit['port'],
                        $dctProxyInit['user'] ?? null,
                        $dctProxyInit['password'] ?? null
                    );
            }

            $this->client = $this->setClient($httpClient);
        }
        return $this->client;
    }
    public function setClient (\Bitrix\Main\Web\HttpClient $httpClient): \Bitrix\Main\Web\HttpClient
    {
        $this->client = $httpClient;
        return $this->client;
    }


    private $logger;
    public function getLogger (): ?\Bitrix\Main\Diag\FileLogger
    {
        return $this->logger;
    }
    public function setLogger (\Bitrix\Main\Diag\FileLogger $logger): \Bitrix\Main\Diag\FileLogger
    {
        $this->logger = $logger;
        return $this->logger;
    }


    /**
     * Функция предварительного экранирования текста для отправки в телеграм
     * @param string $Text  
     * @return string
     */
    public static function escape (string $Text) : string
    {
        return preg_replace(
                '/([_*\[\]()~`>#+\-=|{}.!\\\\])/u',
                '\\\\$1',
                $Text
            );
    }


    public static function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}