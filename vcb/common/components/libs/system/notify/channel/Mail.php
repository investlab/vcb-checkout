<?php

namespace common\components\libs\system\notify\channel;

use common\models\business\SendMailBussiness;

class Mail extends BasicChannel
{
    protected $method_type = "POST";
//    const CONFIG_TEST_MAIL = 'tinbt@nganluong.vn';
    const CONFIG_TEST_MAIL = 'notifyvcb@gmail.com';
//    const CONFIG_TEST_MAIL = 'trongtin30899@gmail.com';


    public function initCall()
    {
        // TODO: Implement initCall() method.
        SendMailBussiness::send(
            self::CONFIG_TEST_MAIL,
            'CẢNH BÁO NHIỀU NOTIFY_URL KHÔNG HOẠT ĐỘNG',
            'notify_link_check',
            [
                'links' => $this->content
            ], 'layouts/basic', ''
        );
    }


}