<?php

namespace common\models\business;

use Yii;
use common\models\db\QueueNotify;
use common\components\libs\Tables;
use common\models\db\PurchaseOrder;
use common\models\db\SupplierContact;
use common\models\db\Supplier;

class QueueNotifyBusiness
{

    /**
     *
     * @param params : type, name, target, content, source, files, time_start, time_end, user_id
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = QueueNotify::getDb()->beginTransaction();
        }
        $model = new QueueNotify();
        $model->type = $params['type'];
        $model->name = $params['name'];
        $model->target = $params['target'];
        $model->content = $params['content'];
        $model->source = $params['source'];
        $model->files = $params['files'];
        $model->time_start = $params['time_start'];
        $model->time_end = $params['time_end'];
        $model->time_queue = 0;
        $model->number_process = 0;
        $model->status = QueueNotify::STATUS_NOT_PROCESS;
        $model->time_created = time();

        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->lastInsertID;
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thêm Queue';
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
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function addNotifyEmailCashoutStatusWaitVerify($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = QueueNotify::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status ", "id" => $params['cashout_id'], "status" => \common\models\db\Cashout::STATUS_WAIT_VERIFY]);
        if ($cashout_info != false) {
            $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $cashout_info['merchant_id']]);
            if ($merchant_info != false) {
                if ($merchant_info['email_notification'] != '') {
                    $inputs = array(
                        'type' => QueueNotify::TYPE_EMAIL,
                        'name' => 'Xác nhận yêu cầu rút tiền thanh toán đơn hàng từ ' . date('H:i, d/m/Y', $cashout_info['time_begin']) . ' đến ' . date('H:i, d/m/Y', $cashout_info['time_end']),
                        'target' => $merchant_info['email_notification'],
                        'content' => self::_getNotifyEmailCashoutStatusWaitVerify($cashout_info, $merchant_info),
                        'source' => MAILER_SOURCE,
                        'files' => '',
                        'time_start' => time(),
                        'time_end' => 0,
                        'user_id' => $params['user_id'],
                    );
                    $result = self::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $id = $result['id'];
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Merchant chưa khai báo email nhận thông báo';
                }
            } else {
                $error_message = 'Merchant không hợp lệ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
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

    private static function _getNotifyEmailCashoutStatusWaitVerify($cashout_info, $merchant_info)
    {
        if ($cashout_info['type'] == \common\models\db\Cashout::TYPE_CHECKOUT_ORDER) {
            $file_temp = Yii::$app->controller->renderPartial('@common/mail/cashout_status_wait_verify.php', [
                'cashout_info' => $cashout_info,
                'merchant_info' => $merchant_info,
                'url_verify' => ROOT_URL . 'merchant/web/checkout-order/withdraw-verify?id=' . $cashout_info['id'],
                'url_cancel' => ROOT_URL . 'merchant/web/checkout-order/withdraw-cancel?id=' . $cashout_info['id'],
            ]);
        } else {
            $file_temp = Yii::$app->controller->renderPartial('@common/mail/cashout_status_wait_verify.php', [
                'cashout_info' => $cashout_info,
                'merchant_info' => $merchant_info,
                'url_verify' => ROOT_URL . 'merchant/web/card-transaction/withdraw-verify?id=' . $cashout_info['id'],
                'url_cancel' => ROOT_URL . 'merchant/web/card-transaction/withdraw-cancel?id=' . $cashout_info['id'],
            ]);
        }
        return $file_temp;
    }

    /**
     *
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function addNotifyEmailCashoutStatusReject($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = QueueNotify::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status ", "id" => $params['cashout_id'], "status" => \common\models\db\Cashout::STATUS_REJECT]);
        if ($cashout_info != false) {
            $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $cashout_info['merchant_id']]);
            if ($merchant_info != false) {
                if ($merchant_info['email_notification'] != '') {
                    $inputs = array(
                        'type' => QueueNotify::TYPE_EMAIL,
                        'name' => 'Thông báo từ chối yêu cầu rút tiền thanh toán đơn hàng từ ' . date('H:i, d/m/Y', $cashout_info['time_begin']) . ' đến ' . date('H:i, d/m/Y', $cashout_info['time_end']),
                        'target' => $merchant_info['email_notification'],
                        'content' => self::_getNotifyEmailCashoutStatusReject($cashout_info, $merchant_info),
                        'source' => MAILER_SOURCE,
                        'files' => '',
                        'time_start' => time(),
                        'time_end' => 0,
                        'user_id' => $params['user_id'],
                    );
                    $result = self::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $id = $result['id'];
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Merchant không hợp lệ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
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

    private static function _getNotifyEmailCashoutStatusReject($cashout_info, $merchant_info)
    {
        $file_temp = Yii::$app->controller->renderPartial('@common/mail/cashout_status_reject.php', [
            'cashout_info' => $cashout_info,
            'merchant_info' => $merchant_info,
            'url_detail' => ROOT_URL . 'merchant/web/checkout-order/withdraw-detail?id=' . $cashout_info['id'],
        ]);
        return $file_temp;
    }

    /**
     *
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function addNotifyEmailCashoutStatusPaid($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = QueueNotify::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status ", "id" => $params['cashout_id'], "status" => \common\models\db\Cashout::STATUS_PAID]);
        if ($cashout_info != false) {
            $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $cashout_info['merchant_id']]);
            if ($merchant_info != false) {
                if ($merchant_info['email_notification'] != '') {
                    $inputs = array(
                        'type' => QueueNotify::TYPE_EMAIL,
                        'name' => 'Thông báo hoàn thành yêu cầu rút tiền thanh toán đơn hàng từ ' . date('H:i, d/m/Y', $cashout_info['time_begin']) . ' đến ' . date('H:i, d/m/Y', $cashout_info['time_end']),
                        'target' => $merchant_info['email_notification'],
                        'content' => self::_getNotifyEmailCashoutStatusPaid($cashout_info, $merchant_info),
                        'source' => MAILER_SOURCE,
                        'files' => '',
                        'time_start' => time(),
                        'time_end' => 0,
                        'user_id' => $params['user_id'],
                    );
                    $result = self::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $id = $result['id'];
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Merchant không hợp lệ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
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

    private static function _getNotifyEmailCashoutStatusPaid($cashout_info, $merchant_info)
    {
        $file_temp = Yii::$app->controller->renderPartial('@common/mail/cashout_status_paid.php', [
            'cashout_info' => $cashout_info,
            'merchant_info' => $merchant_info,
            'url_detail' => ROOT_URL . 'merchant/web/checkout-order/withdraw-detail?id=' . $cashout_info['id'],
        ]);
        return $file_temp;
    }
}
