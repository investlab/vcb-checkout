<?php

namespace common\components\libs;

use common\components\libs\system\notify\Notify;

class NotifySystem
{
    const CHANNEL_DEFAULT = "telegram";


    public static function send($content, $channel = null)
    {
        try {
            $notify = new Notify();
            $notify->setChannel($channel != null ? $channel : self::CHANNEL_DEFAULT);
            $notify->setMessage($content);
            $notify->sendMessage();
        } catch (\Exception $e) {
        }
    }
}