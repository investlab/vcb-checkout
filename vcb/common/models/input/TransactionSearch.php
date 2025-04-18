<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 06/01/2018
 * Time: 2:30 PM
 */


namespace common\models\input;

use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\Merchant;
use common\models\db\Transaction;
use common\models\output\DataPage;
use common\util\TextUtil;
use yii\base\Model;
use yii\data\Pagination;
use Yii;

class TransactionSearch extends Model
{
    public $time_created_from; // Thời gian tạo
    public $time_created_to;
    public $time_paid_from; // Thời gian thanh toán
    public $time_paid_to;
    public $time_cancel_from; // Thời gian hủy
    public $time_cancel_to;

    public $id; // mã giao dịch
    public $cashout_id; // mã phiếu chi
    public $transaction_type_id; // loại giao dịch
    public $merchant_id; // merchant
    public $order_code; // mã đơn hàng
    public $token_code; // token đơn hàng
    public $buyer_info; // thông tin người mua
    public $payment_method_id; // phương thức thanh toán
    public $partner_payment_id; // Kênh thanh toán
    public $partner_payment_method_refer_code; // mã tham chiếu với Kênh thanh toán
    public $bank_refer_code; // mã giao dịch bên ngân hàng
    public $refer_transaction_id; // Giao dịch liên quan
    public $status;
    public $branch_id;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'transaction_type_id', 'id',
                'payment_method_id', 'partner_payment_id', 'refer_transaction_id', 'cashout_id', 'branch_id'
            ], 'integer'],
            [['order_code', 'buyer_info', 'partner_payment_method_refer_code', 'bank_refer_code', 'token_code'], 'string'],
            [['time_created_from', 'time_created_to',
                'time_paid_from', 'time_paid_to', 'time_cancel_from', 'time_cancel_to'], 'safe'],
            [['time_created_from', 'time_created_to',
                'time_paid_from', 'time_paid_to', 'time_cancel_from', 'time_cancel_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
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
        // Thời gian hủy
        if ($this->time_cancel_from != null && trim($this->time_cancel_from) != "") {
            if (!Validation::isDate($this->time_cancel_from)) {
                $errors[] = 'Ngày hủy thanh toán từ không đúng định dạng';
            } else {
                $time_cancel_from = FormatDateTime::toTimeBegin($this->time_cancel_from);
                $conditions[] = "time_paid >= $time_cancel_from ";
            }
        }

        if ($this->time_cancel_to != null && trim($this->time_cancel_to) != "") {
            if (!Validation::isDate($this->time_cancel_to)) {
                $errors[] = 'Ngày hủy thanh toán đến không đúng định dạng';
            } else {
                $time_cancel_to = FormatDateTime::toTimeEnd($this->time_cancel_to);
                $conditions[] = "time_cancel <= $time_cancel_to ";
            }
        }

        if (intval($this->transaction_type_id) > 0) {
            $conditions[] = "transaction_type_id = " . trim($this->transaction_type_id);
        }


        if (trim($this->order_code) != "") {
            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE order_code LIKE '%" . trim($this->order_code) . "%')";
        }

        if (trim($this->token_code) != "") {
            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE token_code LIKE '" . trim($this->token_code) . "')";
        }

        if (trim($this->buyer_info) != "") {
            $buyer_conditions[] = "(buyer_mobile LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_fullname LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_address LIKE '%" . trim($this->buyer_info) . "%' "
                . "OR buyer_email LIKE '%" . trim($this->buyer_info) . "%')";

            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE" . implode(' AND ', $buyer_conditions) . ") ";
        }

        if ($this->id != null && trim($this->id) != "") {
            $conditions[] = "id = '" . trim($this->id) . "'";
        }
        if ($this->cashout_id != null && trim($this->cashout_id) != "") {
            $conditions[] = "cashout_id = '" . trim($this->cashout_id) . "'";
        }
        if (intval($this->payment_method_id) > 0) {
            $conditions[] = "payment_method_id = " . trim($this->payment_method_id);
        }
        if (intval($this->partner_payment_id) > 0) {
            $conditions[] = "partner_payment_id = " . trim($this->partner_payment_id);
        }

        if ($this->partner_payment_method_refer_code != null && trim($this->partner_payment_method_refer_code) != "") {
            $conditions[] = "partner_payment_method_refer_code LIKE '%" . trim($this->partner_payment_method_refer_code) . "%'";
        }

        if ($this->refer_transaction_id != null && trim($this->refer_transaction_id) != "") {
            $conditions[] = "refer_transaction_id = '" . trim($this->refer_transaction_id) . "'";
        }

        if ($this->bank_refer_code != null && trim($this->bank_refer_code) != "") {
            $conditions[] = "bank_refer_code LIKE '%" . trim($this->bank_refer_code) . "%'";
        }

        if (!empty($this->status)) {
            $conditions[] = "status IN (" . implode(',', $this->status) . ") ";
        }

        $branch_id = Yii::$app->user->getIdentity()->branch_id;
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


    public function search()
    {
        $conditions = $this->getConditions($errors);
        $count_new = 0;
        $count_paying = 0;
        $count_paid = 0;
        $count_cancel = 0;
        $total_amount = 0;
        $total_fee = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("transaction", $conditions);
            $amounts = Tables::selectSumDataTable("transaction", $conditions,'amount');
            $total_amount = @$amounts["totals"];

            $sender_fees = Tables::selectSumDataTable("transaction", $conditions,'sender_fee');
            $total_sender_fee = @$sender_fees["totals"];
            $receiver_fees = Tables::selectSumDataTable("transaction", $conditions,'receiver_fee');
            $total_receiver_fee = @$receiver_fees["totals"];
            $partner_payment_sender_fees = Tables::selectSumDataTable("transaction", $conditions,'partner_payment_sender_fee');
            $total_partner_payment_sender_fee = @$partner_payment_sender_fees["totals"];
            $partner_payment_receiver_fees = Tables::selectSumDataTable("transaction", $conditions,'partner_payment_receiver_fee');
            $total_partner_payment_receiver_fee = @$partner_payment_receiver_fees["totals"];

            $total_fee = @$total_sender_fee + @$total_receiver_fee + @$total_partner_payment_sender_fee + @$total_partner_payment_receiver_fee;


            $count_new = Tables::selectCountDataTable("transaction", $conditions . " AND status =  " . Transaction::STATUS_NEW);
            $count_paying = Tables::selectCountDataTable("transaction", $conditions . " AND status =  " . transaction::STATUS_PAYING);
            $count_paid = Tables::selectCountDataTable("transaction", $conditions . " AND status =  " . transaction::STATUS_PAID);
            $count_cancel = Tables::selectCountDataTable("transaction", $conditions . " AND status =  " . transaction::STATUS_CANCEL);



        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $transaction_info = Tables::selectAllDataTable("transaction", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($transaction_info != false) {
                $transaction = Transaction::setRows($transaction_info);
                if ($transaction != null) {
                    foreach ($transaction as $key => $data) {
//                        $fee = $data['sender_fee'] + $data['receiver_fee'] + $data['partner_payment_sender_fee'] + $data['partner_payment_receiver_fee'];
//                        $total_amount += $data['amount'];
//                        $total_fee += $fee;
                    }
                }
                $dataPage->data = $transaction;
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
        $dataPage->total_amount = $total_amount;
        $dataPage->total_fee = $total_fee;

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
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

    public function searchForExport($offset, $limit) {
        $conditions = $this->getConditions($errors);
        //-------
        if ($conditions != false) {
            $transaction_info = Tables::selectAllDataTable("transaction", $conditions, "time_updated DESC ", "id", $limit, $offset);
            if ($transaction_info != false) {
                $transaction_info = Transaction::setRows($transaction_info);
                return $this->_getDataForExport($transaction_info);
            }
        }
        return false;
    }

    private function _getDataForExport($rows) {
        return $this->_getDataExportByRows($rows);
    }

    private function _getDataExportByRows($rows) {
        $result = [];
        $status = Transaction::getStatus();
        foreach ($rows as $row) {
            $result[] = [
                'id' => $row['id'],
                'transaction_type' => $row['transaction_type_info']['name'],
                'partner_name' => $row['partner_payment_info']['name'],
                'merchant_name' => $row['merchant_info']['name'],
                'checkout_order_id' => $row['checkout_order_id'],
                'payment_method_name' => $row['payment_method_info']['name'],
                'partner_payment_method_refer_code' => $row['partner_payment_method_refer_code'],
                'bank_refer_code' => $row['bank_refer_code'],
                'amount' => $row['amount'],
                'sender_fee' => @$row['sender_fee'],
                'receiver_fee' => @$row['receiver_fee'],
                'partner_payment_sender_fee' => @$row['partner_payment_sender_fee'],
                'partner_payment_receiver_fee' => @$row['partner_payment_receiver_fee'],
                'currency' => $row['currency'],
                'status' => $status[$row['status']],
                'refer_transaction_id' => ($row['refer_transaction_id'] == '0') ? '' : $row['refer_transaction_id'],
                'time_created' => (empty($row['time_created'])) ? '' : date('d/m/Y H:i', $row['time_created']),
                'time_updated' => (empty($row['time_updated'])) ? '' : date('d/m/Y H:i', $row['time_updated']),
                'time_paid' => (empty($row['time_paid'])) ? '' : date('d/m/Y H:i', $row['time_paid']),
                'time_cancel' => (empty($row['time_cancel'])) ? '' : date('d/m/Y H:i', $row['time_cancel']),
            ];
        }
        return $result;
    }

}