<?
namespace Bxx;


class im
{

    const CHANELS = [
            'telegram.org' => 'Telegram'
        ];

    private static $logger;
    public static function getLogger (): ?\Bitrix\Main\Diag\FileLogger
    {
        return static::$logger;
    }
    public static function setLogger (\Bitrix\Main\Diag\FileLogger $logger): \Bitrix\Main\Diag\FileLogger
    {
        static::$logger = $logger;
        return static::$logger;
    }


    public static function handler (
            string &$EventName, 
            string &$Lid,
            array &$arFields, 
            int|string &$MessageId=0
        ) 
    {
        static::sendEvent($EventName, $Lid, $arFields, $MessageId);
        return $arFields;
    }


    public static function sendEvent (
            string $EventName, 
            string $Lid, 
            array $dctFields, 
            int|string $MessageId=0,
            string $Chanel='telegram.org',
            ?\Bitrix\Main\Diag\FileLogger $logger=null
        ): bool
    {

        if (!isset(static::CHANELS[$Chanel])) return false;

        if (!$logger) $logger = static::getLogger();
        

        try {
            $rdb = \Bitrix\Main\Mail\Internal\EventMessageTable::getList([
                    'select' => [
                        'ID',
                        'EVENT_NAME',
                        'LID',
                        'ACTIVE',
                        'EMAIL_FROM',
                        'EMAIL_TO',
                        'SUBJECT',
                        'MESSAGE',
                        'BODY_TYPE',
                    ],
                    'filter' => [
                        '=EVENT_NAME' => $EventName,
                        '=ACTIVE' => 'Y',
                        '=LID' => $Lid,
                        'EMAIL_FROM' => '%@'.$Chanel
                    ],
                    'order' => [
                        'ID' => 'DESC',
                    ],
                ]);

            $ChanelCode = static::CHANELS[$Chanel];
            $ChanelClass = '\Bxx\im\\'.$ChanelCode;

            while ($dctTmplEvents = $rdb->fetch())
            {

                if ($logger) {
                    $logger->error("\n[{date}] Шаблон: ".$dctTmplEvents['ID']);
                }
                
                $To = static::replace($dctTmplEvents['EMAIL_TO'], $dctFields);
                $Subject = static::replace($dctTmplEvents['SUBJECT'], $dctFields);
                $Message = static::replace($dctTmplEvents['MESSAGE'], $dctFields);

                // здесь псевдоним $dctTmplEvents['EMAIL_FROM'] должен быть обменян на секрет
                $Secret = \Bxx\Settings\SecretProvider::get($ChanelCode, $dctTmplEvents['EMAIL_FROM']);

                // тут отправка
                $chanel = new $ChanelClass($Secret);
                if ($logger) {
                    $chanel->setLogger($logger);
                }
                $chanel->send($To,$Subject,$Message);
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->error("\n[{date}] ERROR: ".$e->getMessage());
            }
        }

        return true;
    }
    
    private static function replace (string &$Text, array $dctFields): string
    {
        $Text = str_replace(
                array_map(function($key) { return '#'.$key.'#'; }, array_keys($dctFields)),
                array_values($dctFields),
                $Text
            );
        return $Text;
    }

}