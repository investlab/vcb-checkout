<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/29/2018
 * Time: 09:15
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\UserLogin;
use common\models\output\DataPage;
use yii\base\Model;
use common\components\utils\Validation;
use yii\data\Pagination;
use common\models\db\User;

class UserLoginSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $merchant_id;
    public $fullname;
    public $email;
    public $mobile;
    public $ips;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id'], 'integer'],
            [['fullname', 'email', 'mobile', 'ips'], 'string'],
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
        if ($this->fullname != null && trim($this->fullname) != "") {
            $conditions[] = "fullname LIKE '%" . trim($this->fullname) . "%'";
        }
        if ($this->email != null && trim($this->email) != "") {
            $conditions[] = "email LIKE '" . trim($this->email) . "'";
        }
        if ($this->mobile != null && trim($this->mobile) != "") {
            $conditions[] = "mobile LIKE '" . trim($this->mobile) . "'";
        }
        if (intval($this->status) > 0) {
            $conditions[] = "status = " . trim($this->status);
        }
        if (!empty($this->ips)) {
            $conditions[] = "ips LIKE '%" . $this->ips . "%'";
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
            $count = Tables::selectCountDataTable("user_login", $conditions);
            $count_active = Tables::selectCountDataTable("user_login", $conditions . " AND status =  " . UserLogin::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("user_login", $conditions . " AND status =  " . UserLogin::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $user_login = Tables::selectAllDataTable("user_login", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($user_login != false) {
                $dataPage->data = UserLogin::setRows($user_login);
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