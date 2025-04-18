<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 11/03/2017
 * Time: 11:36 SA
 */

namespace console\controllers;

use yii\console\Controller;
use common\components\libs\Tables;
use common\models\db\QueueNotify;
use common\models\business\SendMailBussiness;
use common\components\utils\Strings;
use Yii;

class QueueNotifyController extends Controller
{

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function actionProcess()
    {
        $this->_writeLog('[QueueNotify::process] start');
        $now = $start = time();
        while (time() - $start < 45) {
            $queue_notify_info = QueueNotify::getCurrentQueueInfo();
            echo "<pre>";
            var_dump($queue_notify_info);
            die();
            if ($queue_notify_info != false) {
                set_time_limit(120);
                $result = QueueNotify::process($queue_notify_info);
                if ($result['error_message'] == '') {
                    $this->_writeLog('[QueueNotify::process] id:' . $queue_notify_info['id'] . ', message: xử lý thành công');
                } else {
                    $this->_writeLog('[QueueNotify::process] id:' . $queue_notify_info['id'] . ', message: ' . $result['error_message']);
                }
                sleep(5);
            } else {
                sleep(30);
            }
        }
        $this->_writeLog('[QueueNotify::process] end');
    }

    private static function _writeLog($data) {

        $file = ROOT_PATH . DS . 'console' . DS . 'queue_notify' . DS . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
    }
}
