<?php

namespace checkout\controllers;

use common\components\libs\qrcode\QrCode;
use common\components\libs\Weblib;
use common\components\utils\Logs;
use common\models\db\Zone;
use checkout\components\MerchantCheckoutController;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;
use yii\filters\AccessControl;
use common\components\utils\Strings;
use common\components\libs\Tables;
use common\models\db\Method;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\PaymentMethod;
use common\models\business\PaymentMethodBusiness;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\Transaction;
use common\models\db\CheckoutOrderCallback;
use common\components\utils\Translate;

class Version_2_0Controller extends MerchantCheckoutController
{

    public $layout = 'version_1_1';
    public $paymentMethod = null;
    public $paymentMethodCode = null;

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

    public function actionTransactionDestroy($token_code = '')
    {
        $checkStatusCheckout = CheckoutOrder::findOne(['token_code' => $token_code, 'status' => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
        if ($checkStatusCheckout) {
            $checkStatusCheckout->status = CheckoutOrder::STATUS_CANCEL;
            if ($checkStatusCheckout->save(false)) {
                if ($this->checkout_order['notify_url'] != '') {
                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $this->checkout_order['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
                    // print_r($checkout_order_callback_info); exit();
                    if ($checkout_order_callback_info != false) {
                        CheckoutOrderCallback::process($checkout_order_callback_info);
                    }
                }
                return $this->redirect($checkStatusCheckout['cancel_url']);
            }
        }
    }

    public function actionNotify()
    {
        header('Location:' . $this->checkout_order['notify_url']);
        die();
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
            }
        }
        return true;
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

    public function actionIndex()
    {
        if ($this->checkout_order['time_limit'] <= time()) {
            $this->redirectWarningPage('Đơn đặt hàng đã hết hạn thanh toán. Vui lòng hoàn quay lại tạo đơn hàng mới!');
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

        //check version seamless  version = 2.0
        $data_qr = '';
        $message = 'Lỗi không xác định';
        $code = '0001';
        if ($this->checkout_order['version'] == '2.0') {
            $payment_method_code = ObjInput::get('payment_method_code', 'str', '');
            $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($payment_method_code, 'version_1_0', $this->checkout_order['merchant_id']);
            if ($payment_method_info != false) {
                //var_dump($payment_method_info);
                $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
                //echo $model_payment_method_name.'<br>';
                if (class_exists($model_payment_method_name)) {
                    $model = new $model_payment_method_name();
                    $model->set($this->checkout_order['amount'], 'version_1_0', 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                    $model->active = true;
                    $model->checkout_order = $this->checkout_order;

                    if ($model->getPayerFee() !== false) {
                        $model->load(Yii::$app->request->get());
                        $flag = $model->initOption();
                        @self::writeLog('[INPUT]' . json_encode($model->checkout_order));
                        //var_dump($model->checkout_order);
                        $message = $model->error_message;
                        @self::writeLog('[RESULT]' . $message);
                        if (!empty($model->checkout_order['qrcode'])) {
                            $code = '0000';
                            $message = 'Thành công';
                            if ($payment_method_info['partner_payment_code'] == 'VCB' && $payment_method_info['method_code'] == 'QR-CODE') {
                                $data_qr = self::genQRcode($model->checkout_order['qrcode']['data']);
                            } else {
                                $data_qr = $model->checkout_order['qrcode'];
                            }

                        }
                        // $data_qr = $this->checkout_order['qrcode'];
                    } else {
                        $code = '0005';
                        //die('Chưa cấu hình phí cho phương thức thanh toán này');
                        $message = 'Chưa cấu hình phí cho phương thức thanh toán này';
                    }
                }
            } else {
                $code = '0004';
                $message = 'Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng';
                //$this->redirectErrorPage('Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng');
            }
        }
        return \Yii::createObject([
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'data' => [
                "result_code" => $code,
                "result_message" => Translate::get($message),
                "result_data" => array('data_qr' => $data_qr)
            ],
        ]);
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
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
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

    public static function writeLog($data)
    {
        $file_name = 'checkout_order' . DS . 'version2.0' . DS . date("Ymd") . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
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
        if (ob_get_contents()) ob_end_clean();
        return $imageString;
    }

}
