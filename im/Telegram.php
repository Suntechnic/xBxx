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
            $ParseMode = 'MarkdownV2';
            $Message = substr($Message, strlen('Text:'));
        } elseif (str_starts_with($Message, 'MarkdownV2:')) {
            $ParseMode = 'MarkdownV2';
            $Message = substr($Message, strlen('MarkdownV2:'));
            //$Message = static::escape($Message);
        } elseif (str_starts_with($Message, 'HTML:')) {
            $ParseMode = 'HTML';
            $Message = substr($Message, strlen('HTML:'));
            $Message = static::sanitizeHtml($Message);
        } else {
            $ParseMode = 'HTML';
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
                    'parse_mode' => $ParseMode,
                ];

            //$logger?->debug("\n[{date}] Сообщение подготовлено {parse_mode} {text}",$dctSendData);

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
                    $logger->error("\n[{date}] ERROR Ошибка отправки {chanel}\t\n{response}\t\nStatus: {status}\t\nError: {error}\t\nHeaders: {headers}\t\nLen: {len}",[
                            'chanel' => $ChatId,
                            'status'    => $httpClient->getStatus(),   // 0 при обрыве/таймауте
                            'error'     => print_r($httpClient->getError(), true), // socketTimeout/connect/...
                            'headers'   => print_r($httpClient->getHeaders(), true),
                            'len'       => is_string($response) ? strlen($response) : -1,
                            'response' => $response
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


    public static function sanitizeHtml(string $Html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);

        try {
            $dom->loadHTML(
                '<?xml encoding="UTF-8"><html><body>' . $Html . '</body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $allowed = [
            'b', 'strong', 'i', 'em', 'u', 'ins',
            's', 'strike', 'del', 'code', 'pre',
        ];

        $escape = static fn(string $text): string =>
            htmlspecialchars(
                $text,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $render = static function (
            \DOMNode $node,
            bool $plain = false
        ) use (&$render, $allowed, $escape): string {
            if ($node instanceof \DOMText) {
                return $escape($node->nodeValue);
            }

            if (!$node instanceof \DOMElement) {
                return '';
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, ['script', 'style'], true)) {
                return '';
            }

            if ($tag === 'br') {
                return "\n";
            }

            $keep = !$plain && in_array($tag, $allowed, true);
            $insideCode = $plain || in_array($tag, ['code', 'pre'], true);

            $content = '';

            foreach ($node->childNodes as $child) {
                $content .= $render($child, $insideCode);
            }

            // Не сохраняем вложенное форматирование внутри code/pre.
            if ($keep && $content !== '') {
                return "<{$tag}>{$content}</{$tag}>";
            }

            return $content;
        };

        $result = '';
        $body = $dom->getElementsByTagName('body')->item(0);

        if ($body) {
            foreach ($body->childNodes as $node) {
                $result .= $render($node);
            }
        }

        return $result;
    }
}