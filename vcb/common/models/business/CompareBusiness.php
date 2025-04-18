<?php

namespace common\models\business;

use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use DateTime;
use yii\helpers\VarDumper;

class CompareBusiness
{

    const DIR_PATH = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'compare' . DS;
    const VCB_BIN = '970436';
    const LINE_TYPE_HEADER = '0001';
    const LINE_TYPE_DETAIL = '0002';
    const LINE_TYPE_FOOTER = '0009';
    const PROCESSING_CODE_PAYMENT = '000000';
    const PROCESSING_CODE_REFUND = '200000';
    const TRACE_ECOM = '7399';
    const TRACE_INTERNET_BANKING = '5732';
    const TRACE_QRCODE = '0000';
    const CURRENCY_VND_CODE = '704';
    const MTI = '0210';
    const RC = '0000';
    const USER_CREATE = '0';
    const END_LINE = '0';
    const SECURE_HASH = 't7QvreVBDBxe';
    const order_code_extension = [
        'VCB_PAYGATE_5535221'
    ];
    const order_code_ingorne = [
        '2657699', '2658230', '2657922', '4563730'
    ];
    const PAYMENT_METHOD_IGNORE = [
        'VCB-ATM-CARD',
        'VISA-CREDIT-CARD',
        'MASTERCARD-CREDIT-CARD',
        'JCB-CREDIT-CARD',
        'MPOS-SWIPE-CARD',
        'MPOS-REFUND-SWIPE-CARD',
        'AMEX-CREDIT-CARD',
        'ABB-QRCODE_OFFLINE', 'ACB-QRCODE_OFFLINE', 'AGB-QRCODE_OFFLINE', 'AIRPAY-QRCODE_OFFLINE', 'BAB-QRCODE_OFFLINE', 'BIDC-QRCODE_OFFLINE', 'BIDV-QRCODE_OFFLINE', 'EXB-QRCODE_OFFLINE',
        'GDB-QRCODE_OFFLINE', 'HDB-QRCODE_OFFLINE', 'ICB-QRCODE_OFFLINE', 'IVB-QRCODE_OFFLINE', 'MB-QRCODE_OFFLINE', 'MOMO-QRCODE_OFFLINE', 'MSB-QRCODE_OFFLINE', 'NAB-QRCODE_OFFLINE',
        'NVB-QRCODE_OFFLINE', 'OCB-QRCODE_OFFLINE', 'OJB-QRCODE_OFFLINE', 'PVCOMBANK-QRCODE_OFFLINE', 'SCB-QRCODE_OFFLINE', 'SEA-QRCODE_OFFLINE', 'SGB-QRCODE_OFFLINE',
        'SHB-QRCODE_OFFLINE', 'SMARTPAY-QRCODE_OFFLINE', 'STB-QRCODE_OFFLINE', 'TCB-QRCODE_OFFLINE', 'TPB-QRCODE_OFFLINE', 'VAB-QRCODE_OFFLINE', 'VB-QRCODE_OFFLINE',
        'VCB-QRCODE_OFFLINE', 'VCBPAY-QRCODE_OFFLINE', 'VIB-QRCODE_OFFLINE', 'VIETTELPAY-QRCODE_OFFLINE', 'VIETTELPOST-QRCODE_OFFLINE', 'VINID-QRCODE_OFFLINE',
        'VPB-QRCODE_OFFLINE', 'WCP-QRCODE_OFFLINE', 'WRB-QRCODE_OFFLINE', 'MASTERCARD-REFUND-CREDIT-CARD','JCB-REFUND-CREDIT-CARD', 'VISA-REFUND-CREDIT-CARD', 'AMEX-REFUND-CREDIT-CARD', 'HETHONG-REFUND-TRA-GOP', 'VCB-REFUND-TRA-GOP', 'TPB-REFUND-TRA-GOP', 'VISA-TOKENIZATION', 'MASTERCARD-TOKENIZATION', 'VISA-REFUND-TOKENIZATION', 'MASTERCARD-REFUND-TOKENIZATION', 'AMEX-TOKENIZATION', 'AMEX-REFUND-TOKENIZATION', 'JCB-TOKENIZATION', 'JCB-REFUND-TOKENIZATION'
    ];

//    Danh sách các phương thức hoàn giao dich trả góp cần loại bỏ
    const REFUND_PAYMENT_METHOD_IGNORE = [
        'HSBC-REFUND-TRA-GOP',
        "SHNB-REFUND-TRA-GOP",
        "SVFC-REFUND-TRA-GOP",
        "STB-REFUND-TRA-GOP",
        "BIDV-REFUND-TRA-GOP",
        "SC-REFUND-TRA-GOP",
        "FE-REFUND-TRA-GOP",
        "MSB-REFUND-TRA-GOP",
        "VIB-REFUND-TRA-GOP",
        "SCB-REFUND-TRA-GOP",
        "OCB-REFUND-TRA-GOP",
        "VCB-REFUND-TRA-GOP",
        "ACB-REFUND-TRA-GOP",
        "VPB-REFUND-TRA-GOP",
        "NAB-REFUND-TRA-GOP",
        "PVCOMBANK-REFUND-TRA-GOP",
        "BVB-REFUND-TRA-GOP",
        "MB-REFUND-TRA-GOP",
        "SHB-REFUND-TRA-GOP",
        "KLB-REFUND-TRA-GOP",
        "HOMECREDIT-REFUND-TRA-GOP",
        "CTB-REFUND-TRA-GOP",
        "ICB-REFUND-TRA-GOP",
        "TCB-REFUND-TRA-GOP",
        "EXB-REFUND-TRA-GOP",
        "TPB-REFUND-TRA-GOP"
    ];
    const PAYMENT_METHOD_POS = [
        'MPOS-SWIPE-CARD',
        'MPOS-REFUND-SWIPE-CARD',
    ];

    public static function exportFileCompare($params)
    {

        $error_message = 'Lỗi không xác định';
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        $file_name = self::getFileName($params);
        $file_path = self::DIR_PATH . $file_name;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        $f = fopen($file_path, 'w');
        if ($f) {
            // write header
            fwrite($f, self::getFileHeader($params) . "\n");
            // write detail
            $page = 1;
            $size = 10000;
            $num_of_detail = 0;
            $write = true;

            while ($write) {
                $result = self::getAllTransaction([
                    'time_from' => $params['time_from'],
                    'time_to' => $params['time_to'],
                    'page' => $page,
                    'size' => $size
                ]);


                if (!empty($result['data'])) {
                    $data = $result['data'];
                    foreach ($data as $key => $item) {

                        if (in_array($item['checkout_order_id'], self::order_code_ingorne)) {
                            unset($data[$key]);
                        }

                    }
//                    $data = [];


                    foreach (self::order_code_extension as $item) {
//                        var_dump(getdate());die;

                        if (getdate()['mday'] == 27 && getdate()['month'] == 'October' && getdate()['year'] == 2023) {
//                        if (getdate()['mday'] == 9 && getdate()['month'] == 'October' && getdate()['year'] == 2023) {
                            $transaction_id = str_replace('VCB_PAYGATE_', '', $item);
                            $transaction_info = Transaction::find()->where(['id' => $transaction_id])->one()->toArray();
                            $merchant_code = Merchant::find()->where(['id' => $transaction_info['merchant_id']])->one()->toArray()['merchant_code'];
                            $payment_method_code = PaymentMethod::find()->where(['id' => $transaction_info['payment_method_id']])->one()->toArray()['code'];

//                            $checkout_order = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                            $transaction_info['merchant_code'] = $merchant_code;
                            $transaction_info['cashin_amount'] = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                            $transaction_info['merchant_code'] = $merchant_code;
                            $transaction_info['time_paid'] = 1698304256;
                            $transaction_info['time_created'] = 1698304256;
                            $transaction_info['payment_method_code'] = $payment_method_code;
                            $transaction_info['transaction_type_id'] = TransactionType::getRefundTransactionTypeId();
                            $transaction_info['refer_transaction_id'] = $transaction_id;
                            $transaction_info['bank_refer_code'] = 0;
                            $transaction_info['merchant_code_zalo'] = '09100002170';


                            array_push($data, $transaction_info);


                        }
                    }
                    $page++;

                    $num_of_detail += count($data);
                    foreach ($data as $line) {
                        //if (str_contains($line['payment_method_code'],'CREDIT-CARD')) {
//                              $line['time_created']=$line['time_created']+86400;
//                        }
//                        fwrite($f, self::getFileDetail($line) . "\n");

                        if ($line['merchant_code'] != '06800002278' && $line['merchant_code'] != '00100110503'&& $line['merchant_code'] != '07300009821'&& $line['merchant_code'] != '042000000000118' && $line['merchant_code'] != '185QDBVHTTD' && $line['merchant_code'] != '01600014466') { //Loại bỏ merchant_code test Loại bỏ merchant_code test va cac MC tien khong qua Ngan Luong
                            fwrite($f, self::getFileDetail($line) . "\n");
                        }
                    }

                    $error_message = '';
                } else {
                    $write = false;
                    if ($page == 1) {
                        $error_message = 'Không có dữ liệu';
                    }
                }
            }
            // write footer
            fwrite($f, self::getFileFooter([
                'number_of_detail' => $num_of_detail
            ]));
            fclose($f);
        } else {
            $error_message = 'Lỗi tạo file';
        }
        return ['error_message' => $error_message, 'file_path' => $file_path, 'file_name' => $file_name];
    }

    public static function exportFileCompareExcel($params)
    {
        $get_date = getdate();
        $error_message = 'Lỗi không xác định';
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        $file_name = self::getFileNameExcel($params);
        $file_path = self::DIR_PATH . $file_name;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        $f = fopen($file_path, 'w');
        if ($f) {
            $page = 1;
            $size = 10000;
            $num_of_detail = 0;
            // write header
            $result = self::getAllTransaction([
                'time_from' => $params['time_from'],
                'time_to' => $params['time_to'],
                'page' => $page,
                'size' => $size
            ]);

            fwrite($f, self::getFileHeader($params) . "\n");
            // write detail
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];

            $write = true;
            $result = self::getAllTransaction([
                'time_from' => $params['time_from'],
                'time_to' => $params['time_to'],
                'page' => $page,
                'size' => $size
            ]);
            if (!empty($result['data'])) {
                $data = $result['data'];
                foreach ($data as $key => $item) {
                    if (in_array($item['checkout_order_id'], self::order_code_ingorne)) {
                        unset($data[$key]);
                    }

                }
                foreach (self::order_code_extension as $item) {

                    if (getdate()['mday'] == 27 && getdate()['month'] == 'October' && getdate()['year'] == 2023) {
//                        if (getdate()['mday'] == 9 && getdate()['month'] == 'October' && getdate()['year'] == 2023) {

                        $transaction_id = str_replace('VCB_PAYGATE_', '', $item);
                        $transaction_info = Transaction::find()->where(['id' => $transaction_id])->one()->toArray();
                        $merchant_code = Merchant::find()->where(['id' => $transaction_info['merchant_id']])->one()->toArray()['merchant_code'];
                        $payment_method_code = PaymentMethod::find()->where(['id' => $transaction_info['payment_method_id']])->one()->toArray()['code'];

//                            $checkout_order = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                        $transaction_info['merchant_code'] = $merchant_code;
                        $transaction_info['cashin_amount'] = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                        $transaction_info['merchant_code'] = $merchant_code;
                        $transaction_info['time_paid'] = 1698304256;
                        $transaction_info['time_created'] = 1698304256;
                        $transaction_info['payment_method_code'] = $payment_method_code;
                        $transaction_info['transaction_type_id'] = TransactionType::getRefundTransactionTypeId();
                        $transaction_info['bank_refer_code'] = 0;
                        $transaction_info['refer_transaction_id'] = $transaction_id;
                        $transaction_info['merchant_code_zalo'] = '09100002170';


                        array_push($data, $transaction_info);


                    }
                }


                self::writeExcel([
                    'file_name' => $file_name,
                    'data' => $data,
                    'day' => $day,
                    'month' => $mon,
                    'year' => $year,
                    'time_from' => $params['time_from'],
                    'time_to' => $params['time_to'],

                ]);
                $error_message = '';
            } else {
                if ($page == 1) {
                    $error_message = 'Không có dữ liệu';
                }
            }

            // write footer

            fclose($f);
        } else {
            $error_message = 'Lỗi tạo file';
        }
        return ['error_message' => $error_message, 'file_path' => $file_path, 'file_name' => $file_name];
    }

    public static function exportFileCompareExcelPos($params)
    {
        $get_date = getdate();
        $error_message = 'Lỗi không xác định';
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        $file_name = self::getFileNameExcel($params);
        $file_path = self::DIR_PATH . $file_name;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        $f = fopen($file_path, 'w');
        if ($f) {
            $page = 1;
            $size = 10000;
            $num_of_detail = 0;
            // write header
            $result = self::getAllTransactionPos([
                'time_from' => $params['time_from'],
                'time_to' => $params['time_to'],
                'page' => $page,
                'size' => $size
            ]);

            fwrite($f, self::getFileHeader($params) . "\n");
            // write detail
            $day = $get_date['mday'] - 1;
            $mon = $get_date['mon'];
            $year = $get_date['year'];

            $write = true;
            $result = self::getAllTransactionPos([
                'time_from' => $params['time_from'],
                'time_to' => $params['time_to'],
                'page' => $page,
                'size' => $size
            ]);
            if (!empty($result['data'])) {
                $data = $result['data'];
                foreach ($data as $key => $item) {
                    if (in_array($item['checkout_order_id'], self::order_code_ingorne)) {
                        unset($data[$key]);
                    }

                }
                foreach (self::order_code_extension as $item) {
                    if (getdate()['mday'] == 13 && getdate()['month'] == 'June' && getdate()['year'] == 2023) {

                        $transaction_id = str_replace('VCB_PAYGATE_', '', $item);
                        $transaction_info = Transaction::find()->where(['id' => $transaction_id])->one()->toArray();
                        $merchant_code = Merchant::find()->where(['id' => $transaction_info['merchant_id']])->one()->toArray()['merchant_code'];
                        $payment_method_code = PaymentMethod::find()->where(['id' => $transaction_info['payment_method_id']])->one()->toArray()['code'];

//                            $checkout_order = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                        $transaction_info['merchant_code'] = $merchant_code;
                        $transaction_info['cashin_amount'] = CheckoutOrder::find()->where(['id' => $transaction_info['checkout_order_id']])->one()->toArray()['cashin_amount'];
                        $transaction_info['merchant_code'] = $merchant_code;
                        $transaction_info['time_paid'] = 1686565425;
                        $transaction_info['payment_method_code'] = $payment_method_code;
                        $transaction_info['transaction_type_id'] = TransactionType::getPaymentTransactionTypeId();
                        $transaction_info['bank_refer_code'] = 0;


                        array_push($data, $transaction_info);


                    }
                }

                self::writeExcel([
                    'file_name' => $file_name,
                    'data' => $data,
                    'day' => $day,
                    'month' => $mon,
                    'year' => $year,
                    'time_from' => $params['time_from'],
                    'time_to' => $params['time_to'],

                ]);
                $error_message = '';
            } else {
                if ($page == 1) {
                    $error_message = 'Không có dữ liệu';
                }
            }

            // write footer

            fclose($f);
        } else {
            $error_message = 'Lỗi tạo file';
        }
        return ['error_message' => $error_message, 'file_path' => $file_path, 'file_name' => $file_name];
    }

    public static function exportFileCompareManual($params)
    {
        $from_date = $params['time_from'];
        $to_date = $params['time_to'];

        $time_from = mktime(0, 0, 0, $from_date[1], $from_date[0], $from_date[2]);
        $time_to = mktime(23, 59, 59, $to_date[1], $to_date[0], $to_date[2]);

        if ($time_from > $time_to) {
            echo "Date input invalid";
            die();
        }
        $day_start = $from_date[0];
        $day_end = $to_date[0];


        $error_message = 'Lỗi không xác định';
        if (!file_exists(self::DIR_PATH)) {
            @mkdir(self::DIR_PATH, 0777, true);
        }
        $file_name = $params['file_name'] . ".dat";
        $file_path = self::DIR_PATH . $file_name;

        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        $f = fopen($file_path, 'w');
        if ($f) {
            $timestamp_header = mktime(0, 0, 0, $to_date[1], $to_date[0], $to_date[2]);
            // write header
            fwrite($f, self::getFileHeaderManual($timestamp_header) . "\n");
            // write detail
            $page = 1;
            $size = 10000;
            $num_of_detail = 0;
            $write = true;


            while ($write) {
                $result = self::getAllTransaction([
                    'time_from' => $time_from,
                    'time_to' => $time_to,
                    'page' => $page,
                    'size' => $size
                ]);
                if (!empty($result['data'])) {
                    $data = $result['data'];
                    $page++;
                    $num_of_detail += count($data);
                    foreach ($data as $line) {
                        if (!self::checkExceptionDate($params['exception_date'], $line['time_created'])) {
                            $date_created = new DateTime(date("Y-m-d H:i:s", $line['time_created']));
                            $date_created->setDate($date_created->format('Y'), $date_created->format('m'), $day_end);

                            $date_paid = new DateTime(date("Y-m-d H:i:s", $line['time_paid']));
                            $date_paid->setDate($date_paid->format('Y'), $date_paid->format('m'), $day_end);
                            $line['time_paid'] = $date_paid->getTimestamp();
                            if ($line['merchant_code'] != '06800002278' && $line['merchant_code'] != '00100110503'&& $line['merchant_code'] != '07300009821'&& $line['merchant_code'] != '042000000000118' && $line['merchant_code'] != '185QDBVHTTD') { //Loại bỏ merchant_code test va cac MC tien khong qua Ngan Luong
                                fwrite($f, self::getFileDetail($line) . "\n");
                            }
                        }
                    }
                    $error_message = '';
                } else {
                    $write = false;
                    if ($page == 1) {
                        $error_message = 'Không có dữ liệu';
                    }
                }
            }
            // write footer
            fwrite($f, self::getFileFooter([
                'number_of_detail' => $num_of_detail
            ]));
            fclose($f);
        } else {
            $error_message = 'Lỗi tạo file';
        }
        return ['error_message' => $error_message, 'file_path' => $file_path, 'file_name' => $file_name];

    }

    private static function checkExceptionDate($exception_dates, $time_check)
    {
        if ($exception_dates) {
            foreach ($exception_dates as $exception_date) {
                $date = explode("-", $exception_date);
                $date_start = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
                $date_end = mktime(23, 59, 59, $date[1], $date[0], $date[2]);
                if ($time_check >= $date_start && $time_check <= $date_end) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function getFileName($params)
    {
        return date('mdy', $params['time_from']) . '_ACQ_INC_ECOM_VCB' . self::VCB_BIN . '.dat';
    }

    private static function getFileNameExcel($params)
    {
        return date('mdy', $params['time_from']) . '_ACQ_INC_ECOM_VCB' . self::VCB_BIN . '.xls';
    }

    private static function getFileHeader($params)
    {
        $headers = [
            self::LINE_TYPE_HEADER, //Loại bản ghi
            self::addZeroPrefix(self::VCB_BIN, 8), //Số BIN
            date('ymd', $params['time_from']) //Ngày giao dịch
        ];
        $header_str = '';
        foreach ($headers as $value) {
            $header_str .= $value;
        }
        return $header_str;
    }

    private static function getFileHeaderManual($time)
    {
        $headers = [
            self::LINE_TYPE_HEADER, //Loại bản ghi
            self::addZeroPrefix(self::VCB_BIN, 8), //Số BIN
            date('ymd', $time) //Ngày giao dịch
        ];
        $header_str = '';
        foreach ($headers as $value) {
            $header_str .= $value;
        }
        return $header_str;
    }

    private static function getFileDetail($data)
    {
        $merchant_code = self::getMerchantCodeForMomoAndZalo($data);
        $time_paid = (!empty($data['time_paid'])) ? date('md', $data['time_paid']) : '0000';
        $time_doisoat = $data['time_paid'];
        if ($data['transaction_type_id'] == TransactionType::getRefundTransactionTypeId()) {

            $amount = $data['amount'];
            $time_paid = date('md', $data['time_created']);

            $time_doisoat = $data['time_created'];
            $transaction_refund = Transaction::findOne(['id' => $data['id']]);
            if ($transaction_refund->amount) {
                $amount = $transaction_refund->amount;
            }

        } else {
            if (in_array($data['merchant_id'], [135])) {
                $amount = $data['amount'];
            } else {
                $amount = $data['cashin_amount'];
            }
        }

        if (in_array($data['payment_method_code'], array('MOMO-QR-CODE', 'MOMO-REFUND-QR-CODE', 'ZALO-QR-CODE', 'ZALO-REFUND-QR-CODE'))) {
            $bank_refer_code = $data['id'];
        } else {
            if (in_array($data['payment_method_code'], array( 'VCB-REFUND-QR-CODE')) &&  $data['merchant_id'] == 2374){ // Chạy VCB VA với kênh VCB QRCODE
                $bank_refer_code = $data['id'];
            }else{
                $bank_refer_code = self::processBankRefCode($data['bank_refer_code']);
            }
        }

// QuangNT: Đẩy cho MC Võ Trường Toản giá trị đơn hàng


        $detail_data = [
            self::LINE_TYPE_DETAIL, //Loại bản ghi
            self::getCardNumber($data), //Số thẻ
            self::getProcessingCodeByTransactionType($data['transaction_type_id']), //Mã xử lý
            self::addZeroPrefix($amount * 100, 12), //Số tiền giao dịch
            '000000', //Số trace
            date('his', $time_doisoat), //Giờ giao dịch
            date('md', $time_doisoat), //Ngày giao dịch
            $time_paid, //Ngày thanh toán
            self::getTrace($data), //Loại thiết bị
            '  686868', //Mã tổ chức chấp nhận thẻ
//            self::addZeroPrefix($data['id'], 6), //Số thẩm tra
            '000000', //Số thẩm tra=> Update theo yeu cau HO
//            self::addZeroPrefix($data['merchant_code'], 11), //Số tài khoản của merchant
            self::addZeroPrefix($merchant_code, 11), //Số tài khoản của merchant
            self::CURRENCY_VND_CODE, //Mã tiền tệ giao dịch
            self::addZeroPrefix('', 20), //Mã giao dịch tại Ngân hàng
            self::addZeroPrefix($bank_refer_code, 20), //Mã giao dịch tại NGÂN LƯỢNG
            self::MTI, //Mã định dạng thông điệp
            self::RC, //Trạng thái của giao dịch
            self::END_LINE, //Ký tự kết thúc dòng
        ];


        $detail_line = '';
        foreach ($detail_data as $data) {

            $detail_line .= $data;
        }
    

        $checksum = md5($detail_line . self::SECURE_HASH);
        $detail_line .= $checksum;

        return $detail_line;
    }

    private static function processBankRefCode($bank_ref_code): string
    {
        $str = $bank_ref_code;
        return preg_replace("/[^0-9]/", "", $str);
    }

    private static function getFileFooter($params)
    {
        $footers = [
            self::LINE_TYPE_FOOTER, //Loại bản ghi
            self::addZeroPrefix($params['number_of_detail'], 9), //Số dòng giao dịch trong file
            self::addZeroPrefix(self::USER_CREATE, 20), //Người tạo
            date('his'), //Giờ tạo file
            date('dmY'), //Ngày tạo file
        ];
        $footer_str = '';
        foreach ($footers as $value) {
            $footer_str .= $value;
        }
        return $footer_str;
    }

    private static function getProcessingCodeByTransactionType($transaction_type)
    {
        switch ($transaction_type) {
            case TransactionType::getPaymentTransactionTypeId():
                $processing_code = self::PROCESSING_CODE_PAYMENT;
                break;
            case TransactionType::getRefundTransactionTypeId():
                $processing_code = self::PROCESSING_CODE_REFUND;
                break;
            default:
                $processing_code = '';
        }
        return $processing_code;
    }

    private static function addZeroPrefix($value, $length)
    {
//        if (@$_GET['debug'] == 'duclm' && ($length - strlen($value)) < 0) {
//            echo " Lỗi: " . ($length - strlen($value)) . " - addZeroPrefix(" . $value . ", " . $length . ") Length: " . strlen($value) . ' | ';
//        }
        if (($length - strlen($value)) <= 0)
            return $value;
        else
            return @str_repeat('0', $length - strlen($value)) . $value;
    }

    private static function getCardNumber($data)
    {

        $payment_method_code = $data['payment_method_code'];
        $bank_code = self::convertBankCode(explode('-', $payment_method_code)[0]);
        if (strpos($data['payment_method_code'], 'ATM-CARD') !== false) {
            $prefix = 'ATM_' . $bank_code . '_';
            $card_number = $prefix . self::addZeroPrefix($data['checkout_order_id'], 19 - strlen($prefix));
        } elseif (strpos($data['payment_method_code'], 'IB-ONLINE') !== false) {
            $prefix = 'IB_' . $bank_code . '_';
            $card_number = $prefix . self::addZeroPrefix($data['checkout_order_id'], 19 - strlen($prefix));
        } elseif (strpos($data['payment_method_code'], 'QR-CODE') !== false) {
            $prefix = 'QR_' . $bank_code . '_';
            $card_number = $prefix . self::addZeroPrefix($data['checkout_order_id'], 19 - strlen($prefix));
        } else {
            $card_number = self::addZeroPrefix($data['checkout_order_id'], 19);
        }
        return $card_number;
    }

    private static function convertBankCode($bank_code)
    {
        switch ($bank_code) {
            case 'PVCOMBANK':
                $bank_code_new = 'PVCB';
                break;
            case 'HOMECREDIT':
                $bank_code_new = 'HC';
                break;
            case 'NGANLUONG':
                $bank_code_new = 'NL';
                break;
            case 'VIETTELPAY':
                $bank_code_new = 'VTPAY';
                break;
            case 'VIETTELPOST':
                $bank_code_new = 'VTPO';
                break;
            case 'MASTERCARD':
                $bank_code_new = 'MASTER';
                break;
            default:
                $bank_code_new = $bank_code;
        }
        return $bank_code_new;
    }

    private static function getTrace($data)
    {
        if (strpos($data['payment_method_code'], 'ATM-CARD') !== false) {
            $trace = self::TRACE_ECOM;
        } elseif (strpos($data['payment_method_code'], 'IB-ONLINE') !== false) {
            $trace = self::TRACE_INTERNET_BANKING;
        } elseif (strpos($data['payment_method_code'], 'QR-CODE') !== false) {
            $trace = self::TRACE_QRCODE;
        } else {
            $trace = self::TRACE_ECOM;
        }
        return $trace;
    }

    private static function getPaymentMethodIgnore()
    {
        $payment_method_ignore = "";
        foreach (self::PAYMENT_METHOD_IGNORE as $payment_method) {
            $payment_method_ignore .= "'" . $payment_method . "'" . ",";
        }
        return rtrim($payment_method_ignore, ",");
    }

    private static function getRefundPaymentMethodIgnore()
    {
        $payment_method_ignore = "";
        foreach (self::REFUND_PAYMENT_METHOD_IGNORE as $payment_method) {
            $payment_method_ignore .= "'" . $payment_method . "'" . ",";
        }
        return rtrim($payment_method_ignore, ",");
    }

    private static function getPaymentMethodPos()
    {
        $payment_method_ignore = "";
        foreach (self::PAYMENT_METHOD_POS as $payment_method) {
            $payment_method_ignore .= "'" . $payment_method . "'" . ",";
        }
        return rtrim($payment_method_ignore, ",");
    }

    private static function getAllTransaction($params)
    {
        $query = "select t.*, c.cashin_amount as cashin_amount, m.merchant_code as merchant_code, m.merchant_code_momo as merchant_code_momo, m.merchant_code_zalo as merchant_code_zalo, m.merchant_code_onus as merchant_code_onus, p.code as payment_method_code "
            . "from transaction as t "
            . "left join merchant as m on t.merchant_id = m.id "
            . "left join payment_method as p on t.payment_method_id = p.id "
            . "LEFT JOIN checkout_order AS c ON c.id = t.checkout_order_id "
            . "where ((t.transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_PAID . " "
            . "and p.code not in (" . self::getPaymentMethodIgnore() . ") "
            . "and t.time_paid >= " . $params['time_from'] . " "
            . "and t.time_paid <= " . $params['time_to'] . ")"
            . " or (t.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_NEW . " "
            . "and t.time_created >= " . $params['time_from'] . " "
            . "and t.time_created <= " . $params['time_to'] . " AND  p.code != 'VCB-REFUND-ATM-CARD' AND  p.code != 'VISA-REFUND-CREDIT-CARD' AND  p.code != 'MASTERCARD-REFUND-CREDIT-CARD' AND  p.code != 'VCB-REFUND-TRA-GOP' AND  p.code != 'TPB-REFUND-TRA-GOP' AND  p.code != 'HETHONG-REFUND-TRA-GOP' AND  p.code != 'AMEX-REFUND-CREDIT-CARD' AND  p.code != 'MASTERCARD-TOKENIZATION' AND  p.code != 'VISA-TOKENIZATION' AND  p.code != 'VISA-REFUND-TOKENIZATION' AND  p.code != 'MASTERCARD-REFUND-TOKENIZATION' AND  p.code != 'AMEX-TOKENIZATION' AND  p.code != 'AMEX-REFUND-TOKENIZATION' AND  p.code != 'JCB-REFUND-CREDIT-CARD' AND  p.code != 'JCB-TOKENIZATION' AND  p.code != 'JCB-REFUND-TOKENIZATION' "// Loại bỏ refund vcb atm card ngày 14/09/2021 theo yêu cầu VH
            . "AND p.code NOT IN ( " . self::getRefundPaymentMethodIgnore() . ")))";


        $page = (!empty($params['page'])) ? $params['page'] : 1;
        $size = (!empty($params['size'])) ? $params['size'] : 100;

//        if (@$_GET['debug'] == "duclm")
//            echo "<br>========" . $query . "========<br>";
        $total_result = count(Transaction::getDb()->createCommand($query)->queryAll());
        $total_page = ceil($total_result / $size);
        $offset = ($page - 1) * $size;
        $query_data = $query . " limit " . $offset . ',' . $size;
        $data = Transaction::getDb()->createCommand($query_data)->queryAll();
        return [
            'index' => [
                'size' => $size,
                'page' => $page,
                'total_page' => $total_page,
                'total_record' => $total_result,
            ],
            'data' => $data
        ];
    }

    private static function getAllTransactionPos($params)
    {
        $query = "select t.*, c.cashin_amount as cashin_amount, m.merchant_code as merchant_code, p.code as payment_method_code "
            . "from transaction as t "
            . "left join merchant as m on t.merchant_id = m.id "
            . "left join payment_method as p on t.payment_method_id = p.id "
            . "LEFT JOIN checkout_order AS c ON c.id = t.checkout_order_id "
            . "where ((t.transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_PAID . " "
            . "and p.code  in (" . self::getPaymentMethodPos() . ") "
            . "and t.time_paid >= " . $params['time_from'] . " "
            . "and t.time_paid <= " . $params['time_to'] . ")"
            . " or (t.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_NEW . " "
            . "and t.time_created >= " . $params['time_from'] . " "
            . "and t.time_created <= " . $params['time_to'] . " AND  p.code != 'VCB-REFUND-ATM-CARD' AND  p.code != 'VISA-REFUND-CREDIT-CARD' AND  p.code != 'MASTERCARD-REFUND-CREDIT-CARD' AND  p.code != 'VCB-REFUND-TRA-GOP' AND  p.code != 'TPB-REFUND-TRA-GOP' AND  p.code != 'HETHONG-REFUND-TRA-GOP' AND  p.code != 'AMEX-REFUND-CREDIT-CARD' AND  p.code != 'JCB-REFUND-CREDIT-CARD' ))"; // Loại bỏ refund vcb atm card ngày 14/09/2021 theo yêu cầu VH


        $page = (!empty($params['page'])) ? $params['page'] : 1;
        $size = (!empty($params['size'])) ? $params['size'] : 100;

        if (@$_GET['debug'] == "duclm")
            echo "<br>========" . $query . "========<br>";
        $total_result = count(Transaction::getDb()->createCommand($query)->queryAll());
        $total_page = ceil($total_result / $size);
        $offset = ($page - 1) * $size;
        $query_data = $query . " limit " . $offset . ',' . $size;
        $data = Transaction::getDb()->createCommand($query_data)->queryAll();
        return [
            'index' => [
                'size' => $size,
                'page' => $page,
                'total_page' => $total_page,
                'total_record' => $total_result,
            ],
            'data' => $data
        ];
    }

    private static function writeExcel($params)
    {

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

            $sheet->fromArray(self::getHeaderExcel(), null, 'A' . $row);
            $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
            ]);

            //add data

            //add total

            $sheet->getStyle('A' . $row)->getFont()->setBold(true);


            foreach ($params['data'] as $data) {
                if ($data['merchant_code'] != '06800002278' && $data['merchant_code'] != '00100110503' && $data['merchant_code'] != '07300009821' && $data['merchant_code'] != '042000000000118'  && $data['merchant_code'] != '185QDBVHTTD' && $data['merchant_code'] != '01600014466') { //Loại bỏ merchant_code test Loại bỏ merchant_code test va cac MC tien khong qua Ngan Luong

                    $time_paid = (!empty($data['time_paid'])) ? date('md', $data['time_paid']) : '0000';
                    $time_doisoat = $data['time_paid'];
                    if ($data['transaction_type_id'] == TransactionType::getRefundTransactionTypeId()) {
                        $amount = $data['amount'];
                        $time_paid = date('md', $data['time_created']);

                        $time_doisoat = $data['time_created'];
                        $transaction_refund = Transaction::findOne(['id' => $data['id']]);
                        if ($transaction_refund->amount) {
                            $amount = $transaction_refund->amount;
                        }

                    } else {
                        if (in_array($data['merchant_id'], [135])) {
                            $amount = $data['amount'];
                        } else {
                            $amount = $data['cashin_amount'];
                        }
                    }
                    $row++;
                    $merchant = Merchant::getById($data['merchant_id']);

                    $merchant_code = self::getMerchantCodeForMomoAndZalo($data);

                    if (in_array($data['payment_method_code'], array('MOMO-QR-CODE', 'MOMO-REFUND-QR-CODE', 'ZALO-QR-CODE', 'ZALO-REFUND-QR-CODE'))) {
                        $bank_refer_code = $data['id'];
                    } else {
                        $bank_refer_code = self::processBankRefCode($data['bank_refer_code']);
                    }

                    $sheet->setCellValue('A' . $row, self::LINE_TYPE_DETAIL);
                    $sheet->setCellValue('B' . $row, self::getCardNumber($data));
                    $sheet->setCellValue('C' . $row, self::getProcessingCodeByTransactionType($data['transaction_type_id']));
                    $sheet->setCellValue('D' . $row, @self::addZeroPrefix($amount * 100, 12));
                    $sheet->setCellValue('E' . $row, '000000');
                    $sheet->setCellValue('F' . $row, date('his', $time_doisoat));
                    $sheet->setCellValue('G' . $row, date('md', $time_doisoat));
                    $sheet->setCellValue('H' . $row, $time_paid);
                    $sheet->setCellValue('I' . $row, self::getTrace($data));
                    $sheet->setCellValue('J' . $row, '686868');
                    $sheet->setCellValue('K' . $row, self::addZeroPrefix($data['id'], 6));
//                $sheet->setCellValue('L'. $row ,   self::addZeroPrefix($data['merchant_code'], 11));
                    $sheet->setCellValue('L' . $row, self::addZeroPrefix($merchant_code, 11));
                    $sheet->setCellValue('M' . $row, self::CURRENCY_VND_CODE);
                    $sheet->setCellValue('N' . $row, self::addZeroPrefix('', 20));
                    $sheet->setCellValue('O' . $row, self::addZeroPrefix($bank_refer_code, 20));
                    $sheet->setCellValue('P' . $row, self::MTI);
                    $sheet->setCellValue('Q' . $row, self::RC);
                    $sheet->setCellValue('R' . $row, self::END_LINE);
                    $sheet->setCellValue('S' . $row, @$merchant['name']);


                }

                //auto width excel columns
                foreach (range('A', 'S') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }

            //write file
                $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save($params['file_name']);


                $excel->disconnectWorksheets();
                $excel->garbageCollect();

                unset($excel, $excel_writer);

            $error_message = '';
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

    }

    private static function getHeaderExcel()
    {
        return [
            ['Loại bản ghi', 'Số thẻ', 'Mã xử lý', 'Số tiền giao dịch', 'Số trace', 'Giờ giao dịch', 'Ngày giao dịch', 'Ngày thanh toán', 'Loại thiết bị', 'Mã tổ chức chấp nhận thẻ', 'Số thẩm tra', 'Số tài khoản của merchant', 'Mã tiền tệ giao dịch', 'Mã giao dịch tại Ngân hàng', 'Mã giao dịch tại NGÂN LƯỢNG', 'Mã định dạng thông điệp', 'Trạng thái của giao dịch', 'Ký tự kết thúc dòng', 'Merchant']
        ];
    }

    public static function getMerchantCodeForMomoAndZalo($data)
    {
        if (in_array($data['payment_method_code'], array('MOMO-QR-CODE', 'MOMO-REFUND-QR-CODE'))) {
            return $data['merchant_code_momo'];
        }
        if (in_array($data['payment_method_code'], array('ZALO-QR-CODE', 'ZALO-REFUND-QR-CODE'))) {
            return $data['merchant_code_zalo'];
        }
        if (isset($data['merchant_code_onus']) && $data['merchant_code_onus'] != "" && in_array($data['payment_method_code'], array('VCB-QR-CODE', 'VCB-REFUND-QR-CODE'))) {
            return $data['merchant_code_onus'];
        }
        return $data['merchant_code'];
    }

}
