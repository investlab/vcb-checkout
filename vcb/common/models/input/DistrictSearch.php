<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\Zone;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class DistrictSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $name;
    public $city_id;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'city_id'], 'integer'],
            [['name'], 'string'],
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

        if ($this->city_id > 0) {
            $conditions[] = "parent_id = " . trim($this->city_id);
        }
        if ($this->status > 0) {
            $conditions[] = "status = " . trim($this->status);
        }

        $conditions[] = "parent_id > 0 AND level = 3";

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
            $count = Tables::selectCountDataTable("zone", $conditions);
            $count_active = Tables::selectCountDataTable("zone", $conditions . " AND status =  " . Zone::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("zone", $conditions . " AND status =  " . Zone::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $district = Tables::selectAllDataTable("zone", $conditions, "`left` ASC ", "id", $paging->getLimit(), $paging->getOffset());
            if ($district != false) {
                $dataPage->data = $district;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $district_list = $district;
        foreach ($district_list as $key => $data) {
            $parent_id = $data['parent_id'];

            $city = Tables::selectOneDataTable('zone', "id = " . $parent_id);
            $district_list[$key]['city_name'] = $city['name'];

        }

        $dataPage->data = $district_list;

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 