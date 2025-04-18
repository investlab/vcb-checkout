<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/20/2018
 * Time: 13:21
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\CardTransaction;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class CardTransactionSearch extends Model {
    public $time_created_from; // Thời gian tạo
    public $time_created_to;
    public $time_withdraw_from; // Thời gian rút
    public $time_withdraw_to;
    public $withdraw_time_limit_from; // Thời gian cho phép rút
    public $withdraw_time_limit_to;

    public $id; // mã
    public $cashout_id; // mã phiếu chi
    public $bill_type; // bill_type
    public $merchant_id; // merchant
    public $merchant_refer_code; // merchant
    public $cycle_day; // kỳ thanh toán
    public $card_type_id; // loại thẻ
    public $partner_card_id; // đối tác
    public $partner_card_refer_code; // đối tác
    public $card_code; // mã thẻ
    public $card_serial; // serial thẻ
    public $status; // trạng thái

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'merchant_id', 'cycle_day', 'id', 'bill_type', 'partner_card_id',
                'card_type_id', 'cashout_id','status'
            ], 'integer'],
            [['merchant_refer_code', 'card_code', 'partner_card_refer_code','card_serial'], 'string'],
            [['time_created_from', 'time_created_to',
                'withdraw_time_limit_from', 'withdraw_time_limit_to',
                'time_withdraw_from', 'time_withdraw_to'
            ], 'safe'],
            [['time_created_from', 'time_created_to',
                'withdraw_time_limit_from', 'withdraw_time_limit_to',
                'time_withdraw_from', 'time_withdraw_to'
            ], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
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
        // Thòi gian rút
        if ($this->time_withdraw_from != null && trim($this->time_withdraw_from) != "") {
            if (!Validation::isDate($this->time_withdraw_from)) {
                $errors[] = 'TG rút từ không đúng định dạng';
            } else {
                $time_withdraw_from = FormatDateTime::toTimeBegin($this->time_withdraw_from);
                $conditions[] = "time_withdraw >= $time_withdraw_from ";
            }
        }

        if ($this->time_withdraw_to != null && trim($this->time_withdraw_to) != "") {
            if (!Validation::isDate($this->time_withdraw_to)) {
                $errors[] = 'TG rút đến không đúng định dạng';
            } else {
                $time_withdraw_to = FormatDateTime::toTimeEnd($this->time_withdraw_to);
                $conditions[] = "time_withdraw <= $time_withdraw_to ";
            }
        }

        // Thòi hạn được rút
        if ($this->withdraw_time_limit_from != null && trim($this->withdraw_time_limit_from) != "") {
            if (!Validation::isDate($this->withdraw_time_limit_from)) {
                $errors[] = 'Thời hạn được rút từ không đúng định dạng';
            } else {
                $withdraw_time_limit_from = FormatDateTime::toTimeBegin($this->withdraw_time_limit_from);
                $conditions[] = "withdraw_time_limit >= $withdraw_time_limit_from ";
            }
        }

        if ($this->withdraw_time_limit_to != null && trim($this->withdraw_time_limit_to) != "") {
            if (!Validation::isDate($this->withdraw_time_limit_to)) {
                $errors[] = 'Thời hạn được rút đến không đúng định dạng';
            } else {
                $withdraw_time_limit_to = FormatDateTime::toTimeEnd($this->withdraw_time_limit_to);
                $conditions[] = "withdraw_time_limit <= $withdraw_time_limit_to ";
            }
        }

        if (trim($this->id) != "") {
            $conditions[] = "id = '" . trim($this->id) . "'";
        }
        if (trim($this->cashout_id) != "") {
            $conditions[] = "cashout_id = '" . trim($this->cashout_id) . "'";
        }
        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }
        if (intval($this->partner_card_id) > 0) {
            $conditions[] = "partner_card_id = " . trim($this->partner_card_id);
        }
        if ($this->merchant_refer_code != null && trim($this->merchant_refer_code) != "") {
            $conditions[] = "merchant_refer_code LIKE '%" . trim($this->merchant_refer_code) . "%'";
        }
        if ($this->partner_card_refer_code != null && trim($this->partner_card_refer_code) != "") {
            $conditions[] = "partner_card_refer_code LIKE '%" . trim($this->partner_card_refer_code) . "%'";
        }

        if (intval($this->bill_type) > 0) {
            $conditions[] = "bill_type = " . trim($this->bill_type);
        }
        if (intval($this->cycle_day) > 0) {
            $conditions[] = "cycle_day = " . trim($this->cycle_day);
        }
        if (intval($this->card_type_id) > 0) {
            $conditions[] = "card_type_id = " . trim($this->card_type_id);
        }
        if ($this->card_code != null && trim($this->card_code) != "") {
            $conditions[] = "card_code LIKE '" . trim($this->card_code) . "'";
        }
        if ($this->card_serial != null && trim($this->card_serial) != "") {
            $conditions[] = "card_serial LIKE '" . trim($this->card_serial) . "'";
        }

        if (!empty($this->status)) {
            $conditions[] = "status IN (" . implode(',', $this->status) . ") ";
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
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("card_transaction", $conditions);
        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $card_transaction_info = Tables::selectAllDataTable("card_transaction", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($card_transaction_info != false) {
                $card_transaction =CardTransaction::setRows($card_transaction_info);

                $dataPage->data = $card_transaction;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

    public function searchForExport($offset, $limit)
    {
        $conditions = $this->getConditions($errors);
        //-------
        if ($conditions != false) {
            $card_transaction_info = Tables::selectAllDataTable("card_transaction", $conditions, "time_updated DESC ", "id", $limit, $offset);
            if ($card_transaction_info != false) {
                $card_transaction = CardTransaction::setRows($card_transaction_info);
                return $this->_getDataExportByRows($card_transaction);
            }
        }
        return false;
    }

    private function _getDataExportByRows($rows)
    {
        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'merchant_name' => $row['merchant_info']['name'],
                'merchant_refer_code' => $row['merchant_refer_code'],
                'bill_type_name' => $row['bill_type_name'],
                'cycle_day_name' => $row['cycle_day_name'],
                'card_type_name' => $row['card_type_info']['name'],
                'card_code' => $row['card_code'],
                'card_serial' => $row['card_serial'],
                'card_price' => $row['card_price'],
                'card_amount' => $row['card_amount'],
                'partner_card_name' => $row['partner_card_info']['name'],
                'partner_card_refer_code' => $row['partner_card_refer_code'],
                'partner_card_log_id' => $row['partner_card_log_id'],
                'percent_fee' => $row['percent_fee'],
                'status_name' => $row['status_name'],
                'time_created' => $row['time_created'],
            );
        }
        return $result;
    }


} 