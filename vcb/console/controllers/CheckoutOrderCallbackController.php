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
use common\models\db\CheckoutOrderCallback;
use common\models\business\SendMailBussiness;
use common\components\utils\Strings;
use Yii;

class CheckoutOrderCallbackController extends Controller {

    public function init() {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function actionProcess() {
            $this->_writeLog('[CheckoutOrderCallback::process] start');
            $now = $start = time();
            while (time() - $start < 45) {
                $data = CheckoutOrderCallback::getCurrentProcessInfo();

                $this->_writeLog('[LOGS_INFO]: ' . json_encode($data) );
                    $this->_writeLog('[CheckoutOrderCallback::process] data: ' . json_encode($data));
                    if ($data != false) {
                        set_time_limit(120);
                        $result = CheckoutOrderCallback::process($data);
                        $this->_writeLog('[CheckoutOrderCallback::process] result: ' . json_encode($result));
                        sleep(30);
                    } else {
                        sleep(30);
                    }

            }
            $this->_writeLog('[CheckoutOrderCallback::process] end');

    }

    private static function _writeLog($data) {

        $file = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'console' . DS . "checkout_order_callback" . DS . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
