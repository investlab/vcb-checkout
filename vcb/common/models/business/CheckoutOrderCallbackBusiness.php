<?php

namespace common\models\business;

use common\components\libs\NotifySystem;
use common\components\utils\Logs;
use Yii;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\components\libs\Tables;

class CheckoutOrderCallbackBusiness
{

    /**
     *
     * @param type $params : checkout_order_id, notify_url, time_process
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        @self::_writeLog("[ADD]" . json_encode($params));
        //-----------
//        if ($rollback) {
//            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
//        }
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAID, 'checkout_order_id' => $params['checkout_order_id']]);
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", "id = " .$params['checkout_order_id']." AND status IN (" . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " , " .CheckoutOrder::STATUS_PAID) . ") ";
        $checkout_order_info = CheckoutOrder::find()->where(['id' => $params['checkout_order_id']])->andWhere(['IN', 'status', [CheckoutOrder::STATUS_INSTALLMENT_WAIT, CheckoutOrder::STATUS_PAID]])->asArray()->one();
        if ($checkout_order_info != null) {
            @self::_writeLog("[FIND]" . json_encode($checkout_order_info, true));
            $check_exists = CheckoutOrderCallback::find()->where(['checkout_order_id' => $checkout_order_info['id']])->one();
            if (!$check_exists) {
                $model = new CheckoutOrderCallback();
                $model->checkout_order_id = $checkout_order_info['id'];
                $model->notify_url = $params['notify_url'];
                $model->time_process = $params['time_process'];
                $model->number_process = 0;
                $model->status = CheckoutOrderCallback::STATUS_NEW;
                $model->time_created = time();
                if ($model->validate()) {
                    if ($model->save()) {
                        @self::_writeLog("[RESULT][SUCCESS]" . json_encode($model->getAttributes()));
                        $error_message = '';
                        $id = $model->id;

//                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order_info['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
//                    CheckoutOrderCallback::process($checkout_order_callback_info);
                    } else {
                        $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
                        @self::_writeLog("[RESULT][ERROR]" . $error_message);
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                    @self::_writeLog("[RESULT][ERROR]" . $error_message . json_encode($model->getErrors()));
                }
            } else {
                $error_message = '';
                @NotifySystem::send("DUPLICATE ADD CALLBACK -> checkout_order_id: " . $checkout_order_info['id']);
                $id = $check_exists->id;
            }

        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
            @self::_writeLog("[RESULT][ERROR]" . $error_message);
        }
//        if ($rollback) {
//            if ($commit == true) {
//                $transaction->commit();
//            } else {
//                $transaction->rollBack();
//            }
//        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : checkout_order_id, notify_url, time_process
     * @param type $rollback
     * @return type
     */
    static function addReview($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_REVIEW, 'checkout_order_id' => $params['checkout_order_id']]);
        if ($checkout_order_info != false) {
            $model = new CheckoutOrderCallback();
            $model->checkout_order_id = $checkout_order_info['id'];
            $model->notify_url = $params['notify_url'];
            $model->time_process = $params['time_process'];
            $model->number_process = 0;
            $model->status = CheckoutOrderCallback::STATUS_NEW;
            $model->time_created = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                    $id = $model->getDb()->getLastInsertID();
                } else {
                    $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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

    static function addCallBackQrVcbGateway($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAID, 'checkout_order_id' => $params['checkout_order_id']]);
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", "id = " .$params['checkout_order_id']." AND status IN (" . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " , " .CheckoutOrder::STATUS_PAID) . ") ";
        $checkout_order_info = CheckoutOrder::find()->where(['id' => $params['checkout_order_id']])->andWhere(['IN', 'status', [CheckoutOrder::STATUS_INSTALLMENT_WAIT, CheckoutOrder::STATUS_PAYING]])->asArray()->one();
        if ($checkout_order_info != null) {
            $model = new CheckoutOrderCallback();
            $model->checkout_order_id = $checkout_order_info['id'];
            $model->notify_url = $params['notify_url'];
            $model->time_process = $params['time_process'];
            $model->number_process = 0;
            $model->status = CheckoutOrderCallback::STATUS_NEW;
            $model->time_created = time();
            if ($model->validate()) {

                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                    $id = $model->id;

//                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order_info['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);
//                    CheckoutOrderCallback::process($checkout_order_callback_info);
                } else {
                    $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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

    static function addBoCongAn($params, $rollback = true)
    {

        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAID, 'checkout_order_id' => $params['checkout_order_id']]);
//        $checkout_order_info = Tables::selectOneDataTable("checkout_order", "id = " .$params['checkout_order_id']." AND status IN (" . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " , " .CheckoutOrder::STATUS_PAID) . ") ";
        $checkout_order_info = CheckoutOrder::find()->where(['id' => $params['checkout_order_id']])
//            ->andWhere(['status'=> CheckoutOrder::STATUS_PAYING])
            ->asArray()->one();

        if ($checkout_order_info != null) {
            $model = new CheckoutOrderCallback();
            $model->checkout_order_id = $checkout_order_info['id'];
            $model->notify_url = $params['notify_url'];
            $model->time_process = $params['time_process'];
            $model->number_process = 0;
            $model->status = CheckoutOrderCallback::STATUS_NEW;
            $model->time_created = time();
            if ($model->validate()) {

                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                    $id = $model->id;

                    $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order_info['id'], "status" => CheckoutOrderCallback::STATUS_NEW]);

                    CheckoutOrderCallback::processBCA($checkout_order_callback_info);
                } else {
                    $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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

    public static function addFailure($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        @self::_writeLog("[ADD]" . json_encode($params));
        $id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_FAILURE, 'checkout_order_id' => $params['checkout_order_id']]);
        if ($checkout_order_info != false) {
            @self::_writeLog("[FIND]" . json_encode($checkout_order_info, true));
            $model = new CheckoutOrderCallback();
            $model->checkout_order_id = $checkout_order_info['id'];
            $model->notify_url = $params['notify_url'];
            $model->time_process = $params['time_process'];
            $model->number_process = 0;
            $model->status = CheckoutOrderCallback::STATUS_NEW;
            $model->time_created = time();
            if ($model->validate()) {
                if ($model->save()) {
                    @self::_writeLog("[RESULT][SUCCESS]" . json_encode($model->getAttributes()));
                    $error_message = '';
                    $commit = true;
                    $id = $model->getDb()->getLastInsertID();
                } else {
                    $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
                    @self::_writeLog("[RESULT][ERROR]" . $error_message);
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
                @self::_writeLog("[RESULT][ERROR]" . $error_message);
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
            @self::_writeLog("[RESULT][ERROR]" . $error_message);
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

    static function updateStatus($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
        $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $params['checkout_order_id'], 'status' => CheckoutOrderCallback::STATUS_ERROR]);
        if (!empty($checkout_order_callback)) {
            $checkout_order_callback->status = CheckoutOrderCallback::STATUS_NEW;
            $checkout_order_callback->time_updated = time();
            if ($checkout_order_callback->save()) {
                $error_message = '';
                $commit = true;
                $id = $checkout_order_callback->getDb()->getLastInsertID();
            } else {
                $error_message = 'Có lỗi khi thêm lệnh gọi lại merchant';
            }
        } else {
            $error_message = 'Callback gọi lại không hợp lệ';
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
     * @param type $params : checkout_order_id, user_id
     * @param type $rollback
     * @return type
     */
    static function recall($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderCallback::getDb()->beginTransaction();
        }
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAID . " AND callback_status IN (" . CheckoutOrder::CALLBACK_STATUS_ERROR . "," . CheckoutOrder::CALLBACK_STATUS_SUCCESS . ") ", 'checkout_order_id' => $params['checkout_order_id']]);
        if ($checkout_order_info != false) {
            $model = CheckoutOrderCallback::findOne(["checkout_order_id" => $checkout_order_info['id']]);
            if ($model != null) {
                $model->status = CheckoutOrderCallback::STATUS_NEW;
                $model->number_process = 0;
                $model->time_process = time();
                $model->time_updated = time();
                if ($model->validate() && $model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi yêu cầu gọi lại merchant';
                }
            } else {
                $notify_url = trim($checkout_order_info['notify_url']);
                if ($notify_url != '') {
                    $inputs = array(
                        'checkout_order_id' => $checkout_order_info['id'],
                        'notify_url' => $notify_url,
                        'time_process' => time(),
                    );
                    $result = self::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Đơn thanh toán không có Notify URL để gọi lại';
                }
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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


    private static function _writeLog($data)
    {
        $file_name = 'checkout_order_callback/queue' . DS . date("Ymd", time()) . ".txt";
        $path_info = pathinfo($file_name);
        Logs::create($path_info['dirname'], $path_info['basename'], $data);
    }
}
