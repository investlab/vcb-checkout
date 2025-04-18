<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/19/2018
 * Time: 14:51
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\CardLogFull;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class CardLogSearch extends Model
{
    public $time_created_from; // Thời gian tạo
    public $time_created_to;
    public $time_card_updated_from; // Thời gian thẻ bị gạch
    public $time_card_updated_to;
    public $time_create_transaction_from; // Thời gian xử lý tạo giao dịch
    public $time_create_transaction_to;
    public $time_backup_from; // Thời gian backup
    public $time_backup_to;
    public $withdraw_time_limit_from; // Thời hạn được rút
    public $withdraw_time_limit_to;

    public $id; // mã
    public $bill_type; // bill_type
    public $merchant_id; // merchant
    public $merchant_refer_code; // merchant
    public $cycle_day; // kỳ thanh toán
    public $card_type_id; // loại thẻ
    public $partner_card_id; // đối tác
    public $partner_card_refer_code; // đối tác
    public $card_code; // mã thẻ
    public $card_serial;
    public $card_status; // trạng thái thẻ
    public $transaction_status; // trạng thái giao dịch
    public $backup_status; // trạng thái backup

    public $card_status_merchant;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'merchant_id', 'cycle_day', 'id', 'bill_type', 'partner_card_id',
                'card_type_id', 'card_status', 'transaction_status', 'backup_status','card_status_merchant'
            ], 'integer'],
            [['merchant_refer_code', 'card_code', 'partner_card_refer_code','card_serial'], 'string'],
            [['time_created_from', 'time_created_to',
                'time_card_updated_from', 'time_card_updated_to',
                'time_create_transaction_from', 'time_create_transaction_to',
                'time_backup_from', 'time_backup_to'
            ], 'safe'],
            [['time_created_from', 'time_created_to',
                'time_card_updated_from', 'time_card_updated_to',
                'time_create_transaction_from', 'time_create_transaction_to',
                'time_backup_from', 'time_backup_to',
                'withdraw_time_limit_from', 'withdraw_time_limit_to',
            ], 'date', 'format' => 'dd-mm-yyyy'],
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
        // Thòi gian thẻ bị gạch
        if ($this->time_card_updated_from != null && trim($this->time_card_updated_from) != "") {
            if (!Validation::isDate($this->time_card_updated_from)) {
                $errors[] = 'Ngày thẻ bị gạch từ không đúng định dạng';
            } else {
                $time_card_updated_from = FormatDateTime::toTimeBegin($this->time_card_updated_from);
                $conditions[] = "time_card_updated >= $time_card_updated_from ";
            }
        }

        if ($this->time_card_updated_to != null && trim($this->time_card_updated_to) != "") {
            if (!Validation::isDate($this->time_card_updated_to)) {
                $errors[] = 'Ngày thẻ bị gạch đến không đúng định dạng';
            } else {
                $time_card_updated_to = FormatDateTime::toTimeEnd($this->time_card_updated_to);
                $conditions[] = "time_card_updated <= $time_card_updated_to ";
            }
        }

        // Thòi gian xử lý tạo giao dịch
        if ($this->time_create_transaction_from != null && trim($this->time_create_transaction_from) != "") {
            if (!Validation::isDate($this->time_create_transaction_from)) {
                $errors[] = 'Ngày xử lý tạo GD từ không đúng định dạng';
            } else {
                $time_create_transaction_from = FormatDateTime::toTimeBegin($this->time_create_transaction_from);
                $conditions[] = "time_create_transaction >= $time_create_transaction_from ";
            }
        }

        if ($this->time_create_transaction_to != null && trim($this->time_create_transaction_to) != "") {
            if (!Validation::isDate($this->time_create_transaction_to)) {
                $errors[] = 'Ngày xử lý tạo GD đến không đúng định dạng';
            } else {
                $time_create_transaction_to = FormatDateTime::toTimeEnd($this->time_create_transaction_to);
                $conditions[] = "time_create_transaction <= $time_create_transaction_to ";
            }
        }

        // Thòi gian backup
        if ($this->time_backup_from != null && trim($this->time_backup_from) != "") {
            if (!Validation::isDate($this->time_backup_from)) {
                $errors[] = 'Ngày backup từ không đúng định dạng';
            } else {
                $time_backup_from = FormatDateTime::toTimeBegin($this->time_backup_from);
                $conditions[] = "time_backup >= $time_backup_from ";
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

        if (!empty($this->card_status)) {
            $conditions[] = "card_status IN (" . implode(',', $this->card_status) . ") ";
        }
        if (!empty($this->transaction_status)) {
            $conditions[] = "transaction_status IN (" . implode(',', $this->transaction_status) . ") ";
        }
        if (!empty($this->backup_status)) {
            $conditions[] = "backup_status IN (" . implode(',', $this->backup_status) . ") ";
        }
        if (intval($this->card_status_merchant) > 0) {
            $conditions[] = "card_status = " . trim($this->card_status_merchant);
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
        $count_success = 0;
        $total_card_price = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("card_log_full", $conditions);
            $count_success = Tables::selectCountDataTable("card_log_full", $conditions . " AND card_status =  " . CardLogFull::CARD_STATUS_SUCCESS);
        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $card_log_full_info = Tables::selectAllDataTable("card_log_full", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($card_log_full_info != false) {
                $card_log_full = CardLogFull::setRows($card_log_full_info);
                foreach($card_log_full as $key => $data) {
                    $total_card_price += $data['card_price'];
                }

                $dataPage->data = $card_log_full;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;
        $dataPage->count_success = $count_success;
        $dataPage->total_card_price  = $total_card_price;

        return $dataPage;
    }

    public function searchForExport($offset, $limit)
    {
        $conditions = $this->getConditions($errors);
        //-------
        if ($conditions != false) {
            $card_log_full_info = Tables::selectAllDataTable("card_log_full", $conditions, "time_updated DESC ", "id", $limit, $offset);
            if ($card_log_full_info != false) {
                $card_log = CardLogFull::setRows($card_log_full_info);
                return $this->_getDataExportByRows($card_log);
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
                'card_status_name' => $row['card_status_name'],
                'time_created' => $row['time_created'],
                'time_card_updated' => $row['time_card_updated'],
            );
        }
        return $result;
    }

} 