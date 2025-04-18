<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\AdvBanner;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class AdvBannerSearch extends Model
{
    public $adv_zone_id; // Vùng quảng cáo
    public $name; // Tên
    public $time_begin_from; // thời gian bắt đầu
    public $time_begin_to;
    public $time_end_from; // thời gian kết thúc
    public $time_end_to;
    public $status; // trạng thái

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'adv_zone_id'], 'integer'],
            [['name'], 'string'],
            [['time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_to'], 'safe'],
            [['time_begin_from', 'time_begin_to',
                'time_end_from', 'time_end_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();
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

        if ($this->name != null && trim($this->name) != "") {
            $conditions[] = "name LIKE '%" . trim($this->name) . "%'";
        }


        if (intval($this->adv_zone_id) > 0) {
            $conditions[] = "adv_zone_id = " . trim($this->adv_zone_id);
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
        $adv_banner = null;
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("adv_banner", $conditions);
            $count_active = Tables::selectCountDataTable("adv_banner", $conditions . " AND status =  " . AdvBanner::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("adv_banner", $conditions . " AND status =  " . AdvBanner::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $adv_banner = Tables::selectAllDataTable("adv_banner", $conditions, "time_updated DESC ", "id", $paging->getLimit(), $paging->getOffset());
            if ($adv_banner != false) {
                $dataPage->data = $adv_banner;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }


        set_time_limit(0);
        ini_set('memory_limit', '500M');
        $adv_banner_list = $adv_banner;
        foreach ($adv_banner_list as $key => $data) {
            $adv_zone_id = $data['adv_zone_id'];
            $adv_zone = Tables::selectOneDataTable("adv_zone", "id = " . $adv_zone_id);
            $adv_banner_list[$key]['adv_zone_name'] = $adv_zone['name'];
        }

        $dataPage->data = $adv_banner_list;

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 