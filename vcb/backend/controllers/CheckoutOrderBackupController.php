<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBackupBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrderBackup;
use common\models\db\MerchantFee;
use common\models\db\PaymentMethod;
use common\models\db\Reason;
use common\models\db\TransactionType;
use common\models\form\CheckoutOrderPaidForm;
use common\models\form\CheckoutOrderRefundForm;
use common\models\form\CheckoutOrderWaitRefundForm;
use common\models\form\ReasonCancelForm;
use common\models\input\CheckoutOrderBackupSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class CheckoutOrderBackupController extends BackendController {

    // Danh sách
    public function actionIndex() {
        $search = new CheckoutOrderBackupSearch();
        $search->setAttributes(Yii::$app->request->get());
        if (!Yii::$app->request->get()) {
            $search->time_created_from = date('d-m-Y');
            $search->time_created_to = date('d-m-Y');
        }
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');
        $payment_method_search_arr = Weblib::createComboTableArray('payment_method', 'id', 'name', 1, Translate::get('Chọn phương thức TT'), true, 'name ASC');
        $partner_payment_search_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 1, Translate::get('Chọn kênh thanh toán'), true, 'name ASC');
        $status_arr = CheckoutOrderBackup::getStatus();
        $callback_status_arr = CheckoutOrderBackup::getCallbackStatus();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'callback_status_arr' => $callback_status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'payment_method_search_arr' => $payment_method_search_arr,
            'partner_payment_search_arr' => $partner_payment_search_arr
        ]);
    }

    // Gọi lại merchant
    public function actionMerchantCallBack() {
        $message = null;
        $search = ['checkout-order-backup/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'checkout_order_id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = CheckoutOrderCallbackBusiness::recall($params, true);
            if ($result['error_message'] == '') {
                $message = 'Gọi lại Merchant thành công';
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = 'Không tồn tại đơn thanh toán';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Chi tiết
    public function actionDetail() {
        $checkout_order_id = ObjInput::get('id', 'int');
        $checkout_order_info = Tables::selectOneDataTable("checkout_order_backup", ["id = :id", "id" => $checkout_order_id]);
        $checkout_order = CheckoutOrderBackup::setRow($checkout_order_info);

        return $this->render('detail', [
            'checkout_order' => $checkout_order
        ]);
    }

    // Cập nhật thanh toán
    public function actionUpdateStatusPaid() {
        $model = new CheckoutOrderPaidForm();
        $checkout_order_id = ObjInput::get('id', "int");
        $checkout_order = array();

        if ($checkout_order_id > 0) {
            $checkout_order_info = Tables::selectOneDataTable('checkout_order_backup', ['id = :id', "id" => $checkout_order_id]);
            if ($checkout_order_info) {
                $checkout_order = CheckoutOrderBackup::setRow($checkout_order_info);
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->id = $checkout_order_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('CheckoutOrderPaidForm');

            $params = array(
                'transaction_id' => $form['transaction_id'],
                'time_paid' => Yii::$app->formatter->asTimestamp($form['time_paid']),
                'bank_refer_code' => $form['bank_refer_code'],
                'user_id' => Yii::$app->user->getId()
            );
            $result = TransactionBusiness::paid($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật thanh toán thành công';
            } else {
                $message = $result['error_message'];
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order-backup/index', HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
        return $this->render('update-status-paid', [
            'model' => $model,
            'checkout_order' => $checkout_order,
        ]);
    }

    // Cập nhật đợi hoàn tiền
    public function actionUpdateStatusWaitRefund() {
        $model = new CheckoutOrderWaitRefundForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        $checkout_order_id = ObjInput::get('id', "int");
        $checkout_order = array();

        if ($checkout_order_id > 0) {
            $checkout_order_info = Tables::selectOneDataTable('checkout_order_backup', ['id = :id', "id" => $checkout_order_id]);
            if ($checkout_order_info) {
                $checkout_order = CheckoutOrderBackup::setRow($checkout_order_info);
            }
        }
        $payment_method_arr = Weblib::createComboTableArray('payment_method', 'id', 'name', 'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getRefundTransactionTypeId(), Translate::get('Chọn phương thức TT'), true);


        $partner_payment_arr = array(
            '0' => Translate::get('Chọn kênh thanh toán')
        );


        $model->id = $checkout_order_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('CheckoutOrderWaitRefundForm');

            $params = array(
                'checkout_order_id' => $form['id'],
                'payment_method_id' => $form['payment_method_id'],
                'partner_payment_id' => $form['partner_payment_id'],
                'partner_payment_method_refer_code' => $form['partner_payment_method_refer_code'],
                'user_id' => Yii::$app->user->getId()
            );
            $result = CheckoutOrderBackupBusiness::updateStatusWaitRefund($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật đợi hoàn tiền thành công';
            } else {
                $message = $result['error_message'];
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order-backup/index', HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
        return $this->render('update-status-wait-refund', [
            'model' => $model,
            'checkout_order' => $checkout_order,
            'payment_method_arr' => $payment_method_arr,
            'partner_payment_arr' => $partner_payment_arr
        ]);
    }

    // Lấy kênh thanh toán theo Phương thức thanh toán
    public function actionGetPartnerPaymentByPaymentMethodId() {
        $option = '';
        $partner_payment_ids = array();
        $payment_method_id = ObjInput::get('payment_method_id', 'int', '');

        if ($payment_method_id > 0) {
            $partner_payment_method = Tables::selectAllDataTable("partner_payment_method", ['payment_method_id = :payment_method_id', 'payment_method_id' => $payment_method_id], "", "id");
            if ($partner_payment_method != false) {
                foreach ($partner_payment_method as $key => $data) {
                    $partner_payment_ids[] = $data['partner_payment_id'];
                }
                if (!empty($partner_payment_ids)) {
                    $partner_payment = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ") AND status = " . PaymentMethod::STATUS_ACTIVE, "", "id");

                    if ($partner_payment != false) {
                        foreach ($partner_payment as $key => $data) {
                            $option .= '<option selected="selected" value="' . $data['id'] . '">' . $data['name'] . '</option>';
                        }
                    } else {
                        $option = '<option selected="selected" value="0">'.Translate::get('Không có kênh thanh toán').'</option>';
                    }
                }
            } else {
                $option = '<option selected="selected" value="0">'.Translate::get('Không có kênh thanh toán').'</option>';
            }
        } else {
            $option = '<option selected="selected" value="0">'.Translate::get('Không có kênh thanh toán').'</option>';
        }
        echo $option;
    }

    // Cập nhật hoàn tiền thành công
    public function actionUpdateStatusRefund() {
        $checkout_order_info = false;
        $checkout_order_id = ObjInput::get('id', "int", 0);
        if ($checkout_order_id > 0) {
            $checkout_order_info = Tables::selectOneDataTable('checkout_order_backup', ['id = :id', "id" => $checkout_order_id]);
        }
        if ($checkout_order_info != false) {
            $model = new CheckoutOrderRefundForm();
            $model->id = $checkout_order_id;
            if (Yii::$app->request->post() && $model->load(Yii::$app->request->post())) {
                $form = Yii::$app->request->post('CheckoutOrderRefundForm');
                $params = array(
                    'checkout_order_id' => $form['id'],
                    'time_paid' => Yii::$app->formatter->asTimestamp($form['time_paid']),
                    'bank_refer_code' => $form['bank_refer_code'],
                    'receiver_fee' => ObjInput::formatCurrencyNumber($form['receiver_fee']),
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CheckoutOrderBackupBusiness::updateStatusRefund($params);
                if ($result['error_message'] == '') {
                    $message = 'Cập nhật hoàn tiền thành công';
                } else {
                    $message = $result['error_message'];
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl('checkout-order-backup/index', HTTP_CODE);
                Weblib::showMessage($message, $url);
            }
            $checkout_order = CheckoutOrderBackup::setRow($checkout_order_info);
            $refund_transaction_id = $checkout_order['refund_transaction_id'];
            $refund_transaction = Tables::selectOneDataTable("transaction", ['id = :id', "id" => $refund_transaction_id]);
            return $this->render('update-status-refund', [
                'model' => $model,
                'checkout_order' => $checkout_order,
                'refund_transaction' => $refund_transaction,
            ]);
        }
    }

    // Hủy hoàn tiền
    public function actionCancelWaitRefund(){
        $model = new ReasonCancelForm();
        $checkout_order_id = ObjInput::get('id', "int");
        $errors = null;
        $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);
        $checkout_order_info = Tables::selectOneDataTable('checkout_order_backup', ['id = :id', "id" => $checkout_order_id]);
        $checkout_order = CheckoutOrderBackup::setRow($checkout_order_info);

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
                $result = CheckoutOrderBackupBusiness::cancelWaitRefund($params);
                if ($result['error_message'] == '') {
                    Weblib::showMessage(Translate::get('Hủy hoàn tiền thành công'), Yii::$app->urlManager->createAbsoluteUrl(['checkout-order-backup/index'], HTTP_CODE), false);
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
            'token_code' => array('title' => Translate::get('Mã token')),
            'order_code' => array('title' => Translate::get('Mã đơn hàng')),
            'encode' => array('title' => Translate::get('Mã hóa đơn NL')),
            'bank_refer_code' => array('title' => Translate::get('Mã tham chiếu')),
            'buyer_fullname' => array('title' => Translate::get('Tên người mua')),
            'buyer_email' => array('title' => Translate::get('Email người mua')),
            'buyer_mobile' => array('title' => Translate::get('SĐT người mua')),
            'order_description' => array('title' => Translate::get('Mô tả đơn hàng')),
            'amount' => array('title' => Translate::get('Số tiền đơn hàng')),
            'receiver_fee' => array('title' => Translate::get('Phí giao dịch')),
            'cashout_amount' => array('title' => Translate::get('Số tiền nhận được')),
            'payment_method_name' => array('title' => Translate::get('Phương thức thanh toán')),
            'time_created' => array('title' => Translate::get('Thời gian tạo'), 'type' => 'time'),
            'time_paid' => array('title' => Translate::get('Thời gian thanh toán'), 'type' => 'time'),
            'status' => array('title' => Translate::get('Trạng thái')),
        );
        //------------
        $search = new CheckoutOrderBackupSearch();
        $search->setAttributes(Yii::$app->request->get());

        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "CHECKOUT_ORDER_BACKUP" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "CHECKOUT_ORDER_BACKUP" . date("d-m-Y-H-i-s") . ".xls";
        }
        //----------
        $obj = new ExportData(200);
        if ($obj->init($file_name, $columns, Yii::$app->user->getId())) {
            $data = $search->searchForExport($obj->getOffset(), $obj->getLimit());
            $result = $obj->process($data);
            $result['error'] = $result['error'];
            echo json_encode($result);
        }
        die();
    }

}
