<?php

namespace common\models\business;

use common\models\db\MerchantCardFee;
use common\models\db\Partner;
use common\models\db\Merchant;
use common\models\db\CardType;
use common\components\libs\Tables;

class MerchantCardFeeBusiness
{

    /**
     *
     * @param type $params : card_type_id, bill_type, cycle_day, partner_id, merchant_id, time_begin, percent_fee, currency, user_id
     * @param type $rollback
     * @return type
     */
    static function addAndActive($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        $result = self::add($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $inputs = array(
                'merchant_card_fee_id' => $id,
                'user_id' => $params['user_id'],
            );
            $result = self::request($inputs, false);
            if ($result['error_message'] == '') {
                $inputs = array(
                    'merchant_card_fee_id' => $id,
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
     * @param type $params : card_type_id, bill_type, cycle_day, partner_id, merchant_id, time_begin, percent_fee, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        if (self::_addValidate($params, $error_message, $data)) {
            $model = new MerchantCardFee();
            $model->card_type_id = $params['card_type_id'];
            $model->bill_type = $params['bill_type'];
            $model->cycle_day = $params['cycle_day'];
            $model->partner_id = $params['partner_id'];
            $model->merchant_id = $params['merchant_id'];
            $model->percent_fee = $params['percent_fee'];
            $model->currency = $data['card_type_info']['currency'];
            $model->time_begin = $params['time_begin'];
            $model->time_end = 0;
            $model->status = MerchantCardFee::STATUS_NEW;
            $model->time_created = time();
            $model->time_updated = time();
            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi thêm phí thẻ cào';
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
     * @param type $params : card_type_id, bill_type, cycle_day, partner_id, merchant_id, time_begin, percent_fee, currency, user_id
     * @param type $rollback
     * @return type
     */
    private static function _addValidate($params, &$error_message = '', &$data = null)
    {
        if (!array_key_exists($params['currency'], $GLOBALS['CURRENCY'])) {
            $error_message = 'Loại tiền tệ không hợp lệ';
            return false;
        }
        if (!array_key_exists($params['cycle_day'], $GLOBALS['CYCLE_DAYS'])) {
            $error_message = 'Kỳ thanh toán không hợp lệ';
            return false;
        }
        $data['partner_info'] = Tables::selectOneDataTable("partner", ["id = :id AND status = :status ", "id" => $params['partner_id'], "status" => Partner::STATUS_ACTIVE]);
        if ($data['partner_info'] == false) {
            $error_message = 'Đối tác không hợp lệ';
            return false;
        }
        if ($params['merchant_id'] != 0) {
            $data['merchant_info'] = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
            if ($data['merchant_info'] == false) {
                $error_message = 'Merchant không hợp lệ';
                return false;
            }
        }
        $data['card_type_info'] = Tables::selectOneDataTable("card_type", ["id = :id AND status = :status ", "id" => $params['card_type_id'], "status" => CardType::STATUS_ACTIVE]);
        if ($data['card_type_info'] == false) {
            $error_message = 'Loại thẻ không hợp lệ';
            return false;
        }
        return true;
    }

    /**
     *
     * @param type $params : merchant_card_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function request($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        //----------
        $model = MerchantCardFee::findOne(['id' => $params['merchant_card_fee_id'], 'status' => MerchantCardFee::STATUS_NEW]);
        if ($model != null) {
            $model->status = MerchantCardFee::STATUS_REQUEST;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi trong quá trình xử lý';
            }
        } else {
            $error_message = 'Phí thẻ cào kích hoạt không tồn tại';
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
     * @param type $params : merchant_card_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        //----------
        $model = MerchantCardFee::findOne(['id' => $params['merchant_card_fee_id'], 'status' => MerchantCardFee::STATUS_REQUEST]);
        if ($model != null) {
            $model->status = MerchantCardFee::STATUS_ACTIVE;
            $model->time_active = time();
            $model->user_active = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'merchant_card_fee_id' => $params['merchant_card_fee_id'],
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
            $error_message = 'Phí thẻ cào kích hoạt không tồn tại';
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
     * @param type $params : merchant_card_fee_id, user_id
     * @param type $rollback
     * @return type
     */
    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        //----------
        $model = MerchantCardFee::findOne(['id' => $params['merchant_card_fee_id'], 'status' => MerchantCardFee::STATUS_ACTIVE]);
        if ($model != null) {
            $model->status = MerchantCardFee::STATUS_LOCK;
            $model->time_lock = time();
            $model->user_lock = $params['user_id'];
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'merchant_card_fee_id' => $params['merchant_card_fee_id'],
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
            $error_message = 'Phí thẻ cào muốn khóa không tồn tại';
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
     * @param type $params : merchant_card_fee_id, user_id
     * @param type $rollback
     */
    private static function _updateTimeEndForActive($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        //----------
        $model = MerchantCardFee::findOne(['id' => $params['merchant_card_fee_id'], 'status' => MerchantCardFee::STATUS_ACTIVE]);
        if ($model != null) {
            $all = false;
            $previous = Tables::selectOneDataTable("merchant_card_fee", "id != " . $params['merchant_card_fee_id'] . " "
                . "AND partner_id = " . $model->partner_id . " AND merchant_id = " . $model->merchant_id . " AND card_type_id = " . $model->card_type_id . " AND bill_type = " . $model->bill_type . " AND cycle_day = " . $model->cycle_day . " AND status = " . MerchantCardFee::STATUS_ACTIVE . " "
                . "AND (time_begin < " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active < " . $model->time_active . " )) ", "time_begin DESC, time_active DESC ");
            if ($previous != false) {
                $model_previous = MerchantCardFee::findOne(['id' => $previous['id'], 'status' => MerchantCardFee::STATUS_ACTIVE]);
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
                $next = Tables::selectOneDataTable("merchant_card_fee", "id != " . $params['merchant_card_fee_id'] . " "
                    . "AND partner_id = " . $model->partner_id . " AND merchant_id = " . $model->merchant_id . " AND card_type_id = " . $model->card_type_id . " AND bill_type = " . $model->bill_type . " AND cycle_day = " . $model->cycle_day . " AND status = " . MerchantCardFee::STATUS_ACTIVE . " "
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
            $error_message = 'Phí thẻ cào cập nhật không tồn tại';
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
     * @param type $params : merchant_card_fee_id, user_id
     * @param type $rollback
     */
    private static function _updateTimeEndForLock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = MerchantCardFee::getDb()->beginTransaction();
        }
        //----------
        $model = MerchantCardFee::findOne(['id' => $params['merchant_card_fee_id'], 'status' => MerchantCardFee::STATUS_LOCK]);
        if ($model != null) {
            $all = false;
            $previous = Tables::selectOneDataTable("merchant_card_fee", "id != " . $params['merchant_card_fee_id'] . " "
                . "AND partner_id = " . $model->partner_id . " AND merchant_id = " . $model->merchant_id . " AND card_type_id = " . $model->card_type_id . " AND bill_type = " . $model->bill_type . " AND cycle_day = " . $model->cycle_day . " AND status = " . MerchantCardFee::STATUS_ACTIVE . " "
                . "AND (time_begin < " . $model->time_begin . " OR (time_begin = " . $model->time_begin . " AND time_active < " . $model->time_active . " ))", "time_begin DESC, time_active DESC ");
            if ($previous != false) {
                $model_previous = MerchantCardFee::findOne(['id' => $previous['id'], 'status' => MerchantCardFee::STATUS_ACTIVE]);
                if ($model_previous != null) {
                    $next = Tables::selectOneDataTable("merchant_card_fee", "id != " . $params['merchant_card_fee_id'] . " "
                        . "AND partner_id = " . $model->partner_id . " AND merchant_id = " . $model->merchant_id . " AND card_type_id = " . $model->card_type_id . " AND bill_type = " . $model->bill_type . " AND cycle_day = " . $model->cycle_day . " AND status = " . MerchantCardFee::STATUS_ACTIVE . " "
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
            $error_message = 'Phí thẻ cào muốn cập nhật không tồn tại';
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
