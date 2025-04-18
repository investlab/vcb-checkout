<?php


namespace app\models\bussiness;


use common\api\RefundVersion1_0StaticApi;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\Merchant;
use common\models\db\PaymentMethod;
use app\models\app\CheckoutVersion1_0StaticApi;
use common\models\db\TransactionType;
use app\models\form\CheckoutOrderWaitRefundForm;

class CheckoutFlow
{
    public $merchant_info;
    public $user_info;
    public $amount;
    public $bank_code;
    public $order_description;

    public function GetBank()
    {

        $rs = PaymentMethod::find()->where(['status' => 1, 'transaction_type_id' => 1])->all();

        $ignore = ["TOKENIZATION", "SWIPE-CARD", "QRCODE_OFFLINE", "ATM-CARD", "IB-ONLINE", "WALLET", "QRCODE247", "CREDIT-CARD", "QR-CODE-STATIC", "94102"];
        $paymentMethod = array();
        foreach ($rs as $k => $v) {
            $arr = explode('-', $v->code);
            $bank_code = $arr[0];
            $method = str_replace($bank_code . '-', '', $v->code);
            if (!in_array($method, $ignore))
                $paymentMethod['QRCODE'][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name, "min_amount" => $v->min_amount);
        }
        if (!empty($paymentMethod)) {
            $error_code = 0;
            $error_message = '';
            $response = $paymentMethod;
        } else {
            $error_code = 10009;
            $error_message = 'Không có dữ liệu';
            $response = [];

        }
        return [
            'error_code' => $error_code,
            'error_message' => $error_message,
            'response' => $response,
        ];
    }

    public function Payment()
    {
        $order_code = 'VCB_MPOS_APP' . uniqid();
        $params = [
            'function' => 'CreateOrder',
            'merchant_site_code' => $this->merchant_info['id'],
            'order_code' => $order_code,
            'order_description' => $this->order_description,
            'amount' => $this->amount,
            'currency' => 'VND',
            'buyer_fullname' => $this->merchant_info['name'],
            'buyer_email' => $this->user_info['email'],
            'buyer_mobile' => $this->user_info['mobile'],
            'buyer_address' => $this->merchant_info['address'],
            'return_url' => \Yii::$app->urlManager->createAbsoluteUrl(['checkout/return', 'key' => $order_code, 'status' => "success"], HTTP_CODE),
            'notify_url' => \Yii::$app->urlManager->createAbsoluteUrl(['checkout/notify_url', 'key' => $order_code], HTTP_CODE),
            'cancel_url' => \Yii::$app->urlManager->createAbsoluteUrl(['checkout/cancel', 'key' => $order_code, 'status' => "cancel"], HTTP_CODE),
            'time_limit' => date('c', time() + 3600),
            'language' => 'vi',
            'method_code' => 'QRCODE',
            'payment_method_code' => $this->bank_code . '-' . 'QR-CODE',
            'bank_code' => $this->bank_code,
            'object_code' => '',
            'object_name' => '',

        ];
        $result = CheckoutVersion1_0StaticApi::_createOrder($params);
        return $result;

    }

    public function WaitRefund($params)
    {
        $result = $this->setRefundApp($params);
        return [
            'error_code' => $result['error_code'],
            'error_message' => $result['error_message'],
            'response' => $result['result_data'],
        ];
    }

    public function setRefundApp($params)
    {
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['token_code = :token_code', "token_code" => $params['token_code']]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            return array('error_code' => '0006');
        }
        $model->order_id = $checkout_order['id'];
        $model->refund_type = $params['refund_type'];
        $model->refund_amount = $params['amount'];
        $model->refund_reason = $params['reason'];
        if ($model->validate()) {
            if ($model->refund_type == $GLOBALS['REFUND_TYPE']['TOTAL']) {
                $refund_amount = $checkout_order['amount'];
            } else {
                $refund_amount = $model->refund_amount;
            }
            $refund_reason = empty($model->refund_reason) ? '' : $model->refund_reason;
            $result_refund = CheckoutOrderBusiness::processRequestRefundAPI([
                'checkout_order' => $checkout_order,
                'refund_type' => $model->refund_type,
                'refund_amount' => $refund_amount,
                'refund_reason' => $refund_reason,
                'user_id' => '0',
                'callback' => '',
                'ref_code_refund' => $params['ref_code_refund'],
            ]);
            if ($result_refund['error_message'] === '') {
                $error_code = '0000';
                $error_message = $this->getResultMessage($error_code);
                $result_data = array(
                    'ref_code_refund' => $params['ref_code_refund'],
                    'amount' => $model->refund_amount,
                    'token_code' => $params['token_code'],
                    'transaction_refund_id' => $result_refund['refund_transaction_id'],
                    'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
                    'checksum' => $params['checksum'],


                );
            } else {
                $error_code = '0007';
                $error_message = $result_refund['error_message'];
                $result_data = array(
                    'ref_code_refund' => $params['ref_code_refund'],
                    'amount' => $model->refund_amount,
                    'token_code' => $params['token_code'],
                    'transaction_refund_id' => $result_refund['refund_transaction_id'],
                    'transaction_status' => $GLOBALS['REFUND_STATUS']['FAIL'],
                    'checksum' => $params['checksum'],


                );
            }
            return array('error_code' => intval($error_code), 'error_message' => $error_message, 'result_data' => $result_data);

        } else {
            foreach ($model->getErrors() as $k => $v) {
                $error_message[] = implode(' | ', $v);
            }
            $error_messages = implode(' | ', $error_message);
            return array('error_code' => 10009, 'error_message' => $error_messages, 'result_data' => []);
        }

    }

    public function cancelPayment($params)
    {
        $error_code = '0001';
        $checkStatusCheckout = CheckoutOrder::findOne(['token_code' => $params['token_code'], 'status' => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
        if ($checkStatusCheckout) {
            $checkStatusCheckout->status = CheckoutOrder::STATUS_CANCEL;
            if ($checkStatusCheckout->save(false)) {
                $error_code = '0000';
            }
        } else {
            $error_code = '0006';
        }
        return array('error_code' => intval($error_code), 'error_message' => $this->getResultMessage($error_code), 'result_data' => []);
    }

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Thành công',
            '0001' => 'Lỗi không xác định',
            '0002' => 'Tên hàm không hợp lệ',
            '0003' => 'Mã merchant_site_code không hợp lệ hoặc không tồn tại',
            '0004' => 'Số tiền không hợp lệ',
            '0005' => 'Mã check_sum không đúng',
            '0006' => 'Mã token_code không hợp lệ',
            '0007' => 'Mã yêu cầu hoàn ref_code_refund không hợp lệ',
            '0008' => 'Mã checksum không chính xác',
            '0009' => 'merchant_email không hợp lệ hoặc không tồn tại',
            '0010' => 'Mã merchant_site_code không thuộc merchant_email',
            '0011' => 'Số tiền hoàn vượt quá số tiền thanh toán',
            '0012' => 'Loại giao dịch hoàn không hợp lệ',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

}