<?php

namespace cron\controllers;

use common\components\libs\NotifySystem;
use common\models\db\CheckoutOrder;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use cron\components\CronBasicController;
use Yii;
use yii\filters\VerbFilter;

class SystemHealthController extends CronBasicController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'checkout-order' => ['get'],
                ],
            ]
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    public function actionCheckoutOrder()
    {
        $current_time = time();
        $time_previous = 10; /*Accept Minute*/

//        $checkout_order = CheckoutOrder::find()
//            ->where(['>=','time_created', $current_time - $time_previous * 60])
//            ->andWhere(['status' => CheckoutOrder::STATUS_FAILURE])
//            ->count();
        $time_check = $current_time - $time_previous * 60;

        $connection = Yii::$app->getDb();
        $disable_ONLY_FULL_GROUP_BY = "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
        $connection->createCommand($disable_ONLY_FULL_GROUP_BY)->execute();
        $result = $connection->createCommand("SELECT checkout_order_id, payment_method_id, COUNT(*) as total FROM `transaction` WHERE time_created >= :time_created AND status = :status GROUP BY payment_method_id", [
            ':time_created' => $time_check,
            ':status' => Transaction::STATUS_FAILURE
        ])->queryAll();

        foreach ($result as $transaction) {
            if ($transaction['total'] >= 20) {
//                self::getListCheckoutOrderId($time_check, $transaction['payment_method_id']);
                
                $payment_method_info = PaymentMethod::getPaymentMethodById($transaction['payment_method_id']);
                $text = "<i>Xuất hiện nhiều giao dịch thất bại</i> \n" .
                    "<b>Số giao dịch: </b>" . "<b>" . $transaction['total'] . "</b>" . "\n" .
                    "<b>Phương thức: </b>" . "<u>" . $payment_method_info['name'] . "</u>" . "\n" .
                    "<b>From: </b>" . "<i>" . date("d-m-y h:i:s", $time_check) . "</i>" . "\n" .
                    "<b>To: </b>" . "<u>" . date("d-m-y h:i:s") . "</u>" . "\n"
                    ;
                if ($payment_method_info) {
                    NotifySystem::send($text);
                }
            }
        }
    }

//    /**
//     * @throws \yii\db\Exception
//     */
//    private static function getListCheckoutOrderId($time_check, $payment_method_id)
//    {
//        $connection = Yii::$app->getDb();
//        $transaction = $connection->createCommand("SELECT checkout_order_id FROM `transaction` WHERE time_created >= :time_created AND status = :status AND payment_method_id = :payment_method_id GROUP BY payment_method_id", [
//            ':time_created' => $time_check,
//            ':status' => Transaction::STATUS_FAILURE,
//            ':payment_method_id' => $payment_method_id
//        ])->queryAll();
//    }

}