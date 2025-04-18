<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "payment_log".
 *
 * @property integer $id
 * @property integer $payment_transaction_id
 * @property integer $partner_payment_id
 * @property string $partner_payment_request_id
 * @property string $function
 * @property string $input
 * @property string $output
 * @property integer $time_created
 * @property integer $time_updated
 */
class PaymentLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_transaction_id', 'partner_payment_id', 'function'], 'required'],
            [['payment_transaction_id', 'partner_payment_id', 'time_created', 'time_updated'], 'integer'],
            [['input', 'output'], 'string'],
            [['partner_payment_request_id', 'function'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_transaction_id' => 'Payment Transaction ID',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_request_id' => 'Partner Payment Request ID',
            'function' => 'Function',
            'input' => 'Input',
            'output' => 'Output',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    /**
     *
     * @param type $params : payment_transaction_id, partner_payment_request_id, function, input
     */
    public static function addLog($params)
    {
        $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", ["id = :id ", 'id' => $params['payment_transaction_id']]);
        if ($payment_transaction_info != false) {
            $model = new PaymentLog();
            $model->payment_transaction_id = $params['payment_transaction_id'];
            $model->partner_payment_id = $payment_transaction_info['partner_payment_id'];
            $model->partner_payment_request_id = $params['partner_payment_request_id'];
            $model->function = $params['function'];
            $model->input = $params['input'];
            $model->time_created = time();
            if ($model->validate() && $model->save()) {
                $id = $model->getDb()->getLastInsertID();
                return $id;
            }
        }
        return false;
    }

    /**
     *
     * @param type $params : id, partner_payment_request_id, output
     */
    public static function updateLog($params)
    {
        $model = PaymentLog::findBySql("SELECT * FROM payment_log WHERE id = :id ", ['id' => $params['id']])->one();
        if ($model) {
            if (isset($params['partner_payment_request_id']) && !empty($params['partner_payment_request_id'])) {
                $model->partner_payment_request_id = $params['partner_payment_request_id'];
            }
            $model->output = $params['output'];
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                return true;
            }
        }
        return false;
    }
}
