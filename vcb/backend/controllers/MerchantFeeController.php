<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\MerchantFeeBusiness;
use common\models\db\Merchant;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PaymentMethod;
use common\models\db\TransactionType;
use common\models\form\MerchantFeeForm;
use common\models\input\MerchantFeeSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class MerchantFeeController extends BackendController
{

    // Danh sách
    public function actionIndex()
    {
        $search = new MerchantFeeSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->controller = 'MERCHANT-FEE';
        $page = $search->search();

        $status_arr = MerchantFee::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");
        $method_search_arr = Tables::selectAllDataTable("method");
        $payment_method_search_arr = Tables::selectAllDataTable("payment_method");


        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'method_search_arr' => $method_search_arr,
            'payment_method_search_arr' => $payment_method_search_arr,
            'check_all_operators' => MerchantFee::getOperatorsForCheckAll(),
        ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['merchant-fee/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'merchant_fee_id' => $id,
                'user_id' => Yii::$app->user->getId()

            ];
            $result = MerchantFeeBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa phí thanh toán thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tồn tại phí thanh toán');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Thêm mới
    public function actionAdd()
    {
        $model = new MerchantFeeForm();
        $model->load(Yii::$app->request->get(), '');
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $method_arr = Weblib::createComboTableArray('method', 'id', 'name', 'status = ' . Method::STATUS_ACTIVE, Translate::get('Chọn nhóm thanh toán'), true);
        $payment_method_arr = array();

        $errors = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('MerchantFeeForm');
            $params = array(
                'method_id' => $form['method_id'],
                'payment_method_id' => $form['payment_method_id'],
                'partner_id' => 1,
                'merchant_id' => $form['merchant_id'],
                'min_amount' => ObjInput::formatCurrencyNumber($form['min_amount']),
                'sender_flat_fee' => ObjInput::formatCurrencyNumber($form['sender_flat_fee']),
                'receiver_flat_fee' => ObjInput::formatCurrencyNumber($form['receiver_flat_fee']),
                'sender_percent_fee' => $form['sender_percent_fee'],
                'receiver_percent_fee' => $form['receiver_percent_fee'],
                'time_begin' => Yii::$app->formatter->asTimestamp($form['time_begin']),
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = MerchantFeeBusiness::addAndActive($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm cấu hình phí thanh toán thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl('merchant-fee/index');
                Weblib::showMessage($message, $url);
            } else {
                $errors = Translate::get($result['error_message']);
            }
        }
        return $this->render('add', [
            'model' => $model,
            'errors' => $errors,
            'merchant_arr' => $merchant_arr,
            'method_arr' => $method_arr,
            'payment_method_arr' => $payment_method_arr
        ]);
    }


} 