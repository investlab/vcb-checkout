<?php


namespace common\models\form;


use common\components\utils\ObjInput;
use common\components\utils\Validation;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class MerchantCardFeeForm extends LanguageBasicForm
{
    public $id;
    public $card_type_id;
    public $bill_type;
    public $cycle_day;
    public $partner_id;
    public $merchant_id;
    public $time_begin;
    public $percent_fee;

    public function rules()
    {
        return [
            [['time_begin', 'percent_fee'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['card_type_id', 'bill_type', 'partner_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'merchant_id', 'cycle_day'], 'integer'],
            [['percent_fee'], 'checkValidate'],
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
            'card_type_id' => 'Loại thẻ',
            'bill_type' => 'Loại hóa đơn',
            'cycle_day' => 'Kỳ thanh toán',
            'partner_id' => 'Đối tác',
            'merchant_id' => 'Merchant',
            'percent_fee' => 'Phần trăm phi',
            'time_begin' => 'Thời gian bắt đầu'
        ];
    }

    public function checkValidate($attribute, $param)
    {
        switch ($attribute) {
            case "percent_fee":
                if (!Validation::isNumber($this->percent_fee)) {
                    $this->addError('percent_fee', 'Phần trăm phí không hợp lệ.');
                }
                if ($this->percent_fee < 0 || $this->percent_fee > 100) {
                    $this->addError('percent_fee', 'Phần trăm phí không hợp lệ.');
                }
                break;
        }
    }

} 