<?php


namespace app\models\bussiness;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\UserLogin;
use common\models\output\DataPage;
use yii\data\Pagination;
use Yii;

class OrderFlow
{
    public $time_created_from; // Thời gian tạo
    public $time_created_to;
    public $time_paid_from; // Thời gian thanh toán
    public $time_paid_to;
    public $time_success_from; // TThời gian hoàn tất gọi lại merchant
    public $time_success_to;
    public $time_refund_from; // 	Thời gian hoàn tiền
    public $time_refund_to;
    public $time_withdraw_from; // 	Thời gian rút tiền
    public $time_withdraw_to;
    public $time_limit_from; // 	Thời hạn thanh toán
    public $time_limit_to;
    public $merchant_id; // merchant
    public $order_code; // mã đơn hàng
    public $token_code; // mã token
    public $buyer_info; // thông tin người mua
    public $transaction_id; // mã giao dịch
    public $status;
    public $callback_status;
    public $status_merchant; // search trong merchant
    public $payment_method_id;
    public $branch_id;
    public $pageSize;
    public $page;

    public function History(){
        $conditions = $this->getConditions($errors);
        $count_new = 0;
        $count_paying = 0;
        $count_paid = 0;
        $count_cancel = 0;
        $count_review = 0;
        $count_wait_refund = 0;
        $count_refund = 0;
        $count_wait_widthdaw = 0;
        $count_widthdaw = 0;
        $total_cashin_amount = 0; // Tổng số tiền thanh toán
        $total_cashout_amount = 0; // Tổng số tiền được rút
        //------------
        if ($conditions != false) {
            //$count = Tables::selectCountDataTable("checkout_order", $conditions);
            $total_count_paying = Tables::selectSumDataTable("checkout_order", $conditions, 'amount');
            $total_cashin_amount = @$total_count_paying["totals"];
            $count = @$total_count_paying["counts"];

            $count_new = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_NEW);
            $count_paying = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_PAYING);
            $count_paid = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_PAID);
            $count_cancel = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_CANCEL);
            $count_review = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_REVIEW);
            $count_wait_refund = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_WAIT_REFUND);
            $count_refund = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_REFUND);
            $count_wait_widthdaw = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_WAIT_WIDTHDAW);
            $count_widthdaw = Tables::selectCountDataTable("checkout_order", $conditions . " AND status =  " . CheckoutOrder::STATUS_WIDTHDAW);

            $total_count_withdraw = Tables::selectSumDataTable("checkout_order", $conditions, 'cashout_amount');
            $total_cashout_amount = @$total_count_withdraw["totals"];
        } else {
            $count = 0;
        }

        $total_amount = 0;


        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $checkout_order_info = Tables::selectAllDataTable("checkout_order", $conditions, "time_updated DESC", '', $paging->getLimit(), $paging->getOffset());
            if ($checkout_order_info != false) {
                $checkout_order = CheckoutOrder::setRowsForApp($checkout_order_info);
                if ($checkout_order != null) {
                    foreach ($checkout_order as $key => $data) {
                        $total_amount += $data['amount'];
                        // $total_cashin_amount += $data['cashin_amount'];
                        // $total_cashout_amount += $data['cashout_amount'];
                    }
                }
                $dataPage->data = $checkout_order;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->count_new = $count_new;
        $dataPage->count_paying = $count_paying;
        $dataPage->count_paid = $count_paid;
        $dataPage->count_cancel = $count_cancel;
        $dataPage->count_review = $count_review;
        $dataPage->count_wait_refund = $count_wait_refund;
        $dataPage->count_refund = $count_refund;
        $dataPage->count_wait_widthdaw = $count_wait_widthdaw;
        $dataPage->count_widthdaw = $count_widthdaw;
        $dataPage->total_amount = $total_amount;
        $dataPage->total_cashout_amount = $total_cashout_amount;
        $dataPage->total_cashin_amount = $total_cashin_amount;

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;
        if ($errors == ''){
            return [
                'error_code' => 0,
                'error_message' => $errors,
                'response' => [
                    'total' => $paging->totalCount,
                    'data' =>   $dataPage->data

                ],
            ];
        }else{
            return [
                'error_code' => 10009,
                'error_message' => $errors,
                'response' => []
            ];
        }

    }

    function getConditions(&$errors = array()) {
        $conditions = array();
        // Thòi gian tạo
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors = 'Ngày tạo từ không đúng định dạng';
            } else {
                $time_created_from = FormatDateTime::toTimeBegin($this->time_created_from);
                $conditions[] = "time_created >= $time_created_from ";
            }
        }

        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors = 'Ngày tạo đến không đúng định dạng';
            } else {
                $time_created_to = FormatDateTime::toTimeEnd($this->time_created_to);
                $conditions[] = "time_created <= $time_created_to ";
            }
        }

        // Thời gian thanh toán
        if ($this->time_paid_from != null && trim($this->time_paid_from) != "") {
            if (!Validation::isDate($this->time_paid_from)) {
                $errors[] = 'Ngày thanh toán từ không đúng định dạng';
            } else {
                $time_paid_from = FormatDateTime::toTimeBegin($this->time_paid_from);
                $conditions[] = "time_paid >= $time_paid_from ";
            }
        }

        if ($this->time_paid_to != null && trim($this->time_paid_to) != "") {
            if (!Validation::isDate($this->time_paid_to)) {
                $errors[] = 'Ngày thanh toán đến không đúng định dạng';
            } else {
                $time_paid_to = FormatDateTime::toTimeEnd($this->time_paid_to);
                $conditions[] = "time_paid <= $time_paid_to ";
            }
        }

        // Thời gian hoàn tất gọi lại merchant
        if ($this->time_success_from != null && trim($this->time_success_from) != "") {
            if (!Validation::isDate($this->time_success_from)) {
                $errors[] = 'Ngày hoàn tất gọi lại merchant từ không đúng định dạng';
            } else {
                $time_success_from = FormatDateTime::toTimeBegin($this->time_success_from);
                $conditions[] = "time_success >= $time_success_from ";
            }
        }

        if ($this->time_success_to != null && trim($this->time_success_to) != "") {
            if (!Validation::isDate($this->time_success_to)) {
                $errors[] = 'Ngày hoàn tất gọi lại merchant đến không đúng định dạng';
            } else {
                $time_success_to = FormatDateTime::toTimeEnd($this->time_success_to);
                $conditions[] = "time_success <= $time_success_to ";
            }
        }

        // Thời gian hoàn tiền
        if ($this->time_refund_from != null && trim($this->time_refund_from) != "") {
            if (!Validation::isDate($this->time_refund_from)) {
                $errors[] = 'Ngày hoàn tiền từ không đúng định dạng';
            } else {
                $time_refund_from = FormatDateTime::toTimeBegin($this->time_refund_from);
                $conditions[] = "time_refund >= $time_refund_from ";
            }
        }

        if ($this->time_refund_to != null && trim($this->time_refund_to) != "") {
            if (!Validation::isDate($this->time_refund_to)) {
                $errors[] = 'Ngày hoàn tiền đến không đúng định dạng';
            } else {
                $time_refund_to = FormatDateTime::toTimeEnd($this->time_refund_to);
                $conditions[] = "time_refund <= $time_refund_to ";
            }
        }
        // Thời gian rút tiền
        if ($this->time_withdraw_from != null && trim($this->time_withdraw_from) != "") {
            if (!Validation::isDate($this->time_withdraw_from)) {
                $errors[] = 'Ngày rút tiền từ không đúng định dạng';
            } else {
                $time_withdraw_from = FormatDateTime::toTimeBegin($this->time_withdraw_from);
                $conditions[] = "time_withdraw >= $time_withdraw_from ";
            }
        }

        if ($this->time_withdraw_to != null && trim($this->time_withdraw_to) != "") {
            if (!Validation::isDate($this->time_withdraw_to)) {
                $errors[] = 'Ngày rút tiền đến không đúng định dạng';
            } else {
                $time_withdraw_to = FormatDateTime::toTimeEnd($this->time_withdraw_to);
                $conditions[] = "time_withdraw <= $time_withdraw_to ";
            }
        }
        // Thời hạn thanh toán
        if ($this->time_limit_from != null && trim($this->time_limit_from) != "") {
            if (!Validation::isDate($this->time_limit_from)) {
                $errors[] = 'Thời hạn thanh toán từ không đúng định dạng';
            } else {
                $time_limit_from = FormatDateTime::toTimeBegin($this->time_limit_from);
                $conditions[] = "time_limit >= $time_limit_from ";
            }
        }

        if ($this->time_limit_to != null && trim($this->time_limit_to) != "") {
            if (!Validation::isDate($this->time_limit_to)) {
                $errors[] = 'Thời hạn thanh toán đến không đúng định dạng';
            } else {
                $time_limit_to = FormatDateTime::toTimeEnd($this->time_limit_to);
                $conditions[] = "time_limit <= $time_limit_to ";
            }
        }

        if ($this->order_code != null && trim($this->order_code) != "") {
            $conditions[] = "order_code LIKE '%" . trim($this->order_code) . "%'";
        }
        if ($this->token_code != null && trim($this->token_code) != "") {
            $conditions[] = "token_code LIKE '%" . trim($this->token_code) . "%'";
        }

        if (trim($this->buyer_info) != "") {
            $conditions[] = "(buyer_mobile LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_fullname LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_address LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_email LIKE '%" . trim($this->buyer_info) . "%')";
        }

        if (trim($this->transaction_id) != "") {
            $conditions[] = "transaction_id = '" . trim($this->transaction_id) . "'";
        }

        if (!empty($this->status)) {
            $conditions[] = "status = " . $this->status;
        }


        if (intval($this->payment_method_id) > 0) {
            $payment_method_conditions[] = "payment_method_id = " . $this->payment_method_id . " AND merchant_id = " . $this->merchant_id;
            $conditions[] = "id IN (SELECT checkout_order_id FROM transaction WHERE " . implode(' AND ', $payment_method_conditions) . ") ";
        }
        if (isset(Yii::$app->user->getIdentity()->branch_id)){
            $branch_id = Yii::$app->user->getIdentity()->branch_id;

        }
        if (!empty($branch_id)){
            $merchant_arr = self::getMerchantInBranch($branch_id);
        }else{
            if (intval($this->merchant_id) > 0) {
                $merchant_arr[] = $this->merchant_id;
            } else {
                $merchant_arr = self::getMerchantInBranch($this->branch_id);
            }
        }


        if (!empty($merchant_arr)) {
            $conditions[] = "merchant_id IN " . self::convertToStringArray($merchant_arr);
        }

        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = 1;
        }

        return $conditions;
    }
    public function getMerchantInBranch($branch_id) {
        $merchant_arr = [];
        if (empty($branch_id) || $branch_id == '00') {
            return [];
        }

        $merchants = Merchant::findAll(['branch_id' => $branch_id, 'status' => Merchant::STATUS_ACTIVE]);

        if (!empty($merchants)) {
            foreach ($merchants as $key => $merchant) {
                $merchant_arr[] = $merchant['id'];
            }
        }

        return $merchant_arr;
    }
    public function convertToStringArray($arr) {
        $string = '(';
        if (!empty($arr)) {
            foreach ($arr as $key => $item) {
                if ($key == 0) {
                    $string .= $item;
                } else {
                    $string .= ','. $item;
                }
            }
        }
        $string .= ')';

        return $string;
    }

    public function getTransactionById($checkout_order_id){
        $checkout_order = CheckoutOrder::find()
            ->where(['id' => $checkout_order_id])
            ->asArray()
            ->one();
        if ($checkout_order != false){
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND merchant_id = :merchant_id ", "id" => $checkout_order_id, "merchant_id" => $checkout_order['merchant_id']]);
            if ($checkout_order_info != false){
//                $checkout_order_info['cashin_amount'] = $checkout_order_info['cashout_amount'];
//                unset($checkout_order_info['cashout_amount']);
                $checkout_order = CheckoutOrder::setRowForApp($checkout_order_info);
                $result['error_code']= 0;
                $result['error_message'] = '';
                $result['response'] = $checkout_order;
                return $result;
            }
        }else{
            $result['error_code']= null;
            $result['error_message'] = 'Không tìm thấy thông tin giao dịch';
            $result['response'] = [];
            return $result;
        }

    }
}