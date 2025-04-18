<?php

namespace common\components\libs\system\notify\channel;

abstract class BasicChannel
{
    protected $content;
    protected $url_api;
    protected $method_type = "GET";
    protected $gate = "VIETCOMBANK";

    abstract public function initCall() ;


    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }


    public function process()
    {
        if ($this->method_type == "GET") {
            $this->_sendMethodGet($this->initCall());
        } elseif($this->method_type == "POST") {
            $this->initCall(); // vs trường hợp MAIL
        }
    }

    public function _sendMethodGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    public function _sendMethodPOST()
    {

    }

}