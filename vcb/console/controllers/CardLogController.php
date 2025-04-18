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
use common\models\db\CardTransaction;
use common\models\db\CardLog;
use common\models\business\CardLogBusiness;
use common\components\utils\Strings;
use Yii;

class CardLogController extends Controller
{

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function actionCreateCardTransaction()
    {
        $this->_writeLog('[CardLog::CreateCardTransaction] start');
        $now = $start = time();
        while (time() - $start < 45) {
            $timeout = time() - 300;
            if (CardLog::checkCreateCardTransaction($timeout)) {
                set_time_limit(120);
                $result = CardLogBusiness::createCardTransaction(['timeout' => $timeout]);
                $this->_writeLog('[CardLog::CreateCardTransaction] result: ' . json_encode($result));
                sleep(3);
            } else {
                sleep(10);
            }
        }
        $this->_writeLog('[CardLog::CreateCardTransaction] end');
    }

    public function actionBackup()
    {
        $this->_writeLog('[CardLog::Backup] start');
        $now = $start = time();
        while (time() - $start < 540) {
            $timeout = time() - 300;
            if (CardLog::checkBackup($timeout)) {
                set_time_limit(120);
                $result = CardLogBusiness::backup(['timeout' => $timeout]);
                $this->_writeLog('[CardLog::Backup] result: ' . json_encode($result));
                sleep(5);
            } else {
                sleep(30);
            }
        }
        $this->_writeLog('[CardLog::Backup] end');
    }

    private static function _writeLog($data)
    {
        $file = fopen(ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'console' . DS . date('Ymd') . '.txt', 'a');
        if ($file) {
            fwrite($file, '[' . date('d/m/Y, H:i:s') . ']' . $data . "\n");
            fclose($file);
            return true;
        }
        return false;
    }

}
