<?php

namespace common\models\business;

use Yii;
use common\models\db\OtpUser;
use common\models\db\UserLoginTemp;
use common\components\libs\Tables;
use common\components\utils\Validation;

class OtpUserBusiness
{

    /**
     *
     * @param params : refer_type, refer_id, type, email, mobile, time_limit
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $code = null;
        //------------
        if ($rollback) {
            $transaction = OtpUser::getDb()->beginTransaction();
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
        if ($all) {
            $code = OtpUser::getCode();
            $model = new OtpUser();
            $model->type = $params['type'];
            $model->refer_type = $params['refer_type'];
            $model->refer_id = $params['refer_id'];
            $model->email = $params['email'];
            $model->mobile = $params['mobile'];
            $model->code = OtpUser::encryptCode($code);
            $model->time_limit = $params['time_limit'];
            $model->number = 0;
            $model->status = OtpUser::STATUS_ACTIVE;
            $model->time_created = time();
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm OTP';
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
        return array('error_message' => $error_message, 'id' => $id, 'code' => $code);
    }

    /**
     *
     * @param params : user_login_temp_id
     * @param rollback
     */
    static function addForUserLoginVerifyEmail($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $code = null;
        //------------
        if ($rollback) {
            $transaction = OtpUser::getDb()->beginTransaction();
        }
        $user_login_temp_info = Tables::selectOneDataTable("user_login_temp", "id = " . $params['user_login_temp_id']);
        if ($user_login_temp_info != false) {
            $inputs = array(
                'refer_type' => OtpUser::REFER_TYPE_USER_LOGIN_TEMP,
                'refer_id' => $params['user_login_temp_id'],
                'type' => OtpUser::TYPE_EMAIL,
                'email' => $user_login_temp_info['email'],
                'mobile' => $user_login_temp_info['mobile'],
                'time_limit' => time() + (7 * 86400),
            );
            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $id = $result['id'];
                $code = $result['code'];
                //----------
                $inputs = array(
                    'user_login_temp_id' => $params['user_login_temp_id'],
                    'code' => $code,
                );
                $result = QueueNotifyBusiness::addNotifyUserLoginVerifyEmail($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Thông tin tài khoản không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'code' => $code);
    }

    /**
     *
     * @param params : otp_user_id
     * @param rollback
     */
    static function delete($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = OtpUser::getDb()->beginTransaction();
        }
        $model = OtpUser::findBySql("SELECT * FROM otp_user WHERE id = " . $params['otp_user_id'] . " ")->one();
        if ($model) {
            if ($model->delete()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi xóa OTP';
            }
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
    static function addForUserLoginVerifyResetPassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $code = null;
        //------------
        if ($rollback) {
            $transaction = OtpUser::getDb()->beginTransaction();
        }
        $user_login_info = Tables::selectOneDataTable("user_login", "id = " . $params['user_login_id']);
        if ($user_login_info != false) {
            $inputs = array(
                'refer_type' => OtpUser::REFER_TYPE_USER_LOGIN,
                'refer_id' => $params['user_login_id'],
                'type' => OtpUser::TYPE_EMAIL,
                'email' => $user_login_info['email'],
                'mobile' => $user_login_info['mobile'],
                'time_limit' => time() + (2 * 86400),
            );
            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $id = $result['id'];
                $code = $result['code'];
                //----------
                $inputs = array(
                    'user_login_id' => $params['user_login_id'],
                    'code' => $code,
                );
                $result = QueueNotifyBusiness::addNotifyEmailVerifyResetPassword($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Thông tin tài khoản không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'code' => $code);
    }
}