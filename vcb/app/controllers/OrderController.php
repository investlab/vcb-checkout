<?php


namespace app\controllers;


use app\components\ApiV2Controller;
use app\models\bussiness\OrderFlow;
use common\components\libs\Tables;
use common\models\db\Merchant;
use common\models\db\UserLogin;
use common\models\db\CheckoutOrder;
use Yii;

class OrderController extends ApiV2Controller
{
    public $modelClass = 'api\modules\v2\models\form\TransactionForm';
    private $_modelFlow;
    public $payment_method_info = [];

    public function init() {

        parent::init();
        $this->no_validate = ['history','get-transaction-by-id','get-balance-history','get-notify-transaction-by-id'];
        $this->_modelFlow = new OrderFlow;


    }
    public function actionHistory(){
        $this->_modelFlow->merchant_id = UserLogin::get('merchant_id');
        $this->_modelFlow->user_info = self::$userInfo;
        $this->_modelFlow->page = Yii::$app->request->post('page');
        $this->_modelFlow->size = Yii::$app->request->post('size');
        $this->_modelFlow->token_code = Yii::$app->request->post('token_code');
        $this->_modelFlow->order_code = Yii::$app->request->post('order_code');
        $this->_modelFlow->status = Yii::$app->request->post('status');
        $this->_modelFlow->buyer_info = Yii::$app->request->post('buyer_info');
        $this->_modelFlow->time_created_from = Yii::$app->request->post('time_created_from');
        $this->_modelFlow->time_created_to = Yii::$app->request->post('time_created_to');
        $result = $this->_modelFlow->History();
        $this->forward($result);
    }

    public function actionGetTransactionById()
    {
        $checkout_order_id = Yii::$app->request->post('checkout_order_id');
        $result = $this->_modelFlow->getTransactionById($checkout_order_id);
        $this->forward($result);
    }
}