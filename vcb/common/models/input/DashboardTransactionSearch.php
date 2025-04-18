<?php


namespace common\models\input;


use common\components\libs\Tables;
use common\models\db\Merchant;
use common\models\db\Transaction;
use yii\base\Model;
use yii\data\Pagination;
use common\models\output\DataPage;
use Yii;

class DashboardTransactionSearch extends Model
{
    public $merchant_id;
    public $start_time;
    public $end_time;
    private static $status_color = [
        Transaction::STATUS_NEW => '#999',
        Transaction::STATUS_PAYING => '#F4B13D',
        Transaction::STATUS_CANCEL => '#f35e5e',
        Transaction::STATUS_PAID => '#060c',
    ];

    public function rules()
    {
        return [
            [['merchant_id'], 'integer'],
            [['start_time', 'end_time'], 'string'],
        ];
    }

    function getConditions($params)
    {
        $conditions = [];
        $branch_id = Yii::$app->user->getIdentity()->branch_id;
        if (!empty($branch_id)){
            if (intval($this->merchant_id) > 0){
                $conditions[] = "merchant_id = " . trim($params['merchant_id']);
            }else{
                $merchant_arr = self::getMerchantInBranch($branch_id);
                $conditions[] = "merchant_id IN " . self::convertToStringArray($merchant_arr);


            }
        }else{
            if (intval($this->merchant_id) > 0) {
                $merchant_arr[] = $this->merchant_id;
            }
        }





        if (intval($params['status']) > 0) {
            $conditions[] = "status = " . trim($params['status']);
        }
        if (!empty($params['start_time'])) {
            $conditions[] = "time_created >= " . $params['start_time'];
        }
        if (!empty($params['end_time'])) {
            $conditions[] = "time_created <= " . $params['end_time'];
        }

        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = 1;
        }

        return $conditions;
    }

    public function search()
    {
        $result_date = self::getDate();
        $time = date('d/m/Y', strtotime($this->start_time)) . ' - ' . date('d/m/Y', strtotime($this->end_time));
        $data = [
            'time' => $time,
            'dataset' => [],
            'label_arr' => $result_date['label_arr'],
        ];
        $arr_status = Transaction::getStatus();
        //------------
        $index = 0;
        foreach (self::$status_color as $key => $color) {
            $data['dataset'][$index]['label'] = $arr_status[$key];
            $count = [];
            foreach ($result_date['date_arr'] as $item) {
                $params = [
                    'status' => $key,
                    'merchant_id' => $this->merchant_id,
                    'start_time' => $item['start'],
                    'end_time' => $item['end'],
                ];
                $conditions = $this->getConditions($params);
                $count_trans = Tables::selectCountDataTable('transaction', $conditions);
                $count[] = $count_trans;
            }
            $data['dataset'][$index]['data'] = $count;
            $data['dataset'][$index]['borderWidth'] = 1;
            $data['dataset'][$index]['backgroundColor'] = $color;
            $data['dataset'][$index]['borderColor'] = $color;
            $index++;
        }

        return $data;
    }

    public function getMerchantInBranch($branch_id) {
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

    public function getDate() {
        $label_arr = [];
        $date_arr = [];
        $time_start = strtotime($this->start_time);
        $time_end = strtotime($this->end_time);
        $count_day = ($time_end - $time_start + 1) / 86400;
        $time = $time_start;
        if ($count_day <= 7) {
            for ($i = 1; $i <= $count_day; $i++) {
                $date_arr[$i]['start'] = $time;
                $date_arr[$i]['end'] = $time + 86399;
                $label_arr[] = 'Ngày ' . date('d/m/Y', $time);
                $time += 86400;
            }
        } else {
            for ($i = 1; $i <= ceil($count_day/7); $i++) {
                if ($i == 1) {
                    $start = $time;
                    $end = $time + 86400 * 7 * $i - 1;
                } else {
                    $start = $time + 86400 * 7 * ($i-1);
                    $end = $start + 86400 * 7 - 1;
                    if ($time_end < $end) {
                        $end = $time_end;
                    }
                }
                $date_arr[$i]['start'] = $start;
                $date_arr[$i]['end'] = $end;
                $label_arr[] = 'Tuần ' . $i . ' (' . date('d/m/Y', $start) . ' - ' . date('d/m/Y', $end) . ')';
            }
        }

        return [
            'date_arr' => $date_arr,
            'label_arr' => $label_arr,
        ];
    }

    public function convertToStringArray($arr) {
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
