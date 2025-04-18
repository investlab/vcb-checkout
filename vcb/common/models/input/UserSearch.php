<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\business\BranchBusiness;
use common\models\business\UserAdminAccountBusiness;
use common\models\db\User;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class UserSearch extends Model
{
    public $time_created_from;
    public $time_created_to;
    public $fullname;
    public $username;
    public $email;
    public $user_group;
    public $status;
    public $info_user;
//    public $user_group_code;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'user_group'], 'integer'],
            [['fullname', 'username', 'info_user'], 'string'],
            [['time_created_from', 'time_created_to'], 'date'],
            [['email'], 'email'],
        ];
    }

    public function search()
    {
        $query = User::find()->orderBy('time_created desc');

        $errors = [];
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors[] = 'Ngày tạo từ không đúng định dạng';
            }
        }
        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors[] = 'Ngày tạo đến không đúng định dạng';
            }
        }
        if ($this->email != null && trim($this->email) != "") {
            if (!Validation::isEmail($this->email)) {
                $errors[] = 'Email không đúng định dạng';
            }
        }

        $this->_addConditionUserGroup($query);

        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            $fromdate = FormatDateTime::toTimeBegin($this->time_created_from);
            $query->andWhere(['>=', 'time_created', $fromdate]);
        }
        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            $todate = FormatDateTime::toTimeEnd($this->time_created_to);
            $query->andWhere(['<=', 'time_created', $todate]);
        }

        if ($this->fullname != null && trim($this->fullname) != "") {
            $query->andWhere(['LIKE', 'fullname', trim($this->fullname)]);
        }
        if ($this->username != null && trim($this->username) != "") {
            $query->andWhere(['LIKE', 'username', trim($this->username)]);
        }
        if ($this->email != null && trim($this->email) != "") {
            $query->andWhere(['LIKE', 'email', trim($this->email)]);
        }
        if ($this->status > 0) {
            $query->andWhere(['=', 'status', trim($this->status)]);
        }
        if ($this->user_group > 0) {
            $query->andWhere('id IN (select user_id from user_admin_account where user_group_id = ' . $this->user_group . ')');
        }

//        if($this->user_group_code != null && trim($this->user_group_code) != ""){
//            $tblUserGroup = Tables::selectAllDataTable("user_group","code LIKE '%".$this->user_group_code."%'");
//
//            $user_group_id = array();
//            if($tblUserGroup){
//                foreach($tblUserGroup as $keyG => $dataG){
//                    $user_group_id[] = $dataG['id'];
//                }
//            }
//            $query->andWhere(['IN','user_group_id',implode(',',$user_group_id)]);
//         }

        if ($this->info_user != null && trim($this->info_user) != "") {
            $query->andWhere(['=', 'username', trim($this->info_user)]);
            $query->orWhere(['LIKE', 'fullname', trim($this->info_user)]);
            $query->orWhere(['=', 'mobile', trim($this->info_user)]);

        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());
        $data = $query->asArray()->all();

        foreach ($data as $k => $v) {
            $data[$k]['group_codes'] = '';
            if ($v['branch_id'] != ''){
                $data[$k]['branch_name'] = self::getBranchName($v['branch_id']);
            }else{
                $data[$k]['branch_name'] = '';

            }

            $admin_account = UserAdminAccountBusiness::getByUserId($v['id']);
            if ($admin_account != null) {
                foreach ($admin_account as $k1 => $v1) {
                    if ($data[$k]['group_codes'] == '') {
                        $data[$k]['group_codes'] = $v1['group_code'].' - <strong>'.$v1['group_name'].'</strong>';
                    } else {
                        $data[$k]['group_codes'] .= '<hr>' . $v1['group_code'].' - <strong>'.$v1['group_name'].'</strong>';
                    }
                }
            }
        }

        $dataPage->data = $data;

        $lock = $query->andWhere("status = 2");
        $dataPage->totalLock = $lock->count();

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }

    protected function getBranchName($id){
        $branch = BranchBusiness::viewDetail($id);
        return ($branch['data']['name']);


    }

    protected function _addConditionUserGroup(&$query)
    {
        $sub_user_group_ids = User::getSubUserGroupIds(\Yii::$app->user->getId(), $user_info);
        if ($user_info['username'] != 'administrator') {
            if (empty($sub_user_group_ids)) {
                $sub_user_group_ids = array(0);
            }
            $query->andWhere("(id IN (SELECT user_id FROM user_admin_account WHERE user_group_id IN (" . implode(',', $sub_user_group_ids) . ")) OR (SELECT COUNT(*) FROM user_admin_account WHERE user_admin_account.user_id = user.id LIMIT 0,1) = 0 )");
            //$query->andWhere(['IN', 'user_group_id', $sub_user_group_ids]);
        }
    }
} 