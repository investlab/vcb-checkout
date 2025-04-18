<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/28/2018
 * Time: 10:33
 */

namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\Merchant;
use Yii;

class MerchantBusiness
{
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = new Merchant();
        $model->name = $params['name'];
        $model->logo = $params['logo'];
        $model->partner_id = $params['partner_id'];
        $model->password = $params['password'];
        $model->website = $params['website'];
        $model->email_notification = $params['email_notification'];
        $model->mobile_notification = $params['mobile_notification'];
        $model->url_notification = $params['url_notification'];
        $model->status = Merchant::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        $model->merchant_code = $params['merchant_code'];
        $model->branch_id = ($params['branch_id'] != 0)? $params['branch_id']: null;
        $model->active3D =  $params['active3D'];
        $model->payment_flow = $params['payment_flow'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $inputs = array(
                    'merchant_id' => $id, 
                    'balance' => 0, 
                    'currency' => $GLOBALS['CURRENCY']['VND'], 
                    'status' => \common\models\db\Account::STATUS_ACTIVE, 
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::add($inputs, false);
                if ($result['error_message'] == '') {
                    $commit = true;
                    $error_message = '';   
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi thêm merchant';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : id, name, website, logo, email_notification, mobile_notification, user_id
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        $model->name = $params['name'];
        $model->website = $params['website'];
        if ($params['logo'] != null) {
            $model->logo = $params['logo'];
        }
        $model->email_notification = $params['email_notification'];
        $model->mobile_notification = $params['mobile_notification'];
        $model->url_notification = $params['url_notification'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if (isset($params['active3D'])){
            $model->active3D = $params['active3D'];

        }
        if (isset($params['payment_flow'])){
            $model->payment_flow = $params['payment_flow'];

        }
        if (!empty($params['merchant_code']) && $model->merchant_code != $params['merchant_code']) {
            $model->merchant_code = $params['merchant_code'];
        }
        if (!empty($params['branch_id'])){
            $model->branch_id = $params['branch_id'];
        }
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật merchant';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    static function token($params,$rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        $tokenSecure = $model['active3D'];
        if ($model != null) {
            if ($tokenSecure == Merchant::STATUS_ACTIVE){
                $model->active3D = 0;
                $resultToken = 0;
            }else{
                $model->active3D = 1;
                $resultToken = 1;
            }
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa Merchant';
            }
        } else {
            $error_message = 'Không tìm thấy Merchant này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message,'resultToken' => $resultToken);
    }

    static function paymentFlow($params,$rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        $payment = $model['payment_flow'];
        if ($model != null) {
            if ($payment == Merchant::STATUS_ACTIVE){
                $model->payment_flow = 0;
                $resultPayment = 0;
            }else{
                $model->payment_flow = 1;
                $resultPayment = 1;
            }
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa Merchant';
            }
        } else {
            $error_message = 'Không tìm thấy Merchant này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message,'resultpayment' => $resultPayment);
    }

    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = Merchant::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa Merchant';
            }
        } else {
            $error_message = 'Không tìm thấy Merchant này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = Merchant::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi kích hoạt Merchant';
            }
        } else {
            $error_message = 'Không tìm thấy Merchant này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }


    /**
     *
     * @param type $params : id, new_password, user_id,
     * @param type $rollback
     * @return type
     */
    static function changepassword($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = Merchant::getDb()->beginTransaction();
        }
        $model = Merchant::findOne(['id' => $params['id']]);
        if ($model) {
            $model->password = $params['new_password'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật merchant';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }


} 