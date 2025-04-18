<?php

namespace common\models\business;

use common\models\db\PartnerPaymentFee;
use common\models\db\Partner;
use common\models\db\Merchant;
use common\models\db\Method;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPayment;
use common\components\libs\Tables;

class PartnerPaymentFeeBusiness
{

    /**
     *
     * @param type $params : partner_payment_id, method_id, payment_method_id, partner_id, merchant_id, min_amount, sender_flat_fee, sender_percent_fee, receiver_flat_fee, receiver_percent_fee, currency, time_begin, user_id
     * @param type $rollback
     * @return type
     */
    static function addAndActive($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $inputs = array(
                'partner_payment_fee_id' => $id,
                'user_id' => $params['user_id'],
            );
            $result = self::request($inputs, false);
            if ($result['error_message'] == '') {
                $inputs = array(
                    'partner_payment_fee_id' => $id,
                    'user_id' => $params['user_id'],
                );
                $result = self::active($inputs, false);
                if ($result['error_message'] == '') {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = $result['error_message'];
                }
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
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : partner_id, merchant_id, method_id, payment_method_id, min_amount, sender_flat_fee, sender_percent_fee, receiver_flat_fee, receiver_percent_fee, currency, time_begin, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        if (self::_addValidate($params, $error_message, $data)) {
            $model = new PartnerPaymentFee();
            $model->partner_payment_id = $params['partner_payment_id'];
            $model->partner_id = $params['partner_id'];
            $model->merchant_id = $params['merchant_id'];
            $model->method_id = $params['method_id'];
            $model->payment_method_id = $params['payment_method_id'];
            $model->min_amount = $params['min_amount'];
            $model->sender_percent_fee = $params['sender_percent_fee'];
            $model->sender_flat_fee = $params['sender_flat_fee'];
            $model->receiver_flat_fee = $params['receiver_flat_fee'];
            $model->receiver_percent_fee = $params['receiver_percent_fee'];
            $model->currency = $params['currency'];
            $model->time_begin = $params['time_begin'];
            $model->time_end = 0;
            $model->status = PartnerPaymentFee::STATUS_NEW;
            $model->time_created = time();
            $model->time_updated = time();
            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi thêm phí kênh thanh toán';
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
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : partner_id, merchant_id, method_id, payment_method_id, min_amount, sender_flat_fee, sender_percent_fee, receiver_flat_fee, receiver_percent_fee, currency, time_begin, user_id
     * @param type $rollback
     * @return type
     */
    private static function _addValidate($params, &$error_message = '', &$data = null)
    {
        if ($params['min_amount'] < 0) {
            $error_message = 'Số tiền tối thiểu áp dụng không hợp lệ';
            return false;
        }
        if (!in_array($params['currency'], $GLOBALS['CURRENCY'])) {
            $error_message = 'Loại tiền tệ không hợp lệ';
            return false;
        }
        if ($params['sender_percent_fee'] > 100) {
            $error_message = 'Phí phần trăm người chuyển đang lớn hơn 100%';
            return false;
        }
        if ($params['receiver_percent_fee'] > 100) {
            $error_message = 'Phí phần trăm người nhận đang lớn hơn 100%';
            return false;
        }
        $data['partner_payment_info'] = Tables::selectOneDataTable("partner_payment", ["id = :id AND status = :status ", "id" => $params['partner_payment_id'], "status" => PartnerPayment::STATUS_ACTIVE]);
        if ($data['partner_payment_info'] == false) {
            $error_message = 'Kênh thanh toán không hợp lệ';
            return false;
        }
        if ($params['partner_id'] != 0) {
            $data['partner_info'] = Tables::selectOneDataTable("partner", ["id = :id AND status = :status ", "id" => $params['partner_id'], "status" => Partner::STATUS_ACTIVE]);
            if ($data['partner_info'] == false) {
                $error_message = 'Đối tác không hợp lệ';
                return false;
            }
        }
        if ($params['merchant_id'] != 0) {
            $data['merchant_info'] = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
            if ($data['merchant_info'] == false) {
                $error_message = 'Merchant không hợp lệ';
                return false;
            }
        }
        $data['method_info'] = Tables::selectOneDataTable("method", ["id = :id AND status = :status ", "id" => $params['method_id'], "status" => Method::STATUS_ACTIVE]);
        if ($data['method_info'] == false) {
            $error_message = 'Nhóm phương thức không hợp lệ';
            return false;
        }
        if ($params['payment_method_id'] != 0) {
            $data['payment_method_info'] = Tables::selectOneDataTable("payment_method", ["id = :id AND status = :status ", "id" => $params['payment_method_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
            if ($data['payment_method_info'] == false) {
                $error_message = 'Phương thức không hợp lệ';
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param type $params : partner_payment_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function request($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        //----------
        $model = PartnerPaymentFee::findOne(['id' => $params['partner_payment_fee_id'], 'status' => PartnerPaymentFee::STATUS_NEW]);
        if ($model != null) {
            $model->status = PartnerPaymentFee::STATUS_REQUEST;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi trong quá trình xử lý';
            }
        } else {
            $error_message = 'Phí kích hoạt không tồn tại';
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
     * @param type $params : partner_payment_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        //----------
        $model = PartnerPaymentFee::findOne(['id' => $params['partner_payment_fee_id'], 'status' => PartnerPaymentFee::STATUS_REQUEST]);
        if ($model != null) {
            $model->status = PartnerPaymentFee::STATUS_ACTIVE;
            $model->time_active = time();
            $model->user_active = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'partner_payment_fee_id' => $params['partner_payment_fee_id'],
                    'user_id' => $params['user_id'],
                );
                $update = self::_updateTimeEndForActive($inputs, false);
                if ($update['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $update['error_message'];
                }
            } else {
                $error_message = 'Có lỗi trong quá trình xử lý';
            }
        } else {
            $error_message = 'Phí muốn kích hoạt không tồn tại';
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
     * @param type $params : partner_payment_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        //----------
        $model = PartnerPaymentFee::findOne(['id' => $params['partner_payment_fee_id'], 'status' => PartnerPaymentFee::STATUS_ACTIVE]);
        if ($model != null) {
            $model->status = PartnerPaymentFee::STATUS_LOCK;
            $model->time_lock = time();
            $model->user_lock = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'partner_payment_fee_id' => $params['partner_payment_fee_id'],
                    'user_id' => $params['user_id'],
                );
                $update = self::_updateTimeEndForLock($inputs, false);
                if ($update['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $update['error_message'];
                }
            } else {
                $error_message = 'Có lỗi trong quá trình xử lý';
            }
        } else {
            $error_message = 'Phí muốn khóa không tồn tại';
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
     * @param type $params : partner_payment_fee_id, user_id
     * @param type $rollback
     */
    private static function _updateTimeEndForActive($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        //----------
        $model = PartnerPaymentFee::findOne(['id' => $params['partner_payment_fee_id'], 'status' => PartnerPaymentFee::STATUS_ACTIVE]);
        if ($model != null) {
            $all = false;
            $previous = Tables::selectOneDataTable("partner_payment_fee", "id != " . $params['partner_payment_fee_id'] . " "
                    . "AND partner_payment_id = ".$model->partner_payment_id." "
                    . "AND partner_id = ".$model->partner_id." "
                    . "AND merchant_id = " . $model->merchant_id . " "
                    . "AND method_id = " . $model->method_id . " "
                    . "AND payment_method_id = " . $model->payment_method_id . " "
                    . "AND min_amount = " . $model->min_amount . " "
                    . "AND status = " . PartnerPaymentFee::STATUS_ACTIVE . " "
                    . "AND (time_begin < " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active < " . $model->time_active . " ))", "time_begin DESC, time_active DESC ");
            if ($previous != false) {
                $model_previous = PartnerPaymentFee::findOne(['id' => $previous['id'], 'status' => PartnerPaymentFee::STATUS_ACTIVE]);
                if ($model_previous != null) {
                    $model_previous->time_end = $model->time_begin;
                    $model_previous->time_updated = time();
                    $model_previous->user_updated = $params['user_id'];
                    if ($model_previous->validate() && $model_previous->save()) {
                        $all = true;
                    } else {
                        $error_message = 'Có lỗi trong quá trình xử lý';
                    }
                } else {
                    $error_message = 'Có lỗi trong quá trình xử lý';
                }
            } else {
                $all = true;
            }
            if ($all == true) {
                $all = false;
                $next = Tables::selectOneDataTable("partner_payment_fee", "id != " . $params['partner_payment_fee_id'] . " "
                        . "AND partner_payment_id = ".$model->partner_payment_id." "
                        . "AND partner_id = ".$model->partner_id." "
                        . "AND merchant_id = " . $model->merchant_id . " "
                        . "AND method_id = " . $model->method_id . " "
                        . "AND payment_method_id = " . $model->payment_method_id . " "
                        . "AND min_amount = " . $model->min_amount . " "
                        . "AND status = " . PartnerPaymentFee::STATUS_ACTIVE . " "
                        . "AND (time_begin > " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active > " . $model->time_active . " ))", "time_begin ASC, time_active ASC ");
                if ($next != false) {
                    $model->time_end = $next['time_begin'];
                    $model->time_updated = time();
                    $model->user_updated = $params['user_id'];
                    if ($model->validate() && $model->save()) {
                        $all = true;
                    } else {
                        $error_message = 'Có lỗi trong quá trình xử lý';
                    }
                } else {
                    $all = true;
                }
            }
            if ($all == true) {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = 'Phí muốn cập nhật không tồn tại';
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
     * @param type $params : partner_payment_fee_id, user_id
     * @param type $rollback
     */
    private static function _updateTimeEndForLock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentFee::getDb()->beginTransaction();
        }
        //----------
        $model = PartnerPaymentFee::findOne(['id' => $params['partner_payment_fee_id'], 'status' => PartnerPaymentFee::STATUS_LOCK]);
        if ($model != null) {
            $all = false;
            $previous = Tables::selectOneDataTable("partner_payment_fee", "id != " . $params['partner_payment_fee_id'] . " "
                    . "AND partner_payment_id = ".$model->partner_payment_id." "
                    . "AND partner_id = ".$model->partner_id." "
                    . "AND merchant_id = " . $model->merchant_id . " "
                    . "AND method_id = " . $model->method_id . " "
                    . "AND payment_method_id = " . $model->payment_method_id . " "
                    . "AND min_amount = " . $model->min_amount . " "
                    . "AND status = " . PartnerPaymentFee::STATUS_ACTIVE . " "
                    . "AND (time_begin < " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active < " . $model->time_active . " ))", "time_begin DESC, time_active DESC ");
            if ($previous != false) {
                $model_previous = PartnerPaymentFee::findOne(['id' => $previous['id'], 'status' => PartnerPaymentFee::STATUS_ACTIVE]);
                if ($model_previous != null) {
                    $next = Tables::selectOneDataTable("partner_payment_fee", "id != " . $params['partner_payment_fee_id'] . " "
                            . "AND partner_payment_id = ".$model->partner_payment_id." "
                            . "AND partner_id = ".$model->partner_id." "
                            . "AND merchant_id = " . $model->merchant_id . " "
                            . "AND method_id = " . $model->method_id . " "
                            . "AND payment_method_id = " . $model->payment_method_id . " "
                            . "AND min_amount = " . $model->min_amount . " "
                            . "AND status = " . PartnerPaymentFee::STATUS_ACTIVE . " "
                            . "AND (time_begin > " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active > " . $model->time_active . " ))", "time_begin ASC, time_active ASC ");
                    if ($next != false) {
                        $model_previous->time_end = $next['time_begin'];
                        $model_previous->time_updated = time();
                        $model_previous->user_updated = $params['user_id'];
                        if ($model_previous->validate() && $model_previous->save()) {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = 'Có lỗi trong quá trình xử lý';
                        }
                    } else {
                        $model_previous->time_end = 0;
                        $model_previous->time_updated = time();
                        $model_previous->user_updated = $params['user_id'];
                        if ($model_previous->validate() && $model_previous->save()) {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = 'Có lỗi trong quá trình xử lý';
                        }
                    }
                } else {
                    $error_message = 'Có lỗi trong quá trình xử lý';
                }
            } else {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = 'Phí muốn cập nhật không tồn tại';
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
