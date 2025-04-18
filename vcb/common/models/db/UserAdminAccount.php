<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "user_admin_account".
 *
 * @property integer $id
 * @property integer $user_group_id
 * @property integer $user_id
 * @property string $name
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class UserAdminAccount extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_admin_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_group_id', 'user_id', 'name', 'status'], 'required'],
            [['user_group_id', 'user_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_group_id' => 'User Group ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_LOCK => 'Bị khóa'
        );
    }

    public static function getSubUserGroupIds($user_id)
    {
        $ids = array();
        $user_admin_account_info = Tables::selectAllDataTable("user_admin_account", "user_id = $user_id AND status = " . self::STATUS_ACTIVE);
        if ($user_admin_account_info != false) {
            foreach ($user_admin_account_info as $row) {
                $temp_ids = UserGroup::getSubUserGroupIds($row['user_group_id']);
                if (!empty($temp_ids)) {
                    foreach ($temp_ids as $key => $value) {
                        $ids[$key] = $value;
                    }
                }
            }
        }
        return $ids;
    }

    public static function getUserGroupCodesByUserId($user_id)
    {
        $codes = array();
        $user_group_info = Tables::selectAllDataTable("user_group", "id IN (SELECT user_group_id FROM user_admin_account WHERE user_admin_account.user_id = $user_id AND user_admin_account.status = " . self::STATUS_ACTIVE . ") ");
        if ($user_group_info != false) {
            foreach ($user_group_info as $row) {
                $codes[] = $row['code'];
            }
        }
        return $codes;
    }

    public static function getUserGroupIdsByUserId($user_id)
    {
        $ids = array();
        $user_group_info = Tables::selectAllDataTable("user_admin_account", "user_id = $user_id AND status = " . self::STATUS_ACTIVE . " ");
        if ($user_group_info != false) {
            foreach ($user_group_info as $row) {
                $ids[$row['user_group_id']] = $row['user_group_id'];
            }
        }
        return $ids;
    }

    public static function getUserAdminAccountIdsByUserId($user_id)
    {
        $ids = array();
        $user_group_info = Tables::selectAllDataTable("user_admin_account", "user_id = $user_id AND status = " . self::STATUS_ACTIVE . " ");
        if ($user_group_info != false) {
            foreach ($user_group_info as $row) {
                $ids[$row['id']] = $row['id'];
            }
        }
        return $ids;
    }
}
