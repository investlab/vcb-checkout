<?php

namespace cron\controllers;

use common\models\business\CheckoutOrderBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\Transaction;
use cron\components\CronBasicController;

class CheckoutOrderController extends CronBasicController
{

    public function actionIndex()
    {
        $checkout_orders = CheckoutOrder::find()
            ->where(['<=', 'time_created', time() - 7200])
            ->andWhere(['>=', 'time_created', time() - 86400])
            ->andWhere(['not', ['transaction_id' => null]])
            ->andWhere(['status' => 1])
            ->all();
        foreach ($checkout_orders as $checkout_order) {
            $transaction = Transaction::find()
                ->where(['id' => $checkout_order->transaction_id])
                ->andWhere(['IN', 'partner_payment_id', [12, 15]])
                ->andWhere(['<=', 'time_created', time() - 7200])
                ->andWhere(['status' => 3])
                ->one();
            if ($transaction) {
                $inputs = array(
                    'checkout_order_id' => $checkout_order->id,
                    'user_id' => '0',
                );
                $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                $data_log = [
                    'checkout_order' => $checkout_order->toArray(),
                    'message' => $result,
                ];
                self::_writeLog(json_encode($data_log));
                echo 'Update ' . $checkout_order->id . '<br>';
            }
        }
    }

    public static function actionUpdateCancel(){
        self::_writeLog('[UPDATE_CANCEL VHC]: START ');

        $qr_life_minutes = 60; //LIVE
//        $qr_life_minutes = 1; // TEST //TODO CMT KHI ĐẨY LIVE
        $time_to = time() - $qr_life_minutes * 60;
        $time_from = time() - 24 * 60 * 60;
        $time_arr = [
            'time_from' => $time_from,
            'time_to' => $time_to
        ];
        self::_writeLog('[UPDATE_CANCEL VHC]: time ' . json_encode($time_arr));


        $checkout_order_arr = CheckoutOrder::find()
//            ->where(['id' => 182564])
            ->where(['>=', 'time_created', $time_from])
            ->andWhere(['<=', 'time_created', $time_to])

            ->andWhere(['IN', 'merchant_id', $GLOBALS['MERCHANT_VHC_QR']])
            ->andWhere(['not', ['transaction_id' => null]])
            ->andWhere(['status' => [CheckoutOrder::STATUS_PAYING]])
            ->all();

        foreach ($checkout_order_arr as $checkout_order){
            /** @var $checkout_order CheckoutOrder */
            self::_writeLog('[UPDATE_CANCEL VHC][PROCESSING] ' . $checkout_order->token_code);

            //TODO Xử lý update thất bại GD + đơn hàng
            $input_cancel = [
                'checkout_order_id' => $checkout_order->id,
                'reason_id' => 0,
                'reason' => 'Hết thời hạn thanh toán',
                'user_id' => 0
            ];

            $result_cancel = CheckoutOrderBusiness::cancelRequestPaymentV2($input_cancel);
            self::_writeLog('[UPDATE_CANCEL VHC][RESULT_CANCEL]: ' . json_encode($result_cancel));

        }

        self::_writeLog('[UPDATE_CANCEL VHC]: END ');


    }



    public function actionUpdateByMerchant()
    {
        $merchant_arr = [91];

        $checkout_orders = CheckoutOrder::find()
            ->where(['<=', 'time_created', time() - 43200])
            ->andWhere(['>=', 'time_created', time() - 86400])
            ->andWhere(['not', ['transaction_id' => null]])
            ->andWhere(['IN', 'merchant_id', $merchant_arr])
            ->andWhere(['status' => CheckoutOrder::STATUS_PAYING])
            ->all();
        foreach ($checkout_orders as $checkout_order) {
            $transaction = Transaction::find()
                ->where(['id' => $checkout_order->transaction_id])
                ->andWhere(['IN', 'partner_payment_id', [1, 8]])
                ->andWhere(['<=', 'time_created', time() - 43200])
                ->andWhere(['status' => Transaction::STATUS_PAYING])
                ->one();
            if ($transaction) {
                $inputs = array(
                    'checkout_order_id' => $checkout_order->id,
                    'user_id' => '0',
                );
                $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                $data_log = [
                    'checkout_order' => $checkout_order->toArray(),
                    'message' => $result,
                ];
                self::_writeLog(json_encode($data_log));
                echo 'Update ' . $checkout_order->id . '<br>';
            }
        }
    }

    public function actionUpdateNotifyBoCongAn()  // Cập nhật sau 7 ngày trả thêm status_type = pending
    {
//        var_dump(strtotime(date('Y-m-d', strtotime('-7 days') )) );
//        var_dump(strtotime(date('Y-m-d', strtotime('today') )));die();
        $checkout_orders = CheckoutOrder::find()
            ->where(['>=', 'time_created', strtotime(date('Y-m-d', strtotime('-10 days')))])  // sau 7 ngày
            ->andWhere(['<=', 'time_created', strtotime(date('Y-m-d', strtotime('-7 days')))])  // sau 7 ngày
            ->andWhere(['IN', 'merchant_id', $GLOBALS['MERCHANT_BCA_NOTI']])
//            ->andWhere(['not', ['transaction_id' => null]])
            ->andWhere(['IN', 'status', [CheckoutOrder::STATUS_PAYING, CheckoutOrder::STATUS_NEW]])
            ->all();
        foreach ($checkout_orders as $checkout_order) {
            self::_writeLog('[PARAMS]: checkout_order = ' . $checkout_order->token_code);
            $transaction = Transaction::find()
                ->where(['id' => $checkout_order->transaction_id])
//                ->andWhere(['IN', 'partner_payment_id', [12, 15]])
                ->andWhere(['IN', 'merchant_id', $GLOBALS['MERCHANT_BCA_NOTI']])
//                ->andWhere(['<=', 'time_created', time() - 7200])
//                ->andWhere(['status' => Transaction::STATUS_PAYING])
                ->one();
//            var_dump($transaction);continue;

            if ($transaction) {
                $transaction->status = Transaction::STATUS_FAILURE;
                $transaction->reason = "Giao dịch hết hạn";
                $transaction->save();
                $status_saving = $transaction->save();

                $arr_transaction_log = [
                    'id' => $transaction->id,
                    'status' => $transaction->status,
                    'reason' => $transaction->reason,
                    'status_saving' => $status_saving
                ];
                self::_writeLog('[TRANSACTION_INFO]: ' . json_encode($arr_transaction_log));


                $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $checkout_order->id)->one();
                if ($model != null) {
                    $notify_url = trim($model->notify_url);
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order->id,
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );

                        self::_writeLog('[PARAMS_INPUT_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($inputs));
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailureBCA($inputs, false);
                        self::_writeLog('[ERROR_MESSAGE_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($result));
                        CheckoutOrderCallbackBusiness::addBoCongAn($inputs, false); // hàm để notify
                    }
                } else {
                    self::_writeLog('model null');
                }


//                $data_log = [
//                    'checkout_order' => $checkout_order->toArray(),
//                    'message' => $result,
//                ];
//                self::_writeLog(json_encode($data_log));
//                echo 'Update ' . $checkout_order->id . '<br>';
            } else {
                $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $checkout_order->id . " AND status = " . CheckoutOrder::STATUS_NEW)->one();
                if ($model != null) {
                    $notify_url = trim($model->notify_url);
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order->id,
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );

                        self::_writeLog('[PARAMS_INPUT_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($inputs));
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailureBCA($inputs, false);
                        self::_writeLog('[ERROR_MESSAGE_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($result));
                        CheckoutOrderCallbackBusiness::addBoCongAn($inputs, false); // hàm để notify
                    }
                } else {
                    self::_writeLog('model null');
                    echo 'Update ' . $checkout_order->id . '<br>';

                }
            }
        }
    }

    public function actionUpdateNotifyBoCongAnHandle()  // Cập nhật sau 7 ngày trả thêm status_type = pending
    {
//        var_dump(strtotime(date('Y-m-d', strtotime('-7 days') )) );
//        var_dump(strtotime(date('Y-m-d', strtotime('today') )));die();
        $checkout_orders = CheckoutOrder::find()
            ->where(['token_code' => '988266-CO9C1D029690'])
            ->all();
        foreach ($checkout_orders as $checkout_order) {
            self::_writeLog('[PARAMS]: checkout_order = ' . $checkout_order->token_code);
            $transaction = Transaction::find()
                ->where(['id' => $checkout_order->transaction_id])
//                ->andWhere(['IN', 'partner_payment_id', [12, 15]])
//                ->andWhere(['<=', 'time_created', time() - 7200])
//                ->andWhere(['status' => Transaction::STATUS_PAYING])
                ->one();
//            var_dump($transaction);continue;

            if ($transaction) {
                $transaction->status = Transaction::STATUS_FAILURE;
                $transaction->reason = "Giao dịch hết hạn";
                $transaction->save();
                $status_saving = $transaction->save();

                $arr_transaction_log = [
                    'id' => $transaction->id,
                    'status' => $transaction->status,
                    'reason' => $transaction->reason,
                    'status_saving' => $status_saving
                ];
                self::_writeLog('[TRANSACTION_INFO]: ' . json_encode($arr_transaction_log));


                $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $checkout_order->id . " AND status = " . CheckoutOrder::STATUS_PAYING)->one();
                if ($model != null) {
                    $notify_url = trim($model->notify_url);
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order->id,
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );

                        self::_writeLog('[PARAMS_INPUT_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($inputs));
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailureBCA($inputs, false);
                        self::_writeLog('[ERROR_MESSAGE_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($result));
                        CheckoutOrderCallbackBusiness::addBoCongAn($inputs, false); // hàm để notify
                    }
                } else {
                    self::_writeLog('model null');
                }


                $data_log = [
                    'checkout_order' => $checkout_order->toArray(),
                    'message' => $result,
                ];
                self::_writeLog(json_encode($data_log));
                echo 'Update ' . $checkout_order->id . '<br>';
            } else {
                $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $checkout_order->id . " AND status = " . CheckoutOrder::STATUS_NEW)->one();
                if ($model != null) {
                    $notify_url = trim($model->notify_url);
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order->id,
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );

                        self::_writeLog('[PARAMS_INPUT_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($inputs));
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailureBCA($inputs, false);
                        self::_writeLog('[ERROR_MESSAGE_OF_FUNCTION_UPDATE_FAILURE]: ' . json_encode($result));
                        CheckoutOrderCallbackBusiness::addBoCongAn($inputs, false); // hàm để notify
                    }
                } else {
                    self::_writeLog('model null');
                    echo 'Update ' . $checkout_order->id . '<br>';

                }
            }
        }
    }


    private static function _writeLog($data)
    {
        $file = fopen(ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'cron' . DS . 'checkout_order' . DS . date('Ymd') . '.txt', 'a');
        if ($file) {
            fwrite($file, '[' . date('d/m/Y, H:i:s') . ']' . $data . "\n");
            fclose($file);
        }
    }
}