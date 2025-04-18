<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;


class CurlNetworkController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function actionCheckCallbackUrl()
    {
        $connected = @fsockopen("https://kh.dai-ichi-life.com.vn:8443", 80);
        //website, port  (try 80 or 443)
        if ($connected) {
            $is_conn = 12; //action when connected
            fclose($connected);
        } else {
            $is_conn = 11; //action in connection failure
        }
        echo $is_conn;
        die();
    }

}