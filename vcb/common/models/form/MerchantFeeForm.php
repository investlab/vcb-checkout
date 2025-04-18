<?php


namespace common\models\form;


use common\components\libs\Tables;
use common\components\utils\ObjInput;
use common\components\utils\Validation;
use common\models\db\PaymentMethod;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class MerchantFeeForm extends LanguageBasicForm
{
    public $id;
    public $method_id;
    public $payment_method_id;
    public $merchant_id;
    public $min_amount;
    public $sender_flat_fee;
    public $sender_percent_fee;
    public $receiver_flat_fee;
    public $receiver_percent_fee;
    public $time_begin;

    public function rules()
    {
        return [
            [['time_begin'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['method_id','merchant_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'payment_method_id', 'merchant_id'], 'integer'],
            [['min_amount', 'sender_flat_fee', 'receiver_flat_fee',
                'sender_percent_fee', 'receiver_percent_fee'
            ], 'checkValidate'],
            [['time_begin'], 'safe'],
            [['time_begin'], 'date', 'format' => 'dd-mm-yyyy HH:mm', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy h:m'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'method_id' => 'Nhóm phương thức thanh toán',
            'payment_method_id' => 'Phương thức thanh toán',
            'merchant_id' => 'Merchant',
            'min_amount' => 'Số tiền tối thiểu',
            'sender_flat_fee' => 'Phí cố định người chuyển',
            'sender_percent_fee' => 'Phí phần trăm người chuyển',
            'receiver_flat_fee' => 'Phí cố định người nhận',
            'receiver_percent_fee' => 'Phí phần trăm người nhận',
            'time_begin' => 'Thời gian bắt đầu'
        ];
    }


    public function checkValidate($attribute, $param)
    {
        switch ($attribute) {
            case "min_amount":
                if (ObjInput::formatCurrencyNumber($this->min_amount) < 0) {
                    $this->addError('min_amount', 'Số tiền tối thiểu áp dụng phí phải lớn hơn 0');
                }
                break;
            case "sender_flat_fee":
                if (ObjInput::formatCurrencyNumber($this->sender_flat_fee) < 0) {
                    $this->addError('sender_flat_fee', 'Phí cố định người chuyển phải lớn hơn 0');
                }
                break;
            case "sender_percent_fee":
                if (!Validation::isNumber($this->sender_percent_fee)) {
                    $this->addError('sender_percent_fee', 'Phí phần trăm người chuyển không hợp lệ.');
                }
                if ($this->sender_percent_fee < 0 || $this->sender_percent_fee > 100) {
                    $this->addError('sender_percent_fee', 'Phí phần trăm người chuyển không hợp lệ.');
                }
                break;
            case "receiver_flat_fee":
                if (ObjInput::formatCurrencyNumber($this->receiver_flat_fee) < 0) {
                    $this->addError('receiver_flat_fee', 'Phí cố định người nhận phải lớn hơn 0');
                }
                break;
            case "receiver_percent_fee":
                if (!Validation::isNumber($this->receiver_percent_fee)) {
                    $this->addError('receiver_percent_fee', 'Phí phần trăm người nhận không hợp lệ.');
                }
                if ($this->receiver_percent_fee < 0 || $this->receiver_percent_fee > 100) {
                    $this->addError('receiver_percent_fee', 'Phí phần trăm người nhận không hợp lệ.');
                }
                break;
        }
    }


}