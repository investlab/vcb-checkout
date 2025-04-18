<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_card_log_backup".
 *
 * @property integer $id
 * @property integer $partner_card_id
 * @property integer $type
 * @property string $function
 * @property string $input
 * @property string $output
 * @property string $session_id
 * @property string $result
 * @property string $refer_code
 * @property integer $card_log_id
 * @property integer $card_type_id
 * @property string $card_code
 * @property string $card_serial
 * @property double $card_price
 * @property integer $card_status
 * @property integer $status
 * @property integer $backup_status
 * @property integer $time_backup
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_card_updated
 */
class PartnerCardLogBackup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_card_log_backup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_card_id', 'type', 'function', 'input', 'status'], 'required'],
            [['partner_card_id', 'type', 'card_log_id', 'card_type_id', 'card_status', 'status', 'backup_status', 'time_backup', 'time_created', 'time_updated', 'time_card_updated'], 'integer'],
            [['input', 'output'], 'string'],
            [['card_price'], 'number'],
            [['function', 'session_id', 'result', 'refer_code'], 'string', 'max' => 255],
            [['card_code'], 'string', 'max' => 20],
            [['card_serial'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_card_id' => 'Partner Card ID',
            'type' => 'Type',
            'function' => 'Function',
            'input' => 'Input',
            'output' => 'Output',
            'session_id' => 'Session ID',
            'result' => 'Result',
            'refer_code' => 'Refer Code',
            'card_log_id' => 'Card Log ID',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_status' => 'Card Status',
            'status' => 'Status',
            'backup_status' => 'backup_status',
            'time_backup' => 'time_backup',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_card_updated' => 'Time Card Updated',
        ];
    }
}
