<?php

namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\components\utils\Utilities;
use common\models\business\TransactionBusiness;
use common\models\db\Reason;
use common\models\db\Transaction;
use common\models\form\TransactionCancelForm;
use common\models\form\TransactionPaidForm;
use common\models\input\TransactionSearch;
use common\util\TextUtil;
use yii\filters\AccessControl;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\TransactionType;
use common\models\db\Merchant;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPayment;

class TransactionController extends BackendController {

    // Danh sách
    public function actionIndex() {
        $search = new TransactionSearch();
        $search->setAttributes(Yii::$app->request->get());
        if (empty($search->time_created_from)) {
            $search->time_created_from = date('d-m-Y');
        }
        if (empty($search->time_created_to)) {
            $search->time_created_to = date('d-m-Y');
        }
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $condition_merchant = 1;
        if (!empty($search->branch_id)) {
            $condition_merchant = ' branch_id = '. $search->branch_id;
        }
        $payment_method_search_arr = Weblib::createComboTableArray('payment_method', 'id', 'name', 1, Translate::get('Chọn phương thức TT'), true, 'name ASC');
        $partner_payment_search_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 1, Translate::get('Chọn kênh thanh toán'), true, 'name ASC');
        $transaction_type_search_arr = Weblib::createComboTableArray('transaction_type', 'id', 'name', 1, Translate::get('Chọn loại giao dịch'), true, 'name ASC');
        $status_arr = Transaction::getStatus();
        $check_all_operators = Transaction::getOperatorsForCheckAll();
        $branch_id  = Yii::$app->user->getIdentity()->branch_id;

        if (!empty($branch_id)){
            $branchs = Weblib::createComboTableArray('branch', 'id', 'name', 'id ='.$branch_id, Translate::get('Chọn chi nhánh'), true, 'name ASC');

        }else{
            $branchs = Weblib::createComboTableArray('branch', 'id', 'name', 1, Translate::get('Chọn chi nhánh'), true, 'name ASC');

        }
        if (!empty($branch_id)){
            $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'branch_id ='.$branch_id, Translate::get('Chọn merchant'), true, 'name ASC');
        }else{
            $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');

        }
        return $this->render('index', [
                    'page' => $page,
                    'search' => $search,
                    'status_arr' => $status_arr,
                    'merchant_search_arr' => $merchant_search_arr,
                    'payment_method_search_arr' => $payment_method_search_arr,
                    'partner_payment_search_arr' => $partner_payment_search_arr,
                    'transaction_type_search_arr' => $transaction_type_search_arr,
                    'check_all_operators' => $check_all_operators,
                    'branchs' => $branchs,
        ]);
    }

    // Cập nhật giao dịch thanh toán
    public function actionPaid() {
        $model = new TransactionPaidForm();
        $transaction_id = ObjInput::get('id', "int");
        $transaction = array();
        $errors = null;
        if (intval($transaction_id) > 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id =:id", "id" => $transaction_id]);
            $transaction = Transaction::setRow($transaction_info);
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->id = $transaction_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('TransactionPaidForm');

            $params = array(
                'transaction_id' => $form['id'],
                'bank_refer_code' => $form['bank_refer_code'],
                'time_paid' => Yii::$app->formatter->asTimestamp($form['time_paid']),
                'user_id' => Yii::$app->user->getId()
            );

            $result = TransactionBusiness::paid($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật giao dịch thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl(['transaction/index','id'=>$form['id']], HTTP_CODE);
                Weblib::showMessage($message, $url);
            } else {
                $errors = Translate::get($result['error_message']);
            }
        }
        return $this->render('paid', [
                    'model' => $model,
                    'transaction' => $transaction,
                    'errors' => $errors
        ]);
    }

    // Hủy giao dịch thanh toán
    public function actionCancel() {
        $model = new TransactionCancelForm();
        $transaction_id = ObjInput::get('id', "int");
        $transaction = array();
        $errors = null;
        $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);

        if (intval($transaction_id) > 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id =:id", "id" => $transaction_id]);
            $transaction = Transaction::setRow($transaction_info);
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->id = $transaction_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('TransactionCancelForm');

            $params = array(
                'transaction_id' => $form['id'],
                'reason_id' => $form['reason_id'],
                'reason' => $form['reason'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = TransactionBusiness::cancel($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Hủy thanh toán giao dịch thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl('transaction/index', HTTP_CODE);
                Weblib::showMessage($message, $url);
            } else {
                $errors = Translate::get($result['error_message']);
            }
        }
        return $this->render('cancel', [
                    'model' => $model,
                    'transaction' => $transaction,
                    'reason_arr' => $reason_arr,
                    'errors' => $errors
        ]);
    }

    // Chi tiết
    public function actionDetail() {
        $transaction_id = ObjInput::get('id', 'int');
        $transaction = array();
        if (intval($transaction_id) > 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            $transaction = Transaction::setRow($transaction_info);
        }
        return $this->render('detail', [
                    'transaction' => $transaction
        ]);
    }

    // Thêm yêu cầu nap tien
    public function actionAddDeposit() {
        $model = new \common\models\form\TransactionForm();
        $model->load(Yii::$app->request->get(), '');
        $errors = null;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $method_code = $model->getMethodCode();



            $params = array(
                'payment_method_id' => $model->payment_method_id,
                'merchant_id' => $model->merchant_id,
                'amount' => ObjInput::formatCurrencyNumber($model->amount),
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'bank_account_code' => $model->bank_account_code,
                'bank_account_name' => $model->bank_account_name,
                'bank_account_branch' => $model->bank_account_branch,
                'bank_card_month' => $model->bank_card_month,
                'bank_card_year' => $model->bank_card_year,
                'partner_payment_id' => $model->partner_payment_id,
                'user_id' => Yii::$app->user->getId(),
                'partner_payment_account_id' => '1',
                'bank_refer_code' => $model->bank_refer_code,
            );

            $result = TransactionBusiness::addDepositTransaction($params);
            if ($result['error_message'] == '') {
                $message = 'Thêm yêu cầu nạp thành công';
                $url = Yii::$app->urlManager->createAbsoluteUrl(['transaction/index', 'transaction_type_id' => TransactionType::getDepositTransactionTypeId()], HTTP_CODE);
                Weblib::showMessage($message, $url);
            } else {
                $errors = $result['error_message'];
                Weblib::showMessage($errors, Yii::$app->urlManager->createAbsoluteUrl(['transaction/add-deposit'], HTTP_CODE));
            }
        }
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $payment_method_arr = Weblib::createComboTableArray('payment_method', 'id', 'name', 'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getDepositTransactionTypeId(), Translate::get('Chọn phương thức nạp'), true);
        $payment_method_id = ObjInput::get('payment_method_id', 'int', 0);
        $partner_payment_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 'status = ' . PartnerPayment::STATUS_ACTIVE .
                        " AND id IN (SELECT partner_payment_id FROM partner_payment_method WHERE
             payment_method_id = " . $payment_method_id . ")", Translate::get('Chọn kênh nạp tiền'), true);

        return $this->render('add-deposit', [
                    'model' => $model,
                    'errors' => $errors,
                    'merchant_arr' => $merchant_arr,
                    'payment_method_arr' => $payment_method_arr,
                    'partner_payment_arr' => $partner_payment_arr
        ]);
    }

    // Xuất excel giao dịch
    public function actionExport()
    {
        $columns = [
            'id' => ['title' => Translate::get('Mã giao dịch')],
            'transaction_type' => ['title' => Translate::get('Loại giao dịch')],
            'partner_name' => ['title' => Translate::get('Kênh thanh toán')],
            'merchant_name' => ['title' => Translate::get('Merchant')],
            'checkout_order_id' => ['title' => Translate::get('Mã đơn hàng')],
            'payment_method_name' => ['title' => Translate::get('Phương thức thanh toán')],
            'partner_payment_method_refer_code' => ['title' => Translate::get('Mã tham chiếu kênh thanh toán')],
            'bank_refer_code' => ['title' => Translate::get('Mã giao dịch bên ngân hàng')],
            'amount' => ['title' => Translate::get('Số tiền giao dịch')],
            // 'sender_fee' => ['title' => Translate::get('Phí người chuyển')],
            // 'receiver_fee' => ['title' => Translate::get('Phí người nhận')],
            // 'partner_payment_sender_fee' => ['title' => Translate::get('Phí kênh thanh toán tính cho người chuyển')],
            // 'partner_payment_receiver_fee' => ['title' => Translate::get('Phí kênh thanh toán tính cho người nhận')],
            'currency' => ['title' => Translate::get('Loại tiền tệ')],
            'status' => ['title' => Translate::get('Trạng thái giao dịch')],
            'refer_transaction_id' => ['title' => Translate::get('Mã giao dịch liên quan')],
            'time_created' => ['title' => Translate::get('Thời gian tạo')],
            'time_updated' => ['title' => Translate::get('Thời gian cập nhật')],
            'time_paid' => ['title' => Translate::get('Thời gian thanh toán')],
            'time_cancel' => ['title' => Translate::get('Thời gian hủy')],
        ];
        //------------
        $search = new TransactionSearch();
        $search->setAttributes(Yii::$app->request->get());
        if (empty($search->time_created_from)) {
            $search->time_created_from = date('d-m-Y');
        }
        if (empty($search->time_created_to)) {
            $search->time_created_to = date('d-m-Y');
        }
        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "TRANSACTION" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "TRANSACTION" . date("d-m-Y-H-i-s") . ".xls";
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
