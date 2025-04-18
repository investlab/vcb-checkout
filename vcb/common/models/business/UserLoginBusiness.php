<?php

namespace common\models\business;

use Yii;
use common\models\db\UserLogin;
use common\models\db\UserLoginTemp;
use common\components\libs\Tables;
use common\components\utils\Validation;
use common\models\db\OtpUser;
use common\models\db\SocialNetwork;
use common\models\db\UserLoginSocialNetwork;
use common\util\TextUtil;
use common\models\business\SendMailBussiness;

class UserLoginBusiness
{

    static function getById($id)
    {
        return UserLogin::findOne(["id" => $id]);
    }

    /**
     *
     * @param params : merchant_id, fullname, email, mobile, password, gender, birthday, ips
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $all = true;
        if (!Validation::isEmail($params['email'])) {
            $error_message = 'Email login không đúng định dạng';
            $all = false;
        }
        if ($all && trim($params['mobile']) != '' && !Validation::isMobile($params['mobile'])) {
            $error_message = 'Số điện thoại không đúng định dạng';
            $all = false;
        }
        if ($all) {
            $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => \common\models\db\Merchant::STATUS_ACTIVE]);
            if ($merchant_info != false) {
                $model = new UserLogin();
                $model->merchant_id = $params['merchant_id'];
                $model->fullname = $params['fullname'];
                $model->email = $params['email'];
                $model->mobile = $params['mobile'];
                $model->password = $params['password'];
                $model->gender = $params['gender'];
                $model->birthday = $params['birthday'];
                $model->ips = $params['ips'];
                $model->status = UserLogin::STATUS_ACTIVE;
                $model->time_created = time();
                $model->time_updated = time();
                if ($model->validate()) {
                    if ($model->save()) {
                        $id = $model->getDb()->getLastInsertID();
                        //----------
                        $all = true;
                        if ($params['email'] != '') {
                            $inputs = array(
                                'user_login_id' => $id,
                                'email' => $params['email'],
                            );
                            $result = UserLoginEmailBusiness::add($inputs, false);
                            if ($result['error_message'] != '') {
                                $error_message = $result['error_message'];
                                $all = false;
                            }
                        }
                        if ($params['mobile'] != '') {
                            $inputs = array(
                                'user_login_id' => $id,
                                'mobile' => $params['mobile'],
                            );
                            $result = UserLoginMobileBusiness::add($inputs, false);
                            if ($result['error_message'] != '') {
                                $error_message = $result['error_message'];
                                $all = false;
                            }
                        }
                        if ($all) {
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi thêm tài khoản';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Merchant không hợp lệ';
            }
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param params : user_login_temp_id
     * @param rollback
     */
    static function addByUserLoginTemp($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $user_login_temp_info = Tables::selectOneDataTable("user_login_temp", "id = " . $params['user_login_temp_id']);
        if ($user_login_temp_info != false) {
            $inputs = array(
                'merchant_id' => 0,
                'fullname' => $user_login_temp_info['fullname'],
                'email' => $user_login_temp_info['email'],
                'mobile' => $user_login_temp_info['mobile'],
                'password' => $user_login_temp_info['password'],
                'gender' => $user_login_temp_info['gender'],
                'birthday' => $user_login_temp_info['birthday'],
            );
            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $id = $result['id'];
                //------------
                $inputs = array(
                    'user_login_temp_id' => $params['user_login_temp_id'],
                );
                $result = UserLoginTempBusiness::delete($inputs, false);
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
            $error_message = 'Thông tin tài khoản đăng nhập không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param params : email
     * @param rollback
     */
    static function requestResetPasswordByEmail($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $user_login_email_info = Tables::selectOneDataTable("user_login_email", "email = '" . $params['email'] . "' ");
        if ($user_login_email_info != false) {
            $user_login_info = Tables::selectOneDataTable("user_login", "id = " . $user_login_email_info['user_login_id']);
            if ($user_login_info != false) {
                if ($user_login_info['status'] == UserLogin::STATUS_ACTIVE) {
                    $inputs = array(
                        'user_login_id' => $user_login_info['id'],
                    );
                    $result = OtpUserBusiness::addForUserLoginVerifyResetPassword($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Tài khoản đăng nhập đang bị khóa';
                }
            } else {
                $error_message = 'Tài khoản đăng nhập không tồn tại';
            }
        } else {
            $error_message = 'Email không tồn tại';
        }
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
     * @param params : user_login_id, otp, new_password
     * @param rollback
     */
    static function verifyResetPasswordByEmail($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $otp_user_info = Tables::selectOneDataTable("otp_user", "refer_id = " . $params['user_login_id'] . " AND refer_type = " . OtpUser::REFER_TYPE_USER_LOGIN . " AND code = '" . OtpUser::encryptCode($params['otp']) . "' AND type = " . OtpUser::TYPE_EMAIL . " AND status = " . OtpUser::STATUS_ACTIVE);
        if ($otp_user_info != false) {
            if ($otp_user_info['time_limit'] > time()) {
                $inputs = array(
                    'user_login_id' => $params['user_login_id'],
                    'new_password' => $params['new_password'],
                );
                $result = self::updatePassword($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Mã xác nhận đã hết hạn';
            }
        } else {
            $error_message = 'Mã xác nhận không hợp lệ';
        }
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
     * @param params : user_login_id, fullname, gender, birthday
     * @param rollback
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " ")->one();
        if ($model != null) {
            $model->fullname = $params['fullname'];
            $model->gender = $params['gender'];
            $model->birthday = $params['birthday'];
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id, ips
     * @param rollback
     */
    static function updateIP($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " ")->one();
        if ($model != null) {
            $model->ips = $params['ips'];
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id, password, new_password
     * @param rollback
     */
    static function changePassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " AND status = " . UserLogin::STATUS_ACTIVE)->one();
        if ($model != null) {
            if ($model->password === UserLogin::encryptPassword($params['password'])) {
                $model->password = UserLogin::encryptPassword($params['new_password']);
                $model->time_updated = time();
                if ($model->validate()) {
                    if ($model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi đổi mật khẩu tài khoản đăng nhập';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Mật khẩu cũ không đúng';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function changeForgetPassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE email = '" . $params['email'] . "' AND time_updated = " . $params['time_updated'])->one();
        if ($model != null) {
            $model->password = UserLogin::encryptPassword($params['new_password']);
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi đổi mật khẩu tài khoản đăng nhập';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id, new_password
     * @param rollback
     */
    static function updatePassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " AND status = " . UserLogin::STATUS_ACTIVE)->one();
        if ($model != null) {
            $model->password = UserLogin::encryptPassword($params['new_password']);
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi đổi mật khẩu tài khoản đăng nhập';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id,
     * @param rollback
     */
    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " AND status = " . UserLogin::STATUS_LOCK)->one();
        if ($model != null) {
            $model->status = UserLogin::STATUS_ACTIVE;
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id,
     * @param rollback
     */
    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $model = UserLogin::findBySql("SELECT * FROM user_login WHERE id = " . $params['user_login_id'] . " AND status = " . UserLogin::STATUS_ACTIVE)->one();
        if ($model != null) {
            $model->status = UserLogin::STATUS_LOCK;
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không hợp lệ';
        }
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
     * @param params : user_login_id
     * @param rollback
     */
    static function resetPassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $send_email_to = '';
        //------------
        if ($rollback) {
            $transaction = UserLogin::getDb()->beginTransaction();
        }
        $user_login_info = Tables::selectOneDataTable("user_login", ["id = :id ", "id" => $params['user_login_id']]);
        if ($user_login_info != false) {
            $new_password = TextUtil::generateRandomString(8);
            $inputs = array(
                'user_login_id' => $params['user_login_id'],
                'new_password' => $new_password,
            );
            $result = self::updatePassword($inputs, false);
            if ($result['error_message'] == '') {
                SendMailBussiness::send($user_login_info['email'], 'Mật khẩu đăng nhập Merchant', 'register_user', ['username' => $user_login_info['email'], 'password' => $new_password]);
                $error_message = '';
                $send_email_to = $user_login_info['email'];
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'send_email_to' => $send_email_to);
    }

    static function getMerchantID($user_login_id)
    {
        $merchant_id = null;
        if (isset($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", ["id =:id", "id" => $user_login_id]);
            if ($user_login) {
                $merchant_id = $user_login['merchant_id'];
            }
        }
        return $merchant_id;
    }
}