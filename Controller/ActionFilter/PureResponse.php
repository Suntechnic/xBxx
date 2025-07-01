<?php


namespace Bxx\Controller\ActionFilter;


use \Bitrix\Main\Context;
use \Bitrix\Main\Error;
use \Bitrix\Main\EventResult;



final class PureResponse extends \Bitrix\Main\Engine\ActionFilter\Base
{
    protected static function createHttpResponse (
            string|array $mixContent = '',
            int $Status = 200,
            array $dctHeaders = []
        ): \Bitrix\Main\HttpResponse
    {
        $response = new \Bitrix\Main\HttpResponse();

        if (is_array($mixContent)) {
            $mixContent = json_encode($mixContent, JSON_UNESCAPED_UNICODE);
            $ContentType = 'application/json; charset=UTF-8';
        } else {
            $ContentType = 'text/plain; charset=UTF-8';
        }

        if (!$dctHeaders['Content-Type']) $dctHeaders['Content-Type'] = $ContentType;

        foreach ($dctHeaders as $Key => $Value) {
            if (is_array($Value)) {
                foreach ($Value as $V) {
                    $response->addHeader($Key, $V);
                }
            } else {
                $response->addHeader($Key, $Value);
            }
        }
        
        $response->setStatus($Status);
        $response->setContent($mixContent);

        return $response;
    }

	public function onAfterAction(\Bitrix\Main\Event $event)
	{
        $result = $event->getParameter('result');

        if (is_array($result) || is_string($result)) {
            $resultNew = self::createHttpResponse($result);
            
        } else {
            $controller = $event->getParameter('controller');
            $dctErrors = $controller->getErrors();

            
            if (!empty($dctErrors)) {
                foreach ($dctErrors as $error) {
                    $Code = $error->getCode()?$error->getCode():500;
                    $resultNew = self::createHttpResponse($error->getMessage(), $Code, [
                            'Content-Type' => 'text/plain; charset=UTF-8',
                            'X-Error-Code' => $Code,
                        ]);
                    break;
                }
            }
        }

        if ($resultNew) $event->setParameter('result', $resultNew);
        return $event;
	}
}