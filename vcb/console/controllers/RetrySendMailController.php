<?php

namespace console\controllers;

use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Translate;
use common\models\business\MerchantConfigBusiness;
use common\models\business\PaymentMethodBusiness;
use common\models\business\SendMailBussiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderEmail;
use common\models\db\Merchant;
use common\models\db\Transaction;
use Yii;
use yii\console\Controller;

class RetrySendMailController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function actionGetCheckoutOrder()
    {
        $get_merchant_enable = MerchantConfigBusiness::getMerchantEnableByKey('RETRY_SEND_MAIL_CHECKOUT_ORDER');
        if ($get_merchant_enable) {
            $merchant_infos = Tables::selectAllDataTable('merchant', "id IN (" . implode(', ', $get_merchant_enable) . ")", 'id desc', 'id');


            $mail_send_fail = Tables::getAllDataForQuery("SELECT coe.checkout_order_id, coe.email_send as email
                FROM checkout_order_email AS coe
                         INNER JOIN checkout_order AS co ON coe.checkout_order_id = co.id
                WHERE co.merchant_id IN (" . implode(', ', $get_merchant_enable) . ")
                  AND coe.status IN (1, 3)
                  AND coe.time_created >= " . intval(time() - 90000) . "
                  AND coe.checkout_order_id IN (
                      SELECT checkout_order_id
                      FROM checkout_order_email
                      GROUP BY checkout_order_id
                      HAVING COUNT(checkout_order_id) < 3
                  );");
            if ($mail_send_fail) {
                foreach ($mail_send_fail as $item) {
                    $checkout_order_info = CheckoutOrder::findOne($item['checkout_order_id']);
                    if ($checkout_order_info) {
                        $merchant = $merchant_infos[$checkout_order_info->merchant_id];
                        $transaction_info = Transaction::findOne($checkout_order_info->transaction_id);

                        if ($merchant['is_sent']) {
                            if (trim($merchant['mail_sent']) != '') {
                                $email_cc = explode(',', $merchant['mail_sent']);
                            } else {
                                $email_cc = [];
                            }

                            $check_order_email = new CheckoutOrderEmail();
                            $check_order_email->checkout_order_id = $checkout_order_info->id;
                            $check_order_email->email_send = $item['email'];
                            $data_log = $this->processRetrySendMail($check_order_email, $checkout_order_info, $transaction_info, $email_cc);
                        } else {
                            $data_log = [
                                'token_code' => $checkout_order_info->token_code,
                                'status' => 'FAIL - NOT SEND',
                            ];
                            self::_writeLog($data_log);
                        }

                    } else {
                        $data_log = [
                            'token_code' => "",
                            'status' => 'FAIL - CHECKOUT NOT FOUND',
                        ];
                        self::_writeLog($data_log);
                    }
                }
            } else {
                $data_log = [
                    'token_code' => "",
                    'status' => 'FAIL - EMPTY',
                ];
                self::_writeLog($data_log);
            }
        } else {
            $data_log = [
                'token_code' => "",
                'status' => 'FAIL - EMPTY MC ENABLE',
            ];
            self::_writeLog($data_log);
        }
    }

    public function actionScanCloseBrowser()
    {
        $get_merchant_enable = MerchantConfigBusiness::getMerchantEnableByKey('RETRY_SEND_MAIL_CHECKOUT_ORDER');
        if ($get_merchant_enable) {
            $merchant_infos = Tables::selectAllDataTable('merchant', "id IN (" . implode(', ', $get_merchant_enable) . ")", 'id desc', 'id');

            $mail_fail = Tables::getAllDataForQuery("SELECT
                co.id 
                FROM
                checkout_order_email coe
                RIGHT JOIN checkout_order co ON coe.checkout_order_id = co.id
                WHERE coe.checkout_order_id IS NULL
                AND co.merchant_id IN (" . implode(', ', $get_merchant_enable) . ")
                AND co.time_paid >= " . intval(time() - 900)); // 15 phút

            if ($mail_fail) {
                foreach ($mail_fail as $item) {
                    $checkout_order_info = CheckoutOrder::findOne($item['id']);
                    if ($checkout_order_info) {
                        $merchant = $merchant_infos[$checkout_order_info->merchant_id];
                        $transaction_info = Transaction::findOne($checkout_order_info->transaction_id);
                        if ($merchant['is_sent']) {
                            $email = Tables::selectOneDataTable("user_login", ["merchant_id = :merchant_id", "merchant_id" => $checkout_order_info->merchant_id])['email'];
                            if (trim($merchant['mail_sent']) != '') {
                                $email_cc = explode(',', $merchant['mail_sent']);
                            } else {
                                $email_cc = [];
                            }

                            $check_order_email = new CheckoutOrderEmail();
                            $check_order_email->checkout_order_id = $checkout_order_info->id;
                            $check_order_email->email_send = trim($email);
                            $data_log = $this->processRetrySendMail($check_order_email, $checkout_order_info, $transaction_info, $email_cc);
                            self::_writeLog($data_log);
                        } else {
                            $data_log = [
                                'token_code' => $checkout_order_info->token_code,
                                'status' => 'FAIL - NOT SEND',
                            ];
                            self::_writeLog($data_log);
                        }

                    }
                }
            }
        }
    }


    private static function _writeLog($data)
    {
        $path_process = 'console' . DS . 'mail';
        $path_info = pathinfo($path_process . DS . date('Ymd') . '.txt');
        Logs::create($path_info['dirname'], $path_info['basename'], json_encode($data));
    }

    /**
     * @param CheckoutOrderEmail $check_order_email
     * @param CheckoutOrder $checkout_order_info
     * @param Transaction|null $transaction_info
     * @param $email_cc
     * @return array
     */
    public function processRetrySendMail(CheckoutOrderEmail $check_order_email, CheckoutOrder $checkout_order_info, ?Transaction $transaction_info, $email_cc): array
    {
        $check_order_email->time_created = time();
        $check_order_email->time_updated = time();
        $check_order_email->time_process = time();
        $check_order_email->status = CheckoutOrderEmail::STATUS_NEW;
        if ($check_order_email->save()) {
            $send_mail = SendMailBussiness::sendSuccess(
                $check_order_email->email_send,
                'Thông báo giao dịch thành công - Success Transaction Notification',
                'noti_success',
                [
                    'order_description' => $checkout_order_info->order_description,
                    'order_code' => $checkout_order_info->order_code,
                    'time_paid' => time(),
                    'payment_name' => $checkout_order_info->buyer_fullname,
                    'payment_method' => !empty(PaymentMethodBusiness::getPaymentMethodName($transaction_info->payment_method_id)) ? PaymentMethodBusiness::getPaymentMethodName($transaction_info->payment_method_id) : '',
                    'amount' => $checkout_order_info->cashin_amount,
                    'currency' => $checkout_order_info->currency,
                    'transaction_id' => $checkout_order_info->transaction_id,
                    'email' => $checkout_order_info->buyer_email,
                    'address' => $checkout_order_info->buyer_address,

                ], 'layouts/basic', $email_cc
            );
            if ($send_mail) {
                $check_order_email->status = CheckoutOrderEmail::STATUS_SUCCESS;
            } else {
                $check_order_email->status = CheckoutOrderEmail::STATUS_ERROR;
            }
            if ($check_order_email->save()) {
                $data_log = [
                    'token_code' => $checkout_order_info->token_code,
                    'status' => 'SUCCESS',
                ];
            } else {
                $data_log = [
                    'token_code' => $checkout_order_info->token_code,
                    'status' => 'FAIL - SAVE',
                    'message' => json_encode($check_order_email->getErrors()),
                ];
            }
        } else {
            $data_log = [
                'token_code' => $checkout_order_info->token_code,
                'status' => 'FAIL - CREATE RC',
            ];
        };
        return $data_log;
    }
}