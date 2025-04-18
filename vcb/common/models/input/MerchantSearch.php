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
use common\models\db\User;
use common\models\db\UserAdminAccount;
use common\models\db\UserGroup;
use common\models\output\DataPage;
use Yii;
use yii\base\Model;
use yii\data\Pagination;

class MerchantSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $name;
    public $status;
    public $merchant_code;
    public $branch_id;
    public $active3D;
    public $payment_flow;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'branch_id','active3D','payment_flow'], 'integer'],
            [['name', 'merchant_code'], 'string'],
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

        if (intval($this->status) > 0) {
            $conditions[] = "status = " . trim($this->status);
        }

        if (intval($this->branch_id) > 0) {
            $conditions[] = "branch_id = " . trim($this->branch_id);
        }

        if (!empty($this->merchant_code)) {
            $conditions[] = "merchant_code = '" . trim($this->merchant_code) . "'";
        }
        if ($this->active3D != null)
        {
            $conditions[] = 'active3D ='.$this->active3D;
        }
        if ($this->payment_flow != null)
        {
            $conditions[] = 'payment_flow ='.$this->payment_flow;
        }
        // Check quyền tạo merchant
        $user_id = Yii::$app->get('user')->id;
        $user_group = UserGroup::findOne(['code'=>'ADMIN', 'status'=>UserGroup::STATUS_ACTIVE]);
        $user_admin = UserAdminAccount::findOne(['user_id' => $user_id, 'user_group_id' => $user_group->id, 'status' => UserAdminAccount::STATUS_ACTIVE]);
        if (empty($user_admin)) {
            $conditions[] = "user_created = " . $user_id;
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
            $count = Tables::selectCountDataTable("merchant", $conditions);
            $count_active = Tables::selectCountDataTable("merchant", $conditions . " AND status =  " . Merchant::STATUS_ACTIVE);
            $count_lock = Tables::selectCountDataTable("merchant", $conditions . " AND status =  " . Merchant::STATUS_LOCK);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $merchant = Tables::selectAllDataTable("merchant", $conditions, "time_updated DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($merchant != false) {
                $dataPage->data = $merchant;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }
        $list = $merchant;
        $credit_accounts = CreditAccount::findAll(['status' => CreditAccount::STATUS_ACTIVE]);
        foreach ($list as $key => $data) {
            $list[$key]['operators'] = Merchant::getOperatorsByStatus($data);

            $account = Tables::selectOneDataTable('account',['merchant_id = :merchant_id',"merchant_id" => $data['id']]);
            $list[$key]['account'] = $account;
            $list[$key]['credit_account'] = [];

            if (!empty($credit_accounts)) {
                foreach ($credit_accounts as $key_credit => $val_credit) {
                    if ($val_credit['merchant_id'] == $data['id']) {
                        $list[$key]['credit_account'] = [
                            'branch_code' => $val_credit['branch_code'],
                            'account_number' => $val_credit['account_number'],
                        ];
                    }
                }
            }
        }

        $total_balance = 0;
        $total_balance_pending = 0;
        foreach($list as $keyL => $dataL){
            $total_balance += @$dataL['account']['balance'];
            $total_balance_pending += @$dataL['account']['balance_pending'];

        }
        User::setUsernameForRows($list);

        $dataPage->data = $list;

        $dataPage->count_active = $count_active;
        $dataPage->count_lock = $count_lock;
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;
        $dataPage->total_balance = $total_balance;
        $dataPage->total_balance_pending = $total_balance_pending;

        return $dataPage;
    }

} 