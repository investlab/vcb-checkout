<?php

namespace checkout\controllers;

use common\components\libs\ExportData;
use common\components\libs\ExportDataV2;
use common\components\libs\qrcode\QrCode;
use common\components\libs\NotifySystem;
use common\components\libs\Weblib;
use common\components\utils\Logs;
use common\models\business\CardDeclineBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\LinkCardBusiness;
use common\models\business\ReceiptBussiness;
use common\models\business\SendMailBussiness;
use common\models\business\TransactionBusiness;
use common\models\db\BinAccept;
use common\models\db\BinAcceptV2;
use common\models\db\CheckoutOrderEmail;
use common\models\db\InstallmentConfig;
use common\models\db\InstallmentExcludedDate;
use common\models\db\InstallmentExcludedDateSearch;
use common\models\db\Merchant;
use common\models\db\MerchantFee;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentFee;
use common\models\db\PartnerPaymentMethod;
use common\models\db\TransactionType;
use common\models\db\Zone;
use checkout\components\MerchantCheckoutController;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;
use common\models\form\PdfDownloadForm;
use common\models\input\CheckoutOrderSearch;
use common\partner_payments\PartnerPaymentCyberSourceVcb3ds2;
use common\payment_methods\cyber_source_vcb_3ds2\PaymentMethodCreditCardCyberSourceVcb3ds2MultipleCreditCardForm;
use common\payments\CyberSourceVcb3ds2;
use common\util\Helpers;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use common\components\utils\Strings;
use common\components\libs\Tables;
use common\models\db\Method;
use Yii;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\PaymentMethod;
use common\models\business\PaymentMethodBusiness;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\Transaction;
use common\models\db\CheckoutOrderCallback;
use common\components\utils\Translate;

class Version_1_0Controller extends MerchantCheckoutController
{

    public $layout = 'version_1_1';
    public $paymentMethod = null;
    public $paymentMethodCode = null;
    public static $PDF_URL = ROOT_URL . DS . 'data' . DS . 'pdf' . DS;
    public static $PDF_PATH = ROOT_PATH . DS . 'data' . DS . 'pdf' . DS;
    private static $USER_NAME_CKS = 'nganluong.dev@peacesoft.net';
    private static $PASSWORD_CKS = '5Ti8s76wVqbhs7V@';
    private static $CLIENT_ID = '4b0c-637260218657289133.apps.signserviceapi.com';
    private static $CLIENT_SECRET = 'YzQwYWZkZTc-MWNlYi00YjBj';
    private static $ACCESS_TOKEN_URL = 'https://gateway.vnpt-ca.vn/signservice/v4/oauth/token';
    private static $API_CKS_URL = 'https://gateway.vnpt-ca.vn/signservice/v4/api_gateway';


    public static $access_token_cks = '';
    public static $refresh_token_cks = '';
    public static $RequestID = '';


    public function actionCancel()
    {
        // if ($this->checkout_order['status'] == CheckoutOrder::STATUS_NEW || $this->checkout_order['status'] == CheckoutOrder::STATUS_PAYING) {
//            $inputs = array(
//                'checkout_order_id' => $this->checkout_order['id'],
//                'reason_id' => 0,
//                'reason' => 'Người mua tự hủy đơn hàng',
//                'user_id' => 0,
//            );
//             CheckoutOrderBusiness::cancelRequestPayment($inputs);
        //       }
        if ($this->checkout_order['cancel_url'] != '') {
            header('Location:' . $this->checkout_order['cancel_url']);
            die();
        } else {
            return $this->render('cancel', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
            ));
        }
    }

//    public function actionCancel($token_code = '')
//    {
//        $checkStatusCheckout = CheckoutOrder::findOne(['token_code' => $token_code]);
//
//        if ($checkStatusCheckout && $checkStatusCheckout['status'] == CheckoutOrder::STATUS_PAYING) {
//            $checkStatusCheckout->status = CheckoutOrder::STATUS_NEW;
//            if ($checkStatusCheckout->save(false)) {
//                return $this->redirect(['version_1_0/index/' . $token_code]);
//            }
//        }else{
//            return $this->redirect($checkStatusCheckout['cancel_url']);
//            exit();
//        }
//        return $this->render('cancel', array(
//            'checkout_order' => $this->checkout_order,
//            'transaction' => $this->transaction,
//        ));
//    }

//    public function actionTransactionDestroy($token_code = '') {
//        $checkStatusCheckout = CheckoutOrder::findOne(['token_code' => $token_code, 'status' => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
//        if ($checkStatusCheckout) {
//            $checkStatusCheckout->status = CheckoutOrder::STATUS_CANCEL;
//            if ($checkStatusCheckout->save(false)) {
//                if ($this->checkout_order['notify_url'] != '') {
//                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
//                    // print_r($checkout_order_callback_info); exit();
//                    if ($checkout_order_callback_info != false) {
//                        CheckoutOrderCallback::process($checkout_order_callback_info);
//                    }
//                }
//                return $this->redirect($checkStatusCheckout['cancel_url']);
//            }
//        }
//    }

    public function actionTransactionDestroy($token_code = '', $type = '')
    {
        $this->checkout_order['cancel_url'] = str_replace('payer_cancel', 'user_cancel', $this->checkout_order['cancel_url']);
        if (in_array($this->checkout_order['status'], [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING])) {
            $checkStatusCheckout = CheckoutOrder::findOne(['token_code' => $token_code, 'status' => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
            $checkStatusCheckout->status = CheckoutOrder::STATUS_CANCEL;
            if ($checkStatusCheckout->save(false)) {
                if ($this->checkout_order['notify_url'] != '') {
                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
                    if ($checkout_order_callback_info) {
                        CheckoutOrderCallback::process($checkout_order_callback_info);
                    } else {
//                        @NotifySystem::send("Có đơn hàng thất bại chưa được thêm checkout_order_callback ID: " . $this->checkout_order['id']);
                    }
                }
                return $this->redirect($this->checkout_order['cancel_url']);
            }
        } else {
            return $this->redirect($this->checkout_order['cancel_url']);
        }
    }


    public function actionTransactionDestroyV2($token_code = '', $type = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $token_code = $_POST['token_code'];
        $checkout_order = CheckoutOrder::findOne([
            'token_code' => $token_code,
            'status' => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING, CheckoutOrder::STATUS_CANCEL, CheckoutOrder::STATUS_FAILURE]
        ]);
        if (!is_null($checkout_order)) {
            $message = "Giao dịch QR hết hạn";
            $params = [
                'checkout_order_id' => $checkout_order->id,
                'reason_id' => '50002',
                'reason' => $message,
                'user_id' => 0
            ];
            $cancel_checkout_order = CheckoutOrderBusiness::cancelRequestPaymentV2($params);
            if ($cancel_checkout_order['error_message'] == '') {
                // DONE HUY DON HANG
                return ['result' => true];

            } else {
                // HUY DON HANG THAT BAI
                return ['result' => false];

            }
        }
        return ['result' => false];

    }

    public function actionNotify()
    {
        header('Location:' . $this->checkout_order['notify_url']);
        die();
    }

    public static function notCallbackMerchantArr(){
        return [877];
    }

    public function actionSuccess()
    {

        // Get số thẻ
        if (!is_null($this->transaction['card_info'])) {
            if (!is_null(json_decode($this->transaction['card_info'])->card_number)) {
                $this->checkout_order['card_number'] = json_decode($this->transaction['card_info'])->card_number;
            } else {
                $this->checkout_order['card_number'] = '';
            }
        } else {
            $this->checkout_order['card_number'] = '';
        }

        $this->checkout_order['status'] = CheckoutOrder::findOne(['id' => $this->checkout_order['id']])->status;
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_PAID && $this->checkout_order['status'] != CheckoutOrder::STATUS_INSTALLMENT_WAIT && $this->checkout_order['status'] != CheckoutOrder::STATUS_REVIEW) {
            $this->redirectErrorPage('Order does not exist, access is denied');
        }
        if ($this->checkout_order['notify_url'] != '') {
//            *** CŨ ***
//            $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
            $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
//            var_dump( $this->checkout_order);die();
            $email = $this->checkout_order['user_info']['email'];
            // test!!!
//            $email = 'trongtin30899@gmail.com';// CMT KHI DAY LIVE !!!
            $email_cc = [];
            // print_r($checkout_order_callback_info); exit();
            if ($checkout_order_callback_info != false) {
                self::_writeLog('debug-0');

                if(!in_array(@$this->checkout_order['merchant_id'], self::notCallbackMerchantArr())){
                    self::_writeLog('debug-1');
                    CheckoutOrderCallback::process($checkout_order_callback_info);
                }

                if (isset($this->checkout_order['merchant_info']['is_sent']) && $this->checkout_order['merchant_info']['is_sent'] == 1) {
                    if (trim($this->checkout_order['merchant_info']['mail_sent']) != '') {
                        $email_cc = explode(',', $this->checkout_order['merchant_info']['mail_sent']);
                    } else {
                        $email_cc = [];
                    }

                    if (in_array($this->checkout_order['merchant_id'], $GLOBALS['MERCHANT_EMAIL_TEMPLATE_NEW'])) {
                        $template = 'noti_success_for_buyer_dvc';
                        SendMailBussiness::sendBCA(
                            trim($email),
                            'Payment Confirmation  for register code ' . $this->checkout_order['order_code'],
                            $template,
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'card_number' => $this->checkout_order['card_number'],
                                'order_code' => $this->checkout_order['order_code'],
                                'merchant_name' => $this->checkout_order['merchant_info']['name'],
                                'buyer_name' => $this->checkout_order['buyer_fullname'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],
                                'address_dvc' => Translate::getV1(@$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['area'] . ':' . @$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['address']),
                                'phone_number' => @$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['phone_number'],

                            ], 'layouts/basic', $email_cc
                        );
                    } elseif (in_array($this->checkout_order['merchant_id'], $GLOBALS['MERCHANT_XNC'])) {
//                        $template = 'noti_success_for_buyer_xnc';
                        $template = 'noti_success_for_buyer_xnc_v2';

                        SendMailBussiness::sendBCA(
                            trim($email),
                            'Payment Confirmation E-Visa for register code ' . $this->checkout_order['order_code'],
                            $template,
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'card_number' => $this->checkout_order['card_number'],
                                'order_code' => $this->checkout_order['order_code'],
                                'merchant_name' => $this->checkout_order['merchant_info']['name'],
                                'buyer_name' => $this->checkout_order['buyer_fullname'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],
//                                'receipt_url' => $this->checkout_order['receipt_url'],

                            ], 'layouts/basic', $email_cc
                        );
                    } else {
                        $check_email_send = CheckoutOrderEmail::findOne(['checkout_order_id' => $this->checkout_order['id']]);
                        if (empty($check_email_send)) {
                            $check_order_email = new CheckoutOrderEmail();
                            $check_order_email->checkout_order_id = $this->checkout_order['id'];
                            $check_order_email->email_send = $email;
                            $check_order_email->time_created = time();
                            $check_order_email->time_updated = time();
                            $check_order_email->time_process = time();
                            $check_order_email->status = CheckoutOrderEmail::STATUS_NEW;
                            $check_order_email->save();
                        }

                        $send_mail = SendMailBussiness::sendSuccess(
                            $email,
                            'Thông báo giao dịch thành công - Success Transaction Notification' . ' #' . $this->checkout_order['order_code'],
                            'noti_success',
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'order_code' => $this->checkout_order['order_code'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],

                            ], 'layouts/basic', $email_cc
                        );

                        $check_email_send = CheckoutOrderEmail::find()->where(['checkout_order_id' => $this->checkout_order['id']])->one();
                        if ($check_email_send) {
                            if ($send_mail) {
                                $check_email_send->status = CheckoutOrderEmail::STATUS_SUCCESS;
                            } else {
                                $check_email_send->status = CheckoutOrderEmail::STATUS_ERROR;
                            }
                            $check_email_send->save();
                        }

                    }
                }

                if ($this->checkout_order['merchant_info']['is_sent_mail_buyer']) {
                    $check_email_send = CheckoutOrderEmail::findOne(['checkout_order_id' => $this->checkout_order['id']]);
                    if (empty($check_email_send)) {
                        $check_order_email = new CheckoutOrderEmail();
                        $check_order_email->checkout_order_id = $this->checkout_order['id'];
                        $check_order_email->email_send = $this->checkout_order['buyer_email'];
                        $check_order_email->time_created = time();
                        $check_order_email->time_updated = time();
                        $check_order_email->time_process = time();
                        $check_order_email->status = CheckoutOrderEmail::STATUS_NEW;
                        $check_order_email->save();
                    }
                    $email = $this->checkout_order['buyer_email'];
                    $email_cc = [];


                    if (in_array($this->checkout_order['merchant_id'], $GLOBALS['MERCHANT_EMAIL_TEMPLATE_NEW'])) {
                        $template = 'noti_success_for_buyer_dvc';
                        $send = SendMailBussiness::sendSuccessBCA(
                            trim($email),
                            'Payment Confirmation for register code ' . $this->checkout_order['order_code'],
                            $template,
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'card_number' => $this->checkout_order['card_number'],
                                'order_code' => $this->checkout_order['order_code'],
                                'merchant_name' => $this->checkout_order['merchant_info']['name'],
                                'buyer_name' => $this->checkout_order['buyer_fullname'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],
                                'address_dvc' => Translate::getV1(@$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['area'] . ':' . @$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['address']),
                                'phone_number' => @$GLOBALS['BCA_ALL_CITIES'][$this->checkout_order['merchant_id']]['phone_number'],

                            ], 'layouts/basic', $email_cc
                        );
                    } elseif (in_array($this->checkout_order['merchant_id'], $GLOBALS['MERCHANT_XNC'])) {
//                        $template = 'noti_success_for_buyer_xnc';
                        $template = 'noti_success_for_buyer_xnc_v2';
                        $send = SendMailBussiness::sendSuccessBCA(
                            trim($email),
                            'Payment Confirmation E-Visa for register code ' . $this->checkout_order['order_code'],
                            $template,
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'card_number' => $this->checkout_order['card_number'],
                                'order_code' => $this->checkout_order['order_code'],
                                'merchant_name' => $this->checkout_order['merchant_info']['name'],
                                'buyer_name' => $this->checkout_order['buyer_fullname'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],
//                                    'receipt_url' => $this->checkout_order['receipt_url'],


                            ], 'layouts/basic', $email_cc
                        );
                    } else {
                        $template = 'noti_success_for_buyer';
                        $send = SendMailBussiness::sendSuccess(
                            trim($email),
                            'Thông báo giao dịch thành công',
                            $template,
                            [
                                'order_description' => $this->checkout_order['order_description'],
                                'card_number' => $this->checkout_order['card_number'],
                                'order_code' => $this->checkout_order['order_code'],
                                'merchant_name' => $this->checkout_order['merchant_info']['name'],
                                'buyer_name' => $this->checkout_order['buyer_fullname'],
                                'time_paid' => time(),
                                'payment_name' => $this->checkout_order['buyer_fullname'],
                                'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                                'amount' => $this->checkout_order['cashin_amount'],
                                'currency' => $this->checkout_order['currency'],
                                'transaction_id' => $this->checkout_order['transaction_id'],
                                'email' => $this->checkout_order['buyer_email'],
                                'address' => $this->checkout_order['buyer_address'],

                            ], 'layouts/basic', $email_cc
                        );
                    }
                    if ($send != false) {
                        $check_email_send = CheckoutOrderEmail::findOne(['checkout_order_id' => $this->checkout_order['id']]);
                        if (!empty($check_email_send)) {
                            $is_send = $check_email_send->toArray();
                            if ($is_send) {
//                                self::_writeLogDebug('[$check_order_email]:type : ' . gettype($check_order_email));
//                                self::_writeLogDebug('[$check_order_email]:isEmpty ' . empty($check_order_email) );
                                $check_email_send->time_updated = time();
                                $check_email_send->time_process = time();
                                $check_email_send->status = CheckoutOrderEmail::STATUS_SUCCESS;
                                $check_email_send->save();
                            }
                        }


                    }


                }
            }

//            *** MỚI ***
//            var_dump(123);die();


            else {
                $check_notify = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id ", "checkout_order_id" => $this->checkout_order['id']]);
                if (!$check_notify) {
                    @NotifySystem::send("Có đơn hàng thành công chưa được thêm checkout_order_callback ID: " . $this->checkout_order['id']);
                }
//                @NotifySystem::send("Có đơn hàng thành công chưa được thêm checkout_order_callback ID: " . $this->checkout_order['id']);
            }
        }

        return $this->render('success', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
        ));
    }

    public function actionSuccess1()
    {
        // Check nếu có receipt_url sẵn thì trả luôn url
        if (!is_null($this->checkout_order['receipt_url'])
            && $this->checkout_order['receipt_url'] != ''
            && !in_array($this->checkout_order['token_code'], ['14936497-CO6134B3917E']) // vá
        ) {
            return $this->checkout_order['receipt_url'];
        }

        $result_url = ReceiptBussiness::processMakeBillUrl($this->checkout_order, $this->checkout_order['order_code']);
        if ($result_url['error_message'] == 'Success') {
            $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $this->checkout_order['id']
                . " AND status = " . CheckoutOrder::STATUS_PAID)->one();
            if (!is_null($model)) {
                // Lưu trường receipt_url cho đơn hàng
                $model->updateAttributes(['receipt_url' => $result_url['url']]);
            }
            return $result_url['url'];
        } else {
            return 'Gen Link thất bại';
        }
//        die;


        // Get số thẻ

    }

    /** make-bill-not-sign */
    public function actionMakeBillNotSign()
    {
        // Check nếu có receipt_url sẵn thì trả luôn url
        if (!is_null($this->checkout_order['receipt_url']) && $this->checkout_order['receipt_url'] != '') {
            return $this->checkout_order['receipt_url'];
        }

        $result_url = ReceiptBussiness::processMakeBillNotSignUrl($this->checkout_order, $this->checkout_order['order_code']);
        if ($result_url['error_message'] == 'Success') {
            // Nếu khôg kí số thì ko lưu link
            return $result_url['url'];
        } else {
            return 'Gen Link thất bại';
        }
//        die;
    }

    public function call_receipt_curl($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Để nhận kết quả từ yêu cầu HTTP
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        var_dump($response);die();
        curl_close($curl);

        return $response;
    }

    public function getTokenArr($post, &$missingCode, &$time_begin)
    {
        if (isset($post['code_list']) && $post['code_list'] !== '') {
            $code_list = str_replace(' ', '', $post['code_list']);
            $code_arr = explode(',', $code_list);
            $description_arr = [];
            $timestamp_compare_arr = [];
            //=== KHAI BÁO CÁC KIỂU MÔ TẢ TẠI ĐÂY
            $description_prefix_v1 = "Thanh toan le phi cho ho so ";
            $description_prefix_v2 = "Thanh toan phi cho ho so ";
            $description_prefix_v3 = ""; // kieu cho Evisa

            foreach ($code_arr as $item) {
                /** $item: mã hồ sơ */
                $description_arr_v1[] = $description_prefix_v1 . $item;
                $description_arr_v2[] = $description_prefix_v2 . $item;
                $description_arr_v3[] = $description_prefix_v3 . $item;

                if ($item[0] == 'G') {
                    // get time
                    $remove_sign_result = explode('-', $item);
                    $time_str = $remove_sign_result[1];
                } elseif ($item[0] == 'E') {
                    //VD E240730CHNEM417728629
                    $time_str = substr($item, 1, 6); // Bắt đầu từ vị trí 1 (ký tự thứ 2), lấy 6 ký tự
                }
                // Tách chuỗi ngày thành ngày, tháng, năm
                $year = '20' . substr($time_str, 0, 2); // Nếu đơn từ năm 2000 - 2099!!!
                $month = substr($time_str, 2, 2);
                $day = substr($time_str, 4, 2); // Giả sử năm là từ 2000 trở đi

                // Tạo chuỗi ngày với định dạng 'dd-mm-yyyy'
                $formatted_date = $day . '-' . $month . '-' . $year;

                // Chuyển đổi ngày đã định dạng thành timestamp
                $timestamp = strtotime($formatted_date);
                $timestamp_compare_arr[] = $timestamp;
            }

//            var_dump(min($timestamp_compare_arr));die();
            //TODO Tìm timestamp nhỏ nhất trong mảng $timestamp_compare_arr -> $time_begin

            $time_begin = min($timestamp_compare_arr);
//            $time_begin = '1705701104';// test

            $existingDesc_v1 = $existingDesc_v2 = $existingDesc_v3 = [];
            //OLD
//            $checkout_orders_v1 = CheckoutOrder::getCheckoutOrderForGetReceiptsTool($description_arr_v1, $time_begin, $existingDesc_v1);
//            $checkout_orders_v2 = CheckoutOrder::getCheckoutOrderForGetReceiptsTool($description_arr_v2, $time_begin, $existingDesc_v2);
//            $checkout_orders_v3 = CheckoutOrder::getCheckoutOrderForGetReceiptsTool($description_arr_v3, $time_begin, $existingDesc_v3);
//            $checkout_orders = array_merge($checkout_orders_v1, $checkout_orders_v2, $checkout_orders_v3);

            //TOI UU
            $description_bca_arr = array_merge($description_arr_v1, $description_arr_v2);
//            var_dump($description_arr);die();
            $checkout_orders_bca = CheckoutOrder::getCheckoutOrderForGetReceiptsTool($description_bca_arr, $time_begin, $existingDesc_result);
            $checkout_orders_v3 = CheckoutOrder::getCheckoutOrderForGetReceiptsToolAsOrderCode($description_arr_v3, $time_begin, $existingDesc_v3);
//            var_dump($checkout_orders_v3);die();
            $checkout_orders = array_merge($checkout_orders_bca, $checkout_orders_v3);


//            var_dump($checkout_orders);die();
            $arr_token = $existingCode = [];
            foreach ($checkout_orders as $checkout_order) {
                /** @var $checkout_order CheckoutOrder */
//                var_dump(strpos($checkout_order->order_description, $description_prefix_v1)!== false);die;
                if (strpos($checkout_order->order_description, $description_prefix_v1) !== false) {
                    $arr_token += [str_replace($description_prefix_v1, '', $checkout_order->order_description) => $checkout_order->token_code];
                    $existingCode [] = str_replace($description_prefix_v1, '', $checkout_order->order_description);
                }

                if (strpos($checkout_order->order_description, $description_prefix_v2) !== false) {
                    $arr_token += [str_replace($description_prefix_v2, '', $checkout_order->order_description) => $checkout_order->token_code];
                    $existingCode [] = str_replace($description_prefix_v2, '', $checkout_order->order_description);
                }

                //NEW - MA E
//                if (preg_match('/^E\d{6}/', substr($checkout_order->order_description, 0, 7))) {
                if (preg_match('/^E\d{6}/', substr($checkout_order->order_code, 0, 7))) {
                    $arr_token += [str_replace($description_prefix_v3, '', $checkout_order->order_code) => $checkout_order->token_code];
//                    $existingCode [] = str_replace($description_prefix_v3, '', $checkout_order->order_description);
                    $existingCode [] = str_replace($description_prefix_v3, '', $checkout_order->order_code);
                }
            }
            $missingCode = array_diff($code_arr, $existingCode);
            return $arr_token;
        }
        return false;

    }

    public function getCheckoutOrdersFor3CTool($post, &$missingCode)
    {
        if (isset($post['code_list']) && $post['code_list'] !== '') {
            $code_list = str_replace(' ', '', $post['code_list']);
            $code_arr = explode(',', $code_list);
            $description_arr = [];
            $timestamp_compare_arr = [];
            //=== KHAI BÁO CÁC KIỂU MÔ TẢ TẠI ĐÂY
            $description_prefix_v1 = "Thanh toan le phi cho ho so ";
            $description_prefix_v2 = "Thanh toan phi cho ho so ";

            foreach ($code_arr as $item) {
                /** $item: mã hồ sơ */
                $description_arr_v1[] = $description_prefix_v1 . $item;
                $description_arr_v2[] = $description_prefix_v2 . $item;

                // get time
                $remove_sign_result = explode('-', $item);
                $time_str = $remove_sign_result[1];

                // Tách chuỗi ngày thành ngày, tháng, năm
                $year = '20' . substr($time_str, 0, 2); // Nếu đơn từ năm 2000 - 2099!!!
                $month = substr($time_str, 2, 2);
                $day = substr($time_str, 4, 2); // Giả sử năm là từ 2000 trở đi

                // Tạo chuỗi ngày với định dạng 'dd-mm-yyyy'
                $formatted_date = $day . '-' . $month . '-' . $year;

                // Chuyển đổi ngày đã định dạng thành timestamp
                $timestamp = strtotime($formatted_date);
                $timestamp_compare_arr[] = $timestamp;
            }

            //TODO Tìm timestamp nhỏ nhất trong mảng $timestamp_compare_arr -> $time_begin

            $time_begin = min($timestamp_compare_arr);
//            $time_begin = '1705701104';// test

            $existingDesc_v1 = $existingDesc_v2 = [];
            $checkout_orders_v1 = CheckoutOrder::getCheckoutOrderForExportReceiptsTool($description_arr_v1, $time_begin, $existingDesc_v1);
            $checkout_orders_v2 = CheckoutOrder::getCheckoutOrderForExportReceiptsTool($description_arr_v2, $time_begin, $existingDesc_v2);
            $checkout_orders = array_merge($checkout_orders_v1, $checkout_orders_v2);
            $arr_token = $existingCode = [];
            foreach ($checkout_orders as $checkout_order) {
                /** @var $checkout_order CheckoutOrder */
//                var_dump(strpos($checkout_order->order_description, $description_prefix_v1)!== false);die;
                if (strpos($checkout_order->order_description, $description_prefix_v1) !== false) {
//                    $arr_token += [str_replace($description_prefix_v1, '', $checkout_order->order_description) => $checkout_order->token_code];
                    $existingCode [] = str_replace($description_prefix_v1, '', $checkout_order->order_description);
                }
                if (strpos($checkout_order->order_description, $description_prefix_v2) !== false) {
//                    $arr_token += [str_replace($description_prefix_v2, '', $checkout_order->order_description) => $checkout_order->token_code];
                    $existingCode [] = str_replace($description_prefix_v2, '', $checkout_order->order_description);
                }
            }
            $missingCode = array_diff($code_arr, $existingCode);
            return $checkout_orders;
        }
        return false;

    }

    /** get-many-receipts */
    public function actionGetManyReceipts()
    {
        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if (isset($post['sign_type'])) {
                if (intval($post['sign_type']) == ReceiptBussiness::NOT_SIGN) {
                    $sign = false;
                } elseif (intval($post['sign_type']) == ReceiptBussiness::SIGN) {
                    $sign = true;
                } else {
                    return $this->render('get-many-receipts', [
                        'arr_links_as_token' => [],
                        'error' => 2
                    ]);
                }
            }
            /** sample
             * G01.839.108.000-240529-0122,G01.108.000.000-240604-00884,G01.835.108.000-240530-0101
             * G01.839.108.000-240529-0122
             * G01.108.000.000-240604-00884
             * G01.835.108.000-240530-0101
             */
            //TODO get token list từ danh sách mã hồ sơ

            $missingCode = [];
            $arr_token = self::getTokenArr($post, $missingCode, $time_begin);
            if (is_array($arr_token)) {
                // Code cũ
//            $token_list = str_replace(' ', '', $post['token_list']);
//            $arr_token = explode(',', $token_list);
                // end code cũ

                $arr_links_as_token = [];
                $link_list = [];
                $profile_code_list = [];
                foreach ($arr_token as $index => $token) {
                    $new_root_url = 'https://vcb-assets.nganluong.vn/'; // new root URL cho hoa don
                    if ($sign) {
                        $url = $new_root_url . 'vi/checkout/version_1_0/success1/' . $token; // KÍ
                    } else {
                        $url = $new_root_url . 'vi/checkout/version_1_0/make-bill-not-sign/' . $token; // KHÔNG KÍ
                    }
                    $arr_links_as_token[$index]['token_code'] = $token;
                    $arr_links_as_token[$index]['url'] = $url;
                    $arr_links_as_token[$index]['result_success1'] = self::call_receipt_curl($url);
//                    $arr_links_as_token[$index]['result_success1'] = 'www.google123.com'; // DEBUG
                    $arr_links_as_token[$index]['profile_code'] = $index;

                    $link_list [] = $arr_links_as_token[$index]['result_success1'];
                    $profile_code_list [] = $index;
                }
                return $this->render('get-many-receipts', [
                    'arr_links_as_token' => $arr_links_as_token,
                    'link_list' => $link_list,
                    'profile_code_list' => $profile_code_list,
                    'error' => 0,
                    'missingCode' => $missingCode
                ]);
            } else {
                return $this->render('get-many-receipts', [
                    'arr_links_as_token' => [],
                    'error' => 1,
                    'missingCode' => $missingCode
                ]);
            }


        }
        return $this->render('get-many-receipts', []);
    }

    /** action export-receipts */
    /** Xuất excel thông tin biên lai 3C */
    public function actionExportReceipts()
    {
//        $day_limit = 30;
        $day_limit = 50;
//        var_dump(Yii::$app->request->get());die();
        $get = Yii::$app->request->get();
        $token_code_arr = self::getTokenArr($get, $missingCode, $time_begin);

//        var_dump($missingCode);
//        var_dump($token_code_arr);
//        die;
        if (is_array($missingCode) && !empty($missingCode)) {
            // Trả lỗi về client
            echo json_encode([
                'error' => 'Không tìm thấy các hồ sơ: ' . implode(', ', $missingCode) . ' <br>Vui lòng bỏ các mã này khỏi danh sách và xuất excel lại!',
            ]);
            die();

        }

        if (is_array($token_code_arr) && !empty($token_code_arr)) {
            //TODO Export

            $input_extension = [
                'token_code_arr' => $token_code_arr,
                'time_begin' => $time_begin,
                'day_limit' => $day_limit
            ];

            $columns = array(
//                'token_code' => array('title' => Translate::get('Token code')),
//                'order_description' => array('title' => Translate::get('Mô tả đơn hàng')),
                'receipt_code' => ['title' => Translate::get('MaBienLai')], // la ten file!
                'receipt_date' => ['title' => Translate::get('NgayBienLai')],
                'buyer_fullname' => ['title' => Translate::get('NguoiNop')],
                'document' => ['title' => Translate::get('MST/CMND/HC')],
                'buyer_email' => ['title' => Translate::get('MailNguoiNop')],
                'buyer_address' => ['title' => Translate::get('DiaChiNguoiNop')],
                'decision_no' => ['title' => Translate::get('SoQuyetDinh')],
                'decision_date' => ['title' => Translate::get('NgayQuyetDinh')],
                'decision_people' => ['title' => Translate::get('NguoiQuyetDinh')],
                'receiver' => ['title' => Translate::get('DonViNhanTien')],
                'fee_name' => ['title' => Translate::get('TenLoaiLePhi')],
                'cashin_amount' => ['title' => Translate::get('TienThanhToan')],
                'amount_by_word' => ['title' => Translate::get('TienBangChu')],
            );
            //------------
            $search = new CheckoutOrderSearch();
            $params_search = [
                'token_code' => array_values($token_code_arr),
                'time_created_from' => $time_begin,
                'time_created_to' => $time_begin + 86400 * $day_limit, // 30 ngày
            ];
            $search->setAttributes($params_search);
            if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
                $file_name = "ORDER" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
            } else {
                $file_name = "ORDER" . date("d-m-Y-H-i-s") . ".xls";
            }
            //----------
            $obj = new ExportDataV2(200);
            if ($obj->init($file_name, $columns, Yii::$app->user->getId())) {
                $data = $search->searchForExportFor3CTool($obj->getOffset(), $obj->getLimit(), $input_extension);
                $result = $obj->process($data);
                $result['error'] = Translate::get($result['error']);

                echo json_encode($result);
            }

            die();
        }


    }

    public static function processMakeBillUrl($params, $file_name)
    {

        require_once ROOT_PATH . DS . 'common' . DS . 'components' . DS . 'libs' . DS . 'TCPDF' . DS . 'tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Ngan Luong');
        $pdf->SetTitle('');
        $pdf->SetSubject('');


        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 6));
        $pdf->SetMargins(10, 18, 15);

        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetFont('dejavusans', '', 8);

        $pdf->AddPage();
        $html_content = self::makeHTMLBill($params);

        $pdf->writeHTML($html_content, true, false, true, false, '');

        $pdf->LastPage();
        $file_path = self::$PDF_PATH . $file_name . '.pdf';
        $file_pdf_url = self::$PDF_URL . $file_name . '.pdf';
        $params = array(
            'token' => base64_encode($file_name),

        );
        $file_url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/view-bill', $params], HTTP_CODE);
//         $file_url = ROOT_URL . 'service' . DS . 'vpcp' . DS . 'viewbill?' . http_build_query($params);
        // http://ip-ss-donvi:8080/XrdAdapter/RestService/forward/service/vpcp/viewbill?token=MjAwNTI3MzAwMDE0&dstcode=VN:COM:0106001236:NganLuongPaymentSvc&providerurl=http://10.0.0.41:8083/


        $result = $pdf->Output($file_path, "F");


        if (!empty($result)) {
            $makeAccessToken = self::makeAccessToken();
            if ($makeAccessToken) {
                self::$access_token_cks = $makeAccessToken['access_token'];
                self::$refresh_token_cks = $makeAccessToken['refresh_token'];
                self::$RequestID = self::getGUID();
            }
            $result_cks = self::processMakeChuKiSo($file_name . '.pdf', $file_path, 3);
            self::_writeLogCKS('Process_make_cks' . json_encode($result_cks));
            if ($result_cks != false) {
                return array(
                    'error_message' => 'Success',
                    'url' => $file_url
                );
                self::_writeLogCKS('[' . $data['MaThamChieu'] . ']Update  is_signatured' . $bill['data'][0]['maGD'] . ']' . json_encode($update));
            }

        } else {
            return array(
                'error_message' => 'Có lỗi trong quá trình xuất file',
                'url' => ''
            );
        }
    }

    public function makeAccessToken()
    {
        $params = array(
            'client_id' => self::$CLIENT_ID,
            'client_secret' => self::$CLIENT_SECRET,
            'username' => self::$USER_NAME_CKS,
            'password' => self::$PASSWORD_CKS,
            'grant_type' => 'password'
        );

        self::_writeLogCKS('[URL]' . self::$ACCESS_TOKEN_URL);
        self::_writeLogCKS('[INPUT]' . json_encode($params));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$ACCESS_TOKEN_URL,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($params)
        ]);
        $response = curl_exec($curl);
        self::_writeLogCKS('[OUTPUT]' . json_encode($response));

        curl_close($curl);
        $response = json_decode($response);

        if (isset($response->error)) {
            return false;
        } else {

            return array(
                'access_token' => $response->access_token,
                'refresh_token' => $response->refresh_token,
            );
        }
    }

    public function makeHTMLBill($params)
    {
        $total_word = self::convertNumberToWords(intval($params['cashin_amount']));


        $html_content = '<table width="100%">
  <tr>
       <th align="center">
       <p><img src ="https://upload.nganluong.vn/public/css/nganluong/images/logoNL.png" width="126" height="30" ></p>
    </th>
    <th align="center">
    	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	Nội dung theo mẫu số 03c</p>
    	<p>	Ký hiệu: C1-10/NS</p>
    </th>
    
  </tr>
  
</table>
<h3 align="center">BIÊN LAI THU THUẾ, PHÍ, LỆ PHÍ VÀ THU PHẠT VI PHẠM HÀNH CHÍNH	</h3>
<p align="center">(Áp dụng đối với trường hợp in từ chương trình ứng dụng thu ngân sách nhà nước)</p>
<p align="center">(Liên số:  ……………. Lưu tại: ……………………………..)</p>
<table width="100%">
  <tr height ="100px">
    <th align="center" width="70%"></th>
    <th align="left" width="30%">
    	<p>	Số Sêri: ...........</p>
    	<p>	Số biên lai: ...........</p>
    </th>
    
  </tr>
  
  
</table>
<table  width="100%"> 
<tr>
      <th align="center"  width="15%">  
      </th>
      <th align="center"  width="75%">  
        <p  align="left">Thu phạt: &nbsp; ' . html_entity_decode('&#9744;', ENT_XHTML, "ISO-8859-1") . '&nbsp; </p>
        <p  align="left">Thu phí,lệ phí: &nbsp; ' . html_entity_decode('&#9745;', ENT_XHTML, "ISO-8859-1") . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tên phí lệ phí: Phí xuất nhập cảnh ' . '  </p>
        <p  align="left">Thu thuế: &nbsp; ' . html_entity_decode('&#9744;', ENT_XHTML, "ISO-8859-1") . '</p>
        
        <p align="left">Người nộp: ' . $params['buyer_fullname'] . ' &nbsp;&nbsp; MST/Số CMND/HC : ' . $params['order_code'] . '</p>
        <p align="left">Địa chỉ: ' . $params['buyer_address'] . '</p>
        <p align="left">Theo Quyết định/Thông báo số: ' . $params['order_code'] . ' &nbsp;&nbsp;&nbsp;&nbsp; Ngày:  ' . date('d-m-Y', $params['time_created']) . '</p>
        <p align="left">Của: Cục quản lý xuất nhập cảnh - Hà Nội</p>
        <p align="left">Đơn vị nhận tiền: Cục quản lý xuất nhập cảnh - Hà Nội </p>
      </th>
  </tr>
</table>
<br/>
<br/>
<table > 
<tr>
      <th align="left"  width="15%">  
      </th>
      <th align="left"  width="75%">  
        <table width="100%"  cellpadding="3" style=" border: 0.1px solid black;">
       <thead >
            <tr>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;font-weight: bold;width: 10%">STT</th>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;font-weight: bold;width:65%">Nội dung các khoản nộp NS/Mã định danh hồ sơ (ID)</th>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;width: 25%;font-weight: bold;">Số tiền</th>
   
            </tr>
        </thead>
        <tbody>
            <tr>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 10%;border: 0.1px solid black;padding: 15px;text-align: center">' . 1 . '</td>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 65%;border: 0.1px solid black;padding: 15px;">&nbsp;&nbsp;' . $params['order_description'] . '</td>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 25%;border: 0.1px solid black;padding: 15px;text-align: center">' . number_format($params['cashin_amount']) . ' VND</td>
            </tr>
            <tr>
                <td  style="height:15px;vertical-align:middle;padding: 15px;text-align: center;" colspan="2"><b>Tổng cộng</b></td>
                <td style="height:15px;vertical-align:middle;padding: 15px;text-align: center;">' . number_format($params['cashin_amount']) . ' VND </td>
            </tr>
        </tbody>
    </table>

      </th>
  </tr>
</table>
<div></div>
<table width="100%">
<tr>
<td width="15%"></td>
<td width="75%"><p align="left">Tổng số tiền ghi bằng chữ:    ' . $total_word . ' đồng</p></td>
</tr>

<tr>
<td width="15%"></td>
<td width="75%"><p align="left">Hình thức thanh toán: Thanh toán trực tuyến</p></td>
</tr>

</table>
<table width="100%">
  <tr>
    <th align="center" width="50%" style="margin-top: 15px">
        <p  style="margin: 0"></p>
         <b>Người nộp tiền</b>
        <p style="margin: 0"><i>(Ký, ghi họ tên)</i></p>
    </th>
     <th align="center" width="50%">
    	<p style="margin: 0"><i>Ngày ' . date('d') . ' tháng	' . date('m') . ' năm ' . date('Y') . '</i></p>
         <b>Người nhận tiền</b>
        <p style="margin: 0">(Ký, ghi họ tên)</p>
    </th>
    
  </tr>
</table>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<p align="center">Ghi chú: Chứng từ này sử dụng trong trường hợp thu phạt VPHC; thu phí, lệ phí vào tài khoản phí, lệ phí chờ nộp NS của tổ chức thu phí, lệ phí</p>

';

        return $html_content;
    }

    public function processMakeChuKiSo($file_name, $file_path, $maDV)
    {
        $file_name_log = 'data/logs/vpcp/uat/vnpt_cks/' . date("Ymd", time()) . ".txt";


        $fp = @fopen($file_path, "r");

        if (!$fp) {
            return false;
        } else {
            $html_content = fread($fp, filesize($file_path));
        }
        fclose($fp);
        if (intval($maDV) == 4) {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMzYsMjgzLDU1NiwzNjMiLA0KInBhZ2UiOiAxDQp9DQpd';
        } else if (intval($maDV) == 3) {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMjAsMTAwLDU0MCwxODAiLA0KInBhZ2UiOiAxDQp9DQpd';
        } else {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMTUsMjIxLDUzNSwzMDEiLA0KInBhZ2UiOiAxDQp9DQpd';
        }

        $serviceGroupID = self::getServiceGroupID();
        $CertID = self::getCertID();
        $data_sign = [
            'RequestID' => self::$RequestID,
            'ServiceID' => 'SignServer',
            'FunctionName' => 'SignPdfAdvance',
            'Parameter' => [
                'CertID' => $CertID,
                'ContentType' => 'application/pdf',
                'Type' => 'pdf',
                'ServiceGroupID' => $serviceGroupID,
                'FileName' => $file_name,
                'DataBase64' => base64_encode(($html_content)),
                'VisibleType' => 5,
                'FontSize' => 11,
                'Signatures' => $Signatures,
                'FontName' => 'Time',
                'Comment' => '',
                'FontStyle' => 1,
                'FontColor' => '#FF0303',
                'SignatureText' => 'Công ty CP cổng trung gian thanh toán Ngân Lượng Đã ký',
                'TextAlign' => 1,
                'Image' => 'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAPnUlEQVR4XuzXS2rcQBSF4SOpqqQqqfQoqeVGrfYzifMmYDAEDAGPDJlmCSGbyNQzryCQHWQBPUrINKMsqXORwVsIls4Hh17A/buajvb7PZaLYjxWxACIARADIAZADIAYADEAYgDEAIgBEAMgBkAMgBgAMQBiAMQAiAEQAyAGQAyAGAAxAFL4z66/R1igL7JKdvfr835BLwBtZDtb6m8APi7rJ4BuZH/zYG6KNoW4lNllBEBfM6d3/dCsuq6FTgx0llgAF/MOgKzsR1X72+PxDEN7gtocoDQdrE0hruYbAK1lv0PbfHp29BqrYkRlVihNi0IFlEU94wDojexP3/eX54dvUZl++tZ73SJXNZwu0fgO4r38C0rmFQC9k/0chuHw6eYVSt3C6wa5quCUR6YKZImDMx4mNQHAy5kFwOOPw7g6W7+4f+51AzsdPkeaOJmFiTOZRe4cxNU8AqCL++Nvw8n6+cNzb1UxHV4ODhUbqEgWp9Cxgc+rOQXA428323C6PofXAU5X03Mvh5+OnUQKKtJI4ulziqH2AeLD4w6Ansh2cvx/7F17qBzVGf+deez77t7XJje9aUyqNa3FokRCU1LsA8ViwUYUxaAoWq0FiY1VIkqsomhbCopFQWsVrYFSsdRKHxRSai2FQMAiCPaPlqZJvLmPvbt3X7MzO3P6nTOHmV427uy48WYvnW/yu2d2zmz2sr/vdc757pnS1o3bhcuXSV5Wz0tXb2gmNKYTNAUdukZgBnLpPEzTmKVEcFuiAOsTZUH+pplN5a0bz5fJnrR8It9U5BPRknTGNAkNQhkM2WdqaeRUHrA+FSCZ5HlzQ7l83qc2fQZ5mewVFflZ6NLyDYBpAFgA5nsBginvGcsXEwVYh0gRXp+amtx57ubP+m5fZfrCqg3NgC6tnoFBCJdgQBgGCCIXGCuUQLIrUYD1hR9OTI5fcZ4g31Dki5jP0pJUjQ4mDsk7DwHIa9QfJIVjGekBLqA8oJQowPrAzcVSYd+5m89Xll9EWsV8XUuBMV0CYOBAD8A0MOhBGDD0NPKFnA5g1/pQgGSi55lN05vVUK8o3X5KV9m+TPICt98DBqgwwAg6DE3ARD5XkGFg9CuCkoz/1zTLl91Y2oysWQyzfSbIV9QzBk7Hh4nsYUzlAgZ0QjFfxDzmdycKMNp4tVye3rJl47lE/phPvu4nfJrmZ/pcBn0PfYVx8Q8MUgl8BfATwZ1iYYhKxNxEAUYP945PFC/b9olPIyeHenKGT07rEoUAZ75v5+hr/UDYz6AR/AkhU88gnUkVOpYtQszRRAFGCzsJj27euCWI+WkZ8w3f7TNFPrg8BhYmcwHlBXTksjmQAuxKFGC0UCC8vHl2NjWeKyOrjyEtLT8NnZm+9YP1WH4UOOfqPYAKBshmM1hexo4kBxgtPEMVPdtny1t9ty8tPyWsVvHugWRg6rlkncOjw0UXHifAlUjLErFEAUYJe2h8fuPWWUr6dLmkG6zqyXl9waX84cUnn4jverYP128NU4OaEMpSIthOFODsYpLwzIbpDf56vpGVlbyaJJ9B2j7zpPPmgxBP4IJ87hFcdLkN27VgeS1CEx23Ddtri0ph3bHciwD8LVGAs4sfT01PzEyXZpDSs7KAQ1OW7w/0ONgAls8V8RySeAFp7Y7XIdJbaHcbaHVXJBbnKyDy5wDYSQg4u7gil8/cvHnTObJ8y2Qp6HQwzsC5dOCR5KuE0L9fvcf1HHS5A8ftSGu33CYRX8fcwnFUKPtzOu5PADxI7r92dhUgyfp/OjU57ZOvpVXMZ+DMt2IO3/VHx3pODREv4QirV5bfJrRQbSxhfukUVqqNdwDcRsQfTWYCzz4enpgszk6NlyX5mqbIB/fJl7N4g5BPhyS+Ky3f8WzYngWbiLcIxz/4DyqLyxVl8c+Ozl8HJws9+2Y2blLZvg6GkHwOSX4/6lW8D8gP4r3tWtLyF6unsFSpoN20XgBwH6EyOn8ensjTG2am9JQhlnX9hA+STpcartw+60O+598tyPcU+QQR720i/+TcSbL66gKAmwi/T/YHGC3cXijldk9NTQfjfA4uyeQe/MoeHpf8DsFCw1rBwsIi6tWmIP0WwhxiS6IAunLR7xMaH0Pi9+jEREkVcDKA+wmcJyl2Q/J76V9FvifJd3y3T1hcWsD83JItYj3hR4gtiQJMCnIINxpprdDteC6A1wi3nUFFeKA4kS+L6lxNWD5jcpKHqIQWxP3oMb6ryO+qbP/UqXlUl+rHAFxFeCfZIia+Ve4j3Ds2kS0VJ3MAg/ii9dpS67pW1dYBXHuGavr3T5ZLknwS3/IF6ZyIZV0wsIiEjyDI504Q90/NLaC+3Pqz+h0Xkj2C4lXb3ko4SMTPlCYKME1xiQEqG5+Y1uFY1Wscy90N4G0MJeT6y2MpQzcBMEVoT9yPHueruN9xLFQWqmjU2i8BuCPerF6iANcQHs+XMucVx/PIZDOqzIrAGDhX7pgBhVIWy1bjIIDLhyB/VypjXFeaLECIsmcAwuq9vuTLe/n/jvW7aFtt1Cp1NGvWIwAeSnYJizf+fjJbSF1KmThyBUG87pMvAAESBnlOh1SARrV9mdNxdwI48lFLuwvjWSAo4XThSX41MPWBPfSLfnhqYccNFKDZbIssH+26fTeAp5Jt4gbbHm5GuGAzrd9KVo+x8ZwinREQVNmAeUHMBSNwyHsK4xksn2oeBPANxJcr03lzd76YARj3yVfDOfH54KeP+6HrV4s7notW08LC8ZqthniHkn0Co4nPAthPODA2mSkUp3K+pQesh8MrBigygqtBEUaOyGtUrSvJC+yIWUalEx7NF9Phgg0YuMeBvnE/+L0kPEK71cHi8RUXwF7Ca8lGkdHk3yDj/Hh6CwGmaQQWz31rV6Qw5fLZ6UlQI/RcKYXafPsBAFfHKfTIFMyLCEHUl+xCkv/hJVyS/DD+d1o2kd+IIj9RAEX8dgAv0pe+KzeeQjojiVdl1CEBnCOMv6rrtHFYEZErptCsdvZ0be9CAO9iMDmYK5lAqEhwOQuUrZ/1g/tKYLe7knzl9n+RbBXbn/xLAbxZmEoX8uOpHuI5EGT30QV2q0uvufICKwvWwQHnBfak88aFqawB3/EzlVPIto/1h5/rOh6atQ7UZNQryV7B0fH+1eKGTCEzZgRxPCyiD40dHIMLD2/PlgxSAOxRmy29FzXrR9YfKJDvcmQT/VnqR6PSQbvu3A/gJURK4gHKusFmMwUjdPSB7Q4hvv4E5I1Np/X6YucBAHv7Zf6pnLHDzOmSyIF0jq9WgvoCkb/iPAvgiWS7+AFAxQ7H3C4/1FjqwOtyADGsPYYuZMdNkFxH2N4/9htYNcRng5PfWCTya84bAO5KnhcQD7e0as4ji/9u2tSudqmqJcSUXhIL5AUAHMDp5bJUVt+ZyiuHF9PymxUbrapzBMD1BDdRgHhewCY8BODzZEWHV+YtOG231xPwIb2AzOxxA2ELemU/9Q9m/Xz1qbXiCAU4oVb12skTQz66IrxP+JpV7960fKK90Fyyh1cCFraMUJhKpQDci9VygZHSrkgXDMQSDjiWi5X5jq3mGeaSR8acGUV4BcDnmsv2r1bmLNjCG/ChQ4LKBVJQq4kzCGVfbOun1iO0a12oVb0jyTODzqwSLBCuthrdveQNas3KEN6Aha1GyE+msoL0cFMH3Jgtmuq+eEmfVXeeihjuJQowpCIckt6gYh8WQyzX5UOHhLw/IriTUCLcnp9IZZmGUFh03G8t2yLjfxvAPclTwz5+nCBcTl/4YyunOug0u/FDAkN4qjMQ6SXlBe7MjZux4r7ddNFYsitqTsFNFGBt4BIetFvdb1Q/sGqt5YiQEKEEuQlJ+sHytvysZrCwn0W7fprogZrjP4b/A2Gc81F7bNw2wutkuRfRDB8Apv4FREcLRyhs8GleEYZaNecnaznZ0/vYuKQo9F+EL7aqztPcw61iscdIawALtYBBnvdKTCXhasMmTkdz2RHkH1n7uJ94gKgHLD5Ji0lZWvpV4/3wfnUeHz7pQet0XCwdazYAfF4pIBIPMBp4jnCUZg9/6Tp829hUJjBxtYQbXwlWr0cCnKNVtUFynyR/5CTZH+Ao4ZLmcud1zvmlxeksFOeijesNVNEJp5YB8Mj1yxW+wwCeTZ4dPLqoEL5OlvoyPFxTnM6A6XowB8wwmDeQpeWy9X/Ylov6Uqehsn4kCjDaaBOuba3YjwM4kBtPI50xwQAfzD+TDdjpyWeiDasBWn5lz93ra8iXbBZ9PynBHY2KJQszw5ItBFXEShUCkKi+MO7XK23h+v8I4AWsO0l2C3/Oajh7RWGm1XRUkSgIalkQmmwZQbbqtQQHHLtLrt9a564/2S7+EOH6pRN1u9XoBLtvElSJtwYQZAtBPAsqiqmSWHoSwolEAda3vEa4qnKyYbebndWVhgzK6lfnAa26Jap631nvWX+iACF+T7iWPIHbaQtP4AWZHuOcgKCeH9S3PNd0AXwnxkJPMgpgjOHjkq88jzMhb3AP325U28+baQNMC4sEfP45wUNtsSnzh7XaiXOtvlvO+UgPA/tqz5++Nfz/o/pe/PJz9gVMa3x3YsMYZOQP9hXwYLU6qC9bFbuGg3/9Xo/n41gjIYXn68sDDE82O0PvYwq9/arvHz/Hw+fv7XzJTOmXiD0EECgAR73ahmthP5FfB2CGpK9uI8AHuR6h8KzPe/h6UAA2YB/rQ2Q0yb2Ea6v7oAXXVHvyLbDyxTjAtNYfMmOmrvb5g5gzaFWdv7x1F36rtp/xCDxA+BqqjSS6z708ltLEJ52vkQIMb6F9rvXr18LzkPjeFibBCIEUIf33p9D+wmP4zUqp9c3CVFos8aK+2Km+9zx+AOAcQpfQITgh4CrwAP3IVucRZPKI66xPXz/iWZ++tQ0BfQiP81pDKL1Eh9AUdAWTkCbkQ0jrTh99Aocvvsf5ZGvW2dGp4p/vv4wXl97FJrXrWJvQJDQE1GsrVAJ4H0Iwj7iOPvdE9A8RgpKNInu9i1OHc+T7+Jmc5g1dfHjP8J/Dk8WgCFcVIwR4fUJArwcIPYOtYBFqyhukAi+hiFLwFFyCM1AIUBKRG/TcH+d17BDQK3wNFCCOe+olO8paI3MG1fagJwlcnSz2cd1eVBIY4wvnEffFTQJ5FAejFgL4QAoRDTbI6whvEuGmIxK0GCQMS9L6HQZGg39EJeDDDj+HUFz+33bt2AZgEAaAIIlYjGE8FcN4NNKmR4CE7gZw9ZXt8rN7IZRx/0PIWDBnPoDDMnwFz8E1EAEgAASAABAAAkAACAABIAAEgAAQAAJAAAgAASAABIAAEAAC4AO2lTuAGHkbtQAAAABJRU5ErkJggg==',
            ]
        ];

        self::_writeLogCKS('[URL]' . self::$API_CKS_URL . 'FUNCTION: SignServer/SignPdfAdvance');
        $result = self::call_signature_curl($data_sign);
        unset($data_sign['Parameter']['Image']);
        unset($data_sign['Parameter']['DataBase64']);
        self::_writeLogCKS('[INPUT]' . json_encode($data_sign));
        if ($result['ResponseCode'] == 1) {
            $data = base64_decode($result['Content']['SignedData'], true);
            file_put_contents($file_path, $data);
            unset($result['Content']['SignedData']);
            self::_writeLogCKS('[OUTPUT]' . json_encode($result));

            return true;
        } else {
            self::_writeLogCKS('[OUTPUT]' . json_encode($result));

            return;
        }
    }

    public function getServiceGroupID()
    {
        $file_name = 'data/logs/vpcp/uat/vnpt_cks/' . date("Ymd", time()) . ".txt";

        $data_getProfiles = [
            'RequestID' => self::$RequestID,
            'ServiceID' => 'UserAccount',
            'FunctionName' => 'GetProfile',
        ];
        self::_writeLogCKS('[URL]' . self::$API_CKS_URL);
        self::_writeLogCKS('[FUNCTION: UserAccount/GetProfile');
        self::_writeLogCKS('[INPUT]' . json_encode($data_getProfiles));

        $result = self::call_signature_curl($data_getProfiles);
        self::_writeLogCKS('[OUTPUT]' . json_encode($result));
        if ($result['ResponseCode'] == 1) {
            return $result['Content']['GroupID'];
        } else {
            return '';
        };
    }

    public function getCertID()
    {

        $data_getCert = [
            'RequestID' => self::getGUID(),
            'ServiceID' => 'Certificate',
            'FunctionName' => 'GetAccountCertificateByEmail',
            'Parameter' => [
                'PageIndex' => 0,
                'PageSize' => 10
            ]
        ];

        self::_writeLogCKS('[URL]' . self::$API_CKS_URL);
        self::_writeLogCKS('[FUNCTION: Certificate/GetAccountCertificateByEmail');

        self::_writeLogCKS('[INPUT]' . json_encode($data_getCert));

        $result = self::call_signature_curl($data_getCert);
        self::_writeLogCKS('[OUTPUT]' . json_encode($result));
        if ($result['ResponseCode'] == 1) {
            return $result['Content']['Items'][0]['ID'];
        } else {
            return '';
        }
    }

    public static function getGUID()
    {
        mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    public function call_signature_curl($data)
    {
        $access_token = self::$access_token_cks;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::$API_CKS_URL,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $access_token,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $msg = json_decode($response, true);
        curl_close($curl);

        return $msg;
    }

    public function convertNumberToWords($number)
    {
        $hyphen = ' ';
        $linh = ' linh ';
        $conjunction = '  ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín',
            10 => 'Mười',
            11 => 'Mười một',
            12 => 'Mười hai',
            13 => 'Mười ba',
            14 => 'Mười bốn',
            15 => 'Mười năm',
            16 => 'Mười sáu',
            17 => 'Mười bảy',
            18 => 'Mười tám',
            19 => 'Mười chín',
            20 => 'Hai mươi',
            30 => 'Ba mươi',
            40 => 'Bốn mươi',
            50 => 'Năm mươi',
            60 => 'Sáu mươi',
            70 => 'Bảy mươi',
            80 => 'Tám mươi',
            90 => 'Chín mươi',
            100 => 'trăm',
            1000 => 'nghìn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'nghìn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            // overflow
//            trigger_error('convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING);
            return false;
        }

        if ($number < 0) {
            return $negative . self::convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;

                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    if ($remainder > 0 && $remainder < 10) {
                        $string .= $conjunction . $linh . self::convertNumberToWords($remainder);
                    } else {
                        $string .= $conjunction . self::convertNumberToWords($remainder);

                    }
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }


    public function actionGetNotifyUrl()
    {
        $token_code = ObjInput::get('token_code', 'str', '');
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $token_code])
            ->one();

        $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id ", "checkout_order_id" => $checkout_order->id]);

        ///self::_writeLog('[URL] ' . $url);
        $params = self::_getParamsV2($checkout_order_callback_info);
        if ($params != false) {
            $query_string = http_build_query($params);

            self::_writeLog('[INPUT] ' . $checkout_order_callback_info['notify_url'] . '?' . $query_string);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $checkout_order_callback_info['notify_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (substr($checkout_order_callback_info['notify_url'], 0, 5) == 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            $result = curl_exec($ch);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $result);
            curl_close($ch);

            if ($result != '' && $status == 200) {
                $result = json_decode($result, true);
                var_dump($status);

                var_dump($result);
            } else {
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $checkout_order_callback_info['notify_url'] . '?' . $query_string,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);
                self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $response);
                var_dump($response);
                var_dump($status);
                die;

                curl_close($curl);
                $result = json_decode($response, true);


                return $result;
            }
            die;
        }


//        $token_code = ObjInput::get('token_code', 'str', '');
//        if (CheckoutOrder::checkTokenCode($token_code, $checkout_order)) {
//            $checkout_order['token_code'] =$token_code;
//            $checkout_order['merchant_info'] = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $checkout_order['merchant_id']]);
//            $checkout_order['user_info'] = Tables::selectOneDataTable("user_login", ["merchant_id = :merchant_id", "merchant_id" => $checkout_order['merchant_id']]);
//            $checkout_order['checkout_order_id'] = $checkout_order['id'];
//
//        }
//        $notify_url = $params = self::_getParams($checkout_order);
//
//
//        $url = $checkout_order['notify_url']. '?' .http_build_query($params);
//        var_dump($url);die;
    }

    public function actionGetNotifyUrlBca()
    {
        $token_code = ObjInput::get('token_code', 'str', '');
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $token_code])
            ->one();

        $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id ", "checkout_order_id" => $checkout_order->id]);
        $url = $checkout_order_callback_info['notify_url'];


        ///self::_writeLog('[URL] ' . $url);
        $params = self::_getParamsV3($checkout_order_callback_info);
        if ($params != false) {
            $headers = array(
                'Content-Type: application/json',
            );
            try {
                self::_writeLog('[INPUT] ' . $url . '?' . json_encode($params));

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                if (substr($url, 0, 5) == 'https') {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/10.0');
                curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
                $result = curl_exec($ch);
                var_dump($result);
                die;

                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $result);

                curl_close($ch);
                if ($result != '' && $status == 200) {
                    $result = str_replace('&', '&amp;', trim($result));
                    $data = json_decode($result, true);
                    return $data;
                }
            } catch (\Exception $ex) {
                self::_writeLog('[RESULT][' . $url . ']:' . $ex);

                return false;
            }
        }


//        $token_code = ObjInput::get('token_code', 'str', '');
//        if (CheckoutOrder::checkTokenCode($token_code, $checkout_order)) {
//            $checkout_order['token_code'] =$token_code;
//            $checkout_order['merchant_info'] = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $checkout_order['merchant_id']]);
//            $checkout_order['user_info'] = Tables::selectOneDataTable("user_login", ["merchant_id = :merchant_id", "merchant_id" => $checkout_order['merchant_id']]);
//            $checkout_order['checkout_order_id'] = $checkout_order['id'];
//
//        }
//        $notify_url = $params = self::_getParams($checkout_order);
//
//
//        $url = $checkout_order['notify_url']. '?' .http_build_query($params);
//        var_dump($url);die;
    }


    private static function _writeLog($data)
    {


        $file_name = 'checkout_order_callback' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    private static function _writeLogDebug($data)
    {
        $file_name = 'debug' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    private static function _writeLogCKS($data)
    {


        $file_name = 'cks' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    private static function _getParams($checkout_order_callback_info)
    {
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id", "id" => $checkout_order_callback_info['checkout_order_id']]);
        if ($checkout_order_info != false) {
            $params = CheckoutOrder::getParamsForNotifyUrl($checkout_order_info);
            if (isset($params['result_data'])) {
                return $params;
            }
            $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
            return $params;
        }
        return false;
    }

    private static function _getChecksumNotifyUrl($merchant_id, $params)
    {
        $api_key = Merchant::getApiKey($merchant_id, $merchant_info);
        $str_checksum = $params['token_code'];
        $str_checksum .= '|' . $params['version'];
        $str_checksum .= '|' . $params['order_code'];
        $str_checksum .= '|' . $params['order_description'];
        $str_checksum .= '|' . $params['amount'];
        $str_checksum .= '|' . $params['sender_fee'];
        $str_checksum .= '|' . $params['receiver_fee'];
        $str_checksum .= '|' . $params['currency'];
        $str_checksum .= '|' . $params['return_url'];
        $str_checksum .= '|' . $params['cancel_url'];
        $str_checksum .= '|' . $params['notify_url'];
        $str_checksum .= '|' . $params['status'];
        $str_checksum .= '|' . $params['payment_method_code'];
        $str_checksum .= '|' . $params['payment_method_name'];
        $str_checksum .= '|' . $api_key;
        return md5($str_checksum);
    }

    private static function _getChecksumNotifyUrlBCA($merchant_id, $params)
    {
        $api_key = Merchant::getApiKey($merchant_id, $merchant_info);
        $str_checksum = $params['token_code'];
        $str_checksum .= '|' . $params['version'];
        $str_checksum .= '|' . $params['order_code'];
        $str_checksum .= '|' . $params['order_description'];
        $str_checksum .= '|' . $params['amount'];
        $str_checksum .= '|' . $params['currency'];
        $str_checksum .= '|' . $params['status'];
        $str_checksum .= '|' . $params['so_the'];
        $str_checksum .= '|' . $params['bank_code'];
        $str_checksum .= '|' . $params['thoi_gian_gd'];
        $str_checksum .= '|' . $api_key;
        return md5($str_checksum);
    }

    private function getPaymentMethodName($payment_method_id)
    {
        $payment_method = PaymentMethod::findOne(['id' => $payment_method_id]);
        if ($payment_method != false && $payment_method->name) {
            return $payment_method->name;
        }

        return '';
    }

    private function convertPaymentMethodCode($bank_code, $method)
    {
        $payment_method_code = '';
        if ($bank_code == 'SCB') {
            $bank_code = 'STB';
        } elseif ($bank_code == 'NAB') {
            $bank_code = 'NCB';
        } elseif ($bank_code == 'NL') {
            $bank_code = 'NGANLUONG';
        }

        if ($method == 'ATM_ON' || $method == 'ATM_ONLINE') {
            $payment_method = 'ATM-CARD';
        } elseif ($method == 'IB_ON' || $method == 'IB_ONLINE') {
            $payment_method = 'IB-ONLINE';
        } elseif ($method == 'WALLET') {
            $payment_method = 'WALLET';
        } elseif ($method == 'CREDIT_CARD') {
            $payment_method = 'CREDIT-CARD';
        } elseif ($method == 'CASH_IN_SHOP') {
            $payment_method = 'CASH-IN-SHOP';
        } elseif ($method == 'QRCODE') {
            $payment_method = 'QR-CODE';
        } elseif ($method == 'QRCODE_OFFLINE') {
            $payment_method = 'QRCODE_OFFLINE';
        }

        $payment_method_code = $bank_code . '-' . $payment_method;

        return $payment_method_code;
    }

    public function actionReview()
    {
        $this->checkout_order['status'] = CheckoutOrder::findOne(['id' => $this->checkout_order['id']])->status;
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_REVIEW) {
            $this->redirectErrorPage('Order does not exist, access is denied');
        }
        return $this->render('review', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
        ));
    }

    public function actionFailure()
    {
        if (intval($this->checkout_order['time_limit']) <= time()) {
            $this->redirectWarningPage('Đơn đặt hàng đã hết hạn thanh toán. Vui lòng hoàn quay lại tạo đơn hàng mới!');
        }
        $this->checkout_order['status'] = CheckoutOrder::findOne(['id' => $this->checkout_order['id']])->status;
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_FAILURE) {
            $this->redirectErrorPage('Order does not exist, access is denied');
        }
        if ($this->checkout_order['notify_url'] != '') {
            $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
            // print_r($checkout_order_callback_info); exit();
            if ($checkout_order_callback_info != false) {
                CheckoutOrderCallback::process($checkout_order_callback_info);
                if (isset($this->checkout_order['merchant_info']['is_sent']) && $this->checkout_order['merchant_info']['is_sent'] == 1) {
                    if (strpos($this->checkout_order['merchant_info']['mail_sent'], ',')) {
                        $email_cc = explode(',', $this->checkout_order['merchant_info']['mail_sent']);

                    } else {
                        $email_cc = [];
                    }

                    SendMailBussiness::send(
                        trim($this->checkout_order['user_info']['email']),
                        'Thông báo giao dịch thất bại-Failed Transaction Notification',
                        'noti_fail',
                        [
                            'order_description' => $this->checkout_order['order_description'],
                            'order_code' => $this->checkout_order['order_code'],
                            'time_paid' => time(),
                            'payment_name' => $this->checkout_order['buyer_fullname'],
                            'payment_method' => !empty(self::getPaymentMethodName($this->transaction['payment_method_id'])) ? self::getPaymentMethodName($this->transaction['payment_method_id']) : '',
                            'amount' => $this->checkout_order['orginal_amount'],
                            'currency' => $this->checkout_order['currency'],
                            'transaction_id' => $this->checkout_order['transaction_id'],
                            'email' => $this->checkout_order['buyer_email'],
                            'address' => $this->checkout_order['buyer_address'],
                            'reason' => $this->transaction['reason'],

                        ], 'layouts/basic', $email_cc
                    );
                }
            } else {
                $check_notify = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id ", "checkout_order_id" => $this->checkout_order['id']]);
                if (!$check_notify) {
                    @NotifySystem::send("Có đơn hàng thất bại chưa được thêm checkout_order_callback ID: " . $this->checkout_order['id']);
                }
            }
        }
//        echo "<pre>";
//        var_dump($this->checkout_order['merchant_info']['order_status']);
//        var_dump($this->checkout_order['merchant_info']['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE);
//        die();
        return $this->render('failure', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
            'redirect' => $this->checkout_order['merchant_info']['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE,
        ));
    }

    public function actionViewBill()
    {

        $filename = (ObjInput::get('token', 'str', '', 'GET'));

        $file_url = self::$PDF_URL . base64_decode($filename) . '.pdf';
        $filepath = self::$PDF_PATH . base64_decode($filename) . '.pdf';
        if (file_exists($filepath)) {
            // Set up PDF headers
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($filepath));
            header('Accept-Ranges: bytes');
            // Render the file
            readfile($filepath);
        } else {
            echo 'Không tìm thấy file' . ' ' . str_replace('10.0.0.', '', $_SERVER['SERVER_ADDR']);
        }
        die();
        //  $pdf = new TCPDF();
        //  $result = $pdf->Output(self::$PDF_PATH.($filename) . '.pdf', "I");
        //$this->redirect($file_url);
    }

    public function actionRevert()
    {
        return $this->render('revert', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
//            'redirect' => $this->checkout_order['merchant_info']['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE,
        ));
    }

    public function actionIndex()
    {

        if (intval($this->checkout_order['time_limit']) <= time()) {
            $this->redirectWarningPage('Đơn đặt hàng đã hết hạn thanh toán. Vui lòng hoàn quay lại tạo đơn hàng mới!');
        }
        if ($this->checkout_order['status'] == CheckoutOrder::STATUS_FAILURE) {
            $this->redirectWarningPage('Đơn hàng thất bại. Vui lòng tạo đơn hàng mới!');
//            $this->redirectWarningPage('The order has been processed payment with bank in previous working turn. You have to finish the payment in that turn or back to the seller’s website to create a new order!');
        }
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_NEW) {
            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
//            $this->redirectWarningPage('The order has been processed payment with bank in previous working turn. You have to finish the payment in that turn or back to the seller’s website to create a new order!');
        }
        //-----------
        $method_code = ObjInput::get('method_code', 'str', '');
        $payment_method_code = ObjInput::get('payment_method_code', 'str', '');
        $partner_payment_code = ObjInput::get('partner_payment_code', 'str', '');
        $payment_amount = $this->checkout_order['amount'];
        $methods = Method::getPaymentMethods($method_code, $payment_amount, time());
        if (!empty($method_code)) {
            foreach ($methods as $key => $item) {

                if ($item['code'] != $method_code) {
                    unset($methods[$key]);

                }
            }
        }

        if (!empty($methods)) {
            //---------
            $models = array();
            $error_message = '';
            foreach ($methods as $method) {
                $code = strtolower($method['code']);
                $model_form_name = Method::getModelFormName($code);
                if (class_exists($model_form_name)) {
                    $models[$code] = new $model_form_name($method, Yii::$app->controller->id, ($method_code == $code));
                    $models[$code]->checkout_order = $this->checkout_order;
                    if ($models[$code]->loadPaymentModels($payment_amount, 'index', $payment_method_code, $partner_payment_code, $this->transaction, true)) {
                        if ($models[$code]->payment_model_active != null && $models[$code]->payment_model_active->isSubmit($partner_payment_code, Yii::$app->request->post())) {
                            $models[$code]->payment_model_active->submit();
                        }
                    }
                }
            }
            //-------------
            $status_installment = Tables::selectOneDataTable('installment_config', 'merchant_id = ' . $this->checkout_order['merchant_id'], '', 'status');
            if ($status_installment == true && (int)$status_installment['status'] == 0) {
                $models['tra-gop'] = '';
            }
            $bank_installment = Tables::selectOneDataTable('installment_config', 'merchant_id = ' . $this->checkout_order['merchant_id'], '', 'card_accept');
            if ($bank_installment == true) {
                foreach (json_decode($bank_installment['card_accept'], true) as $item => $value) {
                    $list_bank_installment[$item . '-TRA-GOP'] = $item . '-TRA-GOP';
                }
                return $this->render('index', array(
                    'checkout_order' => $this->checkout_order,
                    'transaction' => $this->transaction,
                    'models' => $models,
                    'methods' => $methods,
                    'error_message' => $error_message,
                    'list_bank_installment' => @$list_bank_installment
                ));
            }
            return $this->render('index', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
                'models' => $models,
                'methods' => $methods,
                'error_message' => $error_message,
            ));
        }
    }

    public function actionRequest()
    {
        if (!in_array($this->checkout_order['status'], array(CheckoutOrder::STATUS_NEW))) {
            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
//            $this->redirectWarningPage('The order has been processed payment with bank in previous working turn. You have to finish the payment in that turn or back to the seller’s website to create a new order!');
        }
        //-----------
        $error_message = '';
        $payment_method_code = ObjInput::get('payment_method_code', 'str', '');

        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method_code, Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
        if ($payment_method_info['method_code'] == "CREDIT-CARD") {
            if (Merchant::getPaymentFlowById($this->checkout_order['merchant_id'])) {
                $payment_method_info['partner_payment_id'] = "15";
                $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB-3DS2";
            } else {
                $payment_method_info['partner_payment_id'] = "12";
                $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB";
            }
        }
        $merchant_fee_info = MerchantFee::getPaymentFee($this->checkout_order['merchant_id'], $payment_method_info['id'], $this->checkout_order['amount'], 'VND', time());
        $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $this->checkout_order['amount']);
        $this->checkout_order['merchant_fee_info'] = $merchant_fee_info;
        if ($payment_method_info != false) {
            $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
//            echo $model_payment_method_name.'<br>';die;
            if (class_exists($model_payment_method_name)) {
                $model = new $model_payment_method_name();
                $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                $model->active = true;
                $model->checkout_order = $this->checkout_order;
                if ($model->getPayerFee() !== false) {
                    if (TransactionType::isInstallmentTransactionType($model['info']['transaction_type_id'])) {
                        $model->partner_payment_fee = $model->getPayerFee();
                    }
                    $model->load(Yii::$app->request->get());
                    $model->initOption();

                    if ($model->isSubmit($payment_method_info['partner_payment_code'], Yii::$app->request->post())) {
                        //print_r($model); exit();
                        if ($payment_method_info['method_code'] == 'TRA-GOP') {
                            $installment_config = Tables::selectOneDataTable('installment_config', 'merchant_id = ' . $this->checkout_order['merchant_id'] . ' AND status = 1', '', '');
                            if ($installment_config) {
                                $checkExcludedDate = $this->getCheckExcludedDate($model, $payment_method_info);
                                if (!$checkExcludedDate['method']) {
                                    $model->submit();
                                } else {
                                    if ($checkExcludedDate['error_message']) {
                                        $this->redirectErrorPage($checkExcludedDate['error_message']);
                                    } else {
                                        $this->redirectWarningPage('Ngày giao dịch bị từ chối vui lòng sử dụng thẻ khác');
                                    }
                                }
                            } else {
                                //die('Chưa cấu hình phí cho phương thức thanh toán này');
                                $this->redirectErrorPage('Cấu hình trả góp đã bị khoá, không thể thực hiện giao dịch.');
                            }
                        } else {
                            $model->submit();
                        }
                    }
                    $lang_request = Yii::$app->language;
                    return $this->render('request', array(
                        'checkout_order' => $this->checkout_order,
                        'transaction' => $this->transaction,
                        'model' => $model,
                        'action' => $model->getRequestActionForm(),
                        'lang_request' => $lang_request,
                    ));
                } else {
                    //die('Chưa cấu hình phí cho phương thức thanh toán này');
                    $this->redirectErrorPage('Chưa cấu hình phí cho phương thức thanh toán này');
                }
            }
        } elseif ($payment_method_code == "MULTIPLE-CREDIT-CARD") {
            $payment_methods = [
                'VISA',
                'MASTERCARD',
                'JCB',
                'AMEX',
            ];

            $payment_method_accept = [];

            foreach ($payment_methods as $payment_method) {
                $payment_method_check_info = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
                if ($payment_method_check_info) {
                    $payment_method_accept[] = $payment_method;
                }
            }


            $payment_method_info_tmp = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method_accept[array_rand($payment_method_accept)] . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);

            $payment_method_info_tmp['id'] = "0";
            $payment_method_info_tmp['code'] = "MULTIPLE-CREDIT-CARD";

            $model = new PaymentMethodCreditCardCyberSourceVcb3ds2MultipleCreditCardForm();
            $model->active = true;
            $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info_tmp, $payment_method_info_tmp['partner_payment_code'], $payment_method_info_tmp['partner_payment_id']);
            $model->checkout_order = $this->checkout_order;
            $model->load(Yii::$app->request->get());
            $model->initOption();
            $lang_request = Yii::$app->language;
            return $this->render('request', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
                'model' => $model,
                'action' => $model->getRequestActionForm(),
                'lang_request' => $lang_request,
            ));
//            echo "<pre>";
//            var_dump($model);
//            die();
        } else {
            $this->redirectErrorPage('Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng');
        }
    }

    public function actionRequestV2()
    {
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_NEW) {
            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
        }
        $payment_method_code = ObjInput::get('payment_method_code', 'str', '');

        if ($payment_method_code != "CREDIT-CARD" && !Yii::$app->request->post()) {
            $this->redirectWarningPage('Có lỗi hệ thống, vui lòng liên hệ chăm sóc khách hàng!');
        }


        if (Yii::$app->request->post()) {
            $form_data = Yii::$app->request->post("PaymentMethodCreditCardCyberSourceVcb3ds2MultipleCreditCardForm");

            $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber(Helpers::removeSpaceString($form_data['card_number']), false);
            if ($type_card) {
                $payment_method_code = strtoupper($type_card) . "-CREDIT-CARD";

                $card_type_code = [
                    'VISA' => '001',
                    'JCB' => '007',
                    'MASTERCARD' => '002',
                    'AMEX' => '003',
                ][strtoupper($type_card)];

                $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method_code, Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
                if ($payment_method_info) {
                    $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
                    if (class_exists($model_payment_method_name)) {
                        $model = new $model_payment_method_name();
                        $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                        $model->active = true;
                        $model->checkout_order = $this->checkout_order;
                        if ($model->getPayerFee() !== false) {
                            if (TransactionType::isInstallmentTransactionType($model['info']['transaction_type_id'])) {
                                $model->partner_payment_fee = $model->getPayerFee();
                            }
                            $model->load(Yii::$app->request->get());
                            $model->initOption();


                            $ex_class_name = explode("\\", get_class($model));

                            $post_form = Yii::$app->request->post();

                            $post_form[end($ex_class_name)] = $form_data;
                            $post_form[end($ex_class_name)]["payment_method_id"] = $payment_method_info['id'];

                            if ($model->isSubmit($payment_method_info['partner_payment_code'], $post_form)) {
                                $model->submit();
                            }
                            $lang_request = Yii::$app->language;
                            return $this->render('request', array(
                                'checkout_order' => $this->checkout_order,
                                'transaction' => $this->transaction,
                                'model' => $model,
                                'action' => $model->getRequestActionForm(),
                                'lang_request' => $lang_request,
                            ));
                        } else {
                            //die('Chưa cấu hình phí cho phương thức thanh toán này');
                            $this->redirectErrorPage('Chưa cấu hình phí cho phương thức thanh toán này');
                        }
                    }
                } elseif ($payment_method_code == "MULTIPLE-CREDIT-CARD") {
                    $payment_methods = [
                        'VISA',
                        'MASTERCARD',
                        'JCB',
                        'AMEX',
                    ];

                    $payment_method_accept = [];

                    foreach ($payment_methods as $payment_method) {
                        $payment_method_check_info = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
                        if ($payment_method_check_info) {
                            $payment_method_accept[] = $payment_method;
                        }
                    }


                    $payment_method_info_tmp = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method_accept[array_rand($payment_method_accept)] . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);

                    $payment_method_info_tmp['id'] = "0";
                    $payment_method_info_tmp['code'] = "MULTIPLE-CREDIT-CARD";

                    $model = new PaymentMethodCreditCardCyberSourceVcb3ds2MultipleCreditCardForm();
                    $model->active = true;
                    $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info_tmp, $payment_method_info_tmp['partner_payment_code'], $payment_method_info_tmp['partner_payment_id']);
                    $model->checkout_order = $this->checkout_order;
                    $model->load(Yii::$app->request->get());
                    $model->initOption();
                    $lang_request = Yii::$app->language;
                    return $this->render('request', array(
                        'checkout_order' => $this->checkout_order,
                        'transaction' => $this->transaction,
                        'model' => $model,
                        'action' => $model->getRequestActionForm(),
                        'lang_request' => $lang_request,
                    ));
                } else {
                    $this->redirectErrorPage('Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng');
                }


            } else {
                $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/transaction-destroy', 'token_code' => $this->token_code], HTTP_CODE);
                $this->redirect($url);
            }
        } else {
            $payment_methods = [
                'VISA',
                'MASTERCARD',
                'JCB',
                'AMEX',
            ];

            $payment_method_accept = [];

            foreach ($payment_methods as $payment_method) {
                $payment_method_check_info = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
                if ($payment_method_check_info) {
                    $payment_method_accept[] = $payment_method;
                }
            }

            if (count($payment_method_accept) == 0) {
                $this->redirectWarningPage('Chưa được cấu hình phương thức thanh toán!');
            }

            $payment_method_info_tmp = PaymentMethodBusiness::getInfoByPaymentMethodCodeV2($payment_method_accept[array_rand($payment_method_accept)] . "-CREDIT-CARD", Yii::$app->controller->id, $this->checkout_order['merchant_id'], $this->checkout_order['amount']);
            $payment_method_info_tmp['id'] = "0";
            $payment_method_info_tmp['code'] = "MULTIPLE-CREDIT-CARD";

            $model = new PaymentMethodCreditCardCyberSourceVcb3ds2MultipleCreditCardForm();
            $model->active = true;
            $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info_tmp, $payment_method_info_tmp['partner_payment_code'], $payment_method_info_tmp['partner_payment_id']);
            $model->checkout_order = $this->checkout_order;
            $model->load(Yii::$app->request->get());
            $model->initOption();
            $lang_request = Yii::$app->language;
            return $this->render('request-v2', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
                'model' => $model,
                'action' => $model->getRequestActionForm(),
                'lang_request' => $lang_request,
            ));
        }


    }

    public function actionConfirmVerify()
    {
        if (!in_array($this->checkout_order['status'], array(CheckoutOrder::STATUS_PAYING, CheckoutOrder::STATUS_PAID, CheckoutOrder::STATUS_INSTALLMENT_WAIT)) && $this->transaction != false) {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
        //-----------
        $error_message = '';
        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodId($this->transaction['payment_method_id'], $this->transaction['partner_payment_id']);

        if ($payment_method_info != false) {
            $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
            $model = new $model_payment_method_name();
            $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'confirm-verify', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id'], $this->transaction);
            $model->active = true;
            $model->checkout_order = $this->checkout_order;
            $model->load(Yii::$app->request->get());
            $model->initOption();

            if ($model->isSubmit($payment_method_info['partner_payment_code'], Yii::$app->request->post())) {
                $model->submit();
            }
            return $this->render('confirm-verify', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
                'model' => $model,
            ));
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    public function actionVerify()
    {
        if (!in_array($this->checkout_order['status'], array(CheckoutOrder::STATUS_PAYING)) && $this->transaction != false) {
            if ($this->checkout_order['status'] == CheckoutOrder::STATUS_PAID) {
                $url_success = self::_getUrlSuccess($this->checkout_order['token_code']);
                $this->redirect($url_success);
            } elseif ($this->checkout_order['status'] == CheckoutOrder::STATUS_REVERT) {
                $url_revert = self::_getUrlRevert($this->checkout_order['token_code']);
                $this->redirect($url_revert);
            } else {
                $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
            }
        }
        //-----------
        $error_message = '';
        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodId($this->transaction['payment_method_id'], $this->transaction['partner_payment_id']);

        if ($payment_method_info != false) {
            $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
            $model = new $model_payment_method_name();
            $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'verify', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id'], $this->transaction);
            $model->active = true;
            $model->checkout_order = $this->checkout_order;
            $model->load(Yii::$app->request->get());
            $model->initOption();
            if ($model->isSubmit($payment_method_info['partner_payment_code'], Yii::$app->request->post())) {
                $model->submit();
            }

            // sandbox local ko can
            if ($model->info['partner_payment_code'] == 'VCB' && $model->info['method_code'] == 'QR-CODE') {
                $model->payment_transaction['partner_payment_info']['qr_data'] = self::genQRcode($model->payment_transaction['partner_payment_info']['qr_data']);
            }

            return $this->render('verify', array(
                'checkout_order' => $this->checkout_order,
                'transaction' => $this->transaction,
                'model' => $model,
            ));
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }


    public function actionWarning()
    {
        $error_message = ObjInput::get('error_message', 'str', '');
        if ($error_message != '') {
            $error_message = base64_decode(base64_decode($error_message));
        }
        return $this->render('warning', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
            'error_message' => $error_message,
        ));
    }

    public function redirectWarningPage($error_message)
    {
        $error_message = urlencode(base64_encode(base64_encode(Translate::get($error_message))));
        $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/warning', 'token_code' => $this->token_code, 'error_message' => $error_message], HTTP_CODE);
        header('Location:' . $url);
        die();
    }


    public function actionUpdateStatusExcluded()
    {
        $model = InstallmentExcludedDate::find()->where(['not', ['status' => 1]])->all();
        $updated_ids = [];
        foreach ($model as $key => $item) {
            if (strtotime($item->apply_from) - time() < 0 && strtotime($item->expired_at) - time() > 0) {
                $item->status = 0;
            } elseif (strtotime($item->apply_from) - time() >= 0) {
                $item->status = 2;//not applied yet
            } elseif (strtotime($item->expired_at) - time() < 0) {
                $item->status = 3;//expired
            }
            if ($item->save()) {
                array_push($updated_ids, $item->id);
            }
        }

    }

    public function actionGetCheckExcludedDate()
    {
        $this->actionUpdateStatusExcluded();
        $card_type = [
            '001' => 'VISA',
            '007' => 'JCB',
            '002' => 'MASTERCARD',
            '003' => 'AMEX',
        ];
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->get();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $bin = substr(str_replace(' ', '', $data['card_number']), 0, 6);
//            $bin_data = Tables::selectAllBySql("SELECT * FROM bin_accept_v2 where code =" . $bin . " AND card_type =" . json_encode($card_type[$data['type_card']]) . " AND bank_code = " . json_encode($data['bank']));
            $bin_data = Tables::selectAllBySql("SELECT * FROM bin_accept_v2 WHERE type = " . BinAcceptV2::TYPE_CREDIT . " AND code =" . $bin . " AND card_type =" . json_encode($card_type[$data['type_card']]) . " AND bank_code = " . json_encode($data['bank']));
            if (empty($bin_data)) {
                $bin = substr(str_replace(' ', '', $data['card_number']), 0, 8);
                $bin_data = Tables::selectAllBySql("SELECT * FROM bin_accept_v2 WHERE type = " . BinAcceptV2::TYPE_CREDIT . " AND code =" . $bin . " AND card_type =" . json_encode($card_type[$data['type_card']]) . " AND bank_code = " . json_encode($data['bank']));
            }
            $query = Tables::selectAllBySql("SELECT * FROM installment_excluded_date where status = 0 AND bank_code = '" . $data['bank'] . "' AND bin = '[" . json_encode($bin) . "]'");
            $method = false;
            $message_eror = '';
            if (count($bin_data) > 0) {
                for ($x = 0; $x < count($query); $x++) {
                    $method_queyr = json_decode($query[$x]['method'], true);
                    if (in_array($card_type[$data['type_card']], $method_queyr) && in_array(getdate()['mday'], explode(',', $query[$x]['excluded_date']))) {
                        $method = true;
                        $message_eror = $query[$x]['message'];
                    }
                }
            } else {
                $message_eror = 'Thẻ chưa được thiết lập thanh toán trả góp vui lòng liên hệ vận hành để được hỗ trợ.';
            }
            return ['method' => $method, 'message_error' => $message_eror];
        } else {
            return 'nothing';
        }
    }

    public function getCheckExcludedDate($model, $payment_method_info)
    {
        $this->actionUpdateStatusExcluded();
        $card_type = [
            '001' => 'VISA',
            '007' => 'JCB',
            '002' => 'MASTERCARD',
            '003' => 'AMEX',
        ];
        $error_message = '';
        $bin = substr(str_replace(' ', '', $model['card_number']), 0, 6);
        $bin_data = Tables::selectAllBySql("SELECT * FROM bin_accept_v2 where code =" . $bin . " AND card_type =" . json_encode($card_type[$model['card_info']]) . " AND bank_code = '" . json_decode($payment_method_info['config'], true)['class'] . "'");
        $query = Tables::selectAllBySql("SELECT * FROM installment_excluded_date where status = 0 AND bank_code = '" . json_decode($payment_method_info['config'], true)['class'] . "' AND bin = '[" . json_encode($bin) . "]'");
        $method = false;
        if (count($bin_data) > 0) {
            for ($x = 0; $x < count($query); $x++) {
                $method_queyr = json_decode($query[$x]['method'], true);
                if (in_array($card_type[$model['card_info']], $method_queyr) && in_array(getdate()['mday'], explode(',', $query[$x]['excluded_date']))) {
                    $method = true;
                    $error_message = $query[$x]['message'];
                } else {
                    $error_message = 'Ngày giao dịch bị từ chối vui lòng sử dụng thẻ khác';
                }
            }
        } else {
            $error_message = 'Thẻ chưa được thiết lập thanh toán trả góp vui lòng liên hệ vận hành để được hỗ trợ.';
        }
        return ['method' => $method, 'error_message' => $error_message];
    }

    /**
     * @throws \SoapFault
     */
    public function actionCheckEnroll()
    {
        $result = false;
        $data = Yii::$app->request->post();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (isset($data['token_code']) && $data['token_code'] != null && $data['token_code'] != "") {
            $checkout_order_info = CheckoutOrder::find()->where(['token_code' => $data['token_code']])->one();
            if ($checkout_order_info) {
                if ($checkout_order_info->status == CheckoutOrder::STATUS_NEW) {
                    if ($data['payment_method_code'] == "MULTIPLE-CREDIT-CARD") {
                        $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber($data['custommer_info']['card_number'], false);
                        if ($type_card) {
                            $continue = true;
                            $data['payment_method_code'] = strtoupper($type_card) . "-CREDIT-CARD";

                            $card_type_code = [
                                'VISA' => '001',
                                'JCB' => '007',
                                'MASTERCARD' => '002',
                                'AMEX' => '003',
//                                'UPI' => '062',
                            ][strtoupper($type_card)];

                        } else {
                            $continue = false;
                        }
                    } else {
                        $card_type_code = $data['custommer_info']['card_type'];
                        $continue = true;
                    }

                    if ($continue) {
                        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($data['payment_method_code'], 'version_1_0');
                        if ($payment_method_info) {
                            if ($payment_method_info['method_code'] == "CREDIT-CARD") {
                                if (Merchant::getPaymentFlowById($checkout_order_info->merchant_id)) {
                                    $payment_method_info['partner_payment_id'] = "15";
                                    $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB-3DS2";
                                } else {
                                    $payment_method_info['partner_payment_id'] = "12";
                                    $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB";
                                }
                            }
                            $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order_info->merchant_id, $payment_method_info['partner_payment_id']);
                            if ($partner_payment_account_info) {
                                $params = [
                                    'partner_payment_account_info' => $partner_payment_account_info
                                ];
                                if (isset($data['custommer_info']['name_on_account'])) {
                                    $card_fullname = $this->_convertName($data['custommer_info']['name_on_account']);
                                } else {
                                    $card_fullname = $checkout_order_info->buyer_fullname;
                                }

                                $this->_processCardFullname($card_fullname, $first_name, $last_name);
                                $merchant_fee_info = MerchantFee::getPaymentFee($checkout_order_info->merchant_id, $payment_method_info['id'], $checkout_order_info->amount, 'VND', time());
                                $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $checkout_order_info->amount);
                                if (isset($data['enrrol_checked']) && $data['enrrol_checked']) {
                                    return self::updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data);
                                }

                                if (isset($data['custommer_info']['country']) != null && in_array($data['custommer_info']['country'], ['US', "CA"])) {
                                    $postal_code = isset($data['custommer_info']['zip_or_portal_code']) ? $data['custommer_info']['zip_or_portal_code'] : '91356';
                                    $state = isset($data['custommer_info']['state']) ? $data['custommer_info']['state'] : '';
                                } else {
                                    $postal_code = "";
                                    $state = "";
                                }

                                $cyber_source = new CyberSourceVcb3ds2($params);
                                $inputs = array(
                                    'reference_code' => $GLOBALS['PREFIX'] . $checkout_order_info->id,
                                    'city' => isset($data['custommer_info']['city']) ? $data['custommer_info']['city'] : 'Ha Noi',
                                    'country' => isset($data['custommer_info']['country']) ? $data['custommer_info']['country'] : 'Viet Nam',
                                    'email' => $checkout_order_info->buyer_email,
                                    'phone' => $checkout_order_info->buyer_mobile,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'postal_code' => $postal_code,
                                    'state' => $state,
                                    'address' => isset($data['custommer_info']['billing_address']) ? $data['custommer_info']['billing_address'] : $checkout_order_info->buyer_address,
                                    'customer_id' => 0,
                                    'account_number' => $data['custommer_info']['card_number'],
                                    'card_type' => $card_type_code,
                                    'expiration_month' => $data['custommer_info']['expiration_month'],
                                    'expiration_year' => $data['custommer_info']['expiration_year'],
                                    'currency' => 'VND',
                                    'amount' => $sender_fee + $checkout_order_info->amount,
                                    'cvv_code' => $data['custommer_info']['card_code'],
                                    'client_ip' => @$_SERVER['REMOTE_ADDR'],
                                    'order_code' => $checkout_order_info->order_code,
                                    'referenceID' => isset($data['custommer_info']['referenceID']) ? $data['custommer_info']['referenceID'] : '',
                                    'ignore_avs' => in_array($checkout_order_info->merchant_id, ['91', "168", "78", "192"]),
//                                'run_authorize' => !in_array($checkout_order_info->merchant_id, ['78', '91', "168"]), // Insert Dai-ichi Merchant
                                );

                                $check_enroll = $cyber_source->checkEnroll($inputs);
//                            @Helpers::writeLog("[CHECK_ENROLL][RESPONSE]" . json_encode($check_enroll));
                                if ($check_enroll == null) {
                                    $result = [
                                        'status' => false,
                                        'error_message' => "Lỗi kết nối đến kênh thanh toán",
                                    ];
                                } else {
                                    $reasonCode = $check_enroll->reasonCode;

                                    $eci = '';
                                    if (isset($check_enroll->payerAuthEnrollReply->eci)) {
                                        $eci = $check_enroll->payerAuthEnrollReply->eci;
                                    } elseif (isset($check_enroll->payerAuthEnrollReply->eciRaw)) {
                                        $eci = $check_enroll->payerAuthEnrollReply->eciRaw;
                                    }
                                    if (CyberSourceVcb3ds2::checkChallenge($check_enroll)) {
                                        $result = [
                                            'status' => true,
                                            'valid' => true,
                                            'auth_info' => array(
                                                'paReq' => $check_enroll->payerAuthEnrollReply->paReq,
                                                'acsURL' => $check_enroll->payerAuthEnrollReply->acsURL,
                                                'authenticationTransactionID' => $check_enroll->payerAuthEnrollReply->authenticationTransactionID,
                                            ),
                                        ];
                                    } elseif ($reasonCode == 100 &&
                                        isset($check_enroll->ccAuthReply->reasonCode) &&
                                        $check_enroll->ccAuthReply->reasonCode == "100" &&
                                        isset($check_enroll->ccAuthReply->authorizationCode) &&
                                        $check_enroll->ccAuthReply->authorizationCode != "" &&
                                        $eci != null &&
                                        !in_array($eci, ["00", "07"])
                                    ) {
                                        return CheckoutOrderBusiness::updateSuccess($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $check_enroll->requestID, $check_enroll->ccAuthReply->authorizationCode);
                                    } elseif (isset($check_enroll->payerAuthEnrollReply->paresStatus)
                                        && in_array($check_enroll->payerAuthEnrollReply->paresStatus, ['Y', 'A'])
                                        && $eci != null && !in_array($eci, ["00", "07"])) {
                                        $result = [
                                            'status' => true,
                                            'valid' => false,
                                            'auth_info' => array(
                                                'authenticationTransactionID' => $check_enroll->payerAuthEnrollReply->authenticationTransactionID,
                                            ),
                                        ];
                                    } else {
                                        return self::updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $check_enroll);
                                    }
                                }
                            } else {
                                $result = [
                                    'status' => false,
                                    'error_message' => "Chưa cấu hình tài khoản kênh thanh toán",
                                ];
                            }
                        } else {
                            $result = [
                                'status' => false,
                                'error_message' => "Chưa cấu hình phương thức thanh toán",
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'error_message' => "Đầu BIN không hợp lệ",
                        ];
                    }

                } else {
                    $result = [
                        'status' => false,
                        'error_message' => "Trạng thái đơn hàng không hợp lệ"
                    ];
                }
            } else {
                $result = [
                    'status' => false,
                    'error_message' => "Không tìm thấy đơn hàng"
                ];
            }
        } else {
            $result = [
                'status' => false,
                'error_message' => "Tham số đầu vào không hợp lệ"
            ];
        }
//        @Helpers::writeLog("[" . $checkout_order_info->order_code . "][CHECK_ENROLL][RESULT]" . json_encode($result));
        return $result;
    }

    /**
     * @throws \SoapFault
     */
    public function actionSetupAuthor()
    {
        $time_start = time();

        $result = false;
        $data = Yii::$app->request->post();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (isset($data['token_code']) && $data['token_code'] != null && $data['token_code'] != "") {
            if (isset($data['custommer_info']) && isset($data['custommer_info']['card_number']) && Helpers::isCreditCard($data['custommer_info']['card_number'])) {
                if (CardDeclineBusiness::checkCard($data['custommer_info']['card_number'])) {
                    $checkout_order_info = CheckoutOrder::find()->where(['token_code' => $data['token_code']])->one();
                    if ($checkout_order_info) {
                        if ($checkout_order_info->status == CheckoutOrder::STATUS_NEW) {
                            if ($data['payment_method_code'] == "MULTIPLE-CREDIT-CARD") {
                                $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber($data['custommer_info']['card_number'], false);
                                if ($type_card) {
                                    $continue = true;
                                    $data['payment_method_code'] = strtoupper($type_card) . "-CREDIT-CARD";

                                    $card_type_code = [
                                        'VISA' => '001',
                                        'JCB' => '007',
                                        'MASTERCARD' => '002',
                                        'AMEX' => '003',
//                                        'UPI' => '062',

                                    ][strtoupper($type_card)];

                                } else {
                                    $continue = false;
                                }
                            } else {
                                $card_type_code = $data['custommer_info']['card_type'];
                                $continue = true;
                            }

                            if ($continue) {
                                $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($data['payment_method_code'], 'version_1_0');
                                if ($payment_method_info) {
                                    if ($payment_method_info['method_code'] == "CREDIT-CARD") {
                                        if (Merchant::getPaymentFlowById($checkout_order_info->merchant_id)) {
                                            $payment_method_info['partner_payment_id'] = "15";
                                            $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB-3DS2";
                                        } else {
                                            $payment_method_info['partner_payment_id'] = "12";
                                            $payment_method_info['partner_payment_code'] = "CYBER-SOURCE-VCB";
                                        }
                                    }
                                    $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order_info->merchant_id, $payment_method_info['partner_payment_id']);
                                    if ($partner_payment_account_info) {
                                        $params = [
                                            'partner_payment_account_info' => $partner_payment_account_info
                                        ];
                                        if (isset($data['custommer_info']['name_on_account'])) {
                                            $card_fullname = $this->_convertName($data['custommer_info']['name_on_account']);
                                        } else {
                                            $card_fullname = $checkout_order_info->buyer_fullname;
                                        }

                                        $this->_processCardFullname($card_fullname, $first_name, $last_name);


                                        $merchant_fee_info = MerchantFee::getPaymentFee($checkout_order_info->merchant_id, $payment_method_info['id'], $checkout_order_info->amount, 'VND', time());
                                        $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $checkout_order_info->amount);
                                        if (isset($data['enrrol_checked']) && $data['enrrol_checked']) {
                                            return self::updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data);
                                        }
                                        $cyber_source = new CyberSourceVcb3ds2($params);


                                        if (isset($data['custommer_info']['country']) != null && in_array($data['custommer_info']['country'], ['US', "CA"])) {
                                            $postal_code = isset($data['custommer_info']['zip_or_portal_code']) ? $data['custommer_info']['zip_or_portal_code'] : '91356';
                                            $state = isset($data['custommer_info']['state']) ? $data['custommer_info']['state'] : '';
                                        } else {
                                            $postal_code = "";
                                            $state = "";
                                        }

                                        $inputs = array(
                                            'reference_code' => $GLOBALS['PREFIX'] . $checkout_order_info->id,
                                            'city' => isset($data['custommer_info']['city']) && $data['custommer_info']['city'] ? $data['custommer_info']['city'] : 'Ha Noi',
                                            'country' => isset($data['custommer_info']['country']) && $data['custommer_info']['country'] ? $data['custommer_info']['country'] : 'Viet Nam',
                                            'email' => 'null@cybersource.com',
                                            'phone' => $checkout_order_info->buyer_mobile,
                                            'first_name' => $first_name,
                                            'last_name' => $last_name,
                                            'postal_code' => $postal_code,
                                            'state' => $state,
                                            'address' => isset($data['custommer_info']['billing_address']) && $data['custommer_info']['billing_address'] ? $data['custommer_info']['billing_address'] : $checkout_order_info->buyer_address,
                                            'customer_id' => 0,
                                            'account_number' => $data['custommer_info']['card_number'],
                                            'card_type' => $card_type_code,
                                            'expiration_month' => $data['custommer_info']['expiration_month'],
                                            'expiration_year' => $data['custommer_info']['expiration_year'],
                                            'currency' => 'VND',
                                            'amount' => $sender_fee + $checkout_order_info->amount,
                                            'cvv_code' => $data['custommer_info']['card_code'],
                                            'client_ip' => @$_SERVER['REMOTE_ADDR'],
                                            'order_code' => $checkout_order_info->order_code,
                                            'referenceID' => isset($data['custommer_info']['referenceID']) ? $data['custommer_info']['referenceID'] : '',
                                        );

                                        $setup = $cyber_source->stepOneAuthSetup($inputs);
                                        if ($setup == null) {
                                            $result = [
                                                'status' => false,
                                                'error_message' => "Lỗi kết nối đến kênh thanh toán",
                                            ];
                                        } else {
                                            if ($setup->reasonCode == "100") {
                                                $result = [
                                                    'status' => true,
                                                    'valid' => true,
                                                    'auth_info' => array(
                                                        'referenceID' => $setup->payerAuthSetupReply->referenceID,
                                                        'accessToken' => $setup->payerAuthSetupReply->accessToken,
                                                        'deviceDataCollectionURL' => $setup->payerAuthSetupReply->deviceDataCollectionURL,
                                                    ),
                                                ];
                                            } else {
                                                return self::updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $setup);
                                            }
                                        }
                                    } else {
                                        $result = [
                                            'status' => false,
                                            'error_message' => "Chưa cấu hình tài khoản kênh thanh toán",
                                        ];
                                    }
                                } else {
                                    $result = [
                                        'status' => false,
                                        'error_message' => "Chưa cấu hình phương thức thanh toán",
                                    ];
                                }
                            } else {
                                $result = [
                                    'status' => false,
                                    'error_message' => "Đầu BIN không hợp lệ",
                                ];
                            }
                        } else {
                            $result = [
                                'status' => false,
                                'error_message' => "Trạng thái đơn hàng không hợp lệ"
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'error_message' => "Không tìm thấy đơn hàng"
                        ];
                    }
                } else {
                    $result = [
                        'status' => false,
                        'error_message' => Translate::get("Chúng tôi không thể xử lý giao dịch với thẻ này. Vui lòng sử dụng thẻ khác!")
                    ];
                }
            } else {
                $result = [
                    'status' => false,
                    'error_message' => Translate::get("Thông tin không hợp lệ")
                ];
            }

        } else {
            $result = [
                'status' => false,
                'error_message' => "Tham số đầu vào không hợp lệ"
            ];
        }

        $time_end = time();
        $result['time_process'] = $time_end - $time_start;
        return $result;
    }

    private function _convertName($content)
    {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    protected function _processCardFullname($fullname, &$first_name = '', &$last_name = '')
    {
        $fullname = trim($fullname);
        $pos = strrpos($fullname, ' ');
        if ($pos !== false) {
            $last_name = trim(substr($fullname, $pos));
            $first_name = trim(substr($fullname, 0, $pos));
        } else {
            $first_name = $fullname;
            $last_name = 'A';
        }
    }

    public static function _getUrlFailure($token_code): string
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/failure', 'token_code' => $token_code], HTTP_CODE);
    }

    public static function _getUrlSuccess($token_code): string
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/success', 'token_code' => $token_code], HTTP_CODE);
    }

    public static function _getUrlRevert($token_code): string
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/revert', 'token_code' => $token_code], HTTP_CODE);
    }

    protected static function updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $reason = false): array
    {
        $checkout_order_inputs = array(
            'checkout_order_id' => $checkout_order_info->id,
            'payment_method_id' => $payment_method_info['id'],
            'partner_payment_id' => $payment_method_info['partner_payment_id'],
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );

        if ($payment_method_info['transaction_type_id'] == TransactionType::getInstallmentTransactionTypeId()) {
            $checkout_order_inputs['transaction_type_id'] = TransactionType::getInstallmentTransactionTypeId();
        }

        $result_request_payment = CheckoutOrderBusiness::requestPayment($checkout_order_inputs);
        if ($result_request_payment['error_message'] == '') {
            $transaction_id = $result_request_payment['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
                $cardInfo = [
                    'card_fullname' => $last_name . " " . $first_name,
                    'card_number' => Strings::encodeCreditCardNumber($data['custommer_info']['card_number']),
                    'card_month' => $data['custommer_info']['expiration_month'],
                    'card_year' => $data['custommer_info']['expiration_year'],
                ];
                Transaction::insertCardInfo($transaction_id, $cardInfo);

                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'partner_payment_method_refer_code' => '',
                    'user_id' => 0,
                    'partner_payment_info' => '',
                );
                $paying = TransactionBusiness::paying($inputs);
                if ($paying['error_message'] == "") {
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'reason_id' => "666",
                        'reason' => "Giao dịch bị từ chối bởi ngân hàng phát hành thẻ",
                        'user_id' => 0,
                    );
                    if ($reason && !in_array($reason->reasonCode, ["475", "100"])) {
                        $inputs['reason_id'] = $reason->reasonCode;
                        $inputs['reason'] = CyberSourceVcb3ds2::getErrorMessage($reason->reasonCode);
                    }
                    if (isset($reason->invalidField) && $reason->invalidField != "") {
                        $invalid_filed = CyberSourceVcb3ds2::getInvalidField($reason);
                        $inputs['reason'] .= " Field Invalid: ";
                        foreach ($invalid_filed as $item) {
                            $inputs['reason'] .= $item . " ";
                        }
                    }

                    if (isset($data['enrrol_checked']) && $data['enrrol_checked']) {
                        $inputs['reason_id'] = "476";
                        $inputs['reason'] = CyberSourceVcb3ds2::getErrorMessage("476") . "(user cancel)";
                    }

                    $failure = TransactionBusiness::failure($inputs);
                    if ($failure['error_message'] === '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order_info->id,
                            'user_id' => '0',
                        );
                        $update_checkout_order_failure = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                        if ($update_checkout_order_failure['error_message'] === '') {
                            $inputs_callback = [
                                'checkout_order_id' => $checkout_order_info->id,
                                'notify_url' => $checkout_order_info->notify_url,
                                'time_process' => time(),
                            ];
                            if (true) {
                                $add_callback = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                            }
                            if ($add_callback['error_message'] == '') {
                                $result = [
                                    'status' => false,
                                    'redirect' => self::_getUrlFailure($checkout_order_info->token_code),
                                ];
                            } else {
                                $result = [
                                    'status' => false,
                                    'error_message' => Translate::get($add_callback['error_message']),
                                ];
                            }
                        } else {
                            $result = [
                                'status' => false,
                                'error_message' => Translate::get($update_checkout_order_failure['error_message']),
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'error_message' => Translate::get($failure['error_message']),
                        ];
                    }
                } else {
                    $result = [
                        'status' => false,
                        'error_message' => Translate::get($paying['error_message']),
                    ];
                }
            } else {
                $result = [
                    'status' => false,
                    'error_message' => Translate::get("Không tìm thấy giao dịch"),
                ];
            }
        } else {
            $result = [
                'status' => false,
                'error_message' => Translate::get($result_request_payment['error_message'] == ''),
            ];
        }

        return $result;
    }

    private static function _getParamsV2($checkout_order_callback_info)
    {
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id", "id" => $checkout_order_callback_info['checkout_order_id']]);
        if ($checkout_order_info != false) {
            $params = CheckoutOrder::getParamsForNotifyUrl($checkout_order_info);
            if (isset($params['result_data'])) {
                return $params;
            }
            $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
            return $params;
        }
        return false;
    }

    private static function _getParamsV3($checkout_order_callback_info)
    {
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id", "id" => $checkout_order_callback_info['checkout_order_id']]);
        if ($checkout_order_info != false) {
            $params = CheckoutOrder::getParamsForNotifyUrlBCA($checkout_order_info);
            if (isset($params['result_data'])) {
                return $params;
            }
            $params['checksum'] = self::_getChecksumNotifyUrlBCA($checkout_order_info['merchant_id'], $params);
            return $params;
        }
        return false;
    }

    protected function genQRcode($qrData)
    {
        ob_start();
        QrCode::png(
            $qrData,
            $outfile = false,
            $level = 3,
            $size = 5,
            $margin = 4,
            $saveandprint = false
        );
        $imageString = base64_encode(ob_get_clean());
        header('Content-Type: text/html');
        ob_end_clean();
        return $imageString;
    }


}
