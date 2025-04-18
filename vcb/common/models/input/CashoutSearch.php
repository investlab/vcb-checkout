<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/5/2018
 * Time: 09:34
 */

namespace common\models\input;

use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\Cashout;
use common\models\db\CheckoutOrder;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;
use common\components\utils\Translate;

class CashoutSearch extends Model {

    public $time_created_from; // Thời gian tạo
    public $time_created_to;
    public $time_begin_from; // Thời gian bắt đầu
    public $time_begin_to;
    public $time_end_from; // Thời gian kết thúc
    public $time_end_to;
    public $time_request_from; // TThời gian yêu cầu
    public $time_request_to;
    public $time_accept_from; // 	Thời gian duyệt
    public $time_accept_to;
    public $time_paid_from; // Thời gian Chuyển ngân
    public $time_paid_to;
    public $time_reject_from; // 	Thời gian từ chối
    public $time_reject_to;
    public $time_cancel_from; // 	Thời hạn hủy
    public $time_cancel_to;
    public $id; // mã yêu cầu
    public $merchant_id; // merchant
    public $partner_id; // đối tác
    public $method_id; // nhóm phương thức
    public $bank_id; // ngân hàng
    public $payment_method_id; // phương thức
    public $partner_payment_id; // kênh thanh toán
    public $transaction_id; // giao dịch rút tiền
    public $bank_account_code; // Số thẻ/Tài khoản/Email ví điện tử
    public $status;
    public $status_merchant;
    public $type;
    public $reference_code_merchant;
    public $reference_code;
    public $pageSize;
    public $page;

    public function rules() {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'status_merchant', 'id', 'type',
            'partner_id', 'method_id', 'bank_id', 'payment_method_id', 'partner_payment_id'
                ], 'integer'],
            [['bank_account_code', 'transaction_id', 'reference_code_merchant', 'reference_code'], 'string'],
            [['time_created_from', 'time_created_to',
            'time_begin_from', 'time_begin_to',
            'time_end_from', 'time_end_to',
            'time_request_from', 'time_request_to',
            'time_accept_from', 'time_accept_to',
            'time_paid_from', 'time_paid_to',
            'time_reject_from', 'time_reject_to',
            'time_cancel_from', 'time_cancel_to',
                ], 'safe'],
            [['time_created_from', 'time_created_to',
            'time_begin_from', 'time_begin_to',
            'time_end_from', 'time_end_to',
            'time_request_from', 'time_request_to',
            'time_accept_from', 'time_accept_to',
            'time_paid_from', 'time_paid_to',
            'time_reject_from', 'time_reject_to',
            'time_cancel_from', 'time_cancel_to',
                ], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array()) {
        $conditions = array();
        // Thòi gian tạo
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors[] = 'TG tạo từ không đúng định dạng';
            } else {
                $time_created_from = FormatDateTime::toTimeBegin($this->time_created_from);
                $conditions[] = "time_created >= $time_created_from ";
            }
        }

        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors[] = 'TG tạo đến không đúng định dạng';
            } else {
                $time_created_to = FormatDateTime::toTimeEnd($this->time_created_to);
                $conditions[] = "time_created <= $time_created_to ";
            }
        }
        // Thòi gian bắt đầu
        if ($this->time_begin_from != null && trim($this->time_begin_from) != "") {
            if (!Validation::isDate($this->time_begin_from)) {
                $errors[] = 'TG bắt đầu từ không đúng định dạng';
            } else {
                $time_begin_from = FormatDateTime::toTimeBegin($this->time_begin_from);
                $conditions[] = "time_begin >= $time_begin_from ";
            }
        }

        if ($this->time_begin_to != null && trim($this->time_begin_to) != "") {
            if (!Validation::isDate($this->time_begin_to)) {
                $errors[] = 'TG bắt đầu đến không đúng định dạng';
            } else {
                $time_begin_to = FormatDateTime::toTimeEnd($this->time_begin_to);
                $conditions[] = "time_begin <= $time_begin_to ";
            }
        }

        // Thòi gian kết thúc
        if ($this->time_end_from != null && trim($this->time_end_from) != "") {
            if (!Validation::isDate($this->time_end_from)) {
                $errors[] = 'TG kết thúc từ không đúng định dạng';
            } else {
                $time_end_from = FormatDateTime::toTimeBegin($this->time_end_from);
                $conditions[] = "time_end >= $time_end_from ";
            }
        }

        if ($this->time_end_to != null && trim($this->time_end_to) != "") {
            if (!Validation::isDate($this->time_end_to)) {
                $errors[] = 'TG kết thúc đến không đúng định dạng';
            } else {
                $time_end_to = FormatDateTime::toTimeEnd($this->time_end_to);
                $conditions[] = "time_end <= $time_end_to ";
            }
        }

        // Thòi gian yêu cầu
        if ($this->time_request_from != null && trim($this->time_request_from) != "") {
            if (!Validation::isDate($this->time_request_from)) {
                $errors[] = 'TG yêu cầu từ không đúng định dạng';
            } else {
                $time_request_from = FormatDateTime::toTimeBegin($this->time_request_from);
                $conditions[] = "time_request >= $time_request_from ";
            }
        }

        if ($this->time_request_to != null && trim($this->time_request_to) != "") {
            if (!Validation::isDate($this->time_request_to)) {
                $errors[] = 'TG yêu cầu đến không đúng định dạng';
            } else {
                $time_request_to = FormatDateTime::toTimeEnd($this->time_request_to);
                $conditions[] = "time_request <= $time_request_to ";
            }
        }

        // Thòi gian duyệt
        if ($this->time_accept_from != null && trim($this->time_accept_from) != "") {
            if (!Validation::isDate($this->time_accept_from)) {
                $errors[] = 'TG duyệt từ không đúng định dạng';
            } else {
                $time_accept_from = FormatDateTime::toTimeBegin($this->time_accept_from);
                $conditions[] = "time_accept >= $time_accept_from ";
            }
        }

        if ($this->time_accept_to != null && trim($this->time_accept_to) != "") {
            if (!Validation::isDate($this->time_accept_to)) {
                $errors[] = 'TG duyệt đến không đúng định dạng';
            } else {
                $time_accept_to = FormatDateTime::toTimeEnd($this->time_accept_to);
                $conditions[] = "time_accept <= $time_accept_to ";
            }
        }

        // Thòi gian từ chối
        if ($this->time_reject_from != null && trim($this->time_reject_from) != "") {
            if (!Validation::isDate($this->time_reject_from)) {
                $errors[] = 'TG từ chối từ không đúng định dạng';
            } else {
                $time_reject_from = FormatDateTime::toTimeBegin($this->time_reject_from);
                $conditions[] = "time_reject >= $time_reject_from ";
            }
        }

        if ($this->time_reject_to != null && trim($this->time_reject_to) != "") {
            if (!Validation::isDate($this->time_reject_to)) {
                $errors[] = 'TG từ chối đến không đúng định dạng';
            } else {
                $time_reject_to = FormatDateTime::toTimeEnd($this->time_reject_to);
                $conditions[] = "time_reject <= $time_reject_to ";
            }
        }

        // Thời gian chuyển ngân
        if ($this->time_paid_from != null && trim($this->time_paid_from) != "") {
            if (!Validation::isDate($this->time_paid_from)) {
                $errors[] = 'TG chuyển ngân từ không đúng định dạng';
            } else {
                $time_paid_from = FormatDateTime::toTimeBegin($this->time_paid_from);
                $conditions[] = "time_paid >= $time_paid_from ";
            }
        }

        if ($this->time_paid_to != null && trim($this->time_paid_to) != "") {
            if (!Validation::isDate($this->time_paid_to)) {
                $errors[] = 'TG chuyển ngân đến không đúng định dạng';
            } else {
                $time_paid_to = FormatDateTime::toTimeEnd($this->time_paid_to);
                $conditions[] = "time_paid <= $time_paid_to ";
            }
        }

        // Thời gian hủy
        if ($this->time_cancel_from != null && trim($this->time_cancel_from) != "") {
            if (!Validation::isDate($this->time_cancel_from)) {
                $errors[] = 'TG hủy từ không đúng định dạng';
            } else {
                $time_cancel_from = FormatDateTime::toTimeBegin($this->time_cancel_from);
                $conditions[] = "time_cancel >= $time_cancel_from ";
            }
        }

        if ($this->time_cancel_to != null && trim($this->time_cancel_to) != "") {
            if (!Validation::isDate($this->time_cancel_to)) {
                $errors[] = 'TG hủy đến không đúng định dạng';
            } else {
                $time_cancel_to = FormatDateTime::toTimeEnd($this->time_success_to);
                $conditions[] = "time_cancel <= $time_cancel_to ";
            }
        }

        if (trim($this->id) != "") {
            $conditions[] = "id = '" . trim($this->id) . "'";
        }
        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }
        if (intval($this->partner_id) > 0) {
            $conditions[] = "partner_id = " . trim($this->partner_id);
        }
        if (intval($this->method_id) > 0) {
            $conditions[] = "method_id = " . trim($this->method_id);
        }
        if (intval($this->bank_id) > 0) {
            $conditions[] = "bank_id = " . trim($this->bank_id);
        }
        if (intval($this->payment_method_id) > 0) {
            $conditions[] = "payment_method_id = " . trim($this->payment_method_id);
        }
        if (trim($this->transaction_id) != "") {
            $conditions[] = "transaction_id = '" . trim($this->transaction_id) . "'";
        }

        if (trim($this->reference_code_merchant) != "") {
            $conditions[] = "reference_code_merchant = '" . trim($this->reference_code_merchant) . "'";
        }
        if ($this->bank_account_code != null && trim($this->bank_account_code) != "") {
            $conditions[] = "bank_account_code LIKE '%" . trim($this->bank_account_code) . "%'";
        }

        if (!empty($this->status)) {
            $conditions[] = "status IN (" . implode(',', $this->status) . ") ";
        }
        if (intval($this->status_merchant) > 0) {
            $conditions[] = "status = " . trim($this->status_merchant);
        }
        if (intval($this->type) > 0) {
            $conditions[] = "type = " . $this->type . " ";
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

        $count_cancel = 0;
        $count_verify = 0;
        $count_wait_accept = 0;
        $count_reject = 0;
        $count_accept = 0;
        $count_paid = 0;

        $total_amount_cancel = 0;
        $total_amount_verify = 0;
        $total_amount_wait_accept = 0;
        $total_amount_reject = 0;
        $total_amount_accept = 0;
        $total_amount_paid = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("cashout", $conditions);

            $total_count_verify = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_VERIFY, 'amount');
            $total_amount_verify = @$total_count_verify["totals"];
            $count_verify = @$total_count_verify["counts"];

            $total_wait_accept = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_WAIT_ACCEPT, 'amount');
            $total_amount_wait_accept = @$total_wait_accept["totals"];
            $count_wait_accept = @$total_wait_accept["counts"];

            $total_reject = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_REJECT, 'amount');
            $total_amount_reject = @$total_reject["totals"];
            $count_reject = @$total_reject["counts"];

            $total_accept = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_ACCEPT, 'amount');
            $total_amount_accept = @$total_accept["totals"];
            $count_accept = @$total_accept["counts"];

            $total_paid = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_PAID, 'amount');
            $total_amount_paid = @$total_paid["totals"];
            $count_paid = @$total_paid["counts"];

            $total_cancel = Tables::selectSumDataTable("cashout", $conditions. ' AND status = '. Cashout::STATUS_CANCEL, 'amount');
            $total_amount_cancel = @$total_cancel["totals"];
            $count_cancel = @$total_cancel["counts"];



        } else {
            $count = 0;
        }
        $total_amount = 0;
        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $cashout = Tables::selectAllDataTable("cashout", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($cashout != false) {
                $cashout = Cashout::setRows($cashout);
                if ($cashout != null) {
                    foreach ($cashout as $key => $data) {
                        $total_amount += $data['amount'];
                    }
                }
                $dataPage->data = $cashout;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->count_paid = $count_paid;
        $dataPage->count_cancel = $count_cancel;
        $dataPage->count_verify = $count_verify;
        $dataPage->count_wait_accept = $count_wait_accept;
        $dataPage->count_reject = $count_reject;
        $dataPage->count_accept = $count_accept;

        $dataPage->total_amount = $total_amount;

        $dataPage->total_amount_paid = $total_amount_paid;
        $dataPage->total_amount_cancel = $total_amount_cancel;
        $dataPage->total_amount_verify = $total_amount_verify;
        $dataPage->total_amount_wait_accept = $total_amount_wait_accept;
        $dataPage->total_amount_reject = $total_amount_reject;
        $dataPage->total_amount_accept = $total_amount_accept;

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

    public function searchForExport($offset, $limit) {
        $conditions = $this->getConditions($errors);
        //-------
        if ($conditions != false) {
            $cashout_info = Tables::selectAllDataTable("cashout", $conditions, "time_updated DESC ", "id", $limit, $offset);
            if ($cashout_info != false) {
                return $this->_getDataForExport($cashout_info);
            }
        }
        return false;
    }

    private function _getDataForExport($rows) {
        $payment_method_ids = array();
        $payment_methods = array();

        foreach ($rows as $row) {
            if (intval($row['payment_method_id']) > 0) {
                $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
            }
        }
        if (!empty($payment_method_ids)) {
            $payment_methods = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") ", "", "id");
        }
        $status_names = Cashout::getStatus();
        foreach ($rows as $key => $row) {
            $rows[$key]['status_name'] = Translate::get($status_names[$row['status']]);
            $rows[$key]['payment_method_info'] = @$payment_methods[$row['payment_method_id']];
        }
        return $this->_getDataExportByRows($rows);
    }

    private function _getDataExportByRows($rows) {
        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'bank_account_code' => $row['bank_account_code'],
                'bank_account_name' => $row['bank_account_name'],
                'bank_account_branch' => $row['bank_account_branch'],
                'id' => $row['id'],
                'time_begin' => $row['time_begin'],
                'time_end' => $row['time_end'],
                'amount' => $row['amount'],
                'receiver_fee' => $row['receiver_fee'],
                'cashout_amount' => intval(@$row['amount']) - intval(@$row['receiver_fee']),
                'payment_method_name' => Translate::get($row['payment_method_info']['name']),
                'time_created' => $row['time_created'],
                'time_accept' => $row['time_accept'],
                'time_paid' => $row['time_paid'],
                'status' => $row['status_name'],
            );
        }
        return $result;
    }

}
