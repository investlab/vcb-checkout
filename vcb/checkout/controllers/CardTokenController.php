<?php

namespace checkout\controllers;

use checkout\components\MerchantCheckoutController;
use common\components\libs\Tables;
use common\components\utils\Encryption;
use common\components\utils\Logs;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\PaymentMethodBusiness;
use common\models\business\SendMailBussiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\LinkCard;
use common\models\db\Method;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\payments\CyberSource;
use common\payments\CyberSourceVcb;
use common\payments\CyberSourceVcb3ds2;
use Firebase\JWT\JWT;
use yii\web\Controller;
use Yii;
use yii\web\Response;

class CardTokenController extends MerchantCheckoutController
{
    public $layout = 'card-token';

    public function actionPayment()
    {
        $tokenCode = ObjInput::get('token_code', 'str', '');

        if (!in_array($this->checkout_order['status'], array(CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING))) {
            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
        }

        $payment_method = PaymentMethod::findOne(['id' => $this->transaction['payment_method_id']]);
        $partner_payment = PartnerPayment::getById($this->transaction['partner_payment_id']);

        if ($payment_method) {
            $payment_method_code = $payment_method['code'];
            $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($payment_method_code, Yii::$app->controller->id);
            if ($payment_method_info != false) {
                $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
                if (class_exists($model_payment_method_name)) {
                    $model = new $model_payment_method_name();
                    $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                    $model->active = true;
                    $model->checkout_order = $this->checkout_order;
                    if ($model->getPayerFee() !== false) {
                        $model->load(Yii::$app->request->get());
                        $model->initOption();

                        $partner_payment_account_info['partner_payment_account_info'] = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->checkout_order['merchant_id'], $this->transaction['partner_payment_id']);
                        $card_token_info = LinkCard::findOne(['id' => $this->transaction['card_token_id']]);
                        $token_cybersource = $card_token_info['token_cybersource'];
                        $iv = $card_token_info['iv'];

                        $transaction = $this->transaction;
                        $checkout_order_id = $transaction['checkout_order_id'];
                        $checkout_order_token_code = $this->checkout_order['token_code'];
                        $transaction_id = $transaction['id'];

                        $inputs = [
                            'transaction_id' => $transaction_id,
                            'merchant_id' => $this->checkout_order['merchant_id'],
                            'partner_payment_id' => $payment_method_info['partner_payment_id'],
                            'partner_payment_code' => $partner_payment['code'],
                            'token_cybersource' => $token_cybersource,
                            'iv' => $iv,
                            "key" => $partner_payment_account_info["partner_payment_account_info"]["checksum_key"],
                            "partner_payment_account_info" => $partner_payment_account_info,
                        ];


                        if ($model->isSubmit($payment_method_info['partner_payment_code'], Yii::$app->request->post())) {
                            if ($this->checkout_order['status'] != CheckoutOrder::STATUS_PAYING) {
                                $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
                            }
                            $post_params = Yii::$app->request->post();
                            $inputs['ProcessorTransactionId'] = $post_params["ProcessorTransactionId"];
                            $inputs['jwt_back'] = $post_params["jwt_back"];


                            $cybersource3ds2 = new CyberSourceVcb3ds2($partner_payment_account_info);

                            $authorize = $cybersource3ds2->authorizeSubcription([
                                'cashin_id' => $GLOBALS['PREFIX'] . $transaction_id,
                                'cashin_amount' => $this->checkout_order['cashin_amount'],
                                'token' => Encryption::decryptAES($token_cybersource, $GLOBALS['AES_KEY'], $iv),
                                'ProcessorTransactionId' => $post_params["ProcessorTransactionId"],
                                'referenceID' => $post_params['reference_id'],
                            ]);


                            if ($cybersource3ds2::isSuccess($authorize)) {
                                $bank_trans_id = $authorize->requestID;
                                $authorizationCode = @$authorize->ccAuthReply->authorizationCode;
                                $inputs = array(
                                    'transaction_id' => $transaction_id,
                                    'time_paid' => time(),
                                    'bank_refer_code' => $bank_trans_id,
                                    'authorizationCode' => $authorizationCode,
                                    'user_id' => 0,
                                );
                                $result = TransactionBusiness::paid($inputs);

                                if ($result['error_message'] === '') {
                                    $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/success', 'token_code' => $this->token_code,], HTTP_CODE);
                                    header('Location:' . $url);
                                    die();
                                } else {
                                    $this->redirectWarningPage(Translate::get("Có lỗi xảy ra"));
                                }
                            } else {
                                if ($cybersource3ds2::isReview($authorize)) {

                                    $inputs_review = array(
                                        'transaction_id' => $transaction_id,
                                        'bank_refer_code' => $authorize->requestID,
                                        'user_id' => 0,
                                    );
                                    $result_review = TransactionBusiness::updateReview($inputs_review);
                                    if ($result_review['error_message'] == '') {
                                        $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/review', 'token_code' => $this->token_code,], HTTP_CODE);
                                        header('Location:' . $url);
                                        die();
                                    } else {
                                        $this->redirectWarningPage(Translate::get("Có lỗi xảy ra"));
                                    }
                                } elseif ($cybersource3ds2::isReject($authorize)) {
                                    $inputs_cancel = array(
                                        'transaction_id' => $transaction_id,
                                        'reason_id' => 0,
                                        'reason' => CyberSourceVcb3ds2::getErrorMessage($authorize->reasonCode),
                                        'user_id' => 0,
                                    );

                                    $result_cancel = TransactionBusiness::failure($inputs_cancel);
                                    if ($result_cancel['error_message'] == '') {
                                        $inputs = array(
                                            'checkout_order_id' => $this->checkout_order['id'],
                                            'user_id' => '0',
                                        );

                                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                        if ($result['error_message'] === '') {
                                            $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/cancel', 'token_code' => $this->token_code,], HTTP_CODE);
                                            header('Location:' . $url);
                                            die();
                                        }
                                    } else {
                                        $this->redirectWarningPage(Translate::get("Có lỗi xảy ra"));
                                    }
                                } else {
                                    $this->redirectWarningPage(Translate::get("Có lỗi xảy ra"));
                                }
                            }
                        } elseif ($this->checkout_order['status'] != CheckoutOrder::STATUS_NEW) {
                            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
                        }
                        $inputs_paying = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => '',
                            'user_id' => 0,
                        );

                        $result = TransactionBusiness::paying($inputs_paying);
                        if ($result['error_message'] == '') {
                            $process_cybersource = $this->processCybersource($inputs);

                            if ($process_cybersource['error_message'] == '') {
                                $dataCyber['referenceID'] = $process_cybersource['setup_response']['referenceID'];
                                $dataCyber['accessToken'] = $process_cybersource['setup_response']['accessToken'];
                                $dataCyber['deviceDataCollectionURL'] = $process_cybersource['setup_response']['deviceDataCollectionURL'];

                                $lang_request = Yii::$app->language;
                                $array = array(
                                    'checkout_order' => $this->checkout_order,
                                    'transaction' => $this->transaction,
                                    'model' => $model,
                                    'action' => $this->getRequestActionForm(),
                                    'lang_request' => $lang_request,
                                    'data_cyber' => $dataCyber,
                                );
                                return $this->render('payment', $array);
                            } else {
                                $error_message = "PAYMENT_PARTNER";
                            }
                        } else {
                            $error_message = "PAYMENT_SYSTEM";
                        }
                    } else {
                        $error_message = "PAYMENT_FEE";
                    }
                } else {
                    $error_message = "PAYMENT_METHOD_CLS";
                }
            } else {
                $error_message = "PAYMENT_METHOD_INF";
            }
        } else {
            $error_message = "PAYMENT_METHOD";
        }
        $this->redirectWarningPage(Translate::get("Đã xảy ra lỗi trong quá trình thanh toán, vui lòng liên hệ bộ phận hỗ trợ!! " . $error_message));
    }

    public function actionEnrollment()
    {
        $data = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = [
            'status' => false,
            'error_message' => ""
        ];

        if (!in_array($this->checkout_order['status'], array(CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING))) {
            $this->redirectWarningPage('Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!');
        }

        $payment_method = PaymentMethod::findOne(['id' => $this->transaction['payment_method_id']]);
        if ($payment_method) {
            $payment_method_code = $payment_method['code'];
            $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($payment_method_code, Yii::$app->controller->id);
            if ($payment_method_info) {
                $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
                if (class_exists($model_payment_method_name)) {
                    $model = new $model_payment_method_name();
                    $model->set($this->checkout_order['amount'], Yii::$app->controller->id, 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                    $model->active = true;
                    $model->checkout_order = $this->checkout_order;
                    if ($model->getPayerFee() !== false) {
                        $model->load(Yii::$app->request->get());
                        $model->initOption();

                        $partner_payment_account_info['partner_payment_account_info'] = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->checkout_order['merchant_id'], $this->transaction['partner_payment_id']);
                        $card_token_info = LinkCard::findOne(['id' => $this->transaction['card_token_id']]);
                        $token_cybersource = $card_token_info['token_cybersource'];
                        $iv = $card_token_info['iv'];

                        $transaction = $this->transaction;
                        $transaction_id = $transaction['id'];


                        $cybersource3ds2 = new CyberSourceVcb3ds2($partner_payment_account_info);

                        $enrollment = $cybersource3ds2->enrrolmentSubcription([
                            'cashin_id' => $GLOBALS['PREFIX'] . $transaction_id,
                            'cashin_amount' => $this->checkout_order['cashin_amount'],
                            'token' => Encryption::decryptAES($token_cybersource, $GLOBALS['AES_KEY'], $iv),
                            'referenceID' => $data['reference_id'],
                        ]);

                        if (isset($enrollment->payerAuthEnrollReply->eci)) {
                            $eci = $enrollment->payerAuthEnrollReply->eci;
                        } elseif (isset($enrollment->payerAuthEnrollReply->eciRaw)) {
                            $eci = $enrollment->payerAuthEnrollReply->eciRaw;
                        } else {
                            $eci = '';
                        }

                        if (CyberSourceVcb3ds2::checkChallenge($enrollment)) {
                            $result['status'] = true;
                            $result['error_message'] = "";

                            $result['enrollment_info'] = [
                                'challenge' => true,
                                'paReq' => $enrollment->payerAuthEnrollReply->paReq,
                                'acsURL' => $enrollment->payerAuthEnrollReply->acsURL,
                                'authenticationTransactionID' => $enrollment->payerAuthEnrollReply->authenticationTransactionID,
                            ];

                        } elseif (isset($enrollment->payerAuthEnrollReply->paresStatus)
                            && $enrollment->payerAuthEnrollReply->paresStatus == "Y"
                            && $eci != null && !in_array($eci, ["00", "07"])) {
                            $result['status'] = true;
                            $result['error_message'] = "";
                            $result['enrollment_info'] = [
                                'challenge' => false,
                                'authenticationTransactionID' => $enrollment->payerAuthEnrollReply->authenticationTransactionID,
                            ];
                        } elseif (CyberSourceVcb3ds2::isReject($enrollment)) {
                            $result['status'] = false;
                            $result['error_message'] = Translate::get("Lỗi hệ thống, vui lòng thử lại!");
                        }
                    } else {
                        $result['status'] = false;
                        $result['error_message'] = Translate::get("Chưa cấu hình phí thanh toán");
                    }
                } else {
                    $result['status'] = false;
                    $result['error_message'] = Translate::get("Lỗi hệ thống, vui lòng thử lại!");
                }
            } else {
                $result['status'] = false;
                $result['error_message'] = Translate::get("Lỗi hệ thống kênh, vui lòng thử lại!");
            }
        } else {
            $result['status'] = false;
            $result['error_message'] = Translate::get("Lỗi hệ thống phương thức, vui lòng thử lại!");
        }
        return $result;
    }

    private function getResponsePayment($checkout_order_id)
    {
        $response = [];
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", [
            "id = :id", "id" => $checkout_order_id
        ]);
        if ($checkout_order_info != false) {
            if (intval($checkout_order_info['transaction_id']) != 0) {
                $transaction_info = Tables::selectOneDataTable("transaction", [
                    "id = :id AND checkout_order_id = :checkout_order_id ",
                    "id" => $checkout_order_info['transaction_id'],
                    "checkout_order_id" => $checkout_order_id
                ]);
                if ($transaction_info != false) {
                    $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                }
            }
            $response = [
                'token_code' => $checkout_order_info['token_code'],
                'version' => strval($checkout_order_info['version']),
                'order_code' => $checkout_order_info['order_code'],
                'order_description' => $checkout_order_info['order_description'],
                'amount' => $checkout_order_info['amount'],
                'sender_fee' => floatval($checkout_order_info['sender_fee']),
                'receiver_fee' => floatval($checkout_order_info['receiver_fee']),
                'currency' => $checkout_order_info['currency'],
//                'return_url' => $checkout_order_info['return_url'],
//                'cancel_url' => $checkout_order_info['cancel_url'],
//                'notify_url' => $checkout_order_info['notify_url'],
//                'status' => intval($checkout_order_info['status']),
//                'payment_method_code' => @$payment_method_info['code'],
                'payment_method_name' => @$payment_method_info['name'],
            ];
        }
        return $response;
    }


    private function processCybersource($params)
    {
        $error_message = 'Lỗi không xác định';
        $result_code = null;
        $bank_trans_id = null;
        $payerAuthEnrollReply = null;
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $params['transaction_id']]);
        if ($transaction_info != false) {
            if ($params['partner_payment_code'] == 'CYBER-SOURCE-VCB-3DS2') {
                $cybersource3ds2 = new CyberSourceVcb3ds2($params["partner_payment_account_info"]);
                $setup = $cybersource3ds2->authSetupSubcription([
                    'cashin_id' => $GLOBALS['PREFIX'] . $transaction_info['id'],
                    'cashin_amount' => $this->checkout_order['cashin_amount'],
                    'token' => Encryption::decryptAES($params["token_cybersource"], $GLOBALS['AES_KEY'], $params['iv']),
                ]);
                if ($setup['result']->decision == 'ACCEPT' && $setup['result']->reasonCode == '100') {
                    $error_message = '';
                    $bank_trans_id = $setup['result']->requestID;
                    $result_code = 'ACCEPT';
                    $setup_response = [
                        'referenceID' => $setup['result']->payerAuthSetupReply->referenceID,
                        'accessToken' => $setup['result']->payerAuthSetupReply->accessToken,
                        'deviceDataCollectionURL' => $setup['result']->payerAuthSetupReply->deviceDataCollectionURL,
                    ];
                } else {
                    $error_message = CyberSourceVcb3ds2::getErrorMessage($setup['result']->reasonCode);
                    $result_code = 'REJECT';
                }
            }
        }
        return array(
            'error_message' => $error_message,
            'result_code' => $result_code,
            'bank_trans_id' => $bank_trans_id,
            'setup_response' => $setup_response
        );
    }

    public function actionSuccess()
    {
        $this->checkout_order['status'] = CheckoutOrder::findOne(['id' => $this->checkout_order['id']])->status;
        if ($this->checkout_order['status'] != CheckoutOrder::STATUS_PAID && $this->checkout_order['status'] != CheckoutOrder::STATUS_INSTALLMENT_WAIT && $this->checkout_order['status'] != CheckoutOrder::STATUS_REVIEW) {
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
                        'Thông báo giao dịch thành công',
                        'noti_success',
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

                        ], 'layouts/basic', $email_cc
                    );

                }

            }
        }
        return $this->render('success', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
        ));
    }

    public
    function actionReview()
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

    public function actionCancel()
    {
        $this->checkout_order['status'] = CheckoutOrder::findOne(['id' => $this->checkout_order['id']])->status;
        $transaction = Transaction::getTransactionByCheckoutOrderId($this->checkout_order['id']);

        if ($transaction['status'] != Transaction::STATUS_FAILURE) {
            $this->redirectWarningPage("Đơn đặt hàng đang được xử lý thanh toán. Vui lòng hoàn tất thanh toán trong lượt đó hoặc quay lại tạo đơn hàng mới!");
        }
        return $this->render('cancel', array(
            'checkout_order' => $this->checkout_order,
            'transaction' => $this->transaction,
        ));
    }

    public
    function redirectWarningPage($error_message)
    {
        $error_message = urlencode(base64_encode(base64_encode(Translate::get($error_message))));
        $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/warning', 'token_code' => $this->token_code, 'error_message' => $error_message], HTTP_CODE);
        header('Location:' . $url);
        die();
    }

    public
    function actionWarning()
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

    private
    function getRequestActionForm()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/payment', 'token_code' => $this->checkout_order['token_code']]);
    }

    private
    function getPaymentMethodName($payment_method_id)
    {
        $payment_method = PaymentMethod::findOne(['id' => $payment_method_id]);
        if ($payment_method != false && $payment_method->name) {
            return $payment_method->name;
        }

        return '';
    }

    public
    function actionGetLog()
    {
        $date = Yii::$app->request->get('date');
        $key_input = Yii::$app->request->get('key');
        $raw = $this->token_code . '?date=' . $date . '&' . 'key=';

        $folder_support = [
            'cbs_vcb_3ds2-output',
            'cbs_vcb-output',
            'api-card_token',
            'api-checkout-version1.0',
            'api-card_voucher-version1.0',
            'api-checkout-version2.0',
            'api-refund-version1.0',
            'nganluong_seamless',
            'checkout_order_callback',
            'checkout_order-version2.0',
            'vcb',
            'checkout_order_callback-queue',
            'refund-api',
            '3ds2x-cardinal-validated',
            'qrcode_vcb-notify',
            'api-mpos_callback',
            'checkout_order_business',
            'partner_payment-vcb_va',
            'partner_payment-bidv_va',
            'api-partner_callback-bidv-va-get-bill',
            'cks',
        ];

        $folder_get_log = false;

        foreach ($folder_support as $val) {
            $md5_str = md5($raw . $val);
            $val = str_replace("-", "/", $val);
            if ($md5_str == $key_input) {
                $folder_get_log = $val;
                break;
            }
        }


        if (!$folder_get_log) {
            echo "<pre>";
            var_dump("Key invalid");
            die();
        }
        if ($folder_get_log == 'refund/api') {
            $ext = ".json";
        } else {
            $ext = ".txt";
        }
        $path = LOG_PATH . $folder_get_log . DS . $date . $ext;
        $file = fopen($path, 'r');
        $content = "";
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $line = htmlentities($line);
                $line = preg_replace('/^(\[[0-9\s,:\/]+\])/', '<strong class="text-primary">$1</strong>', $line);
                $content .= $line . '<br>';
            }
            fclose($file);
        }

        echo "<pre>";
        var_dump($content);
        die();
    }


}