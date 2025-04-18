<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerPaymentFeeBusiness;
use common\models\db\Merchant;
use common\models\db\Method;
use common\models\db\Partner;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentFee;
use common\models\form\PartnerPaymentFeeForm;
use common\models\input\PartnerPaymentFeeSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerPaymentFeeController extends BackendController {

    // Danh sách
    public function actionIndex()
    {
        $search = new PartnerPaymentFeeSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = PartnerPaymentFee::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");
        $method_search_arr = Tables::selectAllDataTable("method");
        $payment_method_search_arr = Tables::selectAllDataTable("payment_method");
        $partner_payment_search_arr = Tables::selectAllDataTable("partner_payment");
        $partner_search_arr = Tables::selectAllDataTable("partner");


        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'method_search_arr' => $method_search_arr,
            'payment_method_search_arr' => $payment_method_search_arr,
            'partner_payment_search_arr' => $partner_payment_search_arr,
            'partner_search_arr' => $partner_search_arr,
            'check_all_operators' => PartnerPaymentFee::getOperatorsForCheckAll(),
        ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['partner-payment-fee/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'partner_payment_fee_id' => $id,
                'user_id' => Yii::$app->user->getId()

            ];
            $result = PartnerPaymentFeeBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa phí kênh thanh toán thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tồn tại phí kênh thanh toán');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Thêm mới
    public function actionAdd()
    {
        $model = new PartnerPaymentFeeForm();
        $partner_payment_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 'status = ' . PartnerPayment::STATUS_ACTIVE, Translate::get('Chọn kênh thanh toán'), true);
        $partner_arr = Weblib::createComboTableArray('partner', 'id', 'name', 'status = ' . Partner::STATUS_ACTIVE, Translate::get('Chọn đối tác'), true);
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $method_arr = Weblib::createComboTableArray('method', 'id', 'name', 'status = ' . Method::STATUS_ACTIVE, Translate::get('Chọn nhóm thanh toán'), true);
        $payment_method_arr = array();

        $errors = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('PartnerPaymentFeeForm');
            $params = array(
                'method_id' => $form['method_id'],
                'partner_payment_id' => $form['partner_payment_id'],
                'partner_id' => 1,
                'payment_method_id' => $form['payment_method_id'],
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

            $result = PartnerPaymentFeeBusiness::addAndActive($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm phí kênh thanh toán thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl('partner-payment-fee/index');
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
            'payment_method_arr' => $payment_method_arr,
            'partner_payment_arr' => $partner_payment_arr,
            'partner_arr' => $partner_arr,
        ]);
    }

} 