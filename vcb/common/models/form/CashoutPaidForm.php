<?php


namespace common\models\form;


use common\components\utils\ObjInput;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CashoutPaidForm extends LanguageBasicForm
{
    public $id;
    public $time_paid;
    public $bank_refer_code;
    public $receiver_fee;


    public function rules()
    {
        return [
            [['time_paid', 'receiver_fee'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['id'], 'integer'],
            [['bank_refer_code'], 'string'],
            [['receiver_fee'], 'checkValidate'],
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
            'receiver_fee' => 'Phí kênh rút tiền thu của Merchant',
            'time_paid' => 'Thời gian giải ngân',
        ];
    }

    public function checkValidate()
    {
        if (ObjInput::formatCurrencyNumber($this->receiver_fee) < 0) {
            $this->addError('receiver_fee', 'Phí rút phải lớn hơn 0');
        }
    }


} 