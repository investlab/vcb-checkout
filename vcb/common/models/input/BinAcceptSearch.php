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
use common\models\db\BinAccept;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class BinAcceptSearch extends Model
{
    public $bin_code;
    public $card_type;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'bin_code'], 'integer'],
            [['card_type'], 'string'],
            [['time_created_from', 'time_created_to'], 'safe'],
            [['time_created_from', 'time_created_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();

        if (!empty($this->bin_code)) {
            $conditions[] = "bin_code=" . trim($this->bin_code);
        }
        if ($this->card_type != null && trim($this->card_type) != "") {
            $conditions[] = "card_type = '" . trim($this->card_type) . "'";
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
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("bin_accept", $conditions);
            $count_active = Tables::selectCountDataTable("bin_accept", $conditions . " AND status =  " . BinAccept::STATUS_ACTIVE);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $bin_accept = Tables::selectAllDataTable("bin_accept", $conditions, "time_created DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($bin_accept != false) {
                $dataPage->data = $bin_accept;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $list = $bin_accept;
        foreach ($list as $key => $data) {
            $list[$key]['operators'] = BinAccept::getOperatorsByStatus($data);
        }

        $dataPage->data = $list;

        $dataPage->count_active = $count_active;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

} 