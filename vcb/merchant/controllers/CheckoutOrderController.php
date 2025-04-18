<?php

namespace merchant\controllers;

use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\business\CashoutBusiness;
use common\models\business\UserLoginBusiness;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\Cashout;
use common\models\db\CheckoutOrder;
use common\models\db\Reason;
use common\models\db\TransactionType;
use common\models\form\MerchantWithdrawCancelForm;
use common\models\form\MerchantWithdrawVerifyForm;
use common\models\form\CheckoutOrderWaitRefundForm;
use common\models\input\CashoutSearch;
use common\models\form\ReasonCancelForm;
use common\models\input\CheckoutOrderSearch;
use Yii;
use merchant\components\MerchantBasicController;
use common\models\db\UserLogin;
use yii\web\Response;
use yii\web\UploadedFile;
use common\components\utils\Translate;
use yii\widgets\ActiveForm;
use common\models\db\PaymentMethod;
use common\models\db\Method;
use common\models\form\CashoutMerchantForm;

class CheckoutOrderController extends MerchantBasicController
{

    // Danh sách giao dịch đơn hàng
    public function actionIndex()
    {

        $search = new CheckoutOrderSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->merchant_id = UserLogin::get('merchant_id');
        $page = $search->search();
        if(UserLogin::hasRight('MERCHANT::CHECKOUT_ORDER::EXPORT')){
            $export_permisson = 1;
        }else{
            $export_permisson = 0;

        }

        if(UserLogin::hasRight('MERCHANT::CHECKOUT_ORDER::REFUND')){
            $refund_permisson = 1;
        }else{
            $refund_permisson = 0;

        }
        $status_arr = CheckoutOrder::getStatus();
        $payment_method_search_arr = Weblib::createComboTableArray('payment_method', 'id', 'name',
            "transaction_type_id = " . TransactionType::getPaymentTransactionTypeId(), Translate::get('Chọn phương thức TT'), true, 'name ASC');

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'export_permisson' => $export_permisson,
            'refund_permisson' => $refund_permisson,
            'payment_method_search_arr' => $payment_method_search_arr
        ]);
    }

    // Chi tiết đơn hàng
    public function actionDetail()
    {
        $checkout_order_id = ObjInput::get('id', 'int', 0);
        $checkout_order_info = false;
        if ($checkout_order_id > 0) {
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND merchant_id = :merchant_id ", "id" => $checkout_order_id, "merchant_id" => UserLogin::get('merchant_id')]);
        }
        if ($checkout_order_info != false) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
            return $this->render('detail', [
                'checkout_order' => $checkout_order
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // Hoàn tiền
    public function actionUpdateStatusWaitRefund() {
        $error = '';
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order_id = ObjInput::get('id', "int");
        if (empty($checkout_order_id)) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index', HTTP_CODE);
            Weblib::showMessage('Lỗi không xác định', $url);
        }
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $checkout_order_id]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index', HTTP_CODE);
            Weblib::showMessage('Lỗi không xác định', $url);
        }
        $model->order_id = $checkout_order_id;
        $refund_type_arr = $model->getRefundType();
        if ($checkout_order['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) {
            unset($refund_type_arr[0]);
            unset($refund_type_arr[$GLOBALS['REFUND_TYPE']['TOTAL']]);
        }
        $payment_method_arr = Weblib::createComboTableArray('payment_method', 'id', 'name', 'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getRefundTransactionTypeId(), Translate::get('Chọn phương thức TT'), true);
        $partner_payment_arr = array(
            '0' => Translate::get('Chọn kênh thanh toán')
        );
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
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
                'user_id' => Yii::$app->user->getId()
            ]);
            if ($result_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT'] 
                    || $result_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                $message = $result_refund['error_message'];
                $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index', HTTP_CODE);
                Weblib::showMessage($message, $url);
            } else {
                $error = $result_refund['error_message'];
            }
        }
        return $this->render('update-status-wait-refund', [
                    'model' => $model,
                    'checkout_order' => $checkout_order,
                    'payment_method_arr' => $payment_method_arr,
                    'partner_payment_arr' => $partner_payment_arr,
                    'refund_type_arr' => $refund_type_arr,
                    'error' => $error
        ]);
    }
    
    // Hủy hoàn tiền
    public function actionCancelWaitRefund(){
        $model = new ReasonCancelForm();
        $checkout_order_id = ObjInput::get('id', "int");
        $errors = null;
        $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $checkout_order_id]);
        $checkout_order = CheckoutOrder::setRow($checkout_order_info);

        $model->id = $checkout_order_id;
        $model->load(Yii::$app->request->get());
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('ReasonCancelForm');

            if ($model->validate()) {
                $params = array(
                    'checkout_order_id' => $form['id'],
                    'reason_id' => $form['reason_id'],
                    'reason' => $form['reason'],
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CheckoutOrderBusiness::cancelWaitRefund($params);
                if ($result['error_message'] == '') {
                    Weblib::showMessage(Translate::get('Hủy hoàn tiền thành công'), Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/index'], HTTP_CODE), false);
                    die();
                } else {
                    $errors = $result['error_message'];
                }
            }
        }

        return $this->render('cancel-wait-refund', [
            'model' => $model,
            'reason_arr' => $reason_arr,
            'errors' => $errors,
            'checkout_order' => $checkout_order
        ]);
    }

    // Xuất excel đơn hàng thanh toán
    public function actionExport()
    {
        $columns = array(
            'buyer_fullname' => array('title' => Translate::get('Tên người mua')),
            'buyer_email' => array('title' => Translate::get('Email người mua')),
            'buyer_mobile' => array('title' => Translate::get('SĐT người mua')),
            'token_code' => array('title' => Translate::get('Mã token')),
            'order_code' => array('title' => Translate::get('Mã đơn hàng')),
            'order_description' => array('title' => Translate::get('Mô tả đơn hàng')),
            'amount' => array('title' => Translate::get('Số tiền đơn hàng'), 'type' => 'number'),
            'receiver_fee' => array('title' => Translate::get('Phí giao dịch'), 'type' => 'number'),
            'cashout_amount' => array('title' => Translate::get('Số tiền nhận được'), 'type' => 'number'),
            'payment_method_name' => array('title' => Translate::get('Phương thức thanh toán')),
            'time_created' => array('title' => Translate::get('Thời gian tạo'), 'type' => 'time'),
            'time_paid' => array('title' => Translate::get('Thời gian thanh toán'), 'type' => 'time'),
            'status' => array('title' => Translate::get('Trạng thái')),
        );
        //------------
        $search = new CheckoutOrderSearch();
        $merchant_id = null;
        $user_login_id = Yii::$app->user->getId();
        if ($user_login_id > 0) {
            $merchant_id = UserLoginBusiness::getMerchantID($user_login_id);
        }
        $search->setAttributes(Yii::$app->request->get());
        $search->merchant_id = $merchant_id;

        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "ORDER" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "ORDER" . date("d-m-Y-H-i-s") . ".xls";
        }
        //----------
        $obj = new ExportData(200);
        if ($obj->init($file_name, $columns, Yii::$app->user->getId())) {
            $data = $search->searchForExport($obj->getOffset(), $obj->getLimit());
            $result = $obj->process($data);
            $result['error'] = Translate::get($result['error']);
            echo json_encode($result);
        }
        die();
    }

    // Danh sách rút tiền
    public function actionWithdraw()
    {
        $search = new CashoutSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->merchant_id = UserLogin::get('merchant_id');
        $search->type = Cashout::TYPE_CHECKOUT_ORDER;
        $page = $search->search();

        $status_arr = Cashout::getStatus();
        $payment_method_search_arr = Weblib::createComboTableArray('payment_method', 'id', 'name',
            "transaction_type_id = " . TransactionType::getWithdrawTransactionTypeId(), Translate::get('Chọn hình thức rút tiền'), true, 'name ASC');

        return $this->render('withdraw', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'payment_method_search_arr' => $payment_method_search_arr
        ]);
    }
    
    // chi tiết rút tiền
    public function actionWithdrawAdd()
    { 
        $error_message = '';
        $model = new CashoutMerchantForm();
        $model->load(Yii::$app->request->get(), '');
        $method_code = $model->getMethodCode();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (Method::isWithdrawIBOffline($method_code)) {
                $partner_payment_data = json_encode(['zone_id' => $model->zone_id]);
            } else {
                $partner_payment_data = '';
            }
            $params = array(
                'payment_method_id' => $model->payment_method_id,
                'merchant_id' => UserLogin::get('merchant_id'),
                'amount' => ObjInput::formatCurrencyNumber($model->amount),
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'bank_account_code' => $model->bank_account_code,
                'bank_account_name' => $model->bank_account_name,
                'bank_account_branch' => $model->bank_account_branch,
                'bank_card_month' => $model->bank_card_month,
                'bank_card_year' => $model->bank_card_year,
                'partner_payment_data' => $partner_payment_data,
                'user_id' => 0,
            );
            $result = CashoutBusiness::addForCheckoutOrder($params);
            if ($result['error_message'] == '') {
                $url = Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-add-success', 'id' => $result['id']], HTTP_CODE);
                header('Location:'.$url);
                die();
            } else {
                $error_message = $result['error_message'];
            }
        }
        $payment_methods = Weblib::createComboTableArray('payment_method', 'id', 'name', 'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getWithdrawTransactionTypeId(), Translate::get('Chọn hình thức rút tiền'), true);
        return $this->render('withdraw-add', [
            'model' => $model,
            'payment_methods' => $payment_methods,
            'method_code' => $method_code,
            'error_message' => $error_message,
        ]);
    }
    
    // chi tiết rút tiền
    public function actionWithdrawAddSuccess()
    {
        $cashout_id = ObjInput::get('id', 'int', 0);
        $cashout_info = false;
        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
        }
        if ($cashout_info != false) {
            $cashout = Cashout::setRow($cashout_info);
            return $this->render('withdraw-add-success', [
                'cashout' => $cashout
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // chi tiết rút tiền
    public function actionWithdrawDetail()
    {
        $cashout_id = ObjInput::get('id', 'int', 0);
        $cashout_info = false;
        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
        }
        if ($cashout_info != false) {
            $cashout = Cashout::setRow($cashout_info);
            return $this->render('withdraw-detail', [
                'cashout' => $cashout
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // Xác nhận yêu cầu rút thành công
    public function actionWithdrawVerify()
    {
        $cashout_id = ObjInput::get('id', 'int', 0);
        $cashout_info = false;
        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
        }
        if ($cashout_info != false) {
            $cashout = Cashout::setRow($cashout_info);
            //-------
            $model = new MerchantWithdrawVerifyForm();
            $model->cashout_id = $cashout_id;
            if ($model->load(Yii::$app->request->post())) {               
                $params = array(
                    'cashout_id' => $model->cashout_id,
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CashoutBusiness::updateStatusVerify($params);
                if ($result['error_message'] == '') {
                    $message = 'Xác nhận yêu cầu rút tiền thành công .';
                } else {
                    $message = $result['error_message'];
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw', HTTP_CODE);
                Weblib::showMessage($message, $url);
            }
            return $this->render('withdraw-verify', [
                'cashout' => $cashout,
                'model' => $model
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // Hủy yêu cầu rút thành công
    public function actionWithdrawCancel()
    {
        $cashout_id = ObjInput::get('id', 'int', 0);
        $cashout_info = false;
        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
        }
        if ($cashout_info != false) {
            $cashout = Cashout::setRow($cashout_info);
            //-------
            $model = new MerchantWithdrawCancelForm();
            $model->cashout_id = $cashout_id;
            if ($model->load(Yii::$app->request->post())) {               
                $params = array(
                    'cashout_id' => $model->cashout_id,
                    'reason_id' => $model->reason_id,
                    'reason' => $model->reason,
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CashoutBusiness::updateStatusCancel($params);
                if ($result['error_message'] == '') {
                    $message = 'Xác nhận yêu cầu rút tiền thành công .';
                } else {
                    $message = $result['error_message'];
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw', HTTP_CODE);
                Weblib::showMessage($message, $url);
            }
            $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);
            return $this->render('withdraw-cancel', [
                'cashout' => $cashout,
                'model' => $model,
                'reason_arr' => $reason_arr,
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // Xuất excel rút tiền
    public function actionWithdrawExport()
    {
        $columns = array(
            'bank_account_code' => array('title' => Translate::get('Số tài khoản')),
            'bank_account_name' => array('title' => Translate::get('Chủ tài khoản')),
            'bank_account_branch' => array('title' => Translate::get('Chi nhánh')),
            'id' => array('title' => Translate::get('Mã yêu cầu')),
            'time_begin' => array('title' => Translate::get('Thời gian bắt đầu'), 'type' => 'time'),
            'time_end' => array('title' => Translate::get('Thời gian kết thúc'), 'type' => 'time'),
            'amount' => array('title' => Translate::get('Số tiền rút')),
            'receiver_fee' => array('title' => Translate::get('Phí giao rút')),
            'cashout_amount' => array('title' => Translate::get('Số tiền nhận được')),
            'payment_method_name' => array('title' => Translate::get('Phương thức thanh toán')),
            'time_created' => array('title' => Translate::get('Thời gian tạo'), 'type' => 'time'),
            'time_accept' => array('title' => Translate::get('Thời gian duyệt'), 'type' => 'time'),
            'time_paid' => array('title' => Translate::get('Thời gian chuyển ngân'), 'type' => 'time'),
            'status' => array('title' => Translate::get('Trạng thái')),
        );
        //------------
        $search = new CashoutSearch();
        $search->setAttributes(Yii::$app->request->get());
        $merchant_id = UserLogin::get('merchant_id');
        $search->merchant_id = $merchant_id;
        $search->type = Cashout::TYPE_CHECKOUT_ORDER;

        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "WITHDRAW" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "WITHDRAW" . date("d-m-Y-H-i-s") . ".xls";
        }
        //----------
        $obj = new ExportData(200);
        if ($obj->init($file_name, $columns, Yii::$app->user->getId())) {
            $data = $search->searchForExport($obj->getOffset(), $obj->getLimit());
            $result = $obj->process($data);
            $result['error'] = Translate::get($result['error']);
            echo json_encode($result);
        }
        die();
    }
}
