<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class TransactionCancelForm extends LanguageBasicForm
{
    public $id;
    public $reason_id;
    public $reason;


    public function rules()
    {
        return [
            [['reason_id'], 'integer'],
            [['reason'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reason_id' => 'Lý do hủy',
            'reason' => 'Mô tả lý do'
        ];
    }

} 