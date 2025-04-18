<?php

namespace cron\controllers;

use common\components\libs\Tables;
use common\models\business\SendMailBussiness;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use Yii;
use cron\components\CronBasicController;
use common\models\business\TransactionBusiness;
use yii\helpers\VarDumper;

class MerchantTotalTransactionController extends CronBasicController
{

    const DIR_PATH = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'export_data' . DS . 'merchant-total-transaction' . DS;
    const STATUS_NEW = 1;
    const STATUS_PAYING = 2;
    const STATUS_CANCEL = 3;
    const STATUS_PAID = 4;

    public function actionIndex()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            44 => 'PHÒNG CS QLHC VỀ TTXH',
            45 => 'PHÒNG QL XNC 44 PHẠM NGỌC THẠCH',
            70 => 'CƠ SỞ ĐĂNG KÝ XE SỐ 1',
            71 => 'CƠ SỞ ĐĂNG KÝ XE SỐ 2',
            72 => 'CƠ SỞ ĐĂNG KÝ XE SỐ 3',
            73 => 'CƠ SỞ ĐĂNG KÝ XE SỐ 4',
            74 => 'CƠ SỞ ĐĂNG KÝ XE SỐ 5',

        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-cahn-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcel([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
//                'linhhv@nganluong.vn'
                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD QRCODE ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }


    }

    public function actionRefund()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $day = $get_date['mday'] - 1;
        $mon = $get_date['mon'];
        $year = $get_date['year'];
        $time_from = mktime(0, 0, 0, $mon, $day, $year);
        $time_to = mktime(23, 59, 59, $mon, $day, $year);
        $data_export = TransactionBusiness::getTransactionRefundAll([
            'time_from' => $time_from,
            'time_to' => $time_to,
            'page' => 1,
            'size' => 100000
        ]);
        $file_name = self::DIR_PATH . 'danh-sach-giao-dich-hoan-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
        $this->writeExcelRefund([
            'file_name' => $file_name,
            'data' => $data_export,
            'day' => $day,
            'month' => $mon,
            'year' => $year,
            'time_from' => $time_from,
            'time_to' => $time_to
        ]);
        $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
        $sent_to = [
//                'linhhv@nganluong.vn',
                'doitac@nganluong.vn'
        ];
        $cc = [
//                'lylk@nganluong.vn',
//            'doitac@nganluong.vn',
        ];
        $current_date = $day . '/' . $mon . '/' . $year;
        $subject = 'Ngân Lượng - Vietcombank: Danh sach giao dich hoan ngay  ngày ' . date('d/m/Y');
        $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
        if ($data_export['index']['total_record'] > 0) {
            SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

        }


    }

    public function actionXnc()
    {
        error_reporting(E_ALL);
        ini_set('memory_limit', '-1');
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
//            9 => 'CỤC QUẢN LÝ XNC',
            91 => 'CỤC QUẢN LÝ XNC',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 2;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day + 1 , $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-xnc-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXNC([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'date' => $get_date,
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'anhnp2.sgd@vietcombank.com.vn',
                'dtkhoa.sgd@vietcombank.com.vn',
                'linhdt2.sgd@vietcombank.com.vn',
                'dongdt.sgd@vietcombank.com.vn',
//                'cuongta.sgd@vietcombank.com.vn',
                'huyenpt.sgd@vietcombank.com.vn'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);
            }
        }
    }


    public function actionXanhpon()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
//            9 => 'CỤC QUẢN LÝ XNC',
            154 => 'BỆNH VIÊN ĐA KHOA XANH PÔN - TAM ỨNG',
            178 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - KHOA NỘI TRÚ',
            179 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - KHOA NGOẠI TRÚ',
            180 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - NHÀ THUỐC',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-xanhpon-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'noittb.tah@vietcombank.com.vn', 'linhdtt.tah@vietcombank.com.vn', 'vananh.dt@isofh.com', 'luan.nt@isofh.com','phuongnnt.tah@vietcombank.com.vn', 'lanbtn.tah@vietcombank.com.vn'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    public function actionFubon()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            1387 => 'Fubon',
//            154 => 'BỆNH VIÊN ĐA KHOA XANH PÔN - TAM ỨNG',
//            178 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - KHOA NỘI TRÚ',
//            179 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - KHOA NGOẠI TRÚ',
//            180 => 'BỆNH VIỆN ĐA KHOA XANH PÔN - NHÀ THUỐC',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-fubon-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelFubon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
//                'noittb.tah@vietcombank.com.vn', 'linhdtt.tah@vietcombank.com.vn', 'vananh.dt@isofh.com', 'luan.nt@isofh.com'
                'thuy.lam@fubon.com' ,'quoc.huynh@fubon.com'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    public function actionBuudien()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            204=> 'BỆNH VIỆN BƯU ĐIỆN',
            205=> 'BVBD PHAM HONG MY',
            206=> 'BVBD NG THI NGOC ANH',
            207=> 'BVBD DO THI HIEN',
            208=> 'BVBD NGUYEN THI NAM',
            209=> 'BVBD NGUYEN T PHUONG',
            210=> 'BVBD TRAN T THANHHOA',
            211=> 'BVBD NGOC THI CAI',
            212=> 'BVBD NG THI THU HA',
            213=> 'BVBD NG THUY DUNG',
            214=> 'BVBD VU THI Y ANH',
            215=> 'BVBD NG T MY PHUONG',
            216=> 'BVBD LUONG THI DIEP',
            217=> 'BVBD TRAN THI Q DIEP',
            218=> 'BVBD VU T TRA VINH',
            219=> 'BVBD NGUYEN THI LIEN',
            220=> 'BVBD NGUYEN THI BICH',
            221=> 'BVBD DAO THI DUNG',
            222=> 'BVBD NGUYEN T TRANG',
            223=> 'BVBD DO T ANH NGOC',
            224=> 'BVBD TRAN TUYET HANH',
            225=> 'BVBD NGUYEN THI DIEU',
            226=> 'BVBD BUI T THANH THU',
            227=> 'BVBD NG THANH HUYEN',
            228=> 'BVBD PHAM D THU LUU',
            229=> 'BVBD NGUYEN T CHUNG',
            230=> 'BVBD NG VIET T THUY',
            231=> 'BVBD NGUYEN THI HOA',
            232=> 'BVBD NGUYEN THI PHUC',
            233=> 'BVBD NGUYEN LAN ANH',
            949=> 'BVBD NGO THI THO',
            1263=> 'BVBD TRAN HONG NHUNG',
            1431=> 'BVBD PHAM THI HA',
            1432=> 'BVBD NG THI ANH THU',
            2345=> 'BVBD BUI THI THANH THU',
            2346=> 'NGUYEN THI LA',
            2353=> 'BVBD VU THI DUNG',
            3315=> 'BVBD TRAN THI TAM',
            3316=> 'BVBD NGUYEN T HUONG',
            3317=> 'BVBD HOANG T L HUONG',
            3461=> 'BVBD NGUYEN P THAO',
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
//            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-buu-dien-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-buu-dien-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelBuuDien([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name,
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
//                'linhhv@nganluong.vn'
                'chungnnt123@gmail.com', 'hangnm.sgd@vietcombank.com.vn','nhungkim.bvbd@gmail.com','trabt.sgd@vietcombank.com.vn','UYENNT5.SGD@vietcombank.com.vn'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionQuangninh()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            119 => 'BỆNH VIỆN ĐA KHOA TỈNH QUẢNG NINH',
            1129 => 'BỆNH VIỆN ĐA KHOA TỈNH QUẢNG NINH TẠM ỨNG',
            1130 => 'BỆNH VIỆN ĐA KHOA TỈNH QUẢNG NINH NHÀ THUỐC',

        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-quang-ninh-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');
            

            
//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'thugiangbvt@gmail.com','ngohangbvtqn@gmail.com','tuantm.qni@vietcombank.com.vn','Kienlp.qni@vietcombank.com.vn','trieuhongmay@gmail.com'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    public function actionBcit()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            858 => 'TRƯỜNG CAO ĐẲNG KỸ THUẬT CÔNG NGHIỆP',
            877 => 'TRƯỜNG CAO ĐẲNG KỸ THUẬT CÔNG NGHIỆP QR',

        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-bcit-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'taiht@bcit.edu.vn',
                'luanntth@bcit.edu.vn'
//                'liennt@peacesoft.net'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionCahp()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            443 => 'CONG AN HAI PHONG',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-cahp-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'Hoanglh.hph@vietcombank.com.vn',
//                'liennt@peacesoft.net'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }


    public function actionCahcm()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            506 => 'CONG AN HO CHI MINH', // live
//            7 => 'CONG AN HO CHI MINH', // local


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-cahcm-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCaHcm([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'taith.hcm@vietcombank.com.vn',
                'minhvn.hcm@vietcombank.com.vn',
                'dungbt.hcm@vietcombank.com.vn',
                'haihtn.hcm@vietcombank.com.vn',
                'anhdtm.hcm@vietcombank.com.vn',
                'huongttt1.hcm@vietcombank.com.vn'
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
            ];



            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionCapt()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            487 => 'CONG AN PHU THO', // live
//            7 => 'CONG AN HO CHI MINH', // local


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-capt-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCapt([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'vannth.pht@vietcombank.com.vn',
                'nhphuong8989@gmail.com',
                'huyenntt.pht@vietcombank.com.vn',
                'minhpth.pht@vietcombank.com.vn',
                'namnt.pht@vietcombank.com.vn',

            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
            ];



            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionCatb()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            497 => 'CONG AN THAI BINH', // live
//            7 => 'CONG AN HO CHI MINH', // local


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-catb-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCaGeneral([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name

            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'ngatt.tbi@vietcombank.com.vn',
                'ngocanh.tctt@gmail.com'

            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
                'tinbt@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];



            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    /** Công an Bến Tre */
    public function actionCabt()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            453 => 'CONG AN BEN TRE', // live
//            7 => 'CONG AN BEN TRE', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-cabt-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCabt([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'anhtth.btr@vietcombank.com.vn',
                'taivu.ph41bentre@gmail.com',
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    /** Công an Tuyên Quang */
    public function actionCatq()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            501 => 'CONG AN TUYEN QUANG', // live
//            7 => 'CONG AN BEN TRE', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-catq-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCatq([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
//                'Anhttv.tqu@vietcombank.com.vn',
//                'Hoatv.tqu@vietcombank.com.vn',
                'Thuylt.tqu@vietcombank.com.vn',
//                'Duclh.tqu@vietcombank.com.vn'
                'minhthn.tqu@vietcombank.com.vn'
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionCakt()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            477 => 'CONG AN KON TUM', // live
//            7 => 'CONG AN BEN TRE', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-cakt-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCatq([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'anhntl.ktu@vietcombank.com.vn'
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    /** Công an Đồng Nai */
    public function actionCaDongNai()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            465 => 'CONG AN DONG NAI', // live
//            7 => 'CONG AN DONG NAI', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-dong-nai-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCaGeneral([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'ntathy.don@vietcombank.com.vn'
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    /** Công an Tiền Giang */
    /** merchant-total-transaction/ca-tien-giang */
    public function actionCaTienGiang()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            499 => 'CONG AN TIEN GIANG', // live
//            7 => 'CONG AN TIEN GIANG', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-tien-giang-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCaGeneral([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'nhidty.tgi@vietcombank.com.vn',
                'quanlynhapcanh1536@gmail.com',
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    /** Công an Thừa Thiên Huế */
    /**
     * Từ 27/08/2024: gộp chung các CA gửi lúc 7h T+1 tại đây!!!
     */

    public function actionCatth()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            498 => 'CONG AN THUA THIEN HUE', // live
            505 => 'CONG AN VINH PHUC', // live
            468 => 'CONG AN HA GIANG', // live
            3353 => 'HỌC VIỆN BÁO CHÍ VÀ TUYÊN TRUYỀN', // live
            503 => 'CONG AN YEN BAI', // live
//            7 => 'CONG AN THUA THIEN HUE', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            if($merchant == 498){
                $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-thua-thien-hue-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';

                // Send mail gửi file đính kèm danh sách giao dịch thành công
                $sent_to = [
                    'qlxnctthue@gmail.com'
                ];

                $cc = [
//                'lylk@nganluong.vn',
                    'doitac@nganluong.vn', // update khi day live !!!
                    'tinbt@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
                ];

            } elseif($merchant == 505){
                $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-vinh-phuc-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';

                // Send mail gửi file đính kèm danh sách giao dịch thành công
                $sent_to = [
                    'minhnt.vph@vietcombank.com.vn',
                    'linhtt.vph@vietcombank.com.vn'
                ];

                $cc = [
                    'doitac@nganluong.vn', // update khi day live !!!
                    'tinbt@nganluong.vn', // update khi day live !!!
                ];
            } elseif($merchant == 3353){
                $file_name = self::DIR_PATH . 'danh-sach-giao-dich-hvbcvtt-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';

                // Send mail gửi file đính kèm danh sách giao dịch thành công
                $sent_to = [
                    'Vuhienhvbc@gmail.com',
                ];

                $cc = [
                    'doitac@nganluong.vn', // update khi day live !!!
                    'tinbt@nganluong.vn', // update khi day live !!!
                ];
            } elseif($merchant == 468){
                $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-ha-giang-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';

                // Send mail gửi file đính kèm danh sách giao dịch thành công
                $sent_to = [
                    'Anhttv.tqu@vietcombank.com.vn',
                    'Hoatv.tqu@vietcombank.com.vn',
                ];

                $cc = [
                    'doitac@nganluong.vn', // update khi day live !!!
                    'tinbt@nganluong.vn', // update khi day live !!!
                ];
            } elseif($merchant == 503){
                $file_name = self::DIR_PATH . 'danh-sach-giao-dich-ca-yen-bai-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';

                // Send mail gửi file đính kèm danh sách giao dịch thành công
                $sent_to = [
                    'Nganth.yba@vietcombank.com.vn',
                    'Trunghq.yba@vietcombank.com.vn',
                    'Loannk.yba@vietcombank.com.vn',
                    'Ductt.yba@vietcombank.com.vn',
                    'Ngocnk.yba@vietcombank.com.vn'
                ];

                $cc = [
                    'doitac@nganluong.vn', // update khi day live !!!
                    'tinbt@nganluong.vn', // update khi day live !!!
                ];
            }
            $this->writeExcelCaGeneral([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name
            ]);
            $this->writeLog('---------------------[END]--------------------');



            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }


    /** 2926
    CÔNG TY TNHH CÔNG NGHỆ VÀ XÉT NGHIỆM Y HỌC POS
     * merchant-total-transaction/xnyh-pos  ?date_time=25-08-2024
     */
    public function actionXnyhPos()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }

        $arr_merchant = [
            2926 => 'CÔNG TY TNHH CÔNG NGHỆ VÀ XÉT NGHIỆM Y HỌC POS', // live
//            7 => 'CÔNG TY TNHH CÔNG NGHỆ VÀ XÉT NGHIỆM Y HỌC POS', // local
        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
//            $day = $get_date['mday'] - 3;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
//            var_dump($time_from);die();
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
//            print_r($data_export);die();
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-xet-nghiem-y-hoc-pos-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelCaGeneral([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_name' => $merchant_name,
                'merchant_id' => $merchant
            ]);
            $this->writeLog('---------------------[END]--------------------');

//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'linh.tranthithuy@medlatec.com',
                'Hoa.nguyenthi@medlatec.com',
                'thao.hoangphuong@medlatec.com'
            ];

            // tesst // cmt khi day live!!!
//            $sent_to = [
//                'tinbt@nganluong.vn'
//            ];

            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn', // update khi day live !!!
                'tinbt@nganluong.vn', // update khi day live !!!
//                'liennt@peacesoft.net', // update khi day live !!!
            ];


            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    public function actionHub()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            2374 => 'TRƯỜNG ĐẠI HỌC NGÂN HÀNG TP.HCM',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-hub-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'trienlt.KDO@vietcombank.com.vn','linhvth.KDO@vietcombank.com.vn','haht.KDO@vietcombank.com.vn','huyendtt.KDO@vietcombank.com.vn','thinhhng.KDO@vietcombank.com.vn','NGANTN@HUB.EDU.VN','ANHNN@HUB.EDU.VN','LIENLTP@HUB.EDU.VN','KYNT@HUB.EDU.VN'
//                'liennt@peacesoft.net'
//                'tthanh.sgd@vietcombank.com.vn'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
//                'linhhv@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionMedTh()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            2838 => 'CÔNG TY TNHH MEDLATEC VIỆT NAM TÂY HỒ',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-medth-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'quynh.daothi@medlatec.com','hang.phamthi@medlatec.com','nga.ngothi@medlatec.com'
            ];
            $cc = [
//                'lylk@nganluong.vn',
                'doitac@nganluong.vn',
//                'linhhv@nganluong.vn',
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }
    public function actionMedBd()
    {
        error_reporting(E_ALL);
        $this->writeLog('---------------------[START]------------------');
        // format date_time parameter: Ymd
        $date_time = Yii::$app->request->get('date_time');
        $this->writeLog('[DATE-TIME] ' . $date_time);
        if (is_null($date_time)) {
            $get_date = getdate();
        } else {
            $get_date = getdate(strtotime($date_time));
        }
        $arr_merchant = [
            2915 => 'CÔNG TY TNHH CÔNG NGHỆ VÀ XÉT NGHIỆM Y HỌC',


        ];
        foreach ($arr_merchant as $merchant => $merchant_name) {
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];
            $time_from = mktime(0, 0, 0, $mon, $day, $year);
            $time_to = mktime(23, 59, 59, $mon, $day, $year);
            $data_export = TransactionBusiness::getTransactionByMerchant([
                'time_from' => $time_from,
                'time_to' => $time_to,
                'merchant_id' => $merchant,
                'page' => 1,
                'size' => 100000
            ]);
            $file_name = self::DIR_PATH . 'danh-sach-giao-dich-medbd-ngay-' . $year . '-' . $mon . '-' . $day . '.xlsx';
            $this->writeExcelXanhPon([
                'file_name' => $file_name,
                'data' => $data_export,
                'day' => $day,
                'month' => $mon,
                'year' => $year,
                'time_from' => $time_from,
                'time_to' => $time_to
            ]);
            $this->writeLog('---------------------[END]--------------------');



//        Send mail gửi file đính kèm danh sách giao dịch thành công
            $sent_to = [
                'linh.tranthithuy@medlatec.com','Hoa.nguyenthi@medlatec.com','thao.hoangphuong@medlatec.com'
            ];
            $cc = [
//                'lylk@nganluong.vn',
//                'liennt@peacesoft.net',
                'doitac@nganluong.vn', // update khi day live !!!
            ];
            $current_date = $day . '/' . $mon . '/' . $year;
            $subject = 'Ngân Lượng - Vietcombank: Đối soát GD  ' . $merchant_name . ' ngày ' . date('d/m/Y');
            $body_content = 'Dear đối tác ' . '<br/>' . 'Ngân Lượng gửi file đối soát ngày ' . $current_date . ' (note:ngày T-1)';
            if ($data_export['index']['total_record'] > 0) {
                SendMailBussiness::sendAttach($sent_to, $subject, 'notify_transaction_daily', ['body_content' => $body_content], 'layouts/basic', $file_name, $cc);

            }
        }
    }

    private function writeExcel($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeader(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total

            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {

                $row++;


                $sheet->setCellValue('A' . $row, $data['token_code']);
                $sheet->setCellValue('B' . $row, $data['order_code']);
                $sheet->setCellValue('C' . $row, @$GLOBALS['PREFIX'] . $data['transaction_id']);
                $sheet->setCellValue('D' . $row, $data['bank_refer_code']);
                $sheet->setCellValue('E' . $row, $data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, $data['buyer_email']);
                $sheet->setCellValue('G' . $row, $data['buyer_mobile']);
                $sheet->setCellValue('H' . $row, $data['order_description']);
                $sheet->setCellValue('I' . $row, number_format($data['amount']));
                $sheet->setCellValue('J' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('K' . $row, number_format($data['cashin_amount']));
                $sheet->setCellValue('L' . $row, $data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, date('H:i,d-m-Y', $data['transaction_paid']));
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');


            }

            //auto width excel columns
            foreach (range('A', 'O') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);
            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }


    private function writeExcelRefund($params)
    {
        $card_number = '';
        $card_name = '';
        $refund_amount = 0;
        $merchant_arr = Tables::selectAllDataTable('merchant','1=1','id','id','','','id,name');
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderRefund(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total

            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $transaction = Transaction::findOne(['checkout_order_id' => $data['checkout_order_id']]);
                if($transaction!=null){
                    if($transaction->card_info !=  null && $transaction->card_info != ''){
                        $card_number = isset(json_decode($transaction->card_info, true)['card_number'])?@json_decode($transaction->card_info, true)['card_number']:'';
                        $card_name = isset(json_decode($transaction->card_info, true)['card_fullname'])?json_decode($transaction->card_info, true)['card_fullname']:'';
                    }

                }
                $transaction_refund = Transaction::findOne(['id' => $data['refund_transaction_id']]);
                if($transaction_refund!=null){
                    $refund_amount = $transaction_refund['amount'];

                }
                $merchant_name = $merchant_arr[$data['merchant_id']]['name'];
                $row++;


                $sheet->setCellValue('A' . $row, $data['token_code']);
                $sheet->setCellValue('B' . $row, $data['order_code']);
                $sheet->setCellValue('C' . $row, @$GLOBALS['PREFIX'] . $data['transaction_id']);
                $sheet->setCellValue('D' . $row, $data['bank_refer_code']);
                $sheet->setCellValue('E' . $row, $data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, $data['buyer_email']);
                $sheet->setCellValue('G' . $row, $data['buyer_mobile']);
                $sheet->setCellValue('H' . $row, $data['order_description']);
                $sheet->setCellValue('I' . $row, number_format($data['amount']));
                $sheet->setCellValue('J' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('K' . $row, number_format($refund_amount));
                $sheet->setCellValue('L' . $row, $data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, $data['transaction_paid']>0?date('H:i,d-m-Y', $data['transaction_paid']):'');
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');
                $sheet->setCellValue('P' .  $row,$merchant_name);
                $sheet->setCellValue('Q' .  $row,$data['partner_payment_method_refer_code']);
                $sheet->setCellValue('R' .  $row,$card_number);
                $sheet->setCellValue('S' .  $row,$card_name);


            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);
            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    private function writeExcelXNC($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderXNC(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $date = $params['date'];

            $time_from_domestic = mktime(0, 0, 0, $date['mon'], $date['mday'] - 1, $date['year']);
            $time_to_domestic = mktime(23, 59, 59, $date['mon'], $date['mday'] - 1, $date['year']);

            $time_from_international = mktime(13, 0, 0, $date['mon'], $date['mday'] - 2, $date['year']);
            $time_to_international = mktime(12, 59, 59, $date['mon'], $date['mday'] - 1, $date['year']);


            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                if ($data['partner_payment_id'] == "15" && ($data['time_paid'] < $time_from_international || $data['time_paid'] > $time_to_international ) ) {
                    continue;
                }
                if ($data['partner_payment_id'] != "15" && ($data['time_paid'] < $time_from_domestic || $data['time_paid'] > $time_to_domestic ) ) {
                    continue;
                }


                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }

                $row++;

                $sheet->setCellValue('A' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('B' . $row, @$data['buyer_email']);
                $sheet->setCellValue('C' . $row, @$data['buyer_mobile']);
                $sheet->setCellValue('D' . $row, @$data['merchant_id']);
                $sheet->setCellValue('E' . $row, @$data['token_code']);
                $sheet->setCellValue('F' . $row, @$data['order_code']);
                $sheet->setCellValue('G' . $row, @$data['order_description']);
                $sheet->setCellValue('H' . $row, $amount_usd);
                $sheet->setCellValue('I' . $row, $currency_echange);
                $sheet->setCellValue('J' . $row, number_format($data['amount']));
                $sheet->setCellValue('K' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('L' . $row, @$data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, date('H:i,d-m-Y', $data['transaction_paid']));
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');
                $sheet->setCellValue('P' . $row, @$data['reason']);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, $account_number);
                $sheet->setCellValue('S' . $row, $account_name);

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    private function writeExcelXanhPon($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderXNC(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }

                $row++;

                $sheet->setCellValue('A' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('B' . $row, @$data['buyer_email']);
                $sheet->setCellValue('C' . $row, @$data['buyer_mobile']);
                $sheet->setCellValue('D' . $row, @$data['merchant_id']);
                $sheet->setCellValue('E' . $row, @$data['token_code']);
                $sheet->setCellValue('F' . $row, @$data['order_code']);
                $sheet->setCellValue('G' . $row, @$data['order_description']);
                $sheet->setCellValue('H' . $row, $amount_usd);
                $sheet->setCellValue('I' . $row, $currency_echange);
                $sheet->setCellValue('J' . $row, number_format($data['amount']));
                $sheet->setCellValue('K' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('L' . $row, @$data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, date('H:i,d-m-Y', $data['transaction_paid']));
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');
                $sheet->setCellValue('P' . $row, @$data['reason']);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, $account_number);
                $sheet->setCellValue('S' . $row, $account_name);

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }
    private function writeExcelCaHcm($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderCaHcm(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }
                $payment_method_name = '';
                if(!empty($data['payment_method_id'])){
                    $payment_method = PaymentMethod::findOne($data['payment_method_id']);
                    if($payment_method != null){
                        $payment_method_name = $payment_method->name;
                    }
                }


                $row++;
                $sheet->setCellValue('A' . $row, @$data['id']);
                $sheet->setCellValue('B' . $row, @$data['order_code']);
                $sheet->setCellValue('C' . $row, 'CONG AN HO CHI MINH');
                $sheet->setCellValue('D' . $row, 'VCB_PAYGATE_' . $data['id']);
                $sheet->setCellValue('E' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, @$data['buyer_email']);
                $sheet->setCellValue('G' . $row, @$account_number);
                $sheet->setCellValue('H' . $row, @$account_name);
                $sheet->setCellValue('I' . $row, @$data['cashin_amount']);
                $sheet->setCellValue('J' . $row, @$data['amount']);
                $sheet->setCellValue('K' . $row, @$data['sender_fee']);
                $sheet->setCellValue('L' . $row, @$currency_echange);
                $sheet->setCellValue('M' . $row, @$data['token_code']);
                $sheet->setCellValue('N' . $row, @$data['order_description']);
                $sheet->setCellValue('O' . $row, @$data['authorization_code']);
                $sheet->setCellValue('P' . $row, $payment_method_name);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, !empty($data['time_paid']) ? date('d/m/Y H:i', $data['time_paid']) : '');

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }
    private function writeExcelCapt($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderCaHcm(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }
                $payment_method_name = '';
                if(!empty($data['payment_method_id'])){
                    $payment_method = PaymentMethod::findOne($data['payment_method_id']);
                    if($payment_method != null){
                        $payment_method_name = $payment_method->name;
                    }
                }


                $row++;
                $sheet->setCellValue('A' . $row, @$data['id']);
                $sheet->setCellValue('B' . $row, @$data['order_code']);
                $sheet->setCellValue('C' . $row, 'CONG AN PHU THO');
                $sheet->setCellValue('D' . $row, 'VCB_PAYGATE_' . $data['id']);
                $sheet->setCellValue('E' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, @$data['buyer_email']);
                $sheet->setCellValue('G' . $row, @$account_number);
                $sheet->setCellValue('H' . $row, @$account_name);
                $sheet->setCellValue('I' . $row, @$data['cashin_amount']);
                $sheet->setCellValue('J' . $row, @$data['amount']);
                $sheet->setCellValue('K' . $row, @$data['sender_fee']);
                $sheet->setCellValue('L' . $row, @$currency_echange);
                $sheet->setCellValue('M' . $row, @$data['token_code']);
                $sheet->setCellValue('N' . $row, @$data['order_description']);
                $sheet->setCellValue('O' . $row, @$data['authorization_code']);
                $sheet->setCellValue('P' . $row, $payment_method_name);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, !empty($data['time_paid']) ? date('d/m/Y H:i', $data['time_paid']) : '');

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    /** for Cong an Ben Tre */
    private function writeExcelCabt($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderCaHcm(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }
                $payment_method_name = '';
                if(!empty($data['payment_method_id'])){
                    $payment_method = PaymentMethod::findOne($data['payment_method_id']);
                    if($payment_method != null){
                        $payment_method_name = $payment_method->name;
                    }
                }


                $row++;
                $sheet->setCellValue('A' . $row, @$data['id']);
                $sheet->setCellValue('B' . $row, @$data['order_code']);
                $sheet->setCellValue('C' . $row, 'CONG AN BEN TRE');
                $sheet->setCellValue('D' . $row, 'VCB_PAYGATE_' . $data['id']);
                $sheet->setCellValue('E' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, @$data['buyer_email']);
                $sheet->setCellValue('G' . $row, @$account_number);
                $sheet->setCellValue('H' . $row, @$account_name);
                $sheet->setCellValue('I' . $row, @$data['cashin_amount']);
                $sheet->setCellValue('J' . $row, @$data['amount']);
                $sheet->setCellValue('K' . $row, @$data['sender_fee']);
                $sheet->setCellValue('L' . $row, @$currency_echange);
                $sheet->setCellValue('M' . $row, @$data['token_code']);
                $sheet->setCellValue('N' . $row, @$data['order_description']);
                $sheet->setCellValue('O' . $row, @$data['authorization_code']);
                $sheet->setCellValue('P' . $row, $payment_method_name);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, !empty($data['time_paid']) ? date('d/m/Y H:i', $data['time_paid']) : '');

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    /** for Cong an Tuyen Quang */
    private function writeExcelCatq($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderCaHcm(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }
                $payment_method_name = '';
                if(!empty($data['payment_method_id'])){
                    $payment_method = PaymentMethod::findOne($data['payment_method_id']);
                    if($payment_method != null){
                        $payment_method_name = $payment_method->name;
                    }
                }


                $row++;
                $sheet->setCellValue('A' . $row, @$data['id']);
                $sheet->setCellValue('B' . $row, @$data['order_code']);
                $sheet->setCellValue('C' . $row, 'CONG AN TUYEN QUANG');
                $sheet->setCellValue('D' . $row, 'VCB_PAYGATE_' . $data['id']);
                $sheet->setCellValue('E' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, @$data['buyer_email']);
                $sheet->setCellValue('G' . $row, @$account_number);
                $sheet->setCellValue('H' . $row, @$account_name);
                $sheet->setCellValue('I' . $row, @$data['cashin_amount']);
                $sheet->setCellValue('J' . $row, @$data['amount']);
                $sheet->setCellValue('K' . $row, @$data['sender_fee']);
                $sheet->setCellValue('L' . $row, @$currency_echange);
                $sheet->setCellValue('M' . $row, @$data['token_code']);
                $sheet->setCellValue('N' . $row, @$data['order_description']);
                $sheet->setCellValue('O' . $row, @$data['authorization_code']);
                $sheet->setCellValue('P' . $row, $payment_method_name);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, !empty($data['time_paid']) ? date('d/m/Y H:i', $data['time_paid']) : '');

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    /** Dùng chung cho Công an */
    private function writeExcelCaGeneral($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $showr_ref_no_arr = [
                2926, 7
            ];  // add cac merchant can show ref No
            if(isset($params['merchant_id']) && in_array($params['merchant_id'], $showr_ref_no_arr) ){
                $sheet->fromArray($this->getHeaderXnyh(), null, 'A' . $row);

            } else{
                $sheet->fromArray($this->getHeaderCaHcm(), null, 'A' . $row);
            }
            $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }
                $payment_method_name = '';
                if(!empty($data['payment_method_id'])){
                    $payment_method = PaymentMethod::findOne($data['payment_method_id']);
                    if($payment_method != null){
                        $payment_method_name = $payment_method->name;
                    }
                }


                $row++;
                $sheet->setCellValue('A' . $row, @$data['id']);
                $sheet->setCellValue('B' . $row, @$data['order_code']);
                $sheet->setCellValue('C' . $row, isset($params['merchant_name']) ? $params['merchant_name'] : '');
                $sheet->setCellValue('D' . $row, 'VCB_PAYGATE_' . $data['id']);
                $sheet->setCellValue('E' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('F' . $row, @$data['buyer_email']);
                $sheet->setCellValue('G' . $row, @$account_number);
                $sheet->setCellValue('H' . $row, @$account_name);
                $sheet->setCellValue('I' . $row, @$data['cashin_amount']);
                $sheet->setCellValue('J' . $row, @$data['amount']);
                $sheet->setCellValue('K' . $row, @$data['sender_fee']);
                $sheet->setCellValue('L' . $row, @$currency_echange);
                $sheet->setCellValue('M' . $row, @$data['token_code']);
                $sheet->setCellValue('N' . $row, @$data['order_description']);
                $sheet->setCellValue('O' . $row, @$data['authorization_code']);
                $sheet->setCellValue('P' . $row, $payment_method_name);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, !empty($data['time_paid']) ? date('d/m/Y H:i', $data['time_paid']) : '');

                //                print_r($data);die();
                //TODO Thêm mã ref no
                if(isset($params['merchant_id']) && in_array($params['merchant_id'], $showr_ref_no_arr) ){
                    $ref_no = '';
                    if(isset($data['partner_payment_info'])){
                        $partner_payment_info_arr = json_decode($data['partner_payment_info'], true);
                        $ref_no = isset($partner_payment_info_arr['rrn']) ? $partner_payment_info_arr['rrn'] : '';
                    }
                    $sheet->setCellValue('S' . $row, $ref_no);
                }

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }
    private function writeExcelFubon($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderXNC(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }

                $row++;

                $sheet->setCellValue('A' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('B' . $row, @$data['buyer_email']);
                $sheet->setCellValue('C' . $row, @$data['buyer_mobile']);
                $sheet->setCellValue('D' . $row, @$data['merchant_id']);
                $sheet->setCellValue('E' . $row, @$data['token_code']);
                $sheet->setCellValue('F' . $row, @$data['order_code']);
                $sheet->setCellValue('G' . $row, @$data['order_description']);
                $sheet->setCellValue('H' . $row, $amount_usd);
                $sheet->setCellValue('I' . $row, $currency_echange);
                $sheet->setCellValue('J' . $row, number_format($data['amount']));
                $sheet->setCellValue('K' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('L' . $row, @$data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, date('H:i,d-m-Y', $data['transaction_paid']));
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');
                $sheet->setCellValue('P' . $row, @$data['reason']);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, $account_number);
                $sheet->setCellValue('S' . $row, $account_name);

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    private function writeExcelBuuDien($params)
    {
        $this->writeLog('[FILE-NAME] ' . $params['file_name']);
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        if (file_exists($params['file_name'])) {
            @unlink($params['file_name']);
        }
        try {
            $row = 1;

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            //add header

            $sheet->fromArray($this->getHeaderXNC(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);
            $arr_status = [
                self::STATUS_NEW => 'Chờ xử lý',
                self::STATUS_PAYING => 'Đang xử lý',
                self::STATUS_PAID => 'Đã hoàn thành',
                self::STATUS_CANCEL => 'Đã hủy',
            ];
            //add data

            //add total
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            foreach ($params['data']['data'] as $data) {
                $amount_usd = 0;
                $currency_echange = 0;
                $account_name = '';
                $account_number = '';
                if (!empty($data['currency_exchange'])) {
                    $exchange_info = json_decode($data['currency_exchange'], true);
                    $currency_echange = $exchange_info['transfer'];
                    $amount_usd = $data['orginal_amount'] / $currency_echange;
                }
                if (!empty($data['card_info'])) {
                    $card_info = json_decode($data['card_info'], true);
                    $account_name = $card_info['card_fullname'];
                    $account_number = $card_info['card_number'];
                }

                $row++;

                $sheet->setCellValue('A' . $row, @$data['buyer_fullname']);
                $sheet->setCellValue('B' . $row, @$data['buyer_email']);
                $sheet->setCellValue('C' . $row, @$data['buyer_mobile']);
                $sheet->setCellValue('D' . $row, @$params['merchant_name']);
                $sheet->setCellValue('E' . $row, @$data['token_code']);
                $sheet->setCellValue('F' . $row, @$data['order_code']);
                $sheet->setCellValue('G' . $row, @$data['order_description']);
                $sheet->setCellValue('H' . $row, $amount_usd);
                $sheet->setCellValue('I' . $row, $currency_echange);
                $sheet->setCellValue('J' . $row, number_format($data['amount']));
                $sheet->setCellValue('K' . $row, number_format($data['cashin_amount'] - $data['amount']));
                $sheet->setCellValue('L' . $row, @$data['name']);
                $sheet->setCellValue('M' . $row, date('H:i,d-m-Y', $data['transaction_create']));
                $sheet->setCellValue('N' . $row, date('H:i,d-m-Y', $data['transaction_paid']));
                $sheet->setCellValue('O' . $row, isset($arr_status[$data['transaction_status']]) ? $arr_status[$data['transaction_status']] : '');
                $sheet->setCellValue('P' . $row, @$data['reason']);
                $sheet->setCellValue('Q' . $row, @$data['bank_refer_code']);
                $sheet->setCellValue('R' . $row, $account_number);
                $sheet->setCellValue('S' . $row, $account_name);

            }

            //auto width excel columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            //write file
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);

            $excel->disconnectWorksheets();
            $excel->garbageCollect();
            unset($excel, $excel_writer);
            $error_message = '';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $this->writeLog('[WRITE-EXCEL-ERROR] ' . $error_message);

    }

    private function getHeader()
    {
        return [
            ['Mã Token', 'Mã Đơn hàng', 'Mã hóa đơn NL', 'Mã tham chiếu', 'Tên người mua', 'Email người mua', 'SĐT người mua', 'Mô tả đơn hàng', 'Số tiền đơn hàng', 'Phí giao dịch', 'Số tiền nhận được', 'Phương thức thanh toán', 'Thời gian tạo', 'Thời gian thanh toán', 'Trạng thái']
        ];
    }

    private function getHeaderRefund()
    {
        return [
            ['Mã Token', 'Mã Đơn hàng', 'Mã hóa đơn NL', 'Mã tham chiếu thanh toán', 'Tên người mua', 'Email người mua', 'SĐT người mua', 'Mô tả đơn hàng', 'Số tiền đơn hàng', 'Phí giao dịch', 'Số tiền nhận được', 'Phương thức thanh toán', 'Thời gian hoàn tiên', 'Thời gian thanh toán', 'Trạng thái','Merchant','Mã tham chiếu hoàn tiền','Số thẻ','Tên chủ thẻ']
        ];
    }

    private function getHeaderXNC()
    {
        return [
            ['Tên người mua', 'Email người mua', 'SĐT người mua', 'Merchant', 'Mã token', 'Mã đơn hàng', 'Mô tả đơn hàng', 'Số tiền đơn hàng(USD)', 'Tỉ giá quy đổi', 'Số tiền đơn hàng(VND)', 'Phí giao dịch', 'Phương thức thanh toán', 'Thời gian tạo', 'Thời gian thanh toán', 'Trạng thái', 'Lý do thất bại', 'Mã tham chiếu', 'Số thẻ', 'Tên chủ thẻ']
        ];
    }

    private function getHeaderCaHcm()
    {
        return [
            ['Mã giao dịch','Mã đơn hàng','Merchant','Mã hóa đơn NL','Tên người mua','Email người mua','Số thẻ','Tên chủ thẻ','Số tiền gửi sang kênh TT','Số tiền giao dịch','Phí người chuyển','Tỉ giá','Mã token','Mô tả đơn hàng','Mã chuẩn chi','Phương thức thanh toán','Mã giao dịch bên ngân hàng','Thời gian thanh toán']
        ];
    }

    private function getHeaderXnyh()
    {
        return [
            ['Mã giao dịch','Mã đơn hàng','Merchant','Mã hóa đơn NL','Tên người mua','Email người mua','Số thẻ','Tên chủ thẻ','Số tiền gửi sang kênh TT','Số tiền giao dịch','Phí người chuyển','Tỉ giá','Mã token','Mô tả đơn hàng','Mã chuẩn chi','Phương thức thanh toán','Mã giao dịch bên ngân hàng','Thời gian thanh toán', 'Ref No']
        ];
    }

}