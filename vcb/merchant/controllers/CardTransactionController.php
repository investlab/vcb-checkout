<?php

namespace merchant\controllers;

use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\business\CashoutBusiness;
use common\models\business\UserLoginBusiness;
use common\models\db\Cashout;
use common\models\db\Reason;
use common\models\db\TransactionType;
use common\models\form\MerchantWithdrawCancelForm;
use common\models\form\MerchantWithdrawVerifyForm;
use common\models\input\CardLogSearch;
use common\models\input\CashoutSearch;
use Yii;
use merchant\components\MerchantBasicController;
use common\models\db\UserLogin;
use yii\web\Response;
use yii\web\UploadedFile;
use common\components\utils\Translate;
use yii\widgets\ActiveForm;
use common\models\db\CardTransaction;
use common\models\db\CardLogFull;
use common\components\utils\Validation;
use common\components\utils\FormatDateTime;

class CardTransactionController extends MerchantBasicController
{

    // Thong ke san luong
    public function actionIndex()
    {
        $date_begin = ObjInput::get('date_begin', 'str', date('d-m-Y'));
        $date_end = ObjInput::get('date_end', 'str', date('d-m-Y'));
        $data = null;
        $error_message = '';
        if (Validation::isDate($date_begin) && Validation::isDate($date_end)) {
            $time_begin = FormatDateTime::toTimeBegin($date_begin);
            $time_end = FormatDateTime::toTimeEnd($date_end);
            $data = CardTransaction::getDataReport(UserLogin::get('merchant_id'), $time_begin, $time_end);
        } else {
            $error_message = 'Định dạng ngày tháng không đúng';
        }
        return $this->render('index', [
            'date_begin' => $date_begin,
            'date_end' => $date_end,
            'error_message' => $error_message,
            'data' => $data,
        ]);
    }

    // Tra cuu giao dich
    public function actionSearch()
    {

        $merchant_id = null;
        $user_login_id = Yii::$app->user->getId();
        if ($user_login_id > 0) {
            $merchant_id = UserLoginBusiness::getMerchantID($user_login_id);
        }
        $search = new CardLogSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->merchant_id = $merchant_id;
        $page = $search->search();

        $card_type_search_arr = Weblib::createComboTableArray('card_type', 'id', 'name', 1, Translate::get('Chọn loại thẻ'), true, 'name ASC');
        $card_status_arr = CardLogFull::getCardStatus();

        return $this->render('search', [
            'page' => $page,
            'search' => $search,
            'card_type_search_arr' => $card_type_search_arr,
            'card_status_arr' => $card_status_arr
        ]);
    }

    // Chi tiết thẻ cào
    public function actionDetail()
    {
        $card_log_info = false;
        $card_log_id = ObjInput::get('id', "int", 0);        
        if ($card_log_id > 0) {
            $card_log_info = Tables::selectOneDataTable('card_log_full', ['id = :id AND merchant_id = :merchant_id ', "id" => $card_log_id, "merchant_id" => UserLogin::get('merchant_id')]);
        }
        if ($card_log_info != false) {
            $card_log = CardLogFull::setRow($card_log_info);
            return $this->render('detail', [
                'card_log' => $card_log,
            ]);
        } else {
            $this->redirectErrorPage('Địa chỉ trang truy cập không tồn tại');
        }
    }

    // Danh sách rút tiền
    public function actionWithdraw()
    {
        $merchant_id = UserLogin::get('merchant_id');
        $search = new CashoutSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->merchant_id = $merchant_id;
        $search->type = Cashout::TYPE_CARD_TRANSACTION;
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
    public function actionWithdrawDetail()
    {
        $cashout_id = ObjInput::get('id', 'int', 0);
        $cashout_info = false;
        if ($cashout_id > 0) {
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CARD_TRANSACTION]);
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
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CARD_TRANSACTION]);
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
                $url = Yii::$app->urlManager->createAbsoluteUrl('card-transaction/withdraw');
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
            $cashout_info = Tables::selectOneDataTable('cashout', ["id = :id AND merchant_id = :merchant_id AND type = :type ", "id" => $cashout_id, "merchant_id" => UserLogin::get('merchant_id'), "type" => Cashout::TYPE_CARD_TRANSACTION]);
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
                $url = Yii::$app->urlManager->createAbsoluteUrl('card-transaction/withdraw');
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
            'bank_account_code' => array('title' => 'Số tài khoản'),
            'bank_account_name' => array('title' => 'Chủ tài khoản'),
            'bank_account_branch' => array('title' => 'Chi nhánh'),
            'id' => array('title' => 'Mã yêu cầu'),
            'time_begin' => array('title' => 'Thời gian bắt đầu', 'type' => 'time'),
            'time_end' => array('title' => 'Thời gian kết thúc', 'type' => 'time'),
            'amount' => array('title' => 'Số tiền rút'),
            'receiver_fee' => array('title' => 'Phí giao rút'),
            'cashout_amount' => array('title' => 'Số tiền nhận được'),
            'payment_method_name' => array('title' => 'Phương thức thanh toán'),
            'time_created' => array('title' => 'Thời gian tạo', 'type' => 'time'),
            'time_accept' => array('title' => 'Thời gian duyệt', 'type' => 'time'),
            'time_paid' => array('title' => 'Thời gian chuyển ngân', 'type' => 'time'),
            'status' => array('title' => 'Trạng thái'),
        );
        //------------
        $search = new CashoutSearch();
        $search->setAttributes(Yii::$app->request->get());
        $merchant_id = UserLogin::get('merchant_id');
        $search->merchant_id = $merchant_id;
        $search->type = Cashout::TYPE_CARD_TRANSACTION;

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
            echo json_encode($result);
        }
        die();
    }

}
