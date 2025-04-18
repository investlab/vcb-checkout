<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class PaymentMethodFeeForm extends LanguageBasicForm
{
    public $id;
    public $payment_method_id;
    public $percentage_fee;
    public $flat_fee;
    public $payer_percentage_fee;
    public $payer_flat_fee;
    public $time_begin;
    public $time_end;

    public function rules()
    {
        return [
            [['payment_method_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['time_begin', 'time_end'], 'required', 'message' => 'Bạn phải chọn {attribute}'],
            [['time_begin', 'time_end'], 'date', 'format' => 'dd-mm-yyyy HH:mm', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
            ['id', 'integer'],
            [['percentage_fee', 'payer_percentage_fee', 'payer_flat_fee', 'flat_fee', 'time_end'], 'validateForm']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_method_id' => 'Phương thức thanh toán',
            'percentage_fee' => 'Phí phần trăm',
            'payer_percentage_fee' => 'Phí phần trăm người mua chịu',
            'payer_flat_fee' => 'Phí cố định người mua chịu',
            'flat_fee' => 'Phí cố định',
            'time_begin' => 'Thời gian áp dụng',
            'time_end' => 'Thời gian kết thúc',
        ];
    }


    public function validateForm($attribute, $param)
    {
        switch ($attribute) {
            case "percentage_fee" :
                if ($this->percentage_fee < 0) {
                    $this->addError($attribute, 'Phí phần trăm không hợp lệ.');
                }
                break;
            case "flat_fee" :
                if ($this->flat_fee < 0) {
                    $this->addError($attribute, 'Phí cố định không hợp lệ.');
                }
                break;
            case "payer_percentage_fee" :
                if ($this->payer_percentage_fee < 0) {
                    $this->addError($attribute, 'Phí phần trăm người mua chịu không hợp lệ.');
                }
                break;
            case "payer_flat_fee" :
                if ($this->payer_flat_fee < 0) {
                    $this->addError($attribute, 'Phí cố định người mua chịu không hợp lệ.');
                }
                break;
            case "time_end" :
                if (strtotime($this->time_end) <= strtotime($this->time_begin)) {
                    $this->addError('time_end', 'Ngày kết thúc phải lớn hơn ngày bắt đầu');
                }
                break;
        }
    }

} 