<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CashoutWaitAcceptForm extends LanguageBasicForm
{
    public $id;
    public $partner_payment_id;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['partner_payment_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn kênh rút tiền'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_payment_id' => 'Kênh rút tiền'
        ];
    }


} 