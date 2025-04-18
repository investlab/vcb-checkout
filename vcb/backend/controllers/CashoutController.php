<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CashoutBusiness;
use common\models\db\Cashout;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
use common\models\db\Reason;
use common\models\db\TransactionType;
use common\models\form\CashoutForm;
use common\models\form\CashoutPaidForm;
use common\models\form\CashoutWaitAcceptForm;
use common\models\form\ReasonCancelForm;
use common\models\input\CashoutSearch;
use Yii;
use yii\rest\ViewAction;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\payments\NganLuongWithdraw;
use common\payments\NganLuongTransfer;
use common\models\db\Method;
use common\models\form\CashoutImportCheckoutOrderForm;
use common\models\form\CashoutVerifyImportCheckoutOrderForm;
use common\models\business\TransactionBusiness;

class CashoutController extends BackendController
{

    // Danh sách
    public function actionIndex()
    {
        $search = new CashoutSearch();
        $search->setAttributes(Yii::$app->request->get());
        if (empty($search->time_created_from)) {
            $search->time_created_from = date('d-m-Y');
        }
        if (empty($search->time_created_to)) {
            $search->time_created_to = date('d-m-Y');
        }
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');
        $payment_method_search_arr = Weblib::createComboTableArray('payment_method', 'id', 'name',
            "transaction_type_id = ".TransactionType::getWithdrawTransactionTypeId() , Translate::get('Chọn PT rút tiền'), true, 'name ASC');
        $partner_payment_search_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 1, Translate::get('Chọn kênh rút tiền'), true, 'name ASC');
        $bank_search_arr = Weblib::createComboTableArray('bank', 'id', 'name', 1, Translate::get('Chọn ngân hàng'), true, 'name ASC');
        $method_search_arr = Weblib::createComboTableArray('method', 'id', 'name', 1, Translate::get('Chọn nhóm phương thức rút tiền'), true, 'name ASC');
        $status_arr = Cashout::getStatus();

        $type_arr = Cashout::getTypes();
        $check_all_operators = Cashout::getOperatorsForCheckAll();
        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'type_arr' => $type_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'payment_method_search_arr' => $payment_method_search_arr,
            'bank_search_arr' => $bank_search_arr,
            'method_search_arr' => $method_search_arr,
            'partner_payment_search_arr' => $partner_payment_search_arr,
            'check_all_operators' => $check_all_operators,
        ]);
    }

    // Import yêu cầu rút tiền cho đơn hàng
    public function actionImportCheckoutOrder()
    {
        $errors = null;
        $model = new CashoutImportCheckoutOrderForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $import_id = $model->importFile();
                if ($import_id != false) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl(['cashout/verify-import-checkout-order',
                        'import_id' => $import_id,
                        'merchant_id' => $model->merchant_id,
                        'method_id' => $model->method_id,
                    ]);
                    $this->redirect($url);
                }
            }
        }
        return $this->render('import-checkout-order', [
            'model' => $model,
        ]);
    }

    // Xác nhận import yêu cầu rút tiền cho đơn hàng
    public function actionVerifyImportCheckoutOrder()
    {
        $error_message = null;
        $model = new CashoutVerifyImportCheckoutOrderForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->checkFileImport($error_message, $rows, $validate_rows)) {
                    if (!empty($rows)) {
                        $items = array();
                        foreach ($rows as $row) {
                            $items[] = array(
                                'payment_method_id' => intval($row['payment_method_id']),
                                'amount' => floatval($row['amount']),
                                'currency' => $GLOBALS['CURRENCY']['VND'],
                                'bank_account_code' => strval($row['account_number']),
                                'bank_account_name' => strval($row['account_name']),
                                'bank_account_branch' => strval($row['branch_name']),
                                'bank_card_month' => strval(@$row['card_month']),
                                'bank_card_year' => strval(@$row['card_year']),
                                'partner_payment_data' => json_encode(['zone_id' => $row['zone_id']]),
                            );
                        }
                        $params = array(
                            'merchant_id' => $model->merchant_id,
                            'items' => $items,
                            'user_id' => Yii::$app->user->getId()
                        );
                        $result = CashoutBusiness::addMultiForCheckoutOrder($params);
                        if ($result['error_message'] == '') {
                            $message = 'Thêm yêu cầu rút tiền cho đơn hàng thành công';
                            $url = Yii::$app->urlManager->createAbsoluteUrl('cashout/index');
                            Weblib::showMessage($message, $url);
                        } else {
                            $error_message = $result['error_message'];
                        }
                    }
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        }
        $model->load(Yii::$app->request->get(), '');
        $model->checkFileImport($error_message, $rows, $validate_rows);
        return $this->render('verify-import-checkout-order', [
            'model' => $model,
            'error_message' => $error_message,
            'rows' => $rows,
            'validate_rows' => $validate_rows,
        ]);
    }

    // Thêm yêu cầu rút tiền cho đơn hàng
    public function actionAddCheckoutOrder()
    {
        $model = new CashoutForm();
        $model->load(Yii::$app->request->get(), '');
        $errors = null;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $method_code = $model->getMethodCode();

            if(Method::isWithdrawIBOffline($method_code)){
                $partner_payment_data = json_encode(['zone_id' => $model->zone_id]);
            }else{
                $partner_payment_data = '';
            }


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
                'partner_payment_data' => $partner_payment_data,
                'user_id' => Yii::$app->user->getId()
            );

            $result = CashoutBusiness::addForCheckoutOrder($params);
            if ($result['error_message'] == '') {
                $message = 'Thêm yêu cầu rút tiền cho đơn hàng thành công';
                $url = Yii::$app->urlManager->createAbsoluteUrl('cashout/index');
                Weblib::showMessage($message, $url);
            } else {
                $errors = $result['error_message'];
            }
        }
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $payment_method_arr = Weblib::createComboTableArray('payment_method', 'id', 'name',
            'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getWithdrawTransactionTypeId(),
            Translate::get('Chọn phương thức TT'), true);
        return $this->render('add-checkout-order', [
            'model' => $model,
            'errors' => $errors,
            'merchant_arr' => $merchant_arr,
            'payment_method_arr' => $payment_method_arr,
        ]);
    }

    // Thêm yêu cầu rút tiền cho đơn hàng
    public function actionAddCardTransaction()
    {
        $model = new CashoutForm();
        $model->load(Yii::$app->request->get(), '');
        $errors = null;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $form = Yii::$app->request->post('CashoutForm');
            $params = array(
                'payment_method_id' => $model->payment_method_id,
                'merchant_id' => $model->merchant_id,
                'time_begin' => Yii::$app->formatter->asTimestamp($model->time_begin),
                'time_end' => Yii::$app->formatter->asTimestamp($model->time_end),
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'bank_account_code' => $model->bank_account_code,
                'bank_account_name' => $model->bank_account_name,
                'bank_account_branch' => $model->bank_account_branch,
                'bank_card_month' => $model->bank_card_month,
                'bank_card_year' => $model->bank_card_year,
                'user_id' => Yii::$app->user->getId()
            );

            $result = CashoutBusiness::addForCardTransaction($params);
            if ($result['error_message'] == '') {
                $message = 'Thêm yêu cầu rút tiền thành công';
                $url = Yii::$app->urlManager->createAbsoluteUrl('cashout/index');
                Weblib::showMessage($message, $url);
            } else {
                $errors = Translate::get($result['error_message']);
            }
        }
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $payment_method_arr = Weblib::createComboTableArray('payment_method', 'id', 'name',
            'status = ' . PaymentMethod::STATUS_ACTIVE . ' AND transaction_type_id = ' . TransactionType::getWithdrawTransactionTypeId(),
            Translate::get('Chọn phương thức TT'), true);
        return $this->render('add-card-transaction', [
            'model' => $model,
            'errors' => $errors,
            'merchant_arr' => $merchant_arr,
            'payment_method_arr' => $payment_method_arr,
        ]);
    }

    // lấy METHOD CODE
    public function actionGetMethodCode()
    {
        $payment_method_id = ObjInput::get('payment_method_id', 'int', '');
        if ($payment_method_id > 0) {
            $method_payment_method = Tables::selectOneDataTable("method_payment_method",
                ["payment_method_id = :payment_method_id",
                    "payment_method_id" => $payment_method_id]);

            if ($method_payment_method) {
                $method_id = $method_payment_method['method_id'];
                $method = Tables::selectOneDataTable("method", ["id = :id", "id" => $method_id]);
                $code = $method['code'];
                return $code;
            }
        }
    }

    // Thông báo merchant xác nhận YC rút tiền
    public function actionUpdateStatusWaitVerify()
    {
        $message = null;
        $search = ['cashout/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'cashout_id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = CashoutBusiness::updateStatusWaitVerify($params, true);
            if ($result['error_message'] == '') {
                $message = 'Thông báo merchant xác nhận YC rút tiền thành công';
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = 'Không tồn tại yêu cầu rút tiền';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    // Xác nhận YC rút tiền
    public function actionUpdateStatusVerify()
    {
        $message = null;
        $search = ['cashout/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'cashout_id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = CashoutBusiness::updateStatusVerify($params, true);
            if ($result['error_message'] == '') {
                $message = 'Xác nhận YC rút tiền thành công';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại yêu cầu rút tiền';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    // Duyệt YC rút tiền
    public function actionUpdateStatusAccept()
    {
        $message = null;
        $search = ['cashout/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'cashout_id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = CashoutBusiness::updateStatusAccept($params, true);
            if ($result['error_message'] == '') {
                $message = 'Duyệt YC rút tiền thành công';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại yêu cầu rút tiền';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    // Hủy yêu cầu rút tiền
    public function actionUpdateStatusCancel()
    {
        $model = new ReasonCancelForm();
        $cashout_id = ObjInput::get('id', "int");
        $errors = null;
        $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);
        $cashout_info = Tables::selectOneDataTable('cashout', ['id = :id', "id" => $cashout_id]);
        $cashout = Cashout::setRow($cashout_info);

        $model->id = $cashout_id;
        $model->load(Yii::$app->request->get());
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('ReasonCancelForm');

            if ($model->validate()) {
                $params = array(
                    'cashout_id' => $form['id'],
                    'reason_id' => $form['reason_id'],
                    'reason' => $form['reason'],
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CashoutBusiness::updateStatusCancel($params);
                if ($result['error_message'] == '') {
                    Weblib::showMessage('Hủy yêu cầu rút tiền thành công', Yii::$app->urlManager->createAbsoluteUrl(['cashout/index']), false);
                    die();
                } else {
                    $errors = $result['error_message'];
                }
            }
        }

        return $this->render('update-status-cancel', [
            'model' => $model,
            'reason_arr' => $reason_arr,
            'errors' => $errors,
            'cashout' => $cashout
        ]);
    }

    // Từ chối yêu cầu rút tiền
    public function actionUpdateStatusReject()
    {
        $model = new ReasonCancelForm();
        $cashout_id = ObjInput::get('id', "int");
        $errors = null;
        $reason_arr = Weblib::createComboTableArray('reason', 'id', 'name', 'status = ' . Reason::STATUS_ACTIVE, Translate::get('Chọn lý do hủy'), true);
        $cashout_info = Tables::selectOneDataTable('cashout', ['id = :id', "id" => $cashout_id]);
        $cashout = Cashout::setRow($cashout_info);

        $model->id = $cashout_id;
        $model->load(Yii::$app->request->get());
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('ReasonCancelForm');

            if ($model->validate()) {
                $params = array(
                    'cashout_id' => $form['id'],
                    'reason_id' => $form['reason_id'],
                    'reason' => $form['reason'],
                    'user_id' => Yii::$app->user->getId()
                );
                $result = CashoutBusiness::updateStatusReject($params);
                if ($result['error_message'] == '') {
                    Weblib::showMessage('Từ chối yêu cầu rút tiền thành công', Yii::$app->urlManager->createAbsoluteUrl(['cashout/index']), false);
                    die();
                } else {
                    $errors = $result['error_message'];
                }
            }
        }

        return $this->render('update-status-reject', [
            'model' => $model,
            'reason_arr' => $reason_arr,
            'errors' => $errors,
            'cashout' => $cashout,
        ]);
    }

    // Gửi duyệt yêu cầu rút tiền
    public function actionUpdateStatusWaitAccept()
    {
        $model = new CashoutWaitAcceptForm();
        $cashout_id = ObjInput::get('id', "int");
        $errors = null;

        $cashout_info = Tables::selectOneDataTable('cashout', ['id = :id', "id" => $cashout_id]);
        $cashout = Cashout::setRow($cashout_info);
        $payment_method_id = $cashout['payment_method_id'];
        $partner_payment_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name',
            'status = ' . PartnerPayment::STATUS_ACTIVE .
            " AND id IN (SELECT partner_payment_id FROM partner_payment_method WHERE
             payment_method_id = " . $payment_method_id . ")", Translate::get('Chọn kênh rút tiền'), true);

        $model->id = $cashout_id;
        $model->load(Yii::$app->request->get());
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('CashoutWaitAcceptForm');
            if ($model->validate()) {
                $all = true;
                if ($form['partner_payment_id'] == PartnerPayment::getIdByCode('NGANLUONG')) {
                    $inputs = array(
                        'merchant_id' => $cashout['merchant_id'],
                        'partner_payment_id' => $form['partner_payment_id'],
                        'currency' => $cashout['currency'],
                        'user_id' => Yii::$app->user->getId(),
                    );
                    $result = \common\models\business\PartnerPaymentAccountBusiness::updatePartnerPaymentBalanceByMerchant($inputs);
                    if ($result['error_message'] != '') {
                        $errors = Translate::get($result['error_message']);
                        $all = false;
                    }
                }
                if ($all) {
                    $params = array(
                        'cashout_id' => $form['id'],
                        'partner_payment_id' => $form['partner_payment_id'],
                        'user_id' => Yii::$app->user->getId()
                    );
                    $result = CashoutBusiness::updateStatusWaitAccept($params);
                    if ($result['error_message'] == '') {
                        if (CALL_API_WITHDRAW) {
                            $result = $this->_processRequestWithdrawByPartnerPayment($cashout_id);
                            if ($result['error_message'] == '') {
                                Weblib::showMessage('Gửi duyệt yêu cầu rút tiền thành công', Yii::$app->urlManager->createAbsoluteUrl(['cashout/index']), false);
                                die();
                            } else {
                                Weblib::showMessage('Có lỗi khi gửi yêu cầu rút tiền tự động', Yii::$app->urlManager->createAbsoluteUrl(['cashout/index']), true);
                                die();
                            }
                        } else {
                            Weblib::showMessage('Đã cập nhật trạng thái gửi duyệt yêu cầu rút tiền', Yii::$app->urlManager->createAbsoluteUrl(['cashout/index']), false);
                            die();
                        }
                    } else {
                        $errors = Translate::get($result['error_message']);
                    }
                }
            }
        }

        return $this->render('update-status-wait-accept', [
            'model' => $model,
            'partner_payment_arr' => $partner_payment_arr,
            'errors' => $errors,
            'cashout' => $cashout,
        ]);
    }

    private function _processRequestWithdrawByPartnerPayment($cashout_id) {
        $error_message = 'Lỗi không xác định';
        $partner_payment_refer_code = null;
        //-----------
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status ", "id" => $cashout_id, "status" => Cashout::STATUS_WAIT_ACCEPT]);
        if ($cashout_info != false) {
            $cashout_info = Cashout::setRow($cashout_info);
            if (Method::isWithdrawIBOffline($cashout_info['method_info']['code']) && $cashout_info['partner_payment_info']['code'] == 'NGANLUONG') {
                $withdraw_transactions = Cashout::getWithdrawTransactionIsPaying($cashout_id);
                if ($withdraw_transactions != false) {
                    $authorization_reference_code = Cashout::getNganLuongAuthorizationReferenceCode($withdraw_transactions);
                    $nganluong_accounts = $this->_getNganLuongAccountsForWithdraw($withdraw_transactions, $total_amount, $withdraw_transaction_maps);
                    if (!empty($nganluong_accounts)) {
                        $withdraw_info = NganLuongWithdraw::getAccountTypeAndBankCode($cashout_info['payment_method_info']['code']);
                        $inputs = array(
                            'authorization_reference_code' => $authorization_reference_code,
                            'total_amount' => $total_amount,
                            'nganluong_accounts' => $nganluong_accounts,
                            'bank_code' => $withdraw_info['bank_code'],
                            'account_type' => $withdraw_info['account_type'],
                            'card_number' => $cashout_info['bank_account_code'],
                            'card_fullname' => $cashout_info['bank_account_name'],
                            'branch_name' => $cashout_info['bank_account_branch'],
                            'zone_id' => intval(@$cashout_info['partner_payment_data']['zone_id']),
                            'reason' => 'Yêu cầu rút tiền '.$cashout_info['id'],
                        );
                        $result = NganLuongWithdraw::CreateRequest($inputs);
                        if ($result['error_code'] === '00') {
                            $all = true;
                            if ($result['transaction_status'] == '00' || $result['transaction_status'] == '01') {
                                foreach ($result['transaction'] as $row) {
                                    if (isset($withdraw_transaction_maps[$row['reference_code']]) && $withdraw_transaction_maps[$row['reference_code']]['amount'] == $row['amount']) {
                                        $inputs = array(
                                            'transaction_id' => @$withdraw_transaction_maps[$row['reference_code']]['id'],
                                            'bank_refer_code' => strval($row['transaction_id']),
                                            'user_id' => Yii::$app->user->getId(),
                                        );
                                        $result = TransactionBusiness::updateBankReferCode($inputs);
                                        if ($result['error_message'] != '') {
                                            $all = false;
                                            $error_message = $result['error_message'];
                                            break;
                                        }
                                    } else {
                                        $all = false;
                                        $error_message = 'Có lỗi khi gửi yêu cầu rút tiền sang Ngân Lượng';
                                        break;
                                    }
                                }
                                if ($all) {
                                    $error_message = '';
                                }
                            } else {
                                $error_message = 'Có lỗi khi gửi yêu cầu rút tiền sang Ngân Lượng';
                            }
                        } else {
                            $error_message = NganLuongWithdraw::getErrorMessage($result['error_code']);
                        }
                    } else {
                        $error_message = 'Không tìm thấy thông tin tài khoản Ngân Lượng để rút';
                    }
                } else {
                    $error_message = 'Lỗi hệ thống, dữ liệu không hợp lệ';
                }
            } elseif (Method::isWithdrawWallet($cashout_info['method_info']['code']) && $cashout_info['partner_payment_info']['code'] == 'NGANLUONG') {
                $withdraw_transactions = Cashout::getWithdrawTransactionIsPaying($cashout_id);
                if ($withdraw_transactions != false) {
                    $authorization_reference_code = Cashout::getNganLuongAuthorizationReferenceCode($withdraw_transactions);
                    $nganluong_accounts = $this->_getNganLuongAccountsForTransfer($withdraw_transactions, $total_amount, $withdraw_transaction_maps);
                    if (!empty($nganluong_accounts)) {
                        $inputs = array(
                            'receive_email' => $cashout_info['bank_account_code'],
                            'amount' => $total_amount,
                            'reference_code' => $authorization_reference_code,
                            'nganluong_transfer_accounts' => $nganluong_accounts,
                        );
                        $result = NganLuongTransfer::tranfer($inputs);
                        if ($result['response_code'] === 'E00') {
                            $transactions = $result['response']['transactions'];
                            $request_id = strval($result['response']['request_id']);
                            if (!empty($transactions)) {
                                $all = true;
                                foreach ($transactions as $row) {
                                    if (isset($withdraw_transaction_maps[$row['sender_email']])) {
                                        $inputs = array(
                                            'transaction_id' => @$withdraw_transaction_maps[$row['sender_email']]['id'],
                                            'bank_refer_code' => strval($row['transaction_id']),
                                            'partner_payment_receiver_fee' => $row['receiver_fee'],
                                            'user_id' => Yii::$app->user->getId(),
                                        );
                                        $result = TransactionBusiness::updateBankReferCode($inputs);
                                        if ($result['error_message'] != '') {
                                            $all = false;
                                            $error_message = $result['error_message'];
                                            break;
                                        }
                                    } else {
                                        $all = false;
                                        $error_message = 'Có lỗi khi gửi yêu cầu rút tiền sang Ngân Lượng';
                                        break;
                                    }
                                }
                                if ($all) {
                                    $inputs = array(
                                        'cashout_id' => $cashout_info['id'],
                                        'time_paid' => time(),
                                        'bank_refer_code' => $request_id,
                                        'user_id' => 0,
                                    );
                                    $result = CashoutBusiness::updateStatusAcceptAndPaid($inputs);
                                    if ($result['error_message'] == '') {
                                        $error_message = '';
                                    } else {
                                        $error_message = $result['error_message'];
                                    }
                                }
                            } else {
                                $error_message = 'Lỗi kết nối với Ngân Lượng';
                            }
                        } else {
                            $error_message = NganLuongTransfer::getErrorMessage($result['response_code']);
                        }
                    } else {
                        $error_message = 'Không tìm thấy thông tin tài khoản Ngân Lượng để rút';
                    }
                } else {
                    $error_message = 'Lỗi hệ thống, dữ liệu không hợp lệ';
                }
            } else {
                $error_message = '';
            }
        } else {
            $error_message = 'Yêu cầu rút tiền không hợp lệ';
        }
        return array('error_message' => $error_message, 'partner_payment_refer_code' => $partner_payment_refer_code);

    }

    private function _getNganLuongAccountsForWithdraw($withdraw_transactions, &$total_amount = 0, &$withdraw_transaction_maps = array()) {
        $nganluong_accounts = array();
        $partner_payment_account_ids = array();
        foreach ($withdraw_transactions as $row) {
            $partner_payment_account_ids[$row['partner_payment_account_id']] = $row['partner_payment_account_id'];
        }
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["id IN (:ids)", "ids" => $partner_payment_account_ids], "id ASC", "id");
        foreach ($withdraw_transactions as $row) {
            $amount = $row['amount'];
            $reference_code = $GLOBALS['PREFIX'].$row['id'];
            $nganluong_accounts[] = array(
                'reference_code' => $reference_code,
                'receiver_email' => @$partner_payment_account_info[$row['partner_payment_account_id']]['partner_payment_account'],
                'amount' => $amount,
            );
            $withdraw_transaction_maps[$reference_code] = array(
                'id' => $row['id'],
                'amount' => $amount,
            );
            $total_amount+= $amount;
        }
        return $nganluong_accounts;
    }

    private function _getNganLuongAccountsForTransfer($withdraw_transactions, &$total_amount = 0, &$withdraw_transaction_maps = array()) {
        $nganluong_accounts = array();
        $partner_payment_account_ids = array();
        foreach ($withdraw_transactions as $row) {
            $partner_payment_account_ids[$row['partner_payment_account_id']] = $row['partner_payment_account_id'];
        }
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["id IN (:ids)", "ids" => $partner_payment_account_ids], "id ASC", "id");
        foreach ($withdraw_transactions as $row) {
            $amount = $row['amount'];
            $sender_email = @$partner_payment_account_info[$row['partner_payment_account_id']]['partner_payment_account'];
            $nganluong_accounts[] = array(
                'sender_email' => $sender_email,
                'amount' => $amount,
            );
            $withdraw_transaction_maps[$sender_email] = array(
                'id' => $row['id'],
                'amount' => $amount,
            );
            $total_amount+= $amount;
        }
        return $nganluong_accounts;
    }

    // Cập nhật đã chuyển ngân
    public function actionUpdateStatusPaid()
    {
        $model = new CashoutPaidForm();
        $cashout_id = ObjInput::get('id', "int");
        $cashout = array();

        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ['id = :id', "id" => $cashout_id]);
            if ($cashout_info) {
                $cashout = Cashout::setRow($cashout_info);
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->id = $cashout_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('CashoutPaidForm');

            $params = array(
                'cashout_id' => $form['id'],
                'time_paid' => Yii::$app->formatter->asTimestamp($form['time_paid']),
                'bank_refer_code' => $form['bank_refer_code'],
                'receiver_fee' => ObjInput::formatCurrencyNumber($form['receiver_fee']),
                'user_id' => Yii::$app->user->getId()
            );
            $result = CashoutBusiness::updateStatusPaid($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật chuyển ngân yêu cầu rút tiền thành công';
            } else {
                $message = $result['error_message'];
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('cashout/index');
            Weblib::showMessage($message, $url);
        }
        return $this->render('update-status-paid', [
            'model' => $model,
            'cashout' => $cashout,
        ]);
    }

    // Chi tiết
    public function actionDetail()
    {
        $cashout_id = ObjInput::get('id', 'int');
        $cashout = null;
        if (isset($cashout_id) && intval($cashout_id) > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ['id = :id', "id" => $cashout_id]);
            $cashout = Cashout::setRow($cashout_info);
        }

        return $this->render('detail', [
            'cashout' => $cashout
        ]);
    }

} 