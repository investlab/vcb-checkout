<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/17/2016
 * Time: 9:43 AM
 */

namespace common\models\business;

use common\components\libs\Tables;
use common\models\db\User;
use common\models\db\UserAdminAccount;
use common\models\db\UserGroup;
use common\models\form\UserForm;
use common\util\TextUtil;
use common\models\db\CreditPartnerBranch;
use common\models\business\CreditPartnerUserBusiness;
use Yii;

class UserBusiness
{
    const MAX_LOGIN_FAIL = 5;

    public static function getByID($id)
    {
        return User::findOne(['id' => $id]);
    }

    public static function getByIDToArray($id)
    {
        $user = User::findOne(['id' => $id]);
        if ($user != null) {
            return $user->toArray();
        }
        return $user;
    }

    public static function getUsername($user_id)
    {
        $user_name = '';
        $user = self::getByIDToArray($user_id);
        if ($user != null) {
            $user_name = $user['username'];
        }
        return $user_name;
    }

    public static function getUserIdByUsername($username)
    {
        $user_ids = [];
        $users = User::find()->andWhere("username LIKE '%" . $username . "%'")->asArray()->all();
        foreach ($users as $k => $v) {
            $user_ids[] = $v['id'];
        }
        return $user_ids;
    }

    public static function getByListId($list_user_id)
    {
        if ($list_user_id != null) {
            $query = User::find();
            $query->andWhere('id IN (' . implode(',', $list_user_id) . ')');
            $data = $query->asArray()->all();
            return $data;
        } else {
            return [];
        }
    }

    public static function getUserByUsername($username)
    {
        $user = User::find()->andWhere(['LIKE', 'username', $username])->one();
        return $user;
    }

    public static function getByUsername($username)
    {
        return User::findOne(['username' => $username, 'status' => 1]);
    }

    public static function getByUserandGroup($username, $user_group_code)
    {
        return User::findOne(['username' => $username, 'user_group_code' => $user_group_code, 'status' => 1]);
    }

    public static function getUserGroupByID($id)
    {
        return UserGroup::findOne(['id' => $id]);
    }

    public static function checkUserStoreKeeper($user_id)
    {
        $user = User::find()->andWhere(['id' => $user_id])->asArray()->one();
        if ($user != null) {
            $user_group = UserGroup::find()
                ->andWhere(['=', 'id', $user['user_group_id']])
                ->andWhere(['=', 'code', 'STORE_KEEPER'])
                ->asArray()->all();
            if ($user_group != null) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function checkUserAccountStoreKeeper($user_admin_account_id)
    {
        $data = UserAdminAccount::find()->andWhere(['id' => $user_admin_account_id])->asArray()->one();
        if ($data != null) {
            $user_group = UserGroup::find()
                ->andWhere(['=', 'id', $data['user_group_id']])
                ->andWhere(['=', 'code', 'STORE_KEEPER'])
                ->asArray()->all();
            if ($user_group != null) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function checkUserAccountSales($user_admin_account_id)
    {
        $data = UserAdminAccount::find()->andWhere(['id' => $user_admin_account_id])->asArray()->one();
        if ($data != null) {
            $user_group = UserGroup::find()
                ->andWhere(['=', 'id', $data['user_group_id']])
                ->andWhere("code IN ('SALES_MANAGE', 'SALES_STAFF', 'TSA_MANAGE', 'AREA_SALES_MANAGER','SALES_PART_TIME')")
                ->asArray()->all();
            if ($user_group != null) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function checkUserAccountCredit($user_admin_account_id)
    {
        $data = UserAdminAccount::find()->andWhere(['id' => $user_admin_account_id])->asArray()->one();
        if ($data != null) {
            $user_group = UserGroup::find()
                ->andWhere(['=', 'id', $data['user_group_id']])
                ->andWhere("code IN ('CREDIT_STAFF', 'CREDIT_MANAGE')")
                ->asArray()->all();
            if ($user_group != null) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    static function getByUserGroupId($user_groupId)
    {
        $list_user = User::find()->andWhere(['IN', 'user_group_id', $user_groupId])->asArray()->all();
        $list_userId = [];
        foreach ($list_user as $keyUser => $dataUser) {
            $list_userId[] = $dataUser['id'];
        }
        return $list_userId;
    }

    public static function checkLoginFail($user_login_id, &$error_message = '')
    {
        // add login fail
        $inputs = array(
            'user_login_id' => intval($user_login_id),
        );
        $result = UserLoginFailBusiness::add($inputs);
        if ($result['error_message'] == '') {
            $time = time() - 3600;
            // count login fail
            $count = Tables::selectCountDataTable("user_login_fail", ["user_login_id = :user_login_id AND time_failed >= :time ", 'user_login_id' => $user_login_id, 'time' => $time]);
            if ($count >= self::MAX_LOGIN_FAIL) {
                $model = self::getByID($user_login_id);
                if ($model != null) {
                    $model->status = User::STATUS_LOCK;
                    if ($model->save()) {
                        $error_message = 'Tài khoản bị khóa do đăng nhập sai nhiều lần lần';
                        // delete all login fail
                        $sql = "DELETE FROM user_login_fail WHERE user_login_id = " . intval($user_login_id);
                        $connect = User::getDb();
                        $command = $connect->createCommand($sql);
                        $command->execute();
                        return true;
                    }
                }
            }
            // delete login fail more than 1 hour
            $sql = "DELETE FROM user_login_fail WHERE user_login_id = " . intval($user_login_id) . " AND time_failed < " . $time;
            $connect = User::getDb();
            $command = $connect->createCommand($sql);
            $command->execute();
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $params : fullname, username, email, mobile, phone, gender, address, zone_id, birthday, user_created
     * @param type $rollback
     * @return type
     */
    public static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $password = $params['password'];
        $user = new User();
        $user->fullname = $params['fullname'];
        $user->username = $params['username'];
        $user->password = md5($password);
        $user->email = $params['email'];
        $user->mobile = $params['mobile'];
        $user->phone = $params['phone'];
        $user->gender = $params['gender'];
        $user->status = User::STATUS_ACTIVE;
        $user->birthday = $params['birthday'];
        $user->branch_id = $params['branch_id'];
        $user->time_created = time();
        if ($user->validate()) {
            if ($user->save()) {
                $error_message = '';
                $id = $user->getDb()->getLastInsertID();
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thêm người dùng';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    /**
     *
     * @param type $params : fullname, username, email, mobile, phone, gender, address, zone_id, birthday, user_created
     * @param type $rollback
     * @return type
     */
    public static function addAndSendEmailPassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------        
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $error_message = '';
            $commit = true;
            $id = $result['id'];
            $password = $result['password'];
            self::_sendEmailPassword($params['email'], $params['username'], $password);
        } else {
            $error_message = $result['error_message'];
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    /**
     *
     * @param type $params : user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, credit_partner_id, credit_partner_branch_ids, user_created
     * @param type $rollback
     * @return type
     */
    public static function addCreditPartnerUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------        
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $password = $result['password'];
            $inputs = array(
                'credit_partner_id' => $params['credit_partner_id'],
                'credit_partner_branch_ids' => $params['credit_partner_branch_ids'],
                'user_id' => $id,
                'user_created' => $params['user_created'],
            );
            $result = CreditPartnerUserBusiness::add($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
                self::_sendEmailPassword($params['email'], $params['username'], $password);
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    /**
     *
     * @param type $params : user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, installment_bank_id, user_created
     * @param type $rollback
     * @return type
     */
    public static function addInstallmentBankUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------        
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $password = $result['password'];
            $inputs = array(
                'installment_bank_id' => $params['installment_bank_id'],
                'user_id' => $id,
                'user_created' => $params['user_created'],
            );
            $result = InstallmentBankUserBusiness::add($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
                self::_sendEmailPassword($params['email'], $params['username'], $password);
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    private static function _sendEmailPassword($email, $username, $password)
    {
        SendMailBussiness::send($email, 'Mật khẩu tài khoản quản trị Vietcombank', 'register_user', [
                'username' => $username,
                'password' => $password]
        );
    }

    /**
     *
     * @param type $params : id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, user_updated
     * @param type $rollback
     * @return type
     */
    public static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $user = User::findOne(['id' => $params['id']]);
        if ($user != null) {
            $user->fullname = $params['fullname'];
            $user->email = $params['email'];
            $user->mobile = $params['mobile'];
            $user->phone = $params['phone'];
            $user->gender = $params['gender'];
            $user->birthday = $params['birthday'];
            $user->branch_id = $params['branch_id'];
            $user->time_updated = time();
            if ($user->validate()) {
                if ($user->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm người dùng';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Người dùng không tồn tại';
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, credit_partner_id, credit_partner_branch_ids, user_updated
     * @param type $rollback
     * @return type
     */
    public static function updateCreditPartnerUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $result = self::update($params, false);
        if ($result['error_message'] == '') {
            $inputs = array(
                'user_id' => $params['id'],
            );
            $result = CreditPartnerUserBusiness::deleteByUserId($inputs, false);
            if ($result['error_message'] == '') {
                $inputs = array(
                    'credit_partner_id' => $params['credit_partner_id'],
                    'credit_partner_branch_ids' => $params['credit_partner_branch_ids'],
                    'user_id' => $params['id'],
                    'user_created' => $params['user_updated'],
                );
                $result = CreditPartnerUserBusiness::add($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------        
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    // Update left,right trong User Group
    public static function _updateIndexUserGroup($table, $id = false)
    {
        $level = 1;
        $index = 0;
        $parent_id = 0;
        if ($id !== false && is_numeric($id)) {
            $usergroup = Tables::selectOneDataTable($table, "$table.id = $id");
            if ($usergroup != false) {
                $level = $usergroup['level'] - 1 == 0 ? $level : $usergroup['level'] - 1;
                $index = $usergroup['left'] - 1 < 0 ? $index : $usergroup['left'];
                $parent_id = $usergroup['parent_id'];
            }
        }
        $queries = UserBusiness::_getQueryUpdateIndexUserGroup($table, $parent_id, $index, $level);
        if (!empty($queries)) {
            $connection = Yii::$app->getDb();
            foreach ($queries as $key => $q) {
                $command = $connection->createCommand($q);
                $result = $command->execute();
            }
        }
        return true;
    }

    protected static function _getQueryUpdateIndexUserGroup($table, $parent_id = 0, &$index = 0, $level = 1)
    {
        $result = array();
        $usergroup = Tables::selectAllDataTable($table, "$table.parent_id = $parent_id ", "$table.position ASC, $table.id ASC ");
        if ($usergroup != false) {
            foreach ($usergroup as $row) {
                $index++;
                $result["id_" . $row['id']] = "UPDATE $table SET $table.level = $level, $table.left = $index, ";
                $temp = UserBusiness::_getQueryUpdateIndexUserGroup($table, $row['id'], $index, $level + 1);
                $index++;
                $result["id_" . $row['id']] .= "$table.right = $index WHERE $table.id = " . $row['id'] . " ;";
                if (!empty($temp)) {
                    $result = array_merge($result, $temp);
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param type $params : user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, supplier_inventory_ids, user_created
     * @param type $rollback
     * @return type
     */
    public static function addStoreKeeperUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $password = $result['password'];
            $inputs = array(
                'supplier_inventory_ids' => $params['supplier_inventory_ids'],
                'user_id' => $id,
                'user_created' => $params['user_created'],
            );
            $result = UserSupplierInventoryBusiness::updateMulti($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
                self::_sendEmailPassword($params['email'], $params['username'], $password);
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    /**
     *
     * @param type $params : id, user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, supplier_inventory_ids, user_updated
     * @param type $rollback
     * @return type
     */
    public static function updateStoreKeeperUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $result = self::update($params, false);
        if ($result['error_message'] == '') {
            $inputs = array(
                'supplier_inventory_ids' => $params['supplier_inventory_ids'],
                'user_id' => $params['id'],
                'user_created' => $params['user_updated'],
            );
            $result = UserSupplierInventoryBusiness::updateMulti($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, supplier_inventory_ids, user_created
     * @param type $rollback
     * @return type
     */
    public static function addSalesUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $password = null;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $password = $result['password'];
//            if ($params['sale_channel'] != null) {
            $inputs = array(
                'items' => $params['sale_channel'],
                'user_id' => $id,
                'user_updated' => $params['user_created'],
            );

            $result = SaleChannelUserBusiness::updateMultiForUser($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
                self::_sendEmailPassword($params['email'], $params['username'], $password);
            } else {
                $error_message = $result['error_message'];
            }
//            } else {
//                $error_message = 'Bạn chưa chọn kênh bán hàng';
//            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'password' => $password);
    }

    /**
     *
     * @param type $params : id, user_group_id, fullname, username, email, mobile, phone, gender, address, zone_id, birthday, supplier_inventory_ids, user_updated
     * @param type $rollback
     * @return type
     */
    public static function updateSalesUser($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = User::getDb()->beginTransaction();
        }
        //------------
        $result = self::update($params, false);
        if ($result['error_message'] == '') {
            if ($params['sale_channel'] != null) {
                foreach ($params['sale_channel'] as $k => $v) {
                    if (!isset($params['sale_channel'][$k]['default'])) {
                        $params['sale_channel'][$k]['default'] = 0;
                    }
                    if ($params['sale_channel'][$k]['parent_id'] == '00') {
                        $params['sale_channel'][$k]['parent_id'] = 0;
                    }
                }
            }
            //var_dump($params['sale_channel']);die;
            $inputs = array(
                'items' => $params['sale_channel'],
                'user_id' => $params['id'],
                'user_updated' => $params['user_updated'],
            );
            $result = SaleChannelUserBusiness::updateMultiForUser($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
//            } else {
//                $error_message = 'Bạn chưa chọn kênh bán hàng';
//            }
        } else {
            $error_message = $result['error_message'];
        }
        //------------
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

}
