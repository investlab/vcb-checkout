<?php

namespace common\models\business;

use common\components\utils\FormatDateTime;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use Yii;
use common\models\db\Merchant;

class DashboardTransactionAmountBusiness {
    const TIME_OF_DAY = 86400;
    
    public static function getData($params) {
        $branch_id  = Yii::$app->user->getIdentity()->branch_id;
        if (!empty($branch_id)){
            if (intval($params['merchant_id']) > 0){
                $merchant_id = ($params['merchant_id']);
            }else{
                $merchant_id = self::getMerchantInBranch($branch_id);

            }
        }else{
            if (intval($params['merchant_id']) > 0) {
                $merchant_id = $params['merchant_id'];
            }else{
                $merchant_id = '00';
            }
        }

        $data = [];
        $labels = [];
        $time_search = explode(' - ', trim($params['time_search']));
        $day_from = $time_search[0]; //format d/m/Y
        $time_from = FormatDateTime::toTimeBegin($day_from);
        $day_to = $time_search[1];
        $time_to = FormatDateTime::toTimeBegin($day_to);
        $number_of_days = intval(round(($time_to - $time_from) / self::TIME_OF_DAY));
        for ($i = 0; $i <= $number_of_days; $i++) {
            $result = self::getTotalTransactionByMerchant([
                'time_paid_from' => $time_from,
                'time_paid_to' => $time_from + self::TIME_OF_DAY - 1,
                'merchant_id' => $merchant_id
            ]);
            array_push($labels, date('d/m/Y', $time_from));
            $total_amount = (!empty($result['total_amount'])) ? $result['total_amount'] : 0;
            array_push($data, $total_amount);
            $time_from += self::TIME_OF_DAY;
        }
        return [
            'data' => $data,
            'labels' => $labels
        ];
    }
    
    private static function getTotalTransactionByMerchant($params) {
        $query = "select count(id) as total_transaction, sum(amount) as total_amount "
                . "from transaction "
                . "where status = " . Transaction::STATUS_PAID . " "
                . "and transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
                . "and time_paid >= " . $params['time_paid_from'] . " "
                . "and time_paid <= " . $params['time_paid_to'];
        if (is_array($params['merchant_id'])){
            $query .= " and merchant_id IN " . self::convertToStringArray($params['merchant_id']);
        }else{
            if ($params['merchant_id'] != '00') {
                $query .= " and merchant_id = " . $params['merchant_id'];
            }
        }
        $command = Transaction::getDb()->createCommand($query);
        $result = $command->queryOne();
        return $result;
    }
    public static function getMerchantInBranch($branch_id) {
        $merchant_arr = [];
        if (empty($branch_id) || $branch_id == '00') {
            return [];
        }

        $merchants = Merchant::findAll(['branch_id' => $branch_id, 'status' => Merchant::STATUS_ACTIVE]);

        if (!empty($merchants)) {
            foreach ($merchants as $key => $merchant) {
                $merchant_arr[] = $merchant['id'];
            }
        }

        return $merchant_arr;
    }

    public static function convertToStringArray($arr) {
        $string = '(';
        if (!empty($arr)) {
            foreach ($arr as $key => $item) {
                if ($key == 0) {
                    $string .= $item;
                } else {
                    $string .= ','. $item;
                }
            }
        }
        $string .= ')';

        return $string;
    }


}