<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 15:45
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\MerchantCardFee;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class MerchantCardFeeSearch extends Model
{
    public $time_created_from; // thời gian tạo
    public $time_created_to;
    public $time_begin_from; // Thời gian bắt đầu
    public $time_begin_to;
    public $time_end_from; // Thời gian kết thúc
    public $time_end_to;

    public $card_type_id;
    public $bill_type;
    public $cycle_day;
    public $partner_id;
    public $merchant_id;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id',
                'card_type_id', 'bill_type', 'cycle_day', 'partner_id'], 'integer'],
            [['time_created_from', 'time_created_to',
                'time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_end'], 'safe'],
            [['time_created_from', 'time_created_to',
                'time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_end'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();

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

        if ($this->time_begin_from != null && trim($this->time_begin_from) != "") {
            if (!Validation::isDate($this->time_begin_from)) {
                $errors[] = 'Ngày bắt đầu từ không đúng định dạng';
            } else {
                $time_begin_from = FormatDateTime::toTimeBegin($this->time_begin_from);
                $conditions[] = "time_begin >= $time_begin_from ";
            }
        }

        if ($this->time_begin_to != null && trim($this->time_begin_to) != "") {
            if (!Validation::isDate($this->time_begin_to)) {
                $errors[] = 'Ngày bắt đầu đến không đúng định dạng';
            } else {
                $time_begin_to = FormatDateTime::toTimeEnd($this->time_begin_to);
                $conditions[] = "time_begin <= $time_begin_to ";
            }
        }

        if ($this->time_end_from != null && trim($this->time_end_from) != "") {
            if (!Validation::isDate($this->time_end_from)) {
                $errors[] = 'Ngày kết thúc từ không đúng định dạng';
            } else {
                $time_end_from = FormatDateTime::toTimeBegin($this->time_end_from);
                $conditions[] = "time_end >= $time_end_from ";
            }
        }

        if ($this->time_end_to != null && trim($this->time_end_to) != "") {
            if (!Validation::isDate($this->time_end_to)) {
                $errors[] = 'Ngày kết thúc đến không đúng định dạng';
            } else {
                $time_end_to = FormatDateTime::toTimeEnd($this->time_end_to);
                $conditions[] = "time_end <= $time_end_to ";
            }
        }

        if (intval($this->card_type_id) > 0) {
            $conditions[] = "card_type_id = " . trim($this->card_type_id);
        }

        if (intval($this->bill_type) > 0) {
            $conditions[] = "bill_type = " . trim($this->bill_type);
        }

        if (intval($this->cycle_day) > 0) {
            $conditions[] = "cycle_day = " . trim($this->cycle_day);
        }
        if (intval($this->partner_id) > 0) {
            $conditions[] = "partner_id = " . trim($this->partner_id);
        }
        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
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
        $count_new = 0;
        $count_request = 0;
        $count_reject = 0;
        $count_active = 0;
        $count_lock = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("merchant_card_fee", $conditions);
            $count_new = Tables::selectCountDataTable("merchant_card_fee", $conditions . " AND status =  " . MerchantCardFee::STATUS_NEW);
            $count_request = Tables::selectCountDataTable("merchant_card_fee", $conditions . " AND status =  " . MerchantCardFee::STATUS_REQUEST);
            $count_reject = Tables::selectCountDataTable("merchant_card_fee", $conditions . " AND status =  " . MerchantCardFee::STATUS_REJECT);
            $count_active = Tables::selectCountDataTable("merchant_card_fee", $conditions . " AND status =  " . MerchantCardFee::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("merchant_card_fee", $conditions . " AND status =  " . MerchantCardFee::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $merchant_card_fee = Tables::selectAllDataTable("merchant_card_fee", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($merchant_card_fee != false) {
                $dataPage->data = MerchantCardFee::setRows($merchant_card_fee);
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->count_new = $count_new;
        $dataPage->count_request = $count_request;
        $dataPage->count_reject = $count_reject;
        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 