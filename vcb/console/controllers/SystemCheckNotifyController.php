<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 11/03/2017
 * Time: 11:36 SA
 */

namespace console\controllers;

use common\components\libs\NotifySystem;
use common\models\business\SendMailBussiness;
use common\models\db\SystemCheckNotify;
use common\util\Helpers;
use yii\console\Controller;
use Yii;

class SystemCheckNotifyController extends Controller {


    public function actionCheckActiveAndNewLink(){ // 60 phút chạy 1 lần cho những link đã active + mới tạo
        $active_link_list = SystemCheckNotify::find()
            ->where(['status' => SystemCheckNotify::STATUS_ACTIVE])
            ->orWhere(['status' => SystemCheckNotify::STATUS_NEW])
            ->all();
        $list_links_not_working  = [];

//        self::_writeLog('[CHECK_ACTIVE_AND_NEW_LINK][START]: \n');
        foreach ($active_link_list as $item){

            $item->time_last_check = time();
            $check_result = Helpers::checkLinkIsActive($item->url_check);
            $last_response = json_encode($check_result);
//            var_dump($last_response);die();
            if ($check_result['http_code'] != 200){
                $item->status = SystemCheckNotify::STATUS_INACTIVE;
                $item->time_updated = time();
                $list_links_not_working []= $item->url_check;
                $list_object_not_working []= $item;

            }else{
                if ($item->status == SystemCheckNotify::STATUS_NEW){
                    $item->status = SystemCheckNotify::STATUS_ACTIVE;
                }
            }
            $item->last_response = $last_response;
            $item->save();
//            self::_writeLog('{"url_check": "'.$item->url_check.'", "last_response": '.$last_response.
//                ', "status_after_checking": "'. SystemCheckNotify::getStatusLog()[$item->status].'"}' );
        }
//        self::_writeLog('[CHECK_ACTIVE_AND_NEW_LINK][END] ');


        if(!empty($list_links_not_working)){
            // cảnh báo Telegram
            $list_links_not_working_telegram = Helpers::convertToStringArrayTelegram($list_links_not_working);
            $text = '<i>Xuất hiện nhiều notify url không hoạt động: </i>'.$list_links_not_working_telegram;
            NotifySystem::send($text);
            // cảnh báo Mail
            $list_object_not_working_mail = $list_object_not_working;
            NotifySystem::send($list_object_not_working_mail, 'mail');
        }


        // cảnh báo mail
//        SendMailBussiness::send(
//            self::CONFIG_TEST_MAIL,
//            'Test canh bao kenh mail',
//            'notify_link_check',
//            [
//                'links' => $list_links_not_working
//            ], 'layouts/basic', ''
//        );


    }

    public function actionCheckInactiveLink(){ // 10 phút chạy 1 lần cho những link KHÔNG active
        $active_link_list = SystemCheckNotify::findAll(['status' => SystemCheckNotify::STATUS_INACTIVE]);
        $list_links_not_working = [];
        foreach ($active_link_list as $item){
            $item->time_last_check = time();
            $check_result = Helpers::checkLinkIsActive($item->url_check);
            $last_response = json_encode($check_result);
//            var_dump($last_response);die();
            if ($check_result['http_code'] == 200){
                $item->status = SystemCheckNotify::STATUS_ACTIVE;
                $item->time_updated = time();
            }else{
                $list_links_not_working []= $item->url_check;
                $list_object_not_working []= $item;
            }
            $item->last_response = $last_response;
            $item->save();
        }
        if(!empty($list_links_not_working)){
            // cảnh báo Telegram
            $list_links_not_working = Helpers::convertToStringArrayTelegram($list_links_not_working);
            $text = '<i>Xuất hiện nhiều notify url không hoạt động: </i>'.$list_links_not_working;
            NotifySystem::send($text);

            // cảnh báo Mail
//            $list_links_not_working_mail = $list_links_not_working;
            $list_object_not_working_mail = $list_object_not_working;
            NotifySystem::send($list_object_not_working_mail, 'mail');
        }
    }

    public function actionTest(){
//        $result = Helpers::checkLinkIsActive('https://www.go1121ogle.com/');
        NotifySystem::send(['https://www.google.com' , 'https://www.facebook.com/'], 'mail');
//        var_dump($result);die();

//        SendMailBussiness::send                                                            (
//            'tinbt@nganluong.vn',
//            'Test canh bao kenh mail',
//            'notify_link_check',
//            [
//                'links' => 'https://www.google.com'
//            ], 'layouts/basic', ''
//        );
    }


    private static function _writeLog($data) {

        $file = ROOT_PATH . DS . 'console' . DS . 'system_check_notify' . DS . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
