<?php

namespace app\models\form;

use common\components\utils\Translate;
use common\components\utils\Validation;
use yii\base\Model;

class CheckoutForm extends Model
{
    public $email;
    public $password;
    public $amount;
    public $bank_code;
    public $order_description;

    public function rules()
    {
        return [
            [['bank_code', 'amount'], 'required', 'on' => 'payment']
        ];
    }

    public function attributeLabels()
    {
        return [
            'bank_code' => 'Mã ngân hàng',
            'amount' => 'Số tiền',
            'order_description' => 'Nội dung ',

        ];
    }


}