<?php

namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\PartnerPaymentMethod;

class PartnerPaymentMethodBusiness
{

    public static function getByIDToArray($id)
    {
        $data = PartnerPaymentMethod::findOne(['id' => $id]);
        if ($data != null) {
            return $data->toArray();
        }
        return $data;
    }

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerPaymentMethod::getDb()->beginTransaction();
        }
        $model = new PartnerPaymentMethod();
        $model->partner_payment_id = $params['partner_payment_id'];
        $model->partner_payment_code = $params['partner_payment_code'];
        $model->payment_method_id = $params['payment_method_id'];
        $model->enviroment = $params['enviroment'];
        $model->position = $params['position'];
        $model->status = PartnerPaymentMethod::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm';
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
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerPaymentMethod::getDb()->beginTransaction();
        }
        $model = PartnerPaymentMethod::findOne(['id' => $params['id']]);
        $model->partner_payment_id = $params['partner_payment_id'];
        $model->partner_payment_code = $params['partner_payment_code'];
        $model->payment_method_id = $params['payment_method_id'];
        $model->enviroment = $params['enviroment'];
        $model->position = $params['position'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật';
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
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : partner_payment_method_id, user_id
     * @param type $rollback
     * @return type
     */
    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentMethod::getDb()->beginTransaction();
        }
        $model = PartnerPaymentMethod::findOne(["id" => $params['partner_payment_method_id'], "status" => PartnerPaymentMethod::STATUS_LOCK]);
        if ($model != null) {
            $inputs = array(
                'payment_method_id' => $model->payment_method_id,
                'enviroment' => $model->enviroment,
                'user_id' => $params['user_id'],
            );
            $result = self::_lockAllByPaymentMethod($inputs, false);
            if ($result['error_message'] == '') {
                $model->status = PartnerPaymentMethod::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->user_updated = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi kích hoạt kênh thanh toán';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Không tìm thấy dữ liệu';
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
     * @param type $params : partner_payment_method_id, user_id
     * @param type $rollback
     * @return type
     */
    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentMethod::getDb()->beginTransaction();
        }
        $model = PartnerPaymentMethod::findOne(["id" => $params['partner_payment_method_id'], "status" => PartnerPaymentMethod::STATUS_ACTIVE]);
        if ($model != null) {
            $model->status = PartnerPaymentMethod::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi khóa kênh thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy dữ liệu';
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
     * @param type $params : payment_method_id, enviroment, user_id
     * @param type $rollback
     * @return type
     */
    private static function _lockAllByPaymentMethod($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentMethod::getDb()->beginTransaction();
        }
        $partner_payment_method_info = Tables::selectAllDataTable("partner_payment_method", ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status ", "payment_method_id" => $params['payment_method_id'], "enviroment" => $params['enviroment'], "status" => PartnerPaymentMethod::STATUS_ACTIVE]);
        if ($partner_payment_method_info != false) {
            $all = true;
            foreach ($partner_payment_method_info as $row) {
                $inputs = array(
                    'partner_payment_method_id' => $row['id'],
                    'user_id' => $params['user_id'],
                );
                $result = self::lock($inputs, false);
                if ($result['error_message'] != '') {
                    $error_message = $result['error_message'];
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = '';
            $commit = true;
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
} 