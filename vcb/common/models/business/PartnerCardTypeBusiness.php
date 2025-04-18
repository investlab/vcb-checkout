<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use common\models\db\PartnerCardType;
use common\components\libs\Tables;
use Yii;

class PartnerCardTypeBusiness
{
    /**
     *
     * @param type $params : partner_card_id, card_types, user_id
     * @param type $rollback
     * @return type
     */
    static function updateMultiCardType($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        if (!empty($params['card_types'])) {
            $all = true;
            foreach ($params['card_types'] as $card_type_id => $cycle_days) {
                $inputs = array(
                    'partner_card_id' => $params['partner_card_id'],
                    'card_type_id' => $card_type_id,
                    'cycle_days' => $cycle_days,
                    'user_id' => $params['user_id'],
                );
                $result = self::updateMulti($inputs, false);
                if ($result['error_message'] != '') {
                    $error_message = $result['error_message'];
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $commit = true;
                $error_message = '';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : partner_card_id, card_type_id, cycle_days, user_id
     * @param type $rollback
     * @return type
     */
    static function updateMulti($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        $all = true;
        if (!empty($params['cycle_days'])) {
            $cycle_exists = array();
            $partner_card_type_info = Tables::selectAllDataTable("partner_card_type", ["partner_card_id =:partner_card_id AND card_type_id = :card_type_id ", "partner_card_id" => $params['partner_card_id'], "card_type_id" => $params['card_type_id']], "cycle_day ASC ", "cycle_day");
            if ($partner_card_type_info != false) {
                $delete_ids = array();
                foreach ($partner_card_type_info as $row) {
                    if (!in_array($row['cycle_day'], $params['cycle_days'])) {
                        $delete_ids[$row['id']] = $row['id'];
                    } else {
                        $cycle_exists[$row['cycle_day']] = $row['cycle_day'];
                    }
                }
                if (!empty($delete_ids)) {
                    $sql = "DELETE FROM partner_card_type WHERE id IN (" . implode(',', $delete_ids) . ") ";
                    $command = PartnerCardType::getDb()->createCommand($sql);
                    if (!$command->execute()) {
                        $error_message = 'Có lỗi khi cập nhật danh sách loại thẻ hỗ trợ';
                        $all = false;
                    }
                }
            }
            foreach ($params['cycle_days'] as $cycle_day) {
                if (!in_array($cycle_day, $cycle_exists)) {
                    $inputs = array(
                        'partner_card_id' => $params['partner_card_id'],
                        'card_type_id' => $params['card_type_id'],
                        'cycle_day' => $cycle_day,
                        'status' => PartnerCardType::STATUS_LOCK,
                        'user_id' => $params['user_id'],
                    );
                    $result = self::add($inputs, false);
                    if ($result['error_message'] != '') {
                        $error_message = $result['error_message'];
                        $all = false;
                        break;
                    }
                }
            }
        } else {
            $partner_card_type_info = Tables::selectAllDataTable("partner_card_type", ["partner_card_id =:partner_card_id AND card_type_id = :card_type_id ", "partner_card_id" => $params['partner_card_id'], "card_type_id" => $params['card_type_id']], "cycle_day ASC ", "cycle_day");
            if ($partner_card_type_info != false) {
                $sql = "DELETE FROM partner_card_type WHERE partner_card_id = " . $params['partner_card_id'] . " AND card_type_id = " . $params['card_type_id'];
                $command = PartnerCardType::getDb()->createCommand($sql);
                if (!$command->execute()) {
                    $error_message = 'Có lỗi khi cập nhật danh sách loại thẻ hỗ trợ';
                    $all = false;
                }
            }
        }
        if ($all) {
            $commit = true;
            $error_message = '';
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
     * @param type $params : partner_card_id, card_type_id, cycle_day, status, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        if (array_key_exists($params['cycle_day'], $GLOBALS['CYCLE_DAYS'])) {
            $partner_card_info = Tables::selectOneDataTable("partner_card", ["id = :id", "id" => $params['partner_card_id']]);
            if ($partner_card_info != false) {
                $card_type_info = Tables::selectOneDataTable("card_type", ["id = :id", "id" => $params['card_type_id']]);
                if ($card_type_info != false) {
                    $model = new PartnerCardType();
                    $model->partner_card_code = $partner_card_info['code'];
                    $model->partner_card_id = $params['partner_card_id'];
                    $model->partner_card_code = $partner_card_info['code'];
                    $model->bill_type = $partner_card_info['bill_type'];
                    $model->card_type_id = $params['card_type_id'];
                    $model->cycle_day = $params['cycle_day'];
                    $model->status = PartnerCardType::STATUS_ACTIVE;
                    $model->time_created = time();
                    $model->user_created = $params['user_id'];

                    if ($model->validate()) {
                        if ($model->save()) {
                            $id = $model->getDb()->getLastInsertID();
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = 'Có lỗi khi thêm loại thẻ hỗ trợ';
                        }
                    } else {
                        $error_message = 'Tham số đầu vào không hợp lệ';
                    }
                } else {
                    $error_message = 'Loại thẻ không tồn tại';
                }
            } else {
                $error_message = 'Đối tác gạch thẻ không tồn tại';
            }
        } else {
            $error_message = 'Kỳ thanh toán không tồn tại';
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
     * @param type $params : partner_card_type_id, user_id
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
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        $model = PartnerCardType::findOne(["id" => $params['partner_card_type_id'], "status" => PartnerCardType::STATUS_LOCK]);

        if ($model != null) {
            $inputs = array(
                'card_type_id' => $model->card_type_id,
                'cycle_day' => $model->cycle_day,
                'user_id' => $params['user_id'],
            );
            $result = self::lockAllByCardTypeAndCycleDay($inputs, false);
            if ($result['error_message'] == '') {
                $model->status = PartnerCardType::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->user_updated = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi kích hoạt loại thẻ hỗ trợ';
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
     * @param type $params : partner_card_type_id, user_id
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
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        $model = PartnerCardType::findOne(["id" => $params['partner_card_type_id'], "status" => PartnerCardType::STATUS_ACTIVE]);

        if ($model != null) {
            $model->status = PartnerCardType::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi kích hoạt loại thẻ hỗ trợ';
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
     * @param type $params : card_type_id, cycle_day, user_id
     * @param type $rollback
     * @return type
     */
    static function lockAllByCardTypeAndCycleDay($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardType::getDb()->beginTransaction();
        }
        $partner_card_type_info = Tables::selectAllDataTable("partner_card_type", ["card_type_id = :card_type_id AND cycle_day = :cycle_day AND status = :status ", "card_type_id" => $params['card_type_id'], "cycle_day" => $params['cycle_day'], "status" => PartnerCardType::STATUS_ACTIVE]);
        if ($partner_card_type_info != false) {
            $all = true;
            foreach ($partner_card_type_info as $row) {
                $inputs = array(
                    'partner_card_type_id' => $row['id'],
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