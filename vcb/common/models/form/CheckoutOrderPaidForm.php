<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CheckoutOrderPaidForm extends LanguageBasicForm
{
    public $id;
    public $time_paid;
    public $bank_refer_code;
    public $transaction_id;


    public function rules()
    {
        return [
            [['time_paid'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['id', 'transaction_id'], 'integer'],
            [['bank_refer_code'], 'string'],
            [['time_paid'], 'safe'],
            [['time_paid'], 'date', 'format' => 'dd-mm-yyyy HH:mm', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy h:m'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_refer_code' => 'Mã giao dịch bên ngân hàng',
            'transaction_id' => 'Giao dịch',
            'time_paid' => 'Thời gian thanh toán',
        ];
    }


} 