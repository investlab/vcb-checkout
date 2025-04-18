<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/2/2018
 * Time: 09:24
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\CheckoutOrderCallbackHistory;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class CheckoutOrderCallbackHistorySearch extends Model
{
    public $time_request_from;
    public $time_request_to;
    public $time_response_from;
    public $time_response_to;
    public $merchant_id;
    public $order_code;
    public $token_code;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id'], 'integer'],
            [['order_code', 'token_code'], 'string'],
            [['time_response_from', 'time_response_to', 'time_request_from', 'time_request_to'], 'safe'],
            [['time_response_from', 'time_response_to', 'time_request_from', 'time_request_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();
        // Thời gian gọi
        if ($this->time_request_from != null && trim($this->time_request_from) != "") {
            if (!Validation::isDate($this->time_request_from)) {
                $errors[] = 'Thời gian gọi từ không đúng định dạng';
            } else {
                $time_request_from = FormatDateTime::toTimeBegin($this->time_request_from);
                $conditions[] = "time_request >= $time_request_from ";
            }
        }

        if ($this->time_request_to != null && trim($this->time_request_to) != "") {
            if (!Validation::isDate($this->time_request_to)) {
                $errors[] = 'Thời gian gọi đến không đúng định dạng';
            } else {
                $time_request_to = FormatDateTime::toTimeEnd($this->time_request_to);
                $conditions[] = "time_request <= $time_request_to ";
            }
        }
        // Thời gian nhận KQ trả về
        if ($this->time_response_from != null && trim($this->time_response_from) != "") {
            if (!Validation::isDate($this->time_response_from)) {
                $errors[] = 'Thời gian nhận KQ từ không đúng định dạng';
            } else {
                $time_response_from = FormatDateTime::toTimeBegin($this->time_response_from);
                $conditions[] = "time_response >= $time_response_from ";
            }
        }

        if ($this->time_response_to != null && trim($this->time_response_to) != "") {
            if (!Validation::isDate($this->time_response_to)) {
                $errors[] = 'Thời gian nhận KQ đến không đúng định dạng';
            } else {
                $time_response_to = FormatDateTime::toTimeEnd($this->time_response_to);
                $conditions[] = "time_response <= $time_response_to ";
            }
        }

        if ($this->order_code != null && trim($this->order_code) != "") {
            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE order_code LIKE '%" . trim($this->order_code) . "%')";
        }
        if ($this->token_code != null && trim($this->token_code) != "") {
            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE token_code LIKE '%" . trim($this->token_code) . "%')";
        }

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "checkout_order_id IN (SELECT id FROM checkout_order WHERE merchant_id = " . trim($this->merchant_id) . ")";
        }
        if (intval($this->status) > 0) {
            $conditions[] = "status = " . trim($this->status);
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
        $merchant = array();
        $count_new = 0;
        $count_processing = 0;
        $count_error = 0;
        $count_success = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("checkout_order_callback_history", $conditions);
            $count_new = Tables::selectCountDataTable("checkout_order_callback_history", $conditions . " AND status =  " . CheckoutOrderCallbackHistory::STATUS_NEW);
            $count_processing = Tables::selectCountDataTable("checkout_order_callback_history", $conditions . " AND status =  " . CheckoutOrderCallbackHistory::STATUS_PROCESSING);
            $count_error = Tables::selectCountDataTable("checkout_order_callback_history", $conditions . " AND status =  " . CheckoutOrderCallbackHistory::STATUS_ERROR);
            $count_success = Tables::selectCountDataTable("checkout_order_callback_history", $conditions . " AND status =  " . CheckoutOrderCallbackHistory::STATUS_SUCCESS);
        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $checkout_order_callback_history = Tables::selectAllDataTable("checkout_order_callback_history", $conditions, "time_request DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($checkout_order_callback_history != false) {
                $dataPage->data = CheckoutOrderCallbackHistory::setRows($checkout_order_callback_history);
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->count_new = $count_new;
        $dataPage->count_processing = $count_processing;
        $dataPage->count_error = $count_error;
        $dataPage->count_success = $count_success;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 