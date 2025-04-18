<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 11/03/2017
 * Time: 11:36 SA
 */

namespace console\controllers;

use common\models\db\BalanceDaily;
use common\models\db\CreditAccount;
use common\models\db\Merchant;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\input\MerchantSearch;
use yii\console\Controller;
use common\components\libs\Tables;
use common\models\db\PartnerPayment;
use common\models\db\Cashout;
use common\models\business\CashoutBusiness;
use common\components\utils\Strings;
use common\payments\NganLuongWithdraw;
use Yii;

class UpdateBalanceDailyController extends Controller
{

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }


    public function actionUpdate()
    {
        // giá trị period  =  time() tại 00:00:00 của ngày tương ứng
        $now = strtotime('today');  // LẤY ĐÚNG TIMESTAMP TẠI 00:00:00
//        $now = 1684602000 + 86400; // 21/05/2023 00:00:00 1684602000
        $yesterday = $now - 86400;
        $balance_daily_yesterday = new BalanceDaily();
        $merchant_list = Tables::selectAllDataTable('merchant', 'status = ' . Merchant::STATUS_ACTIVE , 'id');
        $search = new MerchantSearch();

        // test
        $list = $merchant_list;
        foreach ($list as $key => $data) {

            $account = Tables::selectOneDataTable('account',['merchant_id = :merchant_id',"merchant_id" => $data['id']]);

            // get increased_amount, decreased_amount
            // increased_amount
            // Transaction: merchant_id,  type IN (payment, deposit), status = STATUS_PAID, time_paid
            // decreased_amount
            // Transaction: merchant_id, type IN (WITHDRAW, REFUND), status  = STATUS_PAID, time_paid

            $transactions_for_increased_amount = Transaction::find()
                ->where([ 'merchant_id'  => $data['id']])
                ->andWhere(['between', 'time_paid', $yesterday, $now ])
                ->andWhere([ 'IN','transaction_type_id', [TransactionType::getPaymentTransactionTypeId(), TransactionType::getDepositTransactionTypeId()]])
                ->andWhere(['status' => Transaction::STATUS_PAID])
                ->all();
//                ->createCommand()->getRawSql();

            $increased_amount_yesterday = 0;
            foreach ($transactions_for_increased_amount as $transaction_for_increased_amount){
                $increased_amount_yesterday += $transaction_for_increased_amount->amount;
            }

            $transactions_for_decreased_amount = Transaction::find()
                ->where([ 'merchant_id'  => $data['id']])
                ->andWhere(['between', 'time_paid', $yesterday, $now ])
                ->andWhere([ 'IN','transaction_type_id', [TransactionType::getRefundTransactionTypeId(), TransactionType::getWithdrawTransactionTypeId()]])
                ->andWhere(['status' => Transaction::STATUS_PAID])
                ->all();
//                ->createCommand()->getRawSql();

            $decreased_amount_yesterday = 0;
            foreach ($transactions_for_decreased_amount as $transaction_for_decreased_amount){
                $decreased_amount_yesterday += $transaction_for_decreased_amount->amount;
            }

            $log_yesterday = array(
                'merchant_id' =>  $data['id'],
                'balance_after' => $account['balance'],
                'increased_amount' => $increased_amount_yesterday,
                'decreased_amount' => $decreased_amount_yesterday,
                'period' => $yesterday,
                'time_updated' => $now
            );
//            print_r('merchant_id = ' .$data['id'] . ' | balance = '. $list[$key]['account']['balance'] . ' =========== ');
            $balance_daily_yesterday = BalanceDaily::findOne(['merchant_id' => $data['id'], 'period' => $yesterday]);
            if($balance_daily_yesterday != null){
                $balance_daily_yesterday->balance_after = $account['balance'];
                $balance_daily_yesterday->time_updated = $now;
                $balance_daily_yesterday->increased_amount = $increased_amount_yesterday;
                $balance_daily_yesterday->decreased_amount = $decreased_amount_yesterday;

                if($balance_daily_yesterday->save()){
                    self::_writeLog('[UpdateBalanceDaily::update][SAVE_SUCCESS] ' . json_encode($log_yesterday));
                }else{
                    self::_writeLog('[UpdateBalanceDaily::update][SAVE_FAILED] ' . json_encode($balance_daily_yesterday->getErrors()));
                }
            }else{
                $balance_daily_yesterday = new BalanceDaily();
                $balance_daily_yesterday->merchant_id = $data['id'];
                $balance_daily_yesterday->balance_after = $account['balance'];
                $balance_daily_yesterday->increased_amount = $increased_amount_yesterday;
                $balance_daily_yesterday->decreased_amount = $decreased_amount_yesterday;
                $balance_daily_yesterday->period = $yesterday;
                $balance_daily_yesterday->time_created = $now;
                if($balance_daily_yesterday->save()){
                    self::_writeLog('[UpdateBalanceDaily::update][SAVE_SUCCESS] ' . json_encode($log_yesterday));
                }else{
                    self::_writeLog('[UpdateBalanceDaily::update][SAVE_FAILED] ' . json_encode($balance_daily_yesterday->getErrors()));
                }



            }

            $balance_daily_today = new BalanceDaily();
            $balance_daily_today->merchant_id = $data['id'];
            $balance_daily_today->balance_before = $account['balance'];
            $balance_daily_today->period = $now;
            $balance_daily_today->time_created = $now;
            $log_today = array(
                'merchant_id' =>  $data['id'],
                'balance_before' => $account['balance'],
                'period' => $now,
                'time_created' => $now
            );
            if($balance_daily_today->save()){
                self::_writeLog('[UpdateBalanceDaily::update][SAVE_SUCCESS]: ' . json_encode($log_today));
            }else{
                self::_writeLog('[UpdateBalanceDaily::update][SAVE_FAILED] ' . json_encode($balance_daily_today->getErrors()));
            }
        }

    }

    public function actionTest(){
        self::_writeLog('TEST HEHE');
    }

    public static function _writeLog($data) {

        $file = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'console' . DS . 'update_balance_daily' . DS . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
    }


}
