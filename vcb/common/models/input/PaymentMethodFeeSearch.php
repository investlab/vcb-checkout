<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\PaymentMethodFee;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PaymentMethodFeeSearch extends Model
{

    public $time_created_from; // thời gian tạo
    public $time_created_to;
    public $time_begin_from; // thời gian bắt đầu
    public $time_begin_to;
    public $time_end_from; // thời gian kết thúc
    public $time_end_to;

    public $payment_method_id; // phương thức thanh toán
    public $status; // trạng thái

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'payment_method_id'], 'integer'],
            [['time_created_from', 'time_created_to', 'time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_to'], 'safe'],
            [['time_created_from', 'time_created_to', 'time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();
        // Ngày tạo
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

//        Ngày bắt đầu
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

        // Ngày kết thúc
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

        if ($this->payment_method_id > 0) {
            $conditions[] = "payment_method_id = " . trim($this->payment_method_id);
        }
        if ($this->status > 0) {
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
        $count_active = 0;
        $count_lock = 0;
        $count_notreject = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("payment_method_fee", $conditions);
            $count_active = Tables::selectCountDataTable("payment_method_fee", $conditions . " AND status =  " . PaymentMethodFee::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("payment_method_fee", $conditions . " AND status =  " . PaymentMethodFee::STATUS_LOCK);
            $count_notreject = Tables::selectCountDataTable("payment_method_fee", $conditions . " AND status =  " . PaymentMethodFee::STATUS_REJECT);

        } else {
            $count = 0;
        }

        $payment_method_fee = null;
        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $payment_method_fee = Tables::selectAllDataTable("payment_method_fee", $conditions, "time_updated DESC ", "id", $paging->getLimit(), $paging->getOffset());
            if ($payment_method_fee != false) {
                foreach ($payment_method_fee as $key => $data) {
                    $payment_method_id = $data['payment_method_id'];
                    if (intval($payment_method_id) > 0) {
                        $payment_method = Tables::selectOneDataTable("payment_method", "id = " . $payment_method_id);
                        if ($payment_method) {
                            $payment_method_fee[$key]['payment_method_code'] = $payment_method['code'];
                            $payment_method_fee[$key]['payment_method_name'] = $payment_method['name'];
                        }

                    }
                }
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }


        $dataPage->data = $payment_method_fee;

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->count_notreject = $count_notreject;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 