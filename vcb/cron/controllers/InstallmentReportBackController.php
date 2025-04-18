<?php

namespace cron\controllers;

use common\models\business\SendMailBussiness;
use installment_offline\common\models\business\InstallmentBusiness;
use Yii;
use cron\components\CronBasicController;
use common\models\business\CompareBusiness;

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18');
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Net' . DS . 'SFTP.php';
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Crypt' . DS . 'RSA.php';

class InstallmentReportBackController extends CronBasicController {
    const DIR_PATH = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'compare' . DS . 'cdtg_back' . DS;
//    const SFTP_SERVER = '10.0.0.134';
    const SFTP_SERVER = '10.1.100.21';
//    const SFTP_USER = 'sftp_21';
    const SFTP_USER = 'vietcombank_sftp';
//    const SFTP_PRIVATE_KEY_PATH = ROOT_PATH . DS . '../pri_key/id_rsa_vcb';
    const SFTP_PRIVATE_KEY_PATH = ROOT_PATH . DS . '../pri_key/id_rsa.txt';
    // const SFTP_COMPARE_DIR_PATH = '/data/www/data.nganluong.vn/banks/vcb/vcb_out/';
//    const SFTP_COMPARE_DIR_PATH = 'vcb_out';
    const SFTP_COMPARE_DIR_PATH = 'out';

    public function actionIndex() {
        $this->writeLog('---------------------[START REPORT BACK]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) { // auto run without parameters
            $get_date = getdate();
            $day = $get_date['mday'];
            $mon = $get_date['mon'];
            $year = $get_date['year'];
        } else { // manual run with parameters
            $get_date = getdate(strtotime($date_time));
            $day = $get_date['mday'];
            $mon = $get_date['mon'];
            $year = $get_date['year'];
        }
        $time_from = mktime(0 , 0, 0, $mon, $day, $year);
        $time_to = mktime(23, 59, 59, $mon, $day, $year);

        $file_name = InstallmentBusiness::getFileName([
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);
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
        $data = $sftp->get($file_name,self::DIR_PATH.DS.$file_name);
//die();
        if($data){
            $result_mail = InstallmentBusiness::writeExcelBack($file_name,[
                'time_from' => $time_from - 86400,
                'time_to' => $time_to - 86400,
            ]);
//            var_dump($result_mail);die();
            $file_path_mail = $result_mail['file_path'];
            $send_to = [
                'lylk@nganluong.vn',
                'luongdt@nganluong.vn',
            ];
            $current_date = $day-1 .'/'. $mon .'/'. $year;
            $subject = 'File sai lệch NL - Cổng CDTG VCB ngày '. $day .'/'. $mon .'/'. $year;
            $body_content = 'Dear team Vận Hành,<br>Hệ thống gửi file sai lệch NL - Cổng CDTG VCB ngày '. $current_date .'.<br>'.
                'File được lấy từ thư mục OUT, tại '.self::SFTP_SERVER.',<br>Thời gian lấy file: '.date('H:i:s d/m/Y');
            $mail = SendMailBussiness::sendAttach($send_to,[], $subject, 'notify_transaction_daily_cdtg', ['body_content' => $body_content],'layouts/basic', $file_path_mail);
            if($mail){
                $this->writeLog('Success mail to: ' . json_encode($send_to));
            }else{
                $this->writeLog('Mail false!');
            }
            $this->writeLog('---------------------[END]------------------');
            echo 'done !';die();
        }
        else{
            $send_to = [
                'lylk@nganluong.vn',
                'luongdt@nganluong.vn',
            ];
            $current_date = $day .'/'. $mon .'/'. $year;
            $subject = 'File sai lệch NL - Cổng CDTG VCB ngày '. $current_date;
            $body_content = 'Dear team Vận Hành,<br>Ngày '. $current_date .' không có file sai lệch .<br>';
            SendMailBussiness::send($send_to, $subject, 'notify_transaction_daily_cdtg', ['body_content' => $body_content]);
//            if($mail){
                $this->writeLog('Success mail to: ' . json_encode($send_to));
//            }else{
//                $this->writeLog('Mail false!');
//            }
            $this->writeLog('---------------------[END]------------------');
            echo 'done !';die();
        }

    }

}