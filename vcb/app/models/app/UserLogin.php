<?php

namespace app\models\app;

use common\models\business\UserLoginFailBusiness;
use common\models\db\Merchant;
use common\models\db\MyActiveRecord;
use Yii;
use common\components\libs\Tables;
use common\components\utils\Validation;
use yii\web\IdentityInterface;
use common\components\utils\Translate;

/**
 * This is the model class for table "user_login".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $fullname
 * @property string $email
 * @property string $mobile
 * @property string $password
 * @property integer $gender
 * @property integer $birthday
 * @property string $ips
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class UserLogin extends MyActiveRecord implements IdentityInterface {

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const MALE = 1;
    const FEMALE = 2;

    protected static $info = null;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_login';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['merchant_id', 'gender', 'birthday', 'status', 'time_created', 'time_updated'], 'integer'],
            [['fullname', 'status'], 'required'],
            [['ips'], 'string'],
            [['fullname', 'email'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 50],
            [['email'], 'isEmail'],
            [['mobile'], 'isMobile'],
        ];
    }

    public function isMobile($attribute, $params) {
        if (!Validation::isMobile($this->$attribute)) {
            $this->addError($attribute, Translate::get('Số điện thoại không hợp lệ'));
        }
    }

    public function isEmail($attribute, $params) {
        if (!Validation::isEmail($this->$attribute)) {
            $this->addError($attribute, Translate::get('Email không hợp lệ'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant ID',
            'fullname' => 'Fullname',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'password' => 'Password',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'ips' => 'Ips',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus() {
        return array(
            self::STATUS_ACTIVE => 'Kích hoạt',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getGender() {
        return array(
            self::MALE => 'Nam',
            self::FEMALE => 'Nữ'
        );
    }

    public static function getOperators() {
        return array(
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
            'update-ip' => array('title' => 'Cập nhật dải IP', 'confirm' => false),
            'active' => array('title' => 'Mở khóa tài khoản', 'confirm' => true),
            'lock' => array('title' => 'Khóa tài khoản', 'confirm' => true),
            'reset-password' => array('title' => 'Reset mật khẩu', 'confirm' => true),
            'roles' => array('title' => 'Phân quyền', 'confirm' => false),
        );
    }

    public static function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['view-update'] = $operators['view-update'];
                $result['update-ip'] = $operators['update-ip'];
                $result['lock'] = $operators['lock'];
                $result['reset-password'] = $operators['reset-password'];
                $result['roles'] = $operators['roles'];
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row) {
        $merchant_id = $row['merchant_id'];
        $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);
        $row['merchant_info'] = $merchant;
        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows) {
        $merchant_ids = array();
        foreach ($rows as $row) {
            $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
        }
        $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['operators'] = UserLogin::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function encryptPassword($password) {
        return md5(md5($password));
    }

    public static function getChecksumForUrlVerifyEmail($user_login_temp_id, $code) {
        return md5(md5($user_login_temp_id . '|' . $code . 'c43ck5um'));
    }

    private static function _checkIpLimitation($ips, &$user_ip = '') {
        $allowed_ip_ranges = array();
        if (trim($ips) != '') {
            $allowed_ip_ranges = explode(',', $ips);
        }
        if (!empty($allowed_ip_ranges)) {
            foreach ($allowed_ip_ranges as $ip_range) {
                $user_ip = self::_getUserIpAddress();
                if (self::_ipInRange($user_ip, $ip_range)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    private static function _getUserIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private static function _ipInRange($ip, $range) {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, ( 32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

    public static function getRightIds()
    {
        $result = array();
        $user_id = intval(Yii::$app->user->getId());
        $user_right_info = Tables::selectAllDataTable("user_right_merchant", "user_id = " . $user_id);
        if ($user_right_info != false) {
            foreach ($user_right_info as $row) {
                $result[$row['right_id']] = $row['right_id'];
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

    public static function checkLogin($username, $password, &$message = '', &$user_login_info = false) {
        $message = '';
        $error_code = 0;
        $data = [];
        if (Validation::isMobile($username) || Validation::isEmail($username)) {
            if (Validation::isMobile($username)) {
                $user_login_mobile_info = Tables::selectOneDataTable("user_login_mobile", ["mobile = :mobile ", 'mobile' => $username]);
                if ($user_login_mobile_info != false) {

                    $user_login_info = UserLogin::findOne(["id" => $user_login_mobile_info['user_login_id']]);
                }
            } else {
                $user_login_email_info = Tables::selectOneDataTable("user_login_email", ["email = :email ", 'email' => $username]);
                if ($user_login_email_info != false) {
                    $user_login_info = UserLogin::findOne(["id" => $user_login_email_info['user_login_id']]);
                }
            }

            if ($user_login_info != false && $user_login_info->status == self::STATUS_ACTIVE) {
                if (self::_checkIpLimitation($user_login_info->ips, $user_ip)) {
                    if ($user_login_info->password == self::encryptPassword($password)) {
                        UserLoginFailBusiness::deleteByUserId($user_login_info->id);
                        $data = ($user_login_info->toArray());
                    } else {
                        $error_code = '10008';
                        $message = Translate::get('Mật khẩu không đúng');
                    }
                } else {
                    $error_code = '10002';
                    $message = Translate::get('Địa chỉ IP ') . $user_ip . Translate::get(' không được phép đăng nhập');
                }
            } else {
                $error_code = '10003';
                $message = Translate::get('Tài khoản đăng nhập không tồn tại hoặc đang bị khóa');
            }
        } else {
            $error_code = '10004';

            $message = Translate::get('Tài khoản đăng nhập không hợp lệ');
        }
        return [
            'error_code' => $error_code,
            'error_message' => $message,
            'response' => $data,
        ];
    }

    public static function get($key = false) {
        if (self::$info === null) {
            if (self::isLogin($user_login_info)) {
                self::$info = $user_login_info;
                $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $user_login_info['merchant_id']]);
                if ($merchant_info != false) {
                    self::$info['merchant_id'] = $merchant_info['id'];
                    self::$info['merchant_name'] = $merchant_info['name'];
                    if (trim($merchant_info['logo']) != '' && file_exists(IMAGES_MERCHANT_PATH . $merchant_info['logo'])) {
                        self::$info['merchant_logo'] = IMAGES_MERCHANT_URL . $merchant_info['logo'];
                    } else {
                        self::$info['merchant_logo'] = '';
                    }
                    self::$info['merchant_password'] = $merchant_info['password'];
                    self::$info['merchant_website'] = $merchant_info['website'];
                    self::$info['merchant_email_notification'] = $merchant_info['email_notification'];
                    self::$info['merchant_mobile_notification'] = $merchant_info['mobile_notification'];
                    self::$info['merchant_url_notification'] = $merchant_info['url_notification'];
                }
            } else {
                self::$info = false;
            }
        }
        if ($key !== false) {
            return trim(@self::$info[$key]);
        }
        return self::$info;
    }
    public static function getById($id) {
        $user_info = UserLogin::find()
            ->where(['id' => $id])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->asArray()
            ->one();
        if (!is_null($user_info)) {
            return $user_info;
        }
        return false;
    }
    public static function checkLoginFail($user_login_id) {
        $transaction = UserLogin::getDb()->beginTransaction();
        $commit = false;
        $params = [
            'user_login_id' => $user_login_id
        ];
        $add_fail = UserLoginFailBusiness::add($params);
        if ($add_fail['error_message'] == '') {
            $commit = true;
        }

        $user_login_fail = UserLoginFailBusiness::getAllByUserId($user_login_id);
        $list_fail_id = [];
        $list_fail_time_id = [];
        $time = time() - (60 * 60);

        foreach ($user_login_fail as $k => $v) {
            if ($v['time_failed'] <= $time) {
                $list_fail_time_id[] = $v['id'];
            }
            $list_fail_id[] = $v['id'];
        }
        if ($list_fail_time_id != null) {
            UserLoginFailBusiness::deleteListId($list_fail_time_id);
        }

        $count = UserLoginFailBusiness::getCountByUserId($user_login_id);
        if ($count == 5) {
            $user_login = self::findOne(['id' => $user_login_id]);
            if ($user_login != null) {
                $user_login->status = self::STATUS_LOCK;
                if ($user_login->save() && $list_fail_id != null) {
                    $delete_all = UserLoginFailBusiness::deleteListId($list_fail_id);
                    if ($delete_all) {
                        $commit = true;
                    } else {
                        $commit = false;
                    }
                }
            }
        }

        if ($commit == true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
    }

    public static function isLogin(&$user_login_info = null) {
        if (Yii::$app->user->getId() !== null) {
            $user_login_info = Tables::selectOneDataTable("user_login", ["id = :id ", 'id' => intval(Yii::$app->user->getId())]);
            return true;
        }
        return false;
    }

    public static function login($user_login_info) {
        Yii::$app->user->login($user_login_info);
    }

    public static function logout() {
        Yii::$app->user->logout();
    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id) {
        // TODO: Implement findIdentity() method.
        return static::findOne(['id' => $id, 'status' => UserLogin::STATUS_ACTIVE]);
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
    public static function findIdentityByAccessToken($token, $type = null) {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId() {
        // TODO: Implement getId() method.
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
    public function getAuthKey() {
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
    public function validateAuthKey($authKey) {
        // TODO: Implement validateAuthKey() method.
    }

}
