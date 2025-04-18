<?php

namespace cron\controllers;

use common\components\libs\Tables;
use common\components\utils\Translate;
use common\models\db\Transaction;
use cron\models\Sample;
use common\models\business\SendMailBussiness;
use common\models\db\Bank;
use common\models\db\Merchant;
use cron\components\CronBasicController;
//use common\models\db\PosEquipmentInfo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\db\Expression;

class InstallmentPerBankReportController extends CronBasicController
{

    public function actionIndex()
    {
        ini_set('max_execution_time', '0');
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $mode = Yii::$app->request->get("mode");
        if ($mode == 1) {
            $banks = [
                'HDB',
                'PVCOMBANK',
                'GDB',
                'HOMECREDIT',
                'MB',
                'VCB',
                'BIDV',
                'OCB',
                'SHB',
                'SCB',
                'TPB',
                'SC',
                'SEA',
                'STB',
                'VIB',
                'MSB',
                'SHINHAN',
                'TCB',
                'ACB',
                'ICB',
                'VPB',
                'HSBC',
                'EXB',
                'KLB',
            ];
            echo "<pre>";
            foreach ($banks as $bank) {
                $bank_detail = Bank::find()->where(['=', 'code', $bank])->select('id')->one();
                if ($bank_detail) {
                    $data_MB_bank_report = Tables::selectAllBySql("SELECT * from bank INNER JOIN payment_method ON bank.id = payment_method.bank_id INNER JOIN transaction ON payment_method.id = transaction.payment_method_id INNER JOIN checkout_order ON transaction.id = checkout_order.transaction_id  WHERE transaction.time_paid BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5 AND checkout_order.status = 3 AND checkout_order.time_paid != '' AND transaction.installment_conversion = 1 AND payment_method.bank_id =".$bank_detail['id']);
                    $report_MB_bank = $this->emailReport($bank_detail['id'], $data_MB_bank_report);
                } else {
                    echo $bank;
                    die();
                }
            }
//            echo count($banks);
            echo "Fake Data Success";
            die();
        }
        $bank_code = Yii::$app->request->get("bank_code");
        if (!empty($bank_code)) {
            $bank_detail = Bank::find()->where(['=', 'code', $bank_code])->select('id')->one();
            if ($bank_detail) {
//                    var_dump($bank_detail);
                $data_bank_report = $this->getDataWithPreviousDate();
//                $data = $data_bank_report->andWhere(['bank_id' => $bank_detail['id']])->all();
//                $data = Tables::selectAllBySql("SELECT DISTINCT bank.code from bank INNER JOIN payment_method ON bank.id = payment_method.bank_id INNER JOIN transaction ON payment_method.id = transaction.payment_method_id INNER JOIN checkout_order ON transaction.id = checkout_order.transaction_id  WHERE checkout_order.time_created BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5");
                $data = Tables::selectAllBySql("SELECT * from bank INNER JOIN payment_method ON bank.id = payment_method.bank_id INNER JOIN transaction ON payment_method.id = transaction.payment_method_id INNER JOIN checkout_order ON transaction.id = checkout_order.transaction_id  WHERE transaction.time_paid BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5 AND checkout_order.status = 3 AND checkout_order.time_paid != '' AND transaction.installment_conversion = 1 AND payment_method.bank_id =".$bank_detail['id']);
                if (count($data) > 0) {
                    for ($x=0;$x<count($data);$x++)
                    {
                        $data[$x]['installment_info'] = json_decode($data[$x]['installment_info'],true);
                    }
                    $report_bank = $this->emailReport($bank_detail['id'], $data);
                    if ($report_bank['mail_sent']) {
                        for ($x=0;$x<count($data);$x++)
                        {
                            $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $data[$x]['transaction_id'])->one();
                            $model->installment_conversion = Transaction::InstallmentConversion_SEND;
//                            $model->time_update = time();
                            if ($model) {
                                if ($model->save()) {
                                    $error_message = '';
                                }
                            }
                        }
                        $this->updateStatus($data);
                    }
                    echo json_encode($report_bank);
                    die();
                } else {
                    echo "Không có giao dịch nào";
                    die();
                }
            } else {
                echo "Error!!!!";
                die();
            }
        }
        else{
            $bank_detail = Bank::find()->all();
            for ($y=0;$y<count($bank_detail);$y++)
            {
                $data = Tables::selectAllBySql("SELECT * from bank INNER JOIN payment_method ON bank.id = payment_method.bank_id INNER JOIN transaction ON payment_method.id = transaction.payment_method_id INNER JOIN checkout_order ON transaction.id = checkout_order.transaction_id  WHERE transaction.time_paid BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5 AND checkout_order.status = 3 AND checkout_order.time_paid != '' AND transaction.installment_conversion = 1  AND payment_method.bank_id =".$bank_detail[$y]['id']);
                if (count($data) > 0) {
                    for ($x=0;$x<count($data);$x++)
                    {
                        $data[$x]['installment_info'] = json_decode($data[$x]['installment_info'],true);
                    }
                    for ($x =0;$x < count($data);$x++)
                    {
                        $report_bank = $this->emailReport($data[$x]['bank_id'], $data);
                        if ($report_bank['mail_sent']) {
                            $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $data[$x]['transaction_id'])->one();
                            $model->installment_conversion = Transaction::InstallmentConversion_SEND;
                            if ($model) {
                                if ($model->save()) {
                                    $error_message = '';
                                }
                            }
                            $this->updateStatus($data);
                        }
                    }

                    echo json_encode($report_bank);
                }
            }die();
        }


        $result = [];
        ini_set('max_execution_time', '0');
        date_default_timezone_set('Asia/Ho_Chi_Minh');
//        $weekday = strtolower(date("l"));
        $weekday = "monday";

        $data = $this->getDataWithPreviousDate();
        $MB_bank_id = Bank::find()->where(['=', 'code', "MB"])->select('id')->one()['id'];

        if ($MB_bank_id !== null) {
            if ($weekday == 'monday') {
                $data_MB_bank_report = $this->getDataWithPreviousDate(3);
                $data_MB_bank = $data_MB_bank_report->andWhere(['bank_id' => $MB_bank_id])->all();
                if (count($data_MB_bank) > 0) {
                    $report_MB_bank = $this->emailReport($MB_bank_id, $data_MB_bank);
                    if ($report_MB_bank['mail_sent']) {
                        $this->updateStatus($data_MB_bank_report);
                    }
                    array_push($result, json_encode($report_MB_bank));
                }

            } elseif (!in_array($weekday, ['saturday', 'sunday'])) {
                $data_MB_bank_report = $this->getDataWithPreviousDate();
                $data_MB_bank = $data_MB_bank_report->andWhere(['bank_id' => $MB_bank_id])->all();
                if (count($data_MB_bank) > 0) {
                    $report_MB_bank = $this->emailReport($MB_bank_id, $data_MB_bank);
                    if ($report_MB_bank['mail_sent']) {
                        $this->updateStatus($data_MB_bank_report);
                    }
                    array_push($result, json_encode($report_MB_bank));
                }
            }
            $data = $data->andWhere(['<>', 'bank_id', $MB_bank_id]);
        }

        $banks = $data->groupBy(['bank_id'])->select('bank_id')->asArray()->all();
        foreach ($banks as $bank) {
            $data_report = $this->getDataWithPreviousDate();
            $report = $this->emailReport($bank['bank_id'], $data_report->andWhere(['bank_id' => $bank['bank_id']])->all());
            if ($report['mail_sent']) {
                $this->updateStatus($data_report);
            }
            array_push($result, json_encode($report));
        }

//        foreach ($data->all() as $key => $item) {
//            array_push($bank_ids, $item->bank_id);
//        }
//        $bank_ids = array_values(array_unique($bank_ids));
//        $result = [];
//        $array_banks = Bank::getArrayBank();
//        foreach ($bank_ids as $bank_id) {
//            if (!$array_banks[$bank_id] == "MB") {
//                $data_report = InstallmentConversion::find()->where(['between', 'created_at', date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))),
//                    date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y")))])
//                    ->andWhere(['=', 'status', 'SUCCESS']);
//                $report = $this->emailReport($bank_id, $data_report->andWhere(['bank_id' => $bank_id])->all());
//                array_push($result, $report);
//            }
//        }

//        $this->updateStatus($result);
        pr(["Cronjob executed successfully!", $result]);
    }

    private function getDataWithPreviousDate()
    {
        $checkout = Tables::selectAllBySql("SELECT * from bank INNER JOIN payment_method ON bank.id = payment_method.bank_id INNER JOIN transaction ON payment_method.id = transaction.payment_method_id INNER JOIN checkout_order ON transaction.id = checkout_order.transaction_id  WHERE transaction.time_paid BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5 AND checkout_order.status = 3 AND checkout_order.time_paid != '' AND transaction.installment_conversion = 1 ");
//        $checkout = Tables::selectAllBySql("SELECT * FROM checkout_order INNER JOIN transaction ON checkout_order.transaction_id = transaction.id WHERE checkout_order.time_created BETWEEN " . (time() - 86400) . " AND ". time() ." AND transaction.transaction_type_id = 5");
        return $checkout;
    }

    private function updateStatus($model)
    {
//        foreach ($data_update as $item) {
//            $item->installment_conversion = Transaction::InstallmentConversion_SEND;
//            $item->save();
//        }
    }

    private function emailReport($bank_id, $data)
    {
        $bank_detail = Bank::find()->where(['id' => $bank_id])->one();
        $bank_code = $bank_detail['code'];
        $spreadsheet = IOFactory::load(Yii::$app->basePath.'/../checkout/web/installment_report/banks/templates/' . $bank_code . '.xlsx');
        foreach ($data as $key => $installment) {
            $serial = $this->searchInSheet($spreadsheet, '{SERIAL}');
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($serial as $cell) {
                $sheet->setCellValue($cell, $key + 1);
                $next_row = (int)(filter_var($cell, FILTER_SANITIZE_NUMBER_INT)) + 1;
                $column = preg_replace("/[^A-Z]+/", "", $cell);
                $sheet->setCellValue($column . $next_row, '{SERIAL}');
            }
            $installment['pos_id'] = null;
//            if ($installment['pos_id']) {
//                $pos_mid = PosEquipmentInfo::find()->where(['id' => $installment['pos_id']])
//                    ->select('mid')->one();
//                if ($pos_mid) {
//                    $installment['pos_id'] = $pos_mid['mid'];
//                }
//            }

            $this->replaceInSheet($spreadsheet, $installment, '{TIME}');
            $this->replaceInSheet($spreadsheet, $installment, '{CURRENT_TIME}');
            $this->replaceInSheet($spreadsheet, $installment, '{PREFIX_CURRENT_TIME}');
            $this->replaceInSheet($spreadsheet, $installment, '{GDB_PREFIX_CURRENT_TIME}');
            $this->replaceInSheet($spreadsheet, $installment, '{CURRENT_TIME_AFTER_VP}');
            $this->replaceInSheet($spreadsheet, $installment, '{CURRENT_TIME_VP}');
            $this->replaceInSheet($spreadsheet, $installment, '{BANK_NAME}');
            $this->replaceInSheet($spreadsheet, $installment, '{ORDER_ID}');
            $this->replaceInSheet($spreadsheet, $installment, '{CARD_OWNER_FULLNAME}');
            $this->replaceInSheet($spreadsheet, $installment, '{MERCHANT_ID}');
            $this->replaceInSheet($spreadsheet, $installment, '{MERCHANT_NAME}');
            $this->replaceInSheet($spreadsheet, $installment, '{CARD_NUMBER}');
            $this->replaceInSheet($spreadsheet, $installment, '{PAYER_PHONE}');
            $this->replaceInSheet($spreadsheet, $installment, '{PAYER_EMAIL}');
            $this->replaceInSheet($spreadsheet, $installment, '{AMOUNT}');
            $this->replaceInSheet($spreadsheet, $installment, '{REFERENCE_NUMBER}');
            $this->replaceInSheet($spreadsheet, $installment, '{CREATED_AT}');
            $this->replaceInSheet($spreadsheet, $installment, '{INSTALLMENT_PERIOD}');
            $this->replaceInSheet($spreadsheet, $installment, '{OTP}');
            $this->replaceInSheet($spreadsheet, $installment, '{LOCATION}');
            $this->replaceInSheet($spreadsheet, $installment, '{PAYER_ID}');
            $this->replaceInSheet($spreadsheet, $installment, '{POS_ID}');
            $this->replaceInSheet($spreadsheet, $installment, '{ORIGINAL_AMOUNT}');
            $this->replaceInSheet($spreadsheet, $installment, '{UPDATED_AT}');
            $this->replaceInSheet($spreadsheet, $installment, '{PAYMENT_DATE}');
            $this->replaceInSheet($spreadsheet, $installment, '{PAYMENT_DATE_FORMAT_DD_MM_YYYY}');
            $this->replaceInSheet($spreadsheet, $installment, '{CARD_LAST_4_DIGITS}');
            $this->replaceInSheet($spreadsheet, $installment, '{CARD_FIRST_6_DIGITS}');
            $this->replaceInSheet($spreadsheet, $installment, '{CURRENCY}');
            $this->replaceInSheet($spreadsheet, $installment, '{CARD_TYPE}');
            $this->replaceInSheet($spreadsheet, $installment, '{PARTNER_ID}');
            $this->replaceInSheet($spreadsheet, $installment, '{PARTNER_NAME_DEFAULT}');
            $this->replaceInSheet($spreadsheet, $installment, '{INSTALLMENT_PERCENT}');
            $this->replaceInSheet($spreadsheet, $installment, '{CREATED_AT_FORMAT_DD_MM_YYYY}');
            $this->replaceInSheet($spreadsheet, $installment, '{TIME_NOW}');
        }
        $sheet = $spreadsheet->getActiveSheet();
        $bank_name_cell = $this->searchInSheet($spreadsheet, '{BANK_NAME}');
        $merchant_name_cell = $this->searchInSheet($spreadsheet, '{MERCHANT_NAME}');
        $merchant_id_cell = $this->searchInSheet($spreadsheet, '{MERCHANT_ID}');
        $reference_number_cell = $this->searchInSheet($spreadsheet, '{REFERENCE_NUMBER}');
        $last_serial_cell = $this->searchInSheet($spreadsheet, '{SERIAL}');
        $last_card_owner_fullname_cell = $this->searchInSheet($spreadsheet, '{CARD_OWNER_FULLNAME}');
        $last_card_number_cell = $this->searchInSheet($spreadsheet, '{CARD_NUMBER}');
        $last_payer_phone_cell = $this->searchInSheet($spreadsheet, '{PAYER_PHONE}');
        $last_payer_email_cell = $this->searchInSheet($spreadsheet, '{PAYER_EMAIL}');
        $last_amount_cell = $this->searchInSheet($spreadsheet, '{AMOUNT}');
        $last_created_at_cell = $this->searchInSheet($spreadsheet, '{CREATED_AT}');
        $last_period_cell = $this->searchInSheet($spreadsheet, '{INSTALLMENT_PERIOD}');
        $last_otp_cell = $this->searchInSheet($spreadsheet, '{OTP}');
        $last_location_cell = $this->searchInSheet($spreadsheet, '{LOCATION}');
        $last_order_id_cell = $this->searchInSheet($spreadsheet, '{ORDER_ID}');
        $last_payer_id_cell = $this->searchInSheet($spreadsheet, '{PAYER_ID}');
        $last_pos_id_cell = $this->searchInSheet($spreadsheet, '{POS_ID}');
        $last_updated_at_cell = $this->searchInSheet($spreadsheet, '{UPDATED_AT}');
        $last_original_amount_cell = $this->searchInSheet($spreadsheet, '{ORIGINAL_AMOUNT}');
        $last_payment_date_cell = $this->searchInSheet($spreadsheet, '{PAYMENT_DATE}');
        $last_payment_date_format_dd_mm_yyyy_cell = $this->searchInSheet($spreadsheet, '{PAYMENT_DATE_FORMAT_DD_MM_YYYY}');
        $last_card_last_4_digits_cell = $this->searchInSheet($spreadsheet, '{CARD_LAST_4_DIGITS}');
        $last_card_last_6_digits_cell = $this->searchInSheet($spreadsheet, '{CARD_FIRST_6_DIGITS}');
        $last_currency_cell = $this->searchInSheet($spreadsheet, '{CURRENCY}');
        $last_card_type_cell = $this->searchInSheet($spreadsheet, '{CARD_TYPE}');
        $last_partner_id_cell = $this->searchInSheet($spreadsheet, '{PARTNER_ID}');
        $last_partner_name_default_cell = $this->searchInSheet($spreadsheet, '{PARTNER_NAME_DEFAULT}');
        $last_installment_percent_cell = $this->searchInSheet($spreadsheet, '{INSTALLMENT_PERCENT}');
        $last_created_at_format_dd_mm_yyyy_cell = $this->searchInSheet($spreadsheet, '{CREATED_AT_FORMAT_DD_MM_YYYY}');
        $time_now = $this->searchInSheet($spreadsheet, '{TIME_NOW}');


        $this->setMassBlankValue($sheet, [$last_serial_cell, $last_card_owner_fullname_cell, $last_card_number_cell, $last_payer_email_cell,
            $last_payer_phone_cell, $last_amount_cell, $last_created_at_cell, $last_period_cell, $last_otp_cell, $last_location_cell, $bank_name_cell,
            $merchant_name_cell, $merchant_id_cell, $reference_number_cell, $last_order_id_cell, $last_payer_id_cell, $last_pos_id_cell, $last_original_amount_cell, $last_updated_at_cell,
            $last_payment_date_cell, $last_payment_date_format_dd_mm_yyyy_cell, $last_card_last_4_digits_cell, $last_card_last_6_digits_cell, $last_currency_cell,
            $last_card_type_cell, $last_partner_id_cell, $last_installment_percent_cell, $last_partner_name_default_cell, $last_created_at_format_dd_mm_yyyy_cell,$time_now]);
        $helper = new Sample();
//        $file_name = ROOT_PATH.'checkout/web/installment_report/banks/templates/' . $bank_code . '_' . date('d-m-Y') . '.xlsx';
        $file_name = ROOT_PATH.'/checkout/web/installment_report/banks/reports/' . $bank_code . '_NGAN_LUONG_' . date('d-m-Y') . '.xlsx';
        $helper->write($spreadsheet, $file_name);
        $send_to = $this->emailsToSend($bank_code);
        $ccTo = $this->CCToSend($bank_code);
        $current_date = new \DateTime();
        //$report_date = date('d/m/Y', strtotime($current_date->modify('-1 day')->format('Y-m-d H:i:s')));
//        $subject = 'Thông báo giao dịch ngày ' . $current_date->format('d-m-Y');
        $bank_name = $bank_detail['name'];
        $subject = "$bank_name V/v Ngân Lượng gửi giao dịch trả góp ngày " . $current_date->format('d-m-Y');
//        $body_content = 'Hệ thống NgânLượng.vn xin thông báo các giao dịch thanh toán thành công trên cổng thanh toán Vietcombank ngày ' . $current_date->format('d-m-Y') . '.';
        $body_content = "Dear anh chị Ngân Hàng, <br> <br>
                        Ngân lượng gửi danh sách các giao dịch trả góp ngày " . $current_date->format('d-m-Y') . "<br>" .
            "Nhờ anh chị kiểm tra và hỗ trợ chuyển đổi trả góp cho khách hàng giúp Ngân Lượng <br> <br>
                        Trân trọng cảm ơn Anh/Chị";

        $result = SendMailBussiness::sendCDTG($send_to, $subject, 'notify_transaction_daily_cdtg', ['body_content' => $body_content], 'layouts/basic',$ccTo ,ROOT_PATH . '/cron/web/runtime/export/' . $bank_code . '_NGAN_LUONG_' . date('d-m-Y') . '.xlsx');
        return ['bank_code' => $bank_code, 'mail_sent' => $result];
    }

    private function emailsToSend($bank_code)
    {
        $emails = [];


//        switch ($bank_code) {
//            case 'HDB':
//                $emails = ['uudaitragop@hdbank.com.vn'];
//                break;
//            case 'PVCOMBANK':
//                $emails = ['dangkitragop_pvcombank@pvcombank.com.vn'];
//                break;
//            case 'GDB':
//                $emails = ['vccb247@vietcapitalbank.com.vn'];
//                break;
//            case 'HOMECREDIT':
//                $emails = ['card_operations@homecredit.vn'];
//                break;
//            case 'MB':
//                $emails = ['tragop@mbbank.com.vn'];
//                break;
//            case 'BIDV':
//                $emails = ['tragop@bidv.com.vn'];
//                break;
//            case 'OCB':
//                $emails = ['ttt.dvkh@ocb.com'];
//                break;
////            case 'VCBHT':
////                $emails = ['khanhlt.hat@vietcombank.com.vn'];
////                break;
//            case 'VCBSGD':
//                $emails = [' phathanh@vietcombank.com.vn','ttlanh.ho@vietcombank.com.vn','mylh.ho@vietcombank.com.vn','dieppq2.ho@vietcombank.com.vn'];
//                break;
//            case 'SCB':
//                $emails = ['tragop@scb.com.vn'];
//                break;
//            case 'SHB':
//                $emails = ['P_QLKenhThanhToanThe_TrungTamThe@shb.com.vn'];
//                break;
//            case 'TPB':
//                $emails = ['tragop@tpb.com.vn'];
//                break;
//            case 'SC':
//                $emails = ['ipp.scvn@sc.com'];
//                break;
//            case 'SEA':
//                $emails = ['contact@seabank.com.vn'];
//                break;
//            case 'CTB':
//                $emails = ['lam.thi.mai.ngo@citi.com'];
//                break;
//            case 'STB':
//                $emails = ['tragop@sacombank.com'];
//                break;
//            case 'VIB':
//                $emails = ['rb.card.merchant@vib.com.vn'];
//                break;
//            case 'MSB':
//                $emails = ['ngantt4@msb.com.vn'];
//                break;
//            case 'SHINHAN':
//                $emails = ['nguyenbichngoc@shinhan.com'];
//                break;
//            case 'TCB':
//                $emails = ['thetindung@techcombank.com.vn'];
//                break;
//            case 'ACB':
//                $emails = ['tragopthetindung@acb.com.vn'];
//                break;
//            case 'ICB':
//                $emails = ['HTNVICC@vietinbank.vn'];
//                break;
//            case 'VPB':
//                $emails = ['dichvutragop@vpbank.com.vn'];
//                break;
//            case 'HSBC':
//                $emails = ['card.installment.vnm@hsbc.com.vn'];
//                break;
//            case 'EXB':
//                $emails = ['nhung.pt@eximbank.com.vn'];
//                break;
//            case 'KLB':
//                $emails = ['hotrothe@kienlongbank.com'];
//                break;
////            case 'LPB':
////                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
////                break;
////            case 'FE':
////                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
////                break;
////            case 'NAB':
////                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
////                break;
//        }
//        return $emails;
        return [
            'lylk@nganluong.vn',
//            'luongdt@nganluong.vn',
//            'quangnt@nganluong.vn',
            'kienpt@nganluong.vn'
//            'linhhv@peacesoft.net',
        ];
    }


    private function CCToSend($bank_code)
    {
        $emails = [];


        switch ($bank_code) {
            case 'HDB':
                $emails = ['thoaptk3@hdbank.com.vn', 'support@alepay.vn'];
                break;
            case 'PVCOMBANK':
                $emails = ['tuointh@pvcombank.com.vn', 'hangdtt1@pvcombank.com.vn', 'support@alepay.vn'];
                break;
            case 'GDB':
                $emails = ['hoangnt3@vietcapitalbank.com.vn' . 'support@alepay.vn'];
                break;
            case 'HOMECREDIT':
                $emails = ['loan.trantt@homecredit.vn', 'support@alepay.vn'];
                break;
            case 'MB':
                $emails = ['support@alepay.vn'];
                break;
            case 'BIDV':
                $emails = ['support@alepay.vn'];
                break;
            case 'OCB':
                $emails = ['hongpth@ocb.com.vn', 'huongntd2@ocb.com.vn', 'support@alepay.vn'];
                break;
            case 'VCBSGD':
                $emails = ['dongdt.sgd@vietcombank.com.vn', 'support@alepay.vn'];
                break;
            case 'SCB':
                $emails = ['support@alepay.vn'];
                break;
            case 'SHB':
                $emails = ['trang.vh@shb.com.vn', 'support@alepay.vn'];
                break;
            case 'TPB':
                $emails = ['Phuongptt2@tpb.com.vn', 'support@alepay.vn'];
                break;
            case 'SC':
                $emails = ['support@alepay.vn'];
                break;
            case 'SEA':
                $emails = ['support@alepay.vn'];
                break;
            case 'CTB':
                $emails = ['hai.truong.nguyen@citi.com', 'paylite@citi.com', 'support@alepay.vn'];
                break;
            case 'STB':
                $emails = ['support@alepay.vn', 'doisoat@alepay.vn'];
                break;
            case 'VIB':
                $emails = ['giau.nguyenvan@vib.com.vn', 'support@alepay.vn'];
                break;
            case 'MSB':
                $emails = ['support@alepay.vn'];
                break;
            case 'SHINHAN':
                $emails = ['nguyenthanhhuyendata@gmail.com', 'support@alepay.vn'];
                break;
            case 'TCB':
                $emails = ['hangttt8@techcombank.com.vn', 'huongnt76@techcombank.com.vn', 'trangnt10@techcombank.com.vn', 'quyendl3@techcombank.com.vn', 'haont4@techcombank.com.vn', 'support@alepay.vn'];
                break;
            case 'ACB':
                $emails = ['support@alepay.vn'];
                break;
            case 'ICB':
                $emails = ['support@alepay.vn'];
                break;
            case 'VPB':
                $emails = ['haind@nganluong.vn', 'phuongttl4@vpbank.com.vn', 'trangntt1@vpbank.com.vn', 'support@alepay.vn'];
                break;
            case 'HSBC':
                $emails = ['support@alepay.vn'];
                break;
            case 'EXB':
                $emails = ['hoa.ntn@eximbank.com.vn', 'hien.ltt@eximbank.com.vn', 'support@alepay.vn'];
                break;
            case 'KLB':
                $emails = ['haonx@kienlongbank.com', 'support@alepay.vn'];
                break;
//            case 'LPB':
//                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
//                break;
//            case 'FE':
//                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
//                break;
//            case 'NAB':
//                $emails = ['tungnd@peacesoft.net', 'bennt@peacesoft.net'];
//                break;
        }
//        return $emails;
        return [];
    }

    private function setMassBlankValue($sheet, array $fields)
    {
        foreach ($fields as $cells) {
            foreach ($cells as $cell) {
                if ($cell != null) {
                    $sheet->setCellValue($cell, '');
                }
            }

        }
    }

    private function searchInSheet($object, $value)
    {
        $foundInCells = [];
        foreach ($object->getWorksheetIterator() as $worksheet) {
            $ws = $worksheet->getTitle();
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() == $value) {
                        $foundInCells[] = $cell->getCoordinate();
                    }
                }
            }
        }
        return $foundInCells;
    }

    private function replaceInSheet($spreadsheet, $data, $string)
    {
        $matched = $this->searchInSheet($spreadsheet, $string);
        $data_record = str_replace(['{', '}'], '', strtolower($string));
//        $installment_info = json_decode($data['installment_info'],true);
//        foreach ($installment_info as $value)
//        {
//            $installment[] = $value;
//        }
//        $card_number = str_replace(' ','',$installment[1]);
        $card_info_name = $data['installment_info']['card_name'];
        $card_number = str_replace(' ','',$data['installment_info']['number']);
        if ($matched != []) {
            foreach ($matched as $key => $cell) {
                $sheet = $spreadsheet->getActiveSheet();
                if ($string == '{CARD_NUMBER}') {
                    $sheet->setCellValue($cell, substr($card_number,0,6) . '-XXXXXX-' . substr($card_number,-4));
                } elseif ($string == '{OTP}') {
                    $data_record = 'OTP';
                } elseif ($string == '{CARD_FIRST_6_DIGITS}') {
                    $data_record = 'OTP';
                    $sheet->setCellValue($cell, substr($card_number, 0, 6));
                } elseif ($string == '{CARD_LAST_4_DIGITS}') {
                    $data_record = 'OTP';
                    $sheet->setCellValue($cell, substr($card_number,-4));
                } elseif ($string == '{SERIAL}') {
                    $sheet->setCellValue($cell, $key + 1);
                } elseif ($string == '{REFERENCE_NUMBER}') {
                    $sheet->setCellValue($cell, $data['bank_refer_code']);
                } elseif ($string == '{ORDER_ID}') {
                    $sheet->setCellValue($cell, $data['order_code']);
                } elseif ($string == '{TIME}') {
                    $date = date('d-m-Y', strtotime('-1 days', time()));
//                    $time_str = "Từ 16h ngày $start_time đến 16h ngày $end_time";
                    $sheet->setCellValue($cell, $date);
                } elseif ($string == '{TIME_NOW}') {
                    $date = date('d-m-Y', time());
                    $sheet->setCellValue($cell, $date);
                } elseif ($string == '{MERCHANT_NAME}') {
                    $merchant_name = Merchant::find()->where(['id' => $data['merchant_id']])->one()['name'];
                    $sheet->setCellValue($cell, $merchant_name);
                } elseif ($string == '{BANK_NAME}') {
                    $bank_name = $data['name'];
                    $sheet->setCellValue($cell, $bank_name);
                } elseif ($string == '{CURRENCY}') {
                    $sheet->setCellValue($cell, 'VNĐ');
                } elseif ($string == '{PAYMENT_DATE}') {
                    $payment_date = date("d-m-Y H:i:s", $data['time_paid']);
                    $sheet->setCellValue($cell, $payment_date);
                } elseif ($string == '{PAYMENT_DATE_FORMAT_DD_MM_YYYY}') {
                    $date = date("d-m-Y", $data['time_paid']);
                    $sheet->setCellValue($cell, $date);
                } elseif ($string == '{CREATED_AT_FORMAT_DD_MM_YYYY}') {
                    $date = date("d-m-Y", strtotime($data['time_created']));
                    $sheet->setCellValue($cell, $date);
                } elseif ($string == '{CURRENT_TIME}') {
                    $date = date("d-m-Y", time());
                    $prefix = "Ngày ";
                    $sheet->setCellValue($cell, $prefix . $date);
                } elseif ($string == '{CURRENT_TIME_VP}') {
                    $prefix = "Công ty Cổ phần Cổng Trung gian thanh toán Ngân Lượng gửi Quý Ngân hàng Báo cáo thống kê số lượng Khách hàng đăng ký tham gia Chương trình trả góp cho giao dịch bằng Thẻ tín dụng ngày ";
//                    $date = date("d-m-Y", strtotime('-1 days', time()));
                    $after = ", cụ thể như sau:";
                    $date = date("d-m-Y", time());
                    $sheet->setCellValue($cell, $prefix . $date . $after);
                } elseif ($string == '{CURRENT_TIME_AFTER_VP}') {
                    $prefix = "DANH SÁCH KHÁCH HÀNG ĐĂNG KÝ TRẢ GÓP CHO GIAO DỊCH THẺ TÍN DỤNG NGÀY ";
//                    $date = date("d-m-Y", strtotime('-1 days', time()));
                    $date = date("d-m-Y", time());
                    $sheet->setCellValue($cell, $prefix . $date);
                } elseif ($string == '{PREFIX_CURRENT_TIME}') {
                    $prefix = "Ngày ";
//                    $date = date("d-m-Y", strtotime('-1 days', time()));
                    $date = date("d-m-Y", time());
                    $sheet->setCellValue($cell, $prefix . $date);
                } elseif ($string == '{GDB_PREFIX_CURRENT_TIME}') {
                    $prefix = "CTCP cổng TGTT Ngân Lượng kính gửi Quý Ngân hàng báo cáo thống kê số lượng Khách hàng đăng ký  tham gia Chương trình  trả góp lãi suất 0%  qua thẻ tín dụng ngày ";
                    $date = date("d-m-Y", strtotime('-1 days', time()));
                    $sheet->setCellValue($cell, $prefix . $date);
                } elseif ($string == '{INSTALLMENT_PERCENT}') {
//                    $date = date("d-m-Y", time());
                    $sheet->setCellValue($cell, "100%");
                } elseif ($string == '{PARTNER_NAME_DEFAULT}') {
//                    $date = date("d-m-Y", time());
                    $sheet->setCellValue($cell, "CTCP cổng TGTT Ngân Lượng");
                } elseif ($string == '{MERCHANT_ID}') {
                    $sheet->setCellValue($cell, $data['merchant_id']);
                }else if ($string == '{CARD_OWNER_FULLNAME}'){
                    $sheet->setCellValue($cell, strtoupper($card_info_name));
                }else if ($string == '{INSTALLMENT_PERIOD}'){
                    $sheet->setCellValue($cell, $data['installment_cycle']);
                }else if ($string == '{AMOUNT}') {
                    $sheet->setCellValue($cell, $data['cashin_amount']);
                }else if($string == '{PAYER_EMAIL}')
                {
                    $sheet->setCellValue($cell, $data['buyer_email']);
                }else if($string == '{PAYER_PHONE}')
                {
                    $sheet->setCellValue($cell, $data['buyer_mobile']);
                }else {
                    $sheet->setCellValue($cell, '');
                }
                $next_row = (int)(filter_var($cell, FILTER_SANITIZE_NUMBER_INT)) + 1;
                $column = preg_replace("/[^A-Z]+/", "", $cell);
                $matched_string = ['{CURRENCY}', '{MERCHANT_NAME}', '{CARD_NUMBER}', '{BANK_NAME}', '{SERIAL}', '{ORDER_ID}', '{PAYMENT_DATE_FORMAT_DD_MM_YYYY}',
                    '{INSTALLMENT_PERCENT}', '{CREATED_AT_FORMAT_DD_MM_YYYY}', '{CARD_OWNER_FULLNAME}','{TIME}','{PAYMENT_DATE}','{GDB_PREFIX_CURRENT_TIME}',
                    '{PARTNER_NAME_DEFAULT}','{MERCHANT_ID}','{INSTALLMENT_PERIOD}','{AMOUNT}','{TIME_NOW}','{PAYER_PHONE}','{CARD_LAST_4_DIGITS}','{CARD_FIRST_6_DIGITS}','{REFERENCE_NUMBER}'];

                if (in_array($string, $matched_string) || (isset($data->$data_record) && $data->$data_record != null)) {
                    $sheet->setCellValue($column . $next_row, $string);
                }
            }
        }
    }
}