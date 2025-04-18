<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 8/12/2016
 * Time: 9:52 AM
 */

namespace app\controllers;

use app\components\ApiV2Controller;
use app\models\bussiness\CheckoutFlow;
use common\models\db\Merchant;
use Yii;
use api\components\ApiController;
use common\api\CheckoutVersion1_0StaticApi;
use common\components\utils\ObjInput;

class CheckoutController extends ApiV2Controller
{

    public $modelClass = 'app\models\form\CheckoutForm';
    private $_modelFlow;

    public function init()
    {

        parent::init();
        $this->no_validate = ['get-bank', 'return', 'cancel', 'notify', 'request-refund'];
        $this->_modelFlow = new CheckoutFlow;


    }

    public function actionGetBank()
    {
        $result = $this->_modelFlow->GetBank();
        $this->forward($result);

    }

    public function actionPayment()
    {
        $this->_modelFlow->amount = $this->modelForm->amount;
        $this->_modelFlow->bank_code = $this->modelForm->bank_code;
        $this->_modelFlow->order_description = Yii::$app->request->post('order_description');
        $this->_modelFlow->user_info = self::$userInfo;
        $this->_modelFlow->merchant_info = Merchant::getById($this->_modelFlow->user_info['merchant_id']);
        $result = $this->_modelFlow->Payment();
        $this->forward($result);

    }

    public function actionRequestRefund()
    {
        $user_info = self::$userInfo;
        $api_key = Merchant::getById($user_info['merchant_id']);
        $ref_code_paygate_refund = rand(1000, 99999);
        if (Yii::$app->request->post()) {
            $data = [
                'merchant_site_code' => $user_info['merchant_id'],
                'merchant_email' => $user_info['email'],
                'func' => 'setRefundRequest',
                'ref_code_refund' => $ref_code_paygate_refund,
                'amount' => Yii::$app->request->post('amount'),
                'token_code' => Yii::$app->request->post('token_code'),
                'refund_type' => Yii::$app->request->post('refund_type'),
                'reason' => Yii::$app->request->post('reason'),
                'checksum' => hash('sha256', $ref_code_paygate_refund . ' ' . Yii::$app->request->post('token_code') . ' ' . Yii::$app->request->post('amount') . ' ' . $api_key['password']),
            ];
            $result = $this->_modelFlow->WaitRefund($data);
            $this->forward($result);
        }
    }

    public function actionReturn()
    {
//        $key = ObjInput::get('key');
        echo "Success";
    }

    public function actionCancel()
    {
        if (Yii::$app->request->post()) {
            $data = [
                'token_code' => Yii::$app->request->post('token_code')
            ];
            $result = $this->_modelFlow->cancelPayment($data);
            $this->forward($result);
        }
    }

    public function actionNotify()
    {
        echo "Notify";
    }


}
