<?php


namespace checkout\controllers;


use checkout\components\MerchantCheckoutController;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Translate;
use common\models\business\SendMailBussiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\Merchant;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use phpDocumentor\Reflection\Element;

class ToolController extends MerchantCheckoutController
{
    public $modelClass = 'common\models\db\Bank';
    public $layout = 'card-token';

    public function beforeAction($action)
    {
        $debug_enable = @in_array(get_client_ip(), ["::1", "14.177.239.244", "101.99.7.213", "172.26.0.1", "14.177.239.203", "101.99.7.132","14.177.239.192"]);
        if (!$debug_enable) {
            echo "<pre>";
            var_dump("Bye" . get_client_ip());
            die();
        }
        return true;
    }


    public function actionUpdateCancel()
    {
        $arr_success = [];
        if (\   Yii::$app->request->post()) {
            if (strpos($_POST['token_code'], ',')) {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);


                $arr_token_info = explode(',', $token_code);
                foreach ($arr_token_info as $token) {

                    $result = self::processCancel($token);
                    self::_writeLog('[RESULT]' . $token . @json_encode($result));

                    if ($result['error_message'] == '') {
                        array_push($arr_success, $token);
                    }

                }

            } else {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);

                $result = self::processCancel($token_code);
                self::_writeLog('[RESULT]' . $token_code . @json_encode($result));

                if ($result['error_message'] == '') {
                    array_push($arr_success, $token_code);
                }

            }
        }

        return $this->render('update-cancel', [
            'url' => \Yii::$app->urlManager->createAbsoluteUrl([\Yii::$app->controller->id . '/update-cancel'], HTTP_CODE),
            'arr_success' => $arr_success,
        ]);
    }

    public function actionUpdateSuccess()
    {
        $arr_success = [];
        if (\   Yii::$app->request->post()) {
            if (strpos($_POST['token_code'], ',')) {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);


                $arr_token_info = explode(',', $token_code);
                foreach ($arr_token_info as $token) {

                    $result = self::processSuccess($token);
                    self::_writeLog('[RESULT]' . $token . @json_encode($result));

                    if ($result['error_message'] == '') {
                        array_push($arr_success, $token);
                    }

                }

            } else {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);

                $result = self::processSuccess($token_code);
                self::_writeLog('[RESULT]' . $token_code . @json_encode($result));

                if ($result['error_message'] == '') {
                    array_push($arr_success, $token_code);
                }

            }
        }

        return $this->render('update-success', [
            'url' => \Yii::$app->urlManager->createAbsoluteUrl([\Yii::$app->controller->id . '/update-success'], HTTP_CODE),
            'arr_success' => $arr_success,
        ]);

    }

    /** update-send-mail */
    public function actionUpdateSendMail()
    {
        $arr_success = [];
        $arr_fail = [];
//        var_dump(\Yii::$app->request->post());die();
        if (\   Yii::$app->request->post()) {
//            print_r(strpos($_POST['token_code'], ','));die();
            if (strpos($_POST['token_code'], ',')) {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);
                $arr_token_info = explode(',', $token_code);
                foreach ($arr_token_info as $token) {

                    $result = self::processSendMail($token);
                    self::_writeLog('[RESULT]' . $token . @json_encode($result));

                    if ($result) {
                        array_push($arr_success, $token);
                    }else{
                        array_push($arr_fail, $token);
                    }

                }

            } else {
                $token_code = $_POST['token_code'];
                self::_writeLog('[INPUT]' . $token_code);

                $result = self::processSendMail($token_code);
                self::_writeLog('[RESULT]' . $token_code . @json_encode($result));

                if ($result) {
                    array_push($arr_success, $token_code);
                }else{
                    array_push($arr_fail, $token_code);
                }

            }
        }

        return $this->render('update-send-mail', [
            'url' => \Yii::$app->urlManager->createAbsoluteUrl([\Yii::$app->controller->id . '/update-send-mail'], HTTP_CODE),
            'arr_success' => $arr_success,
            'arr_fail' => $arr_fail,
        ]);

    }

    /**
     * Tool tạo nhanh checksum của Notify với MC thường
     * Với các MC đặc biệt (BCA, BUUDIEN,...) cần cập nhật thêm
     * @param $token_code
     * @return void
     */
    public function actionGetChecksum($token_code){
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["token_code = :token_code", "token_code" => $token_code]);
        $params = CheckoutOrder::getParamsForNotifyUrl($checkout_order_info);
        $checksum = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);

        echo '<pre>';
        echo 'Checksum: ' . $checksum;
        die();
    }


    public function processSuccess($token_code)
    {

        $checkout_order = CheckoutOrder::findOne(['token_code' => trim($token_code)])->toArray();
        $merchant_info = Merchant::findOne($checkout_order['merchant_id']);
        $transaction_info = Transaction::findOne($checkout_order['transaction_id']);

        $email = $checkout_order['buyer_email'];
        $email_cc = [];

        $card_info = json_decode($transaction_info['card_info'], true);

        $checkout_order['time_paid'] = time();
        $result = TransactionBusiness::paidHandle($checkout_order);
        self::_writeLog('[TRANSACTION_UPDATE]' . @json_encode($result));

        if ($result['error_message'] == '') {
            $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
            // print_r($checkout_order_callback_info); exit();

            if(in_array($checkout_order['merchant_id'],$GLOBALS['MERCHANT_EMAIL_TEMPLATE_NEW'])  ) {


                $template = 'noti_success_for_buyer_dvc';
                SendMailBussiness::sendSuccessBCA(
                    trim($email),
                    'Payment Confirmation E-Visa for register code '.$this->checkout_order['order_code'],
                    $template,
                    [
                        'order_description' => $checkout_order['order_description'],
                        'card_number' => $card_info['card_number'],
                        'order_code' => $checkout_order['order_code'],
                        'merchant_name' => $merchant_info['name'],
                        'buyer_name' => $checkout_order['buyer_fullname'],
                        'time_paid' => time(),
                        'payment_name' => $checkout_order['buyer_fullname'],
                        'payment_method' => !empty(self::getPaymentMethodName($transaction_info['payment_method_id'])) ? self::getPaymentMethodName($transaction_info['payment_method_id']) : '',
                        'amount' => $checkout_order['cashin_amount'],
                        'currency' => $checkout_order['currency'],
                        'transaction_id' => $checkout_order['transaction_id'],
                        'email' => $checkout_order['buyer_email'],
                        'address' => $checkout_order['buyer_address'],

                    ], 'layouts/basic', $email_cc
                );
            } elseif (in_array($checkout_order['merchant_id'], $GLOBALS['MERCHANT_XNC'])) {
//                $template = 'noti_success_for_buyer_xnc';
                $template = 'noti_success_for_buyer_xnc_v2';
                $send = SendMailBussiness::sendSuccessBCA(
                    trim($email),
                    'Payment Confirmation E-Visa for register code ' . $checkout_order['order_code'],
                    $template,
                    [
                        'order_description' => $checkout_order['order_description'],
                        'card_number' => $card_info['card_number'],
                        'order_code' => $checkout_order['order_code'],
                        'merchant_name' => $merchant_info['name'],
                        'buyer_name' => $checkout_order['buyer_fullname'],
                        'time_paid' => time(),
                        'payment_name' => $checkout_order['buyer_fullname'],
                        'payment_method' => !empty(self::getPaymentMethodName($transaction_info['payment_method_id'])) ? self::getPaymentMethodName($transaction_info['payment_method_id']) : '',
                        'amount' => $checkout_order['cashin_amount'],
                        'currency' => $checkout_order['currency'],
                        'transaction_id' => $checkout_order['transaction_id'],
                        'email' => $checkout_order['buyer_email'],
                        'address' => $checkout_order['buyer_address'],
//                                    'receipt_url' => $checkout_order['receipt_url'],


                    ], 'layouts/basic', $email_cc
                );
            }


            if ($checkout_order_callback_info != false) {
                $result_callback = CheckoutOrderCallback::process($checkout_order_callback_info);
                self::_writeLog('[CALLBACK-PROCESS]' . @json_encode($result_callback));

                return ['error_message' => $result_callback['error_message']];

            } else {
                $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id ", "checkout_order_id" => $checkout_order['id']]);

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
                    echo "<pre>";

                    if ($result != '' && $status == 200) {
                        $result = json_decode($result, true);

                    }
                }
            }
        } else {
            return ['error_message' => $result['error_message']];
        }

    }
    public function processSendMail($token_code)
    {

        $checkout_order = CheckoutOrder::findOne(['token_code' => trim($token_code)])->toArray();
        $merchant_info = Merchant::findOne($checkout_order['merchant_id']);
        $transaction_info = Transaction::findOne($checkout_order['transaction_id']);

        $email =
            $checkout_order['buyer_email'];
//            'tinbt@nganluong.vn';
        $email_cc = [];
        $card_info = json_decode($transaction_info['card_info'], true);

//        $result = TransactionBusiness::paidHandle($checkout_order);
//        self::_writeLog('[TRANSACTION_UPDATE]' . @json_encode($result));

        $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback",
            ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
        // print_r($checkout_order_callback_info); exit();

        if(in_array($checkout_order['merchant_id'],$GLOBALS['MERCHANT_EMAIL_TEMPLATE_NEW'])  ) {
//            var_dump(123);
            $template = 'noti_success_for_buyer_dvc';
            $result_sending = SendMailBussiness::sendBCAWithResult(
                trim($email),
                'Payment Confirmation for register code '.$checkout_order['order_code'],
                $template,
                [
                    'order_description' => $checkout_order['order_description'],
                    'card_number' => $card_info['card_number'],
                    'order_code' => $checkout_order['order_code'],
                    'merchant_name' => $merchant_info['name'],
                    'buyer_name' => $checkout_order['buyer_fullname'],
                    'time_paid' => $checkout_order['time_paid'],
                    'payment_name' => $checkout_order['buyer_fullname'],
                    'payment_method' => !empty(self::getPaymentMethodName($transaction_info['payment_method_id'])) ? self::getPaymentMethodName($transaction_info['payment_method_id']) : '',
                    'amount' => $checkout_order['cashin_amount'],
                    'currency' => $checkout_order['currency'],
                    'transaction_id' => $checkout_order['transaction_id'],
                    'email' => $checkout_order['buyer_email'],
                    'address' => $checkout_order['buyer_address'],
                    'address_dvc' => Translate::getV1( @$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['area'] . ':' .@$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['address']),
                    'phone_number' => @$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['phone_number'] ,

                ], 'layouts/basic', $email_cc
            );

        }elseif(in_array($checkout_order['merchant_id'],[91])  ) {
//            var_dump(123);
//            $template = 'noti_success_for_buyer_xnc';
            $template = 'noti_success_for_buyer_xnc_v2';
            $result_sending = SendMailBussiness::sendBCAWithResult(
                trim($email),
                'Payment Confirmation E-Visa for register code '.$checkout_order['order_code'],
                $template,
                [
                    'order_description' => $checkout_order['order_description'],
                    'card_number' => $card_info['card_number'],
                    'order_code' => $checkout_order['order_code'],
                    'merchant_name' => $merchant_info['name'],
                    'buyer_name' => $checkout_order['buyer_fullname'],
                    'time_paid' => $checkout_order['time_paid'],
                    'payment_name' => $checkout_order['buyer_fullname'],
                    'payment_method' => !empty(self::getPaymentMethodName($transaction_info['payment_method_id'])) ? self::getPaymentMethodName($transaction_info['payment_method_id']) : '',
                    'amount' => $checkout_order['cashin_amount'],
                    'currency' => $checkout_order['currency'],
                    'transaction_id' => $checkout_order['transaction_id'],
                    'email' => $checkout_order['buyer_email'],
                    'address' => $checkout_order['buyer_address'],

                ], 'layouts/basic', $email_cc
            );

        } else{
//            var_dump(456);
//            var_dump($transaction_info);die();
            $template = 'noti_success_for_buyer';
            $result_sending = SendMailBussiness::sendWithResult(
                trim($email),
                'Thông báo giao dịch thành công',
                $template,
                [
                    'order_description' => $checkout_order['order_description'],
                    'card_number' => $card_info['card_number'],
                    'order_code' => $checkout_order['order_code'],
                    'merchant_name' => $merchant_info['name'],
                    'buyer_name' => $checkout_order['buyer_fullname'],
                    'time_paid' => $checkout_order['time_paid'],
                    'payment_name' => $checkout_order['buyer_fullname'],
                    'payment_method' => !empty(self::getPaymentMethodName($transaction_info['payment_method_id'])) ? self::getPaymentMethodName($transaction_info['payment_method_id']) : '',
                    'amount' => $checkout_order['cashin_amount'],
                    'currency' => $checkout_order['currency'],
                    'transaction_id' => $checkout_order['transaction_id'],
                    'email' => $checkout_order['buyer_email'],
                    'address' => $checkout_order['buyer_address'],

                ], 'layouts/basic', $email_cc
            );
        }
        return $result_sending;
    }

    public function processCancel($token_code)
    {
        $error_message = '';
        $checkout_order = CheckoutOrder::findOne(['token_code' => trim($token_code)]);
        if ($checkout_order) {
            $checkout_order->status = CheckoutOrder::STATUS_CANCEL;
            if ($checkout_order->save()) {
                $transasction = Transaction::findOne(['id' => $checkout_order['transaction_id']]);
                if ($transasction) {
                    $transasction->status = Transaction::STATUS_CANCEL;
                    if ($transasction->save()) {
                        $error_message = '';
                    } else {
                        $error_message = 'Không hủy được  transaction';

                    }

                } else {
                    $error_message = 'Không tìm thấy transaction';

                }
            } else {
                $error_message = 'Không hủy được  checkout order';

            }


        } else {
            $error_message = 'Không tìm thấy order';

        }

        return ['error_message' => $error_message];

    }

    private static function _writeLog($data, $breakLine = true, $addTime = true)
    {
        $file_name = 'updatehandle/' . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
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

    private function getPaymentMethodName($payment_method_id)
    {
        $payment_method = PaymentMethod::findOne(['id' => $payment_method_id]);
        if ($payment_method && $payment_method->name) {
            return $payment_method->name;
        }

        return '';
    }


}