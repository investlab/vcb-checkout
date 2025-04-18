<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/28/2018
 * Time: 09:08
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\Branch;
use common\models\db\Merchant;
use common\models\db\User;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class BranchSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $name;
    public $city;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status'], 'integer'],
            [['name', 'city'], 'string'],
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
        if ($this->city != null && trim($this->city) != "") {
            $conditions[] = "city LIKE '%" . trim($this->city) . "%'";
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
        $count_active = 0;
        $count_lock = 0;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("branch", $conditions);
            $count_active = Tables::selectCountDataTable("branch", $conditions . " AND status =  " . Branch::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("branch", $conditions . " AND status =  " . Branch::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $branchs = Tables::selectAllDataTable("branch", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($branchs != false) {
                $dataPage->data = $branchs;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $list = $branchs;
        $merchants = Merchant::findAll(['status' => Merchant::STATUS_ACTIVE]);
        foreach ($list as $key => $data) {
            $list[$key]['operators'] = Branch::getOperatorsByStatus($data);
            if (!empty($merchants)) {
                foreach ($merchants as $key_mc => $merchant) {
                    if ($data['id'] == $merchant['branch_id']) {
                        $list[$key]['merchant'][] = $merchant['name'];
                    }
                }
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