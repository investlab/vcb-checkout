<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\PaymentMethod;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PaymentMethodSearch extends Model
{
    public $time_created_from; // thời gian tạo
    public $time_created_to;

    public $name; // tên
    public $code; // mã
    public $status; // trạng thái
    public $transaction_type_id; // loại giao dịch

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'transaction_type_id'], 'integer'],
            [['name', 'code'], 'string'],
            [['time_created_from', 'time_created_to'], 'safe'],
            [['time_created_from', 'time_created_to'], 'date', 'format' => 'dd-mm-yyyy'],
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


        if ($this->name != null && trim($this->name) != "") {
            $conditions[] = "name LIKE '%" . trim($this->name) . "%'";
        }

        if ($this->code != null && trim($this->code) != "") {
            $conditions[] = "code LIKE '%" . trim($this->code) . "%'";
        }

        if ($this->status > 0) {
            $conditions[] = "status = " . trim($this->status);
        }
        if ($this->transaction_type_id > 0) {
            $conditions[] = "transaction_type_id = " . trim($this->transaction_type_id);
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
        $marketing_campaign = null;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("payment_method", $conditions);
            $count_active = Tables::selectCountDataTable("payment_method", $conditions . " AND status =  " . PaymentMethod::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("payment_method", $conditions . " AND status =  " . PaymentMethod::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $payment_method = null;
        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $payment_method = Tables::selectAllDataTable("payment_method", $conditions, "time_updated DESC ", "id", $paging->getLimit(), $paging->getOffset());
            if ($payment_method != false) {
                $dataPage->data = $payment_method;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $list = $payment_method;
        if ($list != null) {
            foreach ($list as $key => $data) {
                $transaction_type_id = $data['transaction_type_id'];
                $transaction_type = Tables::selectOneDataTable("transaction_type", ["id = :id", "id" => $transaction_type_id]);
                $list[$key]["transaction_type_name"] = $transaction_type['name'];
            }
        }


        $dataPage->data = $list;

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 