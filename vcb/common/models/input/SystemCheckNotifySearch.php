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
use common\models\db\CreditAccount;
use common\models\db\Merchant;
use common\models\db\SystemCheckNotify;
use common\models\db\User;
use common\models\db\UserAdminAccount;
use common\models\db\UserGroup;
use common\models\output\DataPage;
use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\helpers\Html;

class SystemCheckNotifySearch extends Model
{
    public $id;
    public $merchant_id;
    public $url_check;
    public $status;
    public $time_last_check;
    public $time_created;
    public $time_updated;
    public $last_response;
    public $channel_send_notify;


    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'branch_id','parent_id','active3D','payment_flow','email_requirement'], 'integer'],
            [['time_created_from', 'time_created_to'], 'safe'],
            [['time_created_from', 'time_created_to'], 'date', 'format' => 'dd-mm-yyyy'],
            [['url_check', 'last_response', 'channel_send_notify'], 'string'],
            [['id', 'merchant_id', 'status', 'time_last_check', 'time_created', 'time_updated'], 'integer'],
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();

        if (intval($this->status) > 0) {
            $conditions[] = "status = " . trim($this->status);
        }

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }

        if ($this->url_check != null && trim($this->url_check) != "") {
            $conditions[] = "url_check LIKE '%" . trim($this->url_check) . "%'";
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
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("system_check_notify", $conditions);
            $count_active = Tables::selectCountDataTable("system_check_notify", $conditions . " AND status =  " . SystemCheckNotify::STATUS_ACTIVE);
            $count_inactive = Tables::selectCountDataTable("system_check_notify", $conditions . " AND status =  " . SystemCheckNotify::STATUS_INACTIVE);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $system_check_notify = Tables::selectAllDataTable("system_check_notify", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($system_check_notify != false) {
                $dataPage->data = $system_check_notify;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->data = $system_check_notify;
        $dataPage->count_active = $count_active;
        $dataPage->count_inactive = $count_inactive;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

}