<?php

namespace cron\controllers;

use common\models\business\MerchantBusiness;
use common\models\business\SendMailBussiness;
use common\models\db\Merchant;
use common\models\db\User;
use common\models\db\UserLogin;
use common\models\business\InstallmentBusiness;
use common\models\db\InstallmentConversion;
use Yii;
use cron\components\CronBasicController;
use common\models\business\CompareBusiness;
use yii\db\Expression;

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18');
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Net' . DS . 'SFTP.php';
require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'phpseclib1.0.18' . DS . 'Crypt' . DS . 'RSA.php';

class InstallmentReportController extends CronBasicController {

//    const SFTP_SERVER = '10.0.0.134';
    const SFTP_SERVER = '10.1.100.21';
//    const SFTP_USER = 'sftp_21';
    const SFTP_USER = 'vietcombank_sftp';
//    const SFTP_PRIVATE_KEY_PATH = ROOT_PATH . DS . '../pri_key/id_rsa_vcb';
    const SFTP_PRIVATE_KEY_PATH = ROOT_PATH . DS . '../pri_key/id_rsa.txt';
    // const SFTP_COMPARE_DIR_PATH = '/data/www/data.nganluong.vn/banks/vcb/vcb_out/';
//    const SFTP_COMPARE_DIR_PATH = 'vcb_out';
    const SFTP_COMPARE_DIR_PATH = 'out';
//    const SFTP_COMPARE_DIR_PATH = 'in';

    public function actionIndex() {
        $this->writeLog('---------------------[START]------------------');
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
        $result = InstallmentBusiness::exportFileCompare([
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);
        $result_mail = InstallmentBusiness::writeExcel($result,[
            'time_from' => $time_from,
            'time_to' => $time_to,
        ]);
        $this->writeLog('[EXPORT-RESULT]' . json_encode($result));
        $this->writeLog('[EXPORT-RESULT-MAIL]' . json_encode($result_mail));
        //push file to sftp
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
        // }

        //Send mail gửi file đính kèm danh sách giao dịch thành công
        $file_path_mail = $result_mail['file_path'];
        $file_name_mail = $result_mail['file_name'];
        $send_to = [
            'lylk@nganluong.vn',
            'luongdt@nganluong.vn',
        ];
        $current_date = $day-1 .'/'. $mon .'/'. $year;
        $subject = 'File đối soát NL - Cổng CDTG VCB ngày '. $day .'/'. $mon .'/'. $year;
        $body_content = 'Dear team Vận Hành,<br>Hệ thống gửi file đối soát NL - Cổng CDTG VCB từ 00h ngày '. $current_date .' đến 23h59p59 ngày '. $current_date .'.<br>'.
                        'File được đẩy vào thư mục '.strtoupper(self::SFTP_COMPARE_DIR_PATH).', tại '.self::SFTP_SERVER.',<br>Thời gian đẩy file: '.date('H:i:s d/m/Y');
        $mail = SendMailBussiness::sendAttach($send_to,[], $subject, 'notify_transaction_daily_cdtg', ['body_content' => $body_content],'layouts/basic', $file_path_mail);
        if($mail){
            $this->writeLog('Success mail to: ' . json_encode($send_to));
        }else{
            $this->writeLog('Mail false!');
        }
        $this->writeLog('---------------------[END]------------------');
        echo 'done !';die();
    }
    
}