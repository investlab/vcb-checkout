<?php

namespace console\controllers;

use common\components\libs\ConfigFile;
use common\components\utils\Logs;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\business\CompareBusiness;
use common\models\business\SendMailBussiness;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\Transaction;
use Crypt_RSA;
use Net_SFTP;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18');
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Net' . DS . 'SFTP.php';
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Crypt' . DS . 'RSA.php';

class CompareV2Controller extends Controller
{
    const PATH_COMPARE = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'compare' . DS . 'v2' . DS;
    protected $date;

    public function actionIndex()
    {
        $this->writeLog("=== START ===");
        $date = null;
        $this->date = $date != null ? $date : self::_getDate();


        $file_master_path = $this->getFile();

        if ($file_master_path) {
            $fill_data = $this->fillData($file_master_path);
            if ($fill_data != false) {
                $this->writeLog("=== END FILL DATA ===");
                $this->putFile($fill_data);
            } else {
                $this->writeLog("Fill data fail");
                $this->writeLog("=== END FILL DATA ===");
            }
        } else {
            $this->writeLog("No fill data");
        }


        $this->writeLog("=== END ===");
    }
    public function actionPost()
    {
//        echo "<pre>";
//        var_dump("Stop by QuangNT");
//        die();
        ini_set('memory_limit', '-1');

        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = null;
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) { // auto run without parameters
            $get_date = getdate();
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
        } else { // manual run with parameters
            $get_date = explode("/", $date_time);
            $day = $get_date[0];
            $mon = $get_date[1];
            $year = $get_date[2];
        }
        // echo $mon, $day, $year ;
        $time_from = mktime(0, 0, 0, $mon, $day, $year);
        $time_to = mktime(23, 59, 59, $mon, $day, $year);
        //echo $time_from . ' | '.$time_to . ' | '. $date_time ;
//        $result = CompareBusiness::exportFileCompare([
//            'time_from' => $time_from,
//            'time_to' => $time_to,
//        ]);
//
//
//        $this->writeLog('[EXPORT-RESULT]' . json_encode($result));
//        // if ($result['error_message'] == '') {
//        $file_path = $result['file_path'];
//        $file_name = $result['file_name'];
//
//
//        $this->writeLog('Connect SFTP...');
//        $sftp = new \Net_SFTP(self::SFTP_SERVER);
//        $key = new \Crypt_RSA();
//        $key->loadKey(file_get_contents(self::SFTP_PRIVATE_KEY_PATH));
//        if (!$sftp->login(self::SFTP_USER, $key)) {
//            $this->writeLog('Login Failed');
//            exit();
//        }
//        $this->writeLog('Login Success');
//        $this->writeLog('Current Directory: ' . $sftp->pwd());
//        $cd = $sftp->chdir(self::SFTP_COMPARE_DIR_PATH);
//        if (!$cd) {
//            $this->writeLog('Change Directory Failed');
//            exit();
//        }
//        $this->writeLog('Current Directory: ' . $sftp->pwd());
//        $put = $sftp->put($file_name, $file_path, NET_SFTP_LOCAL_FILE);
//        $this->writeLog('Upload to SFTP Server: ' . json_encode($put));
//
//        $file_names = 'doisoat' . DS . $this->id . DS . date("Ym", time()) . ".txt";
//        $pathinfo = pathinfo($file_names);
//        Logs::create($pathinfo['dirname'], $pathinfo['basename'], Yii::$app->getRequest()->getUserIP() . ' | ' . $file_name . ' | ' . ' Trạng thái đẩy file lên server đối soát: ' . (($put == false) ? "Thất bại " : "Thành công"));
//        $this->writeLog('====================================================================');
        $this->writeLog('Start gửi file vận hành');

        $result_excel = CompareBusiness::exportFileCompareExcelPos([
            'time_from' => 1687366800,
            'time_to' => 1689353999,
        ]);
        $file_path_excel = $result_excel['file_path'];
        $file_name_excel = $result_excel['file_name'];
        //Send mail gửi file đính kèm danh sách giao dịch thành công
        $sent_to = [
//            'doitac@nganluong.vn',
//            'quangnt@nganluong.vn',
            'linhhv@nganluong.vn',
        ];
        $cc = [
        ];
        $current_date = $day . '/' . $mon . '/' . $year;
        $subject = 'File đối soát pos cổng VCB ngày ' . date('d/m/Y');
        $body_content = 'Dear vận hành ' . '<br/>' . 'Team kĩ thuật gửi file đối soát post cổng VCB ' . $current_date . ' (note:ngày T-1)';
        SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name_excel, $cc);
//        $body_content_dat = 'Dear vận hành ' . '<br/>' . 'Team kĩ thuật gửi file đối soát cổng VCB ' . $current_date . ' (note:ngày T-1) theo định dạng dat';

//        SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content_dat], 'layouts/basic', $file_name, $cc);
        $this->writeLog('End gửi file vận hành');

        // }
    }


    /**
     * @throws Exception
     */
    private function fillData($file_path)
    {

        $this->writeLog("=== START FILL DATA ===");
        $merchant_conf = glob("../config/compare/mc-conf/*.env");

        $env_mc = [];
        $row = 1;
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $this->writeLog("Open file success");

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row == 1) {
                    $data[35] = "Order_code";
                    $data[36] = "Token_code";
                    $data[37] = "Cardholder_name";

                    foreach ($merchant_conf as $item) {
                        $data_mc = $data;
                        $conf = ConfigFile::load(dirname(__FILE__) . DS . $item);
                        if (isset($conf['MERCHANT_ID'])) {
                            $env_mc[$conf['MERCHANT_ID']] = $conf;
                            $file_name = $this->getFileName($conf);
                            $file_send_path = self::PATH_COMPARE . $conf['MERCHANT_CODE'] . DS . "send" . DS . $file_name;
                            $env_mc[$conf['MERCHANT_ID']]['PATH_FILE_WRITE'] = $file_send_path;
                            $env_mc[$conf['MERCHANT_ID']]['ROW'] = 1;
                            $env_mc[$conf['MERCHANT_ID']]['FILE_NAME'] = $file_name;
                            $file_write = fopen($file_send_path, "w");
                            $this->setHeaderRow($data_mc, $conf['MERCHANT_ID']);
                            fputcsv($file_write, $data_mc);
                        }
                    }
                } else {
                    if ($row % 60 == 0) {
                        Yii::$app->db->close();
                        Yii::$app->db->open();
                        $this->writeLog("Reconnect database");
                    }
                    $bank_refer_code = false;
                    if (!empty($data[31]) && $data[31] != '0') {
                        $bank_refer_code = trim($data[31]);

                    } elseif (!empty($data[19]) && $data[19] != '0') {
                        $bank_refer_code = trim($data[19]);
                    }
                    if ($bank_refer_code && trim($data[6]) != 'E-VISA VIETNAM') {
                        $this->writeLog("Process line: " . $row);
                        $transaction = Transaction::find()
                            ->where(['bank_refer_code' => $bank_refer_code])
//                            ->andWhere(['status' => Transaction::STATUS_PAID])
                            ->one();
                        if ($transaction) {
                            $checkout_order = CheckoutOrder::find()
                                ->where(['transaction_id' => $transaction->id])
                                ->andWhere(['status' => CheckoutOrder::STATUS_PAID])
                                ->one();
                            if ($checkout_order) {
                                $merchant_id = $checkout_order->merchant_id;
                                $data[35] = $checkout_order->order_code;
                                $data[36] = $checkout_order->token_code;
                                $data[37] = $transaction->card_info != "" ? strtoupper(Strings::_convertToSMS(json_decode($transaction->card_info)->card_fullname)) : "UNKNOWN";
                                $this->setDataRow($data, $merchant_id, $checkout_order);
                                if (array_key_exists($merchant_id, $env_mc)) {
                                    $env_mc[$merchant_id]['ROW']++;
                                    $file_write = fopen($env_mc[$merchant_id]['PATH_FILE_WRITE'], "a");
                                    fputcsv($file_write, $data);
                                }
                            }
                        }
                    }
                }
                $row++;
            }
            return $env_mc;
        } else {
            $this->writeLog("Can't not open file: " . $file_path);
        }
        return false;
    }

    private function getFile()
    {
        $env = ConfigFile::load(dirname(__FILE__) . DS . '../config/compare/master.env');
        $sftp = $this->login($env);
        if ($sftp->chdir($env['PATH'])) {
            $this->writeLog("Change dir server partner to: " . $env['PATH']);
            $ls = $sftp->nlist();
            $date = null;
            $date_get_file = $date != null ? date("Ymd", strtotime($date) - 86400) : self::_getDate(-1);
            $file_name = $env['PREFIX_FILE_NAME'] . $date_get_file . ".csv";
            if (in_array($file_name, $ls)) {
                $this->writeLog("Get file:" . $file_name);
                $file_receive_path = self::PATH_COMPARE . "master" . DS . "receive" . DS . $file_name;
                return $file_receive_path;

//                if ($sftp->get($file_name, $file_receive_path)) {
//                    $this->writeLog("Get file:" . $file_name . " to " . $file_receive_path);
//                    return $file_receive_path;
//                } else {
//                    $this->writeLog("Can't get file from server partner");
//                }
            } else {
                $this->writeLog("File: " . $file_name . ' not exist in server partner');
            }
        } else {
            $this->writeLog("Change dir server partner fail");
        }
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

    protected function writeLog($data) {
        $file_name = 'console' . DS . $this->id . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }


}