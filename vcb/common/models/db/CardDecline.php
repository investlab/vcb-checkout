<?php

namespace common\models\db;

/**
 * @property bool|mixed|string|null $response_insights_category_code
 * @property int|mixed|null $status
 * @property mixed|null $response_code
 * @property mixed|null $hash
 * @property mixed|null $last_four_digits
 * @property mixed|null $first_six_digits
 * @property mixed|null $transaction_id
 */
class CardDecline extends MyActiveRecord
{
    const LIST_CODE_BLOCK_FOREVER = [
        '04',
        '07',
        '12',
        '14',
        '15',
        '41',
        '43',
        '46',
        '57',
        'R0',
        'R1',
        'R3',
    ];

    const LIST_CODE_BLOCK_30_DAY = ['03','19','39','51','52','53','59','61','62','65','75','78','86','91','93','96','N3','N4','Z5','54','55','70','82','1A','N7','01','02','05','06','13','58','64','74','79','80','81','N0','Z3'];
    const LIST_CODE_BLOCK_30_DAY_MASTER = [
        '03',
        '21',
    ];
    public static function tableName(): string
    {
        return 'card_decline';
    }

    public function rules()
    {
        return [
            [['transaction_id', 'first_six_digits', 'last_four_digits', 'status', 'response_code'], 'required'],
            [['transaction_id', 'status', 'response_code'], 'integer'],
            [['response_insights_category_code'], 'string'],
            [['created_at', 'response_insights_category_code'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaction_id' => 'Mã giao dịch',
            'first_six_digits' => 'Sáu số đầu',
            'last_four_digits' => 'Bốn số cuối',
            'status' => 'Status',
            'created_at' => 'Time Created',
        ];
    }


    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created_at = time();
        } else {
            $this->updated_at = time();
        }
        return parent::beforeSave($insert);
    }
}