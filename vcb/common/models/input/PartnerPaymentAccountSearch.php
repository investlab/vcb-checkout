<?php


namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\PartnerPaymentAccount;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PartnerPaymentAccountSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $merchant_id;
    public $partner_payment_id;
    public $partner_payment_account;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'partner_payment_id'], 'integer'],
            [['partner_payment_account'], 'string'],
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

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }

        if (intval($this->partner_payment_id) > 0) {
            $conditions[] = "partner_payment_id = " . trim($this->partner_payment_id);
        }
        if ($this->partner_payment_account != null && trim($this->partner_payment_account) != "") {
            $conditions[] = "partner_payment_account LIKE '%" . trim($this->partner_payment_account) . "%'";
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
        $count_active = 0;
        $count_lock = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("partner_payment_account", $conditions);
            $count_active = Tables::selectCountDataTable("partner_payment_account", $conditions . " AND status =  " . PartnerPaymentAccount::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("partner_payment_account", $conditions . " AND status =  " . PartnerPaymentAccount::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $partner_payment_account = Tables::selectAllDataTable("partner_payment_account", $conditions, "id DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($partner_payment_account != false) {
                $dataPage->data = PartnerPaymentAccount::setRows($partner_payment_account);
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 