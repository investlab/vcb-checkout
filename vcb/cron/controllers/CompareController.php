<?php

namespace cron\controllers;

use common\components\libs\NotifySystem;
use common\components\utils\Logs;
use common\models\business\SendMailBussiness;
use Yii;
use cron\components\CronBasicController;
use common\models\business\CompareBusiness;

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18');
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Net' . DS . 'SFTP.php';
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Crypt' . DS . 'RSA.php';

class CompareController extends CronBasicController
{

    const SFTP_SERVER = '10.0.0.134';
    const SFTP_USER = 'sftp_21';
    const SFTP_PRIVATE_KEY_PATH = ROOT_PATH . DS . '../pri_key/id_rsa_vcb';
    // const SFTP_COMPARE_DIR_PATH = '/data/www/data.nganluong.vn/banks/vcb/vcb_out/';
    const SFTP_COMPARE_DIR_PATH = 'vcb_out';

    public function actionIndex()
    {
//        echo "<pre>";
//        var_dump("Stop by QuangNT");
//        die();
        ini_set('memory_limit', '-1');
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
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
        $result = CompareBusiness::exportFileCompare([
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);


        $this->writeLog('[EXPORT-RESULT]' . json_encode($result));
        // if ($result['error_message'] == '') {
        $file_path = $result['file_path'];
        $file_name = $result['file_name'];

        $this->writeLog('Connect SFTP...');
        $sftp = new \Net_SFTP(self::SFTP_SERVER);
        $key = new \Crypt_RSA();
        $key->loadKey(file_get_contents(self::SFTP_PRIVATE_KEY_PATH));
        if (!$sftp->login(self::SFTP_USER, $key)) {
            $this->writeLog('Login Failed');
            exit();
        }
        $this->writeLog('Login Success');
        $this->writeLog('Current Directory: ' . $sftp->pwd());
        $cd = $sftp->chdir(self::SFTP_COMPARE_DIR_PATH);
        if (!$cd) {
            $this->writeLog('Change Directory Failed');
            exit();
        }
        $this->writeLog('Current Directory: ' . $sftp->pwd());
        $put = $sftp->put($file_name, $file_path, NET_SFTP_LOCAL_FILE);
        $this->writeLog('Upload to SFTP Server: ' . json_encode($put));

        if($put === false){
            @NotifySystem::send("Lỗi đẩy SFTP Compare");
        }

        $file_names = 'doisoat' . DS . $this->id . DS . date("Ym", time()) . ".txt";
        $pathinfo = pathinfo($file_names);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], Yii::$app->getRequest()->getUserIP() . ' | ' . $file_name . ' | ' . ' Trạng thái đẩy file lên server đối soát: ' . (($put == false) ? "Thất bại " : "Thành công"));
        $this->writeLog('====================================================================');
        $this->writeLog('Start gửi file vận hành');

        $result_excel = CompareBusiness::exportFileCompareExcel([
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);
        $file_path_excel = $result_excel['file_path'];
        $file_name_excel = $result_excel['file_name'];
        //Send mail gửi file đính kèm danh sách giao dịch thành công
        $sent_to = [
            'doitac@nganluong.vn',
            'quangnt@nganluong.vn',
            'tinbt@nganluong.vn',
            'ketoan@nganluong.vn'
//            'linhhv@nganluong.vn',
        ];
        $cc = [
        ];
        $current_date = $day . '/' . $mon . '/' . $year;
        $subject = 'File đối soát cổng VCB ngày ' . date('d/m/Y');
        $body_content = 'Dear vận hành ' . '<br/>' . 'Team kĩ thuật gửi file đối soát cổng VCB ' . $current_date . ' (note:ngày T-1)';
        SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name_excel, $cc);
        $body_content_dat = 'Dear vận hành ' . '<br/>' . 'Team kĩ thuật gửi file đối soát cổng VCB ' . $current_date . ' (note:ngày T-1) theo định dạng dat';

        SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content_dat], 'layouts/basic', $file_name, $cc);
        $this->writeLog('End gửi file vận hành');

        // }
    }
    public function actionPost()
    {
        ini_set('memory_limit', '-1');

//        echo "<pre>";
//        var_dump("Stop by QuangNT");
//        die();
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
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
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);
        $file_path_excel = $result_excel['file_path'];
        $file_name_excel = $result_excel['file_name'];
        //Send mail gửi file đính kèm danh sách giao dịch thành công
        $sent_to = [
            'doitac@nganluong.vn',
//            'quangnt@nganluong.vn',
            'linhhv@nganluong.vn',
            'davidsuperpia@gmail.com',
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

    public function actionExportManual()
    {
//        echo "<pre>";
//        var_dump("Hi");
//        die();
        $from_date = Yii::$app->request->get('from_date');
        $to_date = Yii::$app->request->get('to_date');
        $file_name = Yii::$app->request->get('file_name');
        $exception_date = Yii::$app->request->get('exception_date');
        if (empty($from_date) || empty($to_date) || empty($file_name)) {
            echo "Input invalid";
            die();
        }
        $this->writeLog('---------------------[START]------------------');

//        Create Datetime

        $from_date = explode("-", $from_date);
        $to_date = explode("-", $to_date);
        $exception_date_arr = $exception_date != NULL ? explode(";", $exception_date) : false;

        $result = CompareBusiness::exportFileCompareManual([
            'time_from' => $from_date,
            'time_to' => $to_date,
            'exception_date' => $exception_date_arr,
            'file_name' => $file_name
        ]);
//
//        echo "<pre>";
//        var_dump($result);
//        die();

        $this->writeLog('[EXPORT-RESULT]' . json_encode($result));
        // if ($result['error_message'] == '') {
        $file_path = $result['file_path'];
        $file_name = $result['file_name'];

        $this->writeLog('Connect SFTP...');
        $sftp = new \Net_SFTP(self::SFTP_SERVER);
        $key = new \Crypt_RSA();
        $key->loadKey(file_get_contents(self::SFTP_PRIVATE_KEY_PATH));
        if (!$sftp->login(self::SFTP_USER, $key)) {
            $this->writeLog('Login Failed');
            exit();
        }
        $this->writeLog('Login Success');
        $this->writeLog('Current Directory: ' . $sftp->pwd());
        $cd = $sftp->chdir(self::SFTP_COMPARE_DIR_PATH);
        if (!$cd) {
            $this->writeLog('Change Directory Failed');
            exit();
        }
        $this->writeLog('Current Directory: ' . $sftp->pwd());
        $put = $sftp->put($file_name, $file_path, NET_SFTP_LOCAL_FILE);
        $this->writeLog('Upload to SFTP Server: ' . json_encode($put));

        $file_names = 'doisoat' . DS . $this->id . DS . date("Ym", time()) . ".txt";
        $pathinfo = pathinfo($file_names);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], Yii::$app->getRequest()->getUserIP() . ' | ' . $file_name . ' | ' . ' Trạng thái đẩy file lên server đối soát: ' . (($put == false) ? "Thất bại " : "Thành công"));


    }

    public function actionDoisoat()
    {
        $date = Yii::$app->request->post('date', date("Ym", time()));
        $file_names = LOG_PATH . 'doisoat' . DS . $this->id . DS . $date . ".txt";
        $fp = @fopen($file_names, 'r');
        $contents = @fread($fp, filesize($file_names)); //đọc file
        @fclose($fp); //đóng file

        echo $contents;
    }

}
