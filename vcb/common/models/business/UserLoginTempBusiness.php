<?php

namespace common\models\business;

use Yii;
use common\models\db\UserLogin;
use common\models\db\UserLoginTemp;
use common\components\libs\Tables;
use common\components\utils\Validation;
use common\models\db\OtpUser;
use common\components\utils\Translate;

class UserLoginTempBusiness {

    /**
     *
     * @param params : fullname, email, mobile, password, gender, birthday
     * @param rollback
     */
    static function add($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLoginTemp::getDb()->beginTransaction();
        }
        $all = true;
        if (trim($params['email']) != '' && !Validation::isEmail($params['email'])) {
            $error_message = 'Email không đúng định dạng';
            $all = false;
        }
        if ($all && trim($params['mobile']) != '' && !Validation::isMobile($params['mobile'])) {
            $error_message = 'Số điện thoại không đúng định dạng';
            $all = false;
        }
        if ($all && !Validation::isPassword($params['password'])) {
            $error_message = 'Mật khẩu không đúng định dạng';
            $all = false;
        }
        if ($all) {
            $model = new UserLoginTemp();
            $model->fullname = $params['fullname'];
            $model->email = $params['email'];
            $model->mobile = $params['mobile'];
            $model->password = UserLogin::encryptPassword($params['password']);
            $model->gender = $params['gender'];
            $model->birthday = $params['birthday'];
            $model->time_limit = time() + (30 * 86400);
            $model->time_created = time();
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => Translate::get($error_message), 'id' => $id);
    }

    /**
     *
     * @param params : fullname, email, mobile, password
     * @param rollback
     */
    static function register($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLoginTemp::getDb()->beginTransaction();
        }
        $inputs = array(
            'fullname' => $params['fullname'],
            'email' => $params['email'],
            'mobile' => $params['mobile'],
            'password' => $params['password'],
            'gender' => 1,
            'birthday' => 0,
        );
        $result = self::add($inputs, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            //------------
            $inputs = array(
                'user_login_temp_id' => $id,
            );
            $result = OtpUserBusiness::addForUserLoginVerifyEmail($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => Translate::get($error_message), 'id' => $id);
    }

    /**
     *
     * @param params : user_login_temp_id, otp
     * @param rollback
     */
    static function verifyEmail($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLoginTemp::getDb()->beginTransaction();
        }
        $model = UserLoginTemp::findBySql("SELECT * FROM user_login_temp WHERE id = " . $params['user_login_temp_id'] . " ")->one();

        if ($model) {
            $otp_user_info = Tables::selectOneDataTable("otp_user", "refer_id = " . $params['user_login_temp_id'] . " AND refer_type = " . OtpUser::REFER_TYPE_USER_LOGIN_TEMP . " AND type = " . OtpUser::TYPE_EMAIL . " AND email = '" . $model->email . "' AND status = " . OtpUser::STATUS_ACTIVE . " ");

            if ($otp_user_info != false) {
                if (OtpUser::encryptCode($params['otp']) === $otp_user_info['code']) {
                    if ($otp_user_info['time_limit'] > time()) {
                        $inputs = array(
                            'otp_user_id' => $otp_user_info['id']
                        );
                        $result = OtpUserBusiness::delete($inputs, false);

                        if ($result['error_message'] == '') {
                            $inputs = array(
                                'user_login_temp_id' => $params['user_login_temp_id'],
                            );
                            $result = UserLoginBusiness::addByUserLoginTemp($inputs, false);
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
                        $error_message = 'Mã xác thực đã hết hạn';
                    }
                } else {

                    $error_message = 'Mã xác thực không đúng';
                }
            } else {
                $error_message = 'Mã xác thực không tồn tại';
            }
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => Translate::get($error_message));
    }

    /**
     *
     * @param params : user_login_temp_id
     * @param rollback
     */
    static function delete($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = UserLoginTemp::getDb()->beginTransaction();
        }
        $model = UserLoginTemp::findBySql("SELECT * FROM user_login_temp WHERE id = " . $params['user_login_temp_id'] . " ")->one();
        if ($model) {
            if ($model->delete()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi xóa thông tin đăng ký tài khoản';
            }
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => Translate::get($error_message));
    }

}
