<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/27/2018
 * Time: 13:11
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\PartnerPaymentFee;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PartnerPaymentFeeSearch extends Model {
    public $time_created_from; // thời gian tạo
    public $time_created_to;
    public $time_begin_from; // Thời gian bắt đầu
    public $time_begin_to;
    public $time_end_from; // Thời gian kết thúc
    public $time_end_to;

    public $method_id; // Nhóm phương thức thanh toán
    public $payment_method_id; // Phương thức thanh toán
    public $merchant_id;
    public $partner_payment_id;
    public $partner_id;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'method_id', 'payment_method_id',
            'partner_id','partner_payment_id'
            ], 'integer'],
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

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }

        if (intval($this->method_id) > 0) {
            $conditions[] = "method_id = " . trim($this->method_id);
        }

        if (intval($this->payment_method_id) > 0) {
            $conditions[] = "payment_method_id = " . trim($this->payment_method_id);
        }
        if (intval($this->partner_payment_id) > 0) {
            $conditions[] = "partner_payment_id = " . trim($this->partner_payment_id);
        }
        if (intval($this->partner_id) > 0) {
            $conditions[] = "partner_id = " . trim($this->partner_id);
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
        $count_active = 0;        $count_lock = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("partner_payment_fee", $conditions);
            $count_new = Tables::selectCountDataTable("partner_payment_fee", $conditions . " AND status =  " . PartnerPaymentFee::STATUS_NEW);
            $count_request = Tables::selectCountDataTable("partner_payment_fee", $conditions . " AND status =  " . PartnerPaymentFee::STATUS_REQUEST);
            $count_reject = Tables::selectCountDataTable("partner_payment_fee", $conditions . " AND status =  " . PartnerPaymentFee::STATUS_REJECT);
            $count_active = Tables::selectCountDataTable("partner_payment_fee", $conditions . " AND status =  " . PartnerPaymentFee::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("partner_payment_fee", $conditions . " AND status =  " . PartnerPaymentFee::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $partner_payment_fee = Tables::selectAllDataTable("partner_payment_fee", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($partner_payment_fee != false) {
                $dataPage->data = PartnerPaymentFee::setRows($partner_payment_fee);
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