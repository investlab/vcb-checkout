<?php
namespace api\controllers;

error_reporting(E_ALL);
ini_set('display_errors', true);

use common\api\CheckoutVersion1_0StaticApi;
use common\api\RefundVersion1_0StaticApi;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\Logs;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\PaymentMethod;
use common\models\db\TransactionType;
use common\models\form\CheckoutOrderRefundForm;
use common\models\form\CheckoutOrderWaitRefundForm;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use Yii;
use api\components\ApiController;
use common\api\PayoutVersion1_0StaticApi;
use common\components\utils\ObjInput;
use yii\filters\VerbFilter;

class RefundController extends ApiController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                    'test' => ['post', 'get'],
                ],
            ]
        ];
    }
    //ngan luong goi lai khi cap nhat trang thai hoan tien thanh cong ben ngan luong se update trang thai thanh cong ben cong
    public function actionEncryptData(){
        $key = md5(KEY_STRING);
        $data_in = Yii::$app->request->post();
        echo base64_encode(self::functionEncrypt($key,$data_in));
        die();
    }
    public function actionTest(){
        $this->_writeLogCallback('[DATACALLBACK] ' . file_get_contents('php://input'));
        echo 'ok';die;

    }
    private function checkCheckSum($data){
        $check_sum_in = $data['check_sum'];
        $check_sum_string = $data['refund_type'].'|'.$data['refund_amount'].'|'.$data['refund_reason'].'|'.$data['order_id'].'|'.KEY_CHECKSUM_STRING;
        $check_sum = hash('sha256',$check_sum_string);
        if($check_sum_in == $check_sum){
            return true;
        }
        return false;
    }
    public function actionRequestRefundFromMerchant()
    {
        $data_decrypt = [];
        $error = '';
        $checkout_order_id = null;
        $data_in = Yii::$app->request->post();
        $this->_writeLog('[REQUEST] ' . json_encode($data_in));
        $key = md5(KEY_STRING);
        if(isset($data_in["data"]) && is_string($data_in["data"])){
            $data_decrypt = self::functionDecrypt($key,base64_decode($data_in["data"]));
        }
        $this->_writeLog('[DECRYPT_REQUEST] ' . json_encode($data_decrypt));
        if(!isset($data_decrypt['refund_type']) || !isset($data_decrypt['refund_amount']) || !isset($data_decrypt['refund_reason']) || !isset($data_decrypt['order_id']) || !isset($data_decrypt['check_sum'])){
            if((int)$data_decrypt['refund_amount'] <= 0 || !in_array($data_decrypt['refund_type'],[1,2]) || $data_decrypt['order_id'] == '' || $data_decrypt['refund_reason'] == ''){
                if((int)$data_decrypt['refund_amount'] < 10000){
                    $err = 'Số tiền không hợp lệ !';
                    $this->_writeLog('[RESPONSE] ' . $err);
                    echo $err;
                    die();
                }
                $err = 'Dữ liệu đầu vào không hợp lệ !';
                $this->_writeLog('[RESPONSE] ' . $err);
                echo $err;
                die();
            }
            $err = 'Thiếu dữ liệu đầu vào !';
            $this->_writeLog('[RESPONSE] ' . $err);
            echo $err;
            die();
        }
        $validate_check_sum = self::checkCheckSum($data_decrypt);
        if($validate_check_sum == false){
            $err = 'Mã check_sum không đúng !';
            $this->_writeLog('[RESPONSE] ' . $err);
            echo $err;
            die();
        }
        $checkout_order_id = $data_decrypt['order_id'];
        if (empty($checkout_order_id)) {
            $err = 'Dữ liệu đầu vào không hợp lệ !';
            $this->_writeLog('[RESPONSE] ' . $err);
            echo $err;
            die();
        }
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $checkout_order_id]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            $err = 'ID hoá đơn thanh toán không tồn tại';
            $this->_writeLog('[RESPONSE] ' . $err);
            echo $err;
            die();
        }
        $model->order_id = $checkout_order_id;
        $model->refund_type = $data_decrypt['refund_type'];
        $model->refund_amount = $data_decrypt['refund_amount'];
        $model->refund_reason = $data_decrypt['refund_reason'];
        if ($model->validate()) {
            if ($model->refund_type == $GLOBALS['REFUND_TYPE']['TOTAL']) {
                $refund_amount = $checkout_order['amount'];
            } else {
                $refund_amount = $model->refund_amount;
            }
            $refund_reason = empty($model->refund_reason) ? '' : $model->refund_reason;
            $result_refund = CheckoutOrderBusiness::processRequestRefund([
                'checkout_order' => $checkout_order,
                'refund_type' => $model->refund_type,
                'refund_amount' => $refund_amount,
                'refund_reason' => $refund_reason,
                'user_id' => 0
            ]);
            if ($result_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT']
                || $result_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                $this->_writeLog('[RESPONSE] ' . $result_refund);
                print_r($result_refund);
                die();
            } else {
                echo $result_refund['error_message'];
                die();
            }
        }
        print_r('Tham số đầu vào không hợp lệ');
        die();
    }
    public function actionRefundCallBack()
    {
        $this->writeLog('[REQUEST] ' . json_encode($_REQUEST));
        Logs::writeELKLog($_REQUEST, 'nl-vietcombank-callback', 'INPUT', 'RefundCallBack', '', 'callback/refund');
        $order_code = ObjInput::get('order_code', "str", '');
        $bank_refer_code = ObjInput::get('bank_refer_code', "str", '');
        $check_sum = ObjInput::get('check_sum', "str", '');
        if($order_code != ''){
            $check_sum_key = md5($order_code.$bank_refer_code.'NGANLUONG_2020');
            if($check_sum_key == $check_sum){
                $checkout_order_info = false;
                if ($order_code > 0) {
                    $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['transaction_id = :transaction_id', "transaction_id" => $order_code]);
                }
                if ($checkout_order_info != false) {
                    $params = array(
                        'checkout_order_id' => $checkout_order_info['id'],
                        'time_paid' => time(),
                        'bank_refer_code' => $bank_refer_code,
                        'receiver_fee' => 0,
                        'user_id' => 6789
                    );
                    $result = CheckoutOrderBusiness::updateStatusRefund($params);
                    $this->writeLog('[RESPONSE] ' . json_encode($result));
                    Logs::writeELKLog($result, 'nl-vietcombank-callback', 'RESPONSE', 'RefundCallBack', '', 'callback/refund');
                    if ($result['error_message'] == '') {
                        $response = [
                            'err' => '00',
                            'message' => 'Cap nhat thanh cong',
                        ];
                    } else {
                        $response = [
                            'err' => '01',
                            'message' => $result['error_message'],
                        ];
                    }
                }
            }
            else{
                $response = [
                    'err' => '03',
                    'message' => 'sai checksum',
                ];
            }
        }
        else{
            $response = [
                'err' => '02',
                'message' => 'sai tham so',
            ];
        }
        $this->writeLog('[RETURN] ' . json_encode($response));
        Logs::writeELKLog($response, 'nl-vietcombank-callback', 'OUTPUT', 'RefundCallBack', '', 'callback/refund');

        echo json_encode($response);
    }
    public function actionIndex()
    {
        $has_encrypt = false;
        $function = ObjInput::get('func', 'str', '');
        $obj = new RefundVersion1_0StaticApi();
        if (!empty($_POST)){
            $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        }
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;
        exit();
    }

    private function writeLog($data) {
        $file_name = 'nganluong_call_back_refund' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    private function _writeLog($data) {
        $file_name = 'merchant_request_refund' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }
    private function _writeLogCallback($data) {
        $file_name = 'callback' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
