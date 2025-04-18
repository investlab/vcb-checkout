<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 06/01/2017
 * Time: 8:00 AM
 */

namespace common\models\input;

use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\CheckoutOrderBackup;
use common\models\db\Transaction;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;
use common\components\utils\Translate;

class CheckoutOrderBackupSearch extends Model {

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
    public $pageSize;
    public $page;

    public function rules() {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'status_merchant', 'payment_method_id'], 'integer'],
            [['order_code', 'buyer_info', 'callback_status', 'transaction_id', 'token_code'], 'string'],
            [['time_created_from', 'time_created_to',
                'time_paid_from', 'time_paid_to', 'time_success_from', 'time_success_to',
                'time_refund_from', 'time_refund_to', 'time_withdraw_from', 'time_withdraw_to'
            ], 'safe'],
            [['time_created_from', 'time_created_to',
                'time_paid_from', 'time_paid_to', 'time_success_from', 'time_success_to',
                'time_refund_from', 'time_refund_to', 'time_withdraw_from', 'time_withdraw_to'
            ], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array()) {
        $conditions = array();
        // Thòi gian tạo
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors[] = 'Ngày tạo từ không đúng định dạng';
            } else {
                $time_created_from = FormatDateTime::toTimeBegin($this->time_created_from);
                $conditions[] = "time_created >= $time_created_from ";
            }
        }

        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors[] = 'Ngày tạo đến không đúng định dạng';
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

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
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
            $conditions[] = "status IN (" . implode(',', $this->status) . ") ";
        }
        if (!empty($this->callback_status)) {
            $conditions[] = "callback_status IN (" . implode(',', $this->callback_status) . ") ";
        }

        // search trong merchant
        if (intval($this->status_merchant) > 0) {
            $conditions[] = "status = " . $this->status_merchant;
        }

        if (intval($this->payment_method_id) > 0) {
            $payment_method_conditions[] = "payment_method_id = " . $this->payment_method_id . " AND merchant_id = " . $this->merchant_id;
            $conditions[] = "id IN (SELECT checkout_order_id FROM transaction WHERE " . implode(' AND ', $payment_method_conditions) . ") ";
        }


        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = 1;
        }

        return $conditions;
    }

    public function search() {
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

            $total_count_paying = Tables::selectSumDataTable("checkout_order_backup", $conditions, 'amount');
            $total_cashin_amount = @$total_count_paying["totals"];
            $count = @$total_count_paying["counts"];

            $count_new = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_NEW);
            $count_paying = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_PAYING);
            $count_paid = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_PAID);
            $count_cancel = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_CANCEL);
            $count_review = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_REVIEW);
            $count_wait_refund = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_WAIT_REFUND);
            $count_refund = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_REFUND);
            $count_wait_widthdaw = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_WAIT_WIDTHDAW);
            $count_widthdaw = Tables::selectCountDataTable("checkout_order_backup", $conditions . " AND status =  " . CheckoutOrderBackup::STATUS_WIDTHDAW);

            $total_count_withdraw = Tables::selectSumDataTable("checkout_order_backup", $conditions, 'cashout_amount');
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
            $checkout_order_info = Tables::selectAllDataTable("checkout_order_backup", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($checkout_order_info != false) {
                $checkout_order = CheckoutOrderBackup::setRows($checkout_order_info);
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

        return $dataPage;
    }

    public function searchForExport($offset, $limit) {
        $conditions = $this->getConditions($errors);
        //-------
        if ($conditions != false) {
            $checkout_order_info = Tables::selectAllDataTable("checkout_order_backup", $conditions, "time_updated DESC ", "id", $limit, $offset);
            if ($checkout_order_info != false) {
                return $this->_getDataForExport($checkout_order_info);
            }
        }
        return false;
    }

    private function _getDataForExport($rows) {
        $transaction_current_ids = array();

        $transaction_currents = array();

        foreach ($rows as $row) {
            if (intval($row['transaction_id']) > 0) {
                $transaction_current_ids[$row['transaction_id']] = $row['transaction_id'];
            }
        }

        if (!empty($transaction_current_ids)) {
            $transaction_currents_info = Tables::selectAllDataTable("transaction", "id IN (" . implode(',', $transaction_current_ids) . ") ", "", "id");
            $transaction_currents = Transaction::setRows($transaction_currents_info);
        }
        $status_names = CheckoutOrderBackup::getStatus();
        foreach ($rows as $key => $row) {
            $rows[$key]['status_name'] = Translate::get($status_names[$row['status']]);
            $rows[$key]['transaction_current_info'] = @$transaction_currents[$row['transaction_id']];
            $rows[$key]['operators'] = CheckoutOrderBackup::getOperatorsByStatus($row);
        }
        return $this->_getDataExportByRows($rows);
    }

    private function _getDataExportByRows($rows) {
        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'buyer_fullname' => $row['buyer_fullname'],
                'buyer_email' => $row['buyer_email'],
                'buyer_mobile' => $row['buyer_mobile'],
                'token_code' => $row['token_code'],
                'order_code' => $row['order_code'],
                'order_description' => $row['order_description'],
                'amount' => $row['amount'],
                'receiver_fee' => $row['receiver_fee'],
                'cashout_amount' => $row['cashout_amount'],
                'payment_method_name' => Translate::get($row['transaction_current_info']['payment_method_info']['name']),
                'time_created' => $row['time_created'],
                'time_paid' => $row['time_paid'],
                'status' => $row['status_name'],
                'encode' => @$GLOBALS['PREFIX'] . $row['transaction_id'],
                'bank_refer_code' => @$row['transaction_current_info']['bank_refer_code']
            );
        }
        return $result;
    }

}
