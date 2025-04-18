<?php

namespace common\components\libs\system\notify\channel;

class Telegram extends BasicChannel
{
    protected $url_api = 'https://api.telegram.org/bot';
    protected $key = '1458420809:AAEi_Ja4hZVTbba8ad7Sp0o9OCVZo6agWsw'; /* Kiu Bot*/
    protected $function = 'sendMessage';
//    protected $chat_id = '1091874447'; /*QuangNT*/
    protected $chat_id = '-1002025745036'; /*Notify Paygate*/

    public function initCall(): string
    {
        return $this->url_api . $this->key . '/' .
            $this->function . "?" . http_build_query([
                "chat_id" => $this->chat_id,
                "parse_mode" => "html",
                "text" => $this->getContent()
            ]);
    }

    private function getContent()
    {
        $pre = APP_ENV == 'prod' ? '' : '[TEST] ';
        return $pre . "<b>$this->gate NOTIFY</b>\n" . $this->content;
    }
}