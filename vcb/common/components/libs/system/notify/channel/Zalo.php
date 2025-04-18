<?php

namespace common\components\libs\system\notify\channel;

class Zalo extends BasicChannel
{

    public function initCall(): string
    {
        return 'https://api.telegram.org/bot1458420809:AAEi_Ja4hZVTbba8ad7Sp0o9OCVZo6agWsw/sendMessage?chat_id=1091874447&text=SendFromZalo';
    }
}