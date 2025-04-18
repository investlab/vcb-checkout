<?php

namespace console\controllers;

use common\components\libs\ConfigFile;
use common\components\libs\NotifySystem;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\business\CompareBusiness;
use common\models\business\SendMailBussiness;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use Crypt_RSA;
use Net_SFTP;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18');
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Net' . DS . 'SFTP.php';
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Crypt' . DS . 'RSA.php';

class CheckoutOrderReportController extends Controller
{
    const PATH_COMPARE = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'compare' . DS . 'v2' . DS;
    protected $date;

    public function actionIndex($date = null)
    {
        $this->writeLog("=== START ===");

        $this->date = $date != null ? $date : self::_getDate(-1);

        $file_master_path = $this->getFile();
        if ($file_master_path) {
            $fill_data = $this->processData($file_master_path);
            if ($fill_data) {
                $this->writeLog("=== END PROCESS DATA ===");
            } else {
                $this->writeLog("Process data fail");
                $this->writeLog("=== END PROCESS DATA ===");
            }
        } else {
            $this->writeLog("No fill data");
        }


        $this->writeLog("=== END ===");
    }

    private function processData($file_path)
    {
        $this->writeLog("=== START FILL DATA ===");
        $row = 1;
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $this->writeLog("Open file success");
            $list_bank_refer_codes = array();
            $list_transaction_ids = array();
            $list_transaction_refund = array();

            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                if ($row == 1) {
                } else {

                    $bank_refer_code = false;
                    if (!empty($data[31]) && $data[31] != '0') {
                        $bank_refer_code = trim($data[31]);

                    } elseif (!empty($data[19]) && $data[19] != '0') {
                        $bank_refer_code = trim($data[19]);
                    }
                    if ($bank_refer_code) {
                        $list_bank_refer_codes[] = "'" . $bank_refer_code . "'";
                    }
                }
                $row++;
            }
            $this->writeLog("[COUNT_LIST_BANK]" . count($list_bank_refer_codes));
            $transactions = array();
            if (!empty($list_bank_refer_codes)) {
                $transactions = Tables::selectAllDataTable(Transaction::tableName(), "transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " AND bank_refer_code IN (" . implode(',', $list_bank_refer_codes) . ") ", "", "id");
                $this->writeLog("[COUNT_TRANSACTION]" . count($transactions));
                foreach ($transactions as $transaction) {
                    $list_transaction_ids[$transaction['id']] = $transaction['id'];
                }
            }
            if (!empty($list_transaction_refund)) {
                foreach ($list_transaction_refund as $transaction) {
                    $list_transaction_ids[$transaction] = $transaction;
                }
            }
            $sql_update = "UPDATE " . Transaction::tableName() . " SET compare = 1 WHERE id IN (" . implode(',', array_keys($transactions)) . ")";

//            echo "<pre>";
//            var_dump($sql_update);
//            die();

            try {
                $connection = Yii::$app->getDb();
                $command = $connection->createCommand($sql_update);
                $result = $command->execute();
                if ($result) {
                    $this->writeLog("[UPDATE COMPARE] Success - " . date("Ymd/m/y H:i:s", time()));
                } else {
                    $this->writeLog("[UPDATE COMPARE] Fail - " . date("Ymd/m/y H:i:s", time()));
                }
            } catch (\Exception $exception) {
                $this->writeLog("[UPDATE COMPARE] Fail - " . date("Ymd/m/y H:i:s", time()) . $exception->getMessage());
                NotifySystem::send("KÍU - Lỗi update giao dịch báo có!!! " . date("Ymd/m/y H:i:s", time()), "<br>" . $exception->getMessage());
            }

            return true;
        } else {
            $this->writeLog("Can't not open file: " . $file_path);
        }
        return false;
    }

    private function getFile()
    {
//        $env = ConfigFile::load("../config/compare/master.env");
//        $sftp = $this->login($env);

//        $date = Yii::$app->request->get('date');
        $date_get_file = $this->date;
        $file_name = "NGANLUONG_" . $date_get_file . ".csv";
        return self::PATH_COMPARE . "master" . DS . "receive" . DS . $file_name;

//        if ($sftp->chdir($env['PATH'])) {
//            $this->writeLog("Change dir server partner to: " . $env['PATH']);
//            $ls = $sftp->nlist();
//            $date = Yii::$app->request->get('date');
//            $date_get_file = $date != null ? date("Ymd", strtotime($date) - 86400) : self::_getDate(-1);
//            $file_name = $env['PREFIX_FILE_NAME'] . $date_get_file . ".csv";
//            if (in_array($file_name, $ls)) {
//                $this->writeLog("Get file:" . $file_name);
//                $file_receive_path = self::PATH_COMPARE . "master" . DS . "receive" . DS . $file_name;
//                if ($sftp->get($file_name, $file_receive_path)) {
//                    $this->writeLog("Get file:" . $file_name . " to " . $file_receive_path);
//                    return $file_receive_path;
//                } else {
//                    $this->writeLog("Can't get file from server partner");
//                }
//            } else {
//                $this->writeLog("File: " . $file_name . ' not exist in server partner');
//            }
//        } else {
//            $this->writeLog("Change dir server partner fail");
//        }
        return false;
    }

    private function login($env)
    {
        $sftp = new Net_SFTP($env['ADDRESS'], $env['PORT']);
        if (isset($env['PRIVATE_KEY'])) {
            $pass = new Crypt_RSA();
            $path_pri_key = ROOT_PATH . DS . 'cron' . DS . 'config' . DS . 'compare' . DS . 'pri_key' . DS;
            $pass->loadKey(file_get_contents($path_pri_key . $env['PRIVATE_KEY']));
        } elseif (isset($env['PASSWORD'])) {
            $pass = $env['PASSWORD'];
        } else {
            die("How to login?????");
        }

        if (!$sftp->login($env['USER'], $pass)) {
            $this->writeLog("Login server partner fail");
            return false;
        } else {
            $this->writeLog("Login server partner success");
            return $sftp;
        }
    }

    private function putFile($datas)
    {
        $this->writeLog("=== START PUT FILE ===");
        foreach ($datas as $data) {
            $this->writeLog("=== " . $data['MERCHANT_CODE'] . " ===");
            $sftp = $this->login($data);
            if ($sftp->chdir($data['PATH'])) {
                $this->writeLog("Change dir to: " . $data['PATH']);
                $put = $sftp->put($data['FILE_NAME'], $data['PATH_FILE_WRITE'], NET_SFTP_LOCAL_FILE);
                if ($put) {
                    $this->writeLog("Put file success");
                    $this->sendMail($data['MERCHANT_ID'], $data['PATH_FILE_WRITE']);
                    $change_permission = $sftp->chmod(0755, $sftp->pwd() . "/" . $data['FILE_NAME']);
                    if ($change_permission) {
                        unset($sftp);
                        $this->writeLog("Change permission success");
                    } else {
                        $this->writeLog("Change permission fail");
                    }
                } else {
                    $this->writeLog("Put file fail");
                }
            } else {
                $this->writeLog("Change dir fail");
            }
        }
        $this->writeLog("=== END PUT FILE ===");
    }

    private static function _getDate($dif = 0)
    {
        return date('Ymd', strtotime($dif . ' day', time()));
    }

    private function _convertDate($date, $format)
    {
        if (strpos($format, "h") || strpos($format, "i")) {
            $date .= " 08:30";
        }

        return date($format, strtotime($date));
    }

    private function setDataRow(&$data, $merchant_id, $checkout_order)
    {
        switch ($merchant_id) {
            case "78": /*Daiichi*/
            {
                $status_names = CheckoutOrder::getStatus();
//                $data[18] = $data[35];
                $data[38] = Translate::get($status_names[$checkout_order->status]);
                $this->unsetColumns($data, $merchant_id);
                break;
            }
        }
    }

    private function setHeaderRow(&$data, $merchant_id)
    {
        switch ($merchant_id) {
            case "78": /*Daiichi*/
            {
                $data[38] = "Status";
                $this->unsetColumns($data, $merchant_id);
                break;
            }
        }
    }

    private function unsetColumns(&$data, $merchant_id)
    {
        switch ($merchant_id) {
            case "78": /*Daiichi*/
            {
                unset($data[6]); /*Address*/
                unset($data[17]); /*Trace2*/
                unset($data[19]); /*MID DATA_1*/
                unset($data[20]); /*ARN*/
                unset($data[33]); /*Batch*/
                unset($data[39]); /*Reserved*/
                unset($data[40]); /*Reserved*/
                unset($data[41]); /*Reserved*/
                unset($data[42]); /*Reserved*/
                break;
            }
        }

    }

    private function sendMail($merchant_id, $file_path)
    {
        $sent_to = [
            'doitac@nganluong.vn',
//            'quangnt@nganluong.vn',
//            'linhhv@nganluong.vn',
        ];
        $cc = [];
        $merchant = Merchant::find()->where(['id' => $merchant_id])->one();
        $subject = 'File đối soát(v2) cổng VCB ngày ' . date('d/m/Y');
        $body_content = 'Dear vận hành ' . '<br/>' . 'Team kĩ thuật gửi file đối soát cổng VCB ' . $this->date . ' (note:ngày T-1) <br>Merchant: ' . $merchant->name;
        SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_path, $cc);

    }

    private function getFileName($env): string
    {
        $file_name = $env['PREFIX_FILE_NAME'];
        if (isset($env['SUFFIX_FILE_NAME'])) {
            $file_name .= $this->_convertDate($this->date, $env['SUFFIX_FILE_NAME']);
        } else {
            $file_name .= $this->date;
        }
        $file_name .= $env['EXT_FILE'];

//        $test_file = Yii::$app->request->get('is_test');
//        if ($test_file == 'quangnt') {
//            $file_name .= '_test';
//        }


        return $file_name;
    }

    protected function writeLog($data)
    {
        $file_name = 'console' . DS . $this->id . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }


}