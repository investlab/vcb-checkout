<?php

namespace common\components\libs\system\notify;
use common\components\libs\system\notify\channel\Telegram;

class Notify
{
    protected $channel;
    protected $message;

    /**
     * @param mixed $channel
     */
    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function sendMessage()
    {
        $channel_class = self::getChannelClass($this->channel);
//        var_dump($channel_class);die();

        if ($channel_class) {
            $channel = new $channel_class;
            $channel->setContent($this->message);
            $channel->process();
        }
    }

    private static function getChannelClass($channel)
    {
        $source = '\\';
//        $namespace = DS . 'common\components\libs\system\notify\channel' . DS;
        $namespace = 'common\components\libs\system\notify\channel\\';
        $path_file = ROOT_PATH . DS .'common' . DS .'components' . DS .'libs' . DS . 'system' . DS . 'notify' . DS . 'channel' . DS;
        $class_name = ucfirst($channel);
        $class_file = $class_name . ".php";
//        var_dump($path_file . $class_file);die();
        if (file_exists($path_file . $class_file)) {
            return $namespace . $class_name;
        }
        return false;
    }


}