<?php

namespace common\models\db;

use common\models\business\UserAdminAccountBusiness;
use Yii;
use yii\web\IdentityInterface;
use common\components\libs\Tables;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $fullname
 * @property integer $birthday
 * @property integer $gender
 * @property string $email
 * @property string $phone
 * @property string $mobile
 * @property integer $zone_id
 * @property string $address
 * @property string $username
 * @property string $password
 * @property string $company_name
 * @property string $company_code
 * @property string $company_address
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class User extends MyActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const SYSTEM_USER_ID = 9999;

    protected $supplier_inventory_ids = null;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'birthday', 'gender', 'zone_id', 'status', 'time_created', 'time_updated'], 'integer'],
            [['address', 'company_address'], 'string'],
            [['company_code'], 'string', 'max' => 50],
            [['fullname', 'email', 'company_name'], 'string', 'max' => 255],
            [['phone', 'mobile'], 'string', 'max' => 20],
            [['username'], 'string', 'max' => 25],
            [['password'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Fullname',
            'birthday' => 'Birthday',
            'gender' => 'Gender',
            'email' => 'Email',
            'phone' => 'Phone',
            'mobile' => 'Mobile',
            'zone_id' => 'Zone ID',
            'address' => 'Address',
            'username' => 'Username',
            'branch_id' => 'Branch ID',
            'password' => 'Password',
            'company_name' => 'Company Name',
            'company_code' => 'Company Code',
            'company_address' => 'Company Address',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => 1]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    public static function setUsernameForRow(&$row)
    {
        $ids = array();
        if (isset($row['user_created']) && intval($row['user_created']) != 0) {
            $ids[$row['user_created']] = $row['user_created'];
        }
        if (isset($row['user_updated']) && intval($row['user_updated']) != 0) {
            $ids[$row['user_updated']] = $row['user_updated'];
        }
        if (isset($row['user_active']) && intval($row['user_active']) != 0) {
            $ids[$row['user_active']] = $row['user_active'];
        }
        if (isset($row['user_deactive']) && intval($row['user_deactive']) != 0) {
            $ids[$row['user_deactive']] = $row['user_deactive'];
        }
        if (isset($row['user_lock']) && intval($row['user_lock']) != 0) {
            $ids[$row['user_lock']] = $row['user_lock'];
        }
        if (!empty($ids)) {
            $user_admins = array();
            $result = Tables::selectAllDataTable("user", "id IN (" . implode(',', $ids) . ") ", "id DESC ");
            foreach ($result as $item) {
                $user_admins[$item['id']] = $item;
            }
            if (array_key_exists('user_created', $row)) {
                $row['user_created_name'] = @$user_admins[$row['user_created']]['username'];
            }
            if (array_key_exists('user_updated', $row)) {
                $row['user_updated_name'] = @$user_admins[$row['user_updated']]['username'];
            }
            if (array_key_exists('user_active', $row)) {
                $row['user_active_name'] = @$user_admins[$row['user_active']]['username'];
            }
            if (array_key_exists('user_deactive', $row)) {
                $row['user_deactive_name'] = @$user_admins[$row['user_deactive']]['username'];
            }
            if (array_key_exists('user_lock', $row)) {
                $row['user_lock_name'] = @$user_admins[$row['user_lock']]['username'];
            }
        }
    }

    public static function setUsernameForRows(&$data)
    {
        $ids = array();
        foreach ($data as $key => $row) {
            if (isset($row['user_created']) && intval($row['user_created']) != 0) {
                $ids[$row['user_created']] = $row['user_created'];
            }
            if (isset($row['user_updated']) && intval($row['user_updated']) != 0) {
                $ids[$row['user_updated']] = $row['user_updated'];
            }
            if (isset($row['user_active']) && intval($row['user_active']) != 0) {
                $ids[$row['user_active']] = $row['user_active'];
            }
            if (isset($row['user_deactive']) && intval($row['user_deactive']) != 0) {
                $ids[$row['user_deactive']] = $row['user_deactive'];
            }
            if (isset($row['user_lock']) && intval($row['user_lock']) != 0) {
                $ids[$row['user_lock']] = $row['user_lock'];
            }
            if (isset($row['user_paid']) && intval($row['user_paid']) != 0) {
                $ids[$row['user_paid']] = $row['user_paid'];
            }
        }
        if (!empty($ids)) {
            $user_admins = array();
            $result = Tables::selectAllDataTable("user", "id IN (" . implode(',', $ids) . ") ", "id DESC ");
            foreach ($result as $row) {
                $user_admins[$row['id']] = $row;
            }
            foreach ($data as $key => $row) {
                if (array_key_exists('user_created', $row)) {
                    $data[$key]['user_created_name'] = @$user_admins[$row['user_created']]['username'];
                }
                if (array_key_exists('user_updated', $row)) {
                    $data[$key]['user_updated_name'] = @$user_admins[$row['user_updated']]['username'];
                }
                if (array_key_exists('user_active', $row)) {
                    $data[$key]['user_active_name'] = @$user_admins[$row['user_active']]['username'];
                }
                if (array_key_exists('user_deactive', $row)) {
                    $data[$key]['user_deactive_name'] = @$user_admins[$row['user_deactive']]['username'];
                }
                if (array_key_exists('user_lock', $row)) {
                    $data[$key]['user_lock_name'] = @$user_admins[$row['user_lock']]['username'];
                }
                if (array_key_exists('user_paid', $row)) {
                    $data[$key]['user_paid_name'] = @$user_admins[$row['user_paid']]['username'];
                }
            }
        }
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_LOCK => 'Bị khóa'
        );
    }

    public static function getGender()
    {
        return array(
            1 => 'Nam',
            2 => 'Nữ'
        );
    }

    public static function getRightIds()
    {
        $result = array();
        $user_id = intval(Yii::$app->user->getId());
        $user_admin_account_ids = UserAdminAccount::getUserAdminAccountIdsByUserId($user_id);
        if (!empty($user_admin_account_ids)) {
            $user_right_info = Tables::selectAllDataTable("user_right", "user_id = " . $user_id . " AND user_admin_account_id IN (" . implode(',', $user_admin_account_ids) . ") ");
            if ($user_right_info != false) {
                foreach ($user_right_info as $row) {
                    $result[$row['right_id']] = $row['right_id'];
                }
            }
        }
        return $result;
    }

    public static function getRightCodes()
    {
        $result = array();
        $right_ids = self::getRightIds();
        if (!empty($right_ids)) {
            $right_info = Tables::selectAllDataTable("right", "id IN (" . implode(',', $right_ids) . ") ");
            if ($right_info != false) {
                foreach ($right_info as $row) {
                    $result[$row['id']] = $row['code'];
                }
            }
        }
        return $result;
    }

    public static function hasRight($right_code)
    {
        return in_array($right_code, self::getRightCodes());
    }

    public static function getSubUserGroupIds($user_id, &$user_info)
    {
        $ids = array();
        $user_info = Tables::selectOneDataTable("user", "id = $user_id AND status = " . self::STATUS_ACTIVE);
        if ($user_info != false) {
            $ids = UserAdminAccount::getSubUserGroupIds($user_id);
        }
        return $ids;
    }

    public static function getUserInfo($user_id)
    {
        $user_info = Tables::selectOneDataTable("user", "id = " . intval($user_id) . " AND status = " . self::STATUS_ACTIVE);
        return $user_info;
    }

    public static function getUserName($user_id)
    {
        $user_info = self::getUserInfo($user_id);
        if ($user_info != false) {
            return $user_info['username'];
        }
        return '';
    }

    protected function _setSupplierInventoryIds()
    {
        $this->supplier_inventory_ids = array();
        $user_id = $this->getId();
        $user_supplier_inventory_info = Tables::selectAllDataTable("user_supplier_inventory", ["user_id = :user_id AND status = :status ", 'user_id' => $user_id, 'status' => UserSupplierInventory::STATUS_ACTIVE]);
        if ($user_supplier_inventory_info != false) {
            foreach ($user_supplier_inventory_info as $row) {
                $this->supplier_inventory_ids[$row['supplier_inventory_id']] = $row['supplier_inventory_id'];
            }
        }
    }

    public function getSupplierInventoryIds()
    {
        if ($this->supplier_inventory_ids === null) {
            $this->_setSupplierInventoryIds();
        }
        return $this->supplier_inventory_ids;
    }

    public function checkRightAcceptInventoryInfo($inventory_id)
    {
        $supplier_inventory_ids = $this->getSupplierInventoryIds();
        if (!empty($supplier_inventory_ids) && in_array($inventory_id, $supplier_inventory_ids)) {
            return true;
        }
        return false;
    }

    public static function checkUserGroup($user_id, $check_user_group_codes)
    {
        $user_group_codes = UserAdminAccount::getUserGroupCodesByUserId($user_id);
        if (!empty($user_group_codes)) {
            foreach ($user_group_codes as $user_group_code) {
                if (in_array($user_group_code, $check_user_group_codes)) {
                    return true;
                }
            }
        }
        return false;
    }
}
