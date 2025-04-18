<?php

namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BankBusiness;
use common\models\business\UserBusiness;
use common\models\db\Bank;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class EditPaymentTransactionForm extends LanguageBasicForm
{
    public $id;
    public $partner_payment_method_receipt;
    public $user_create;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['partner_payment_method_receipt', 'user_create'], 'string'],
            [['partner_payment_method_receipt', 'user_create'], 'checkString']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_payment_method_receipt' => 'Mã tham chiếu',
            'user_create' => 'Người tạo',
        ];
    }

    public function checkString($attribute, $param)
    {
//        var_dump($this->partner_payment_method_receipt);die;
        if (trim($this->user_create) == '' && trim($this->partner_payment_method_receipt) == '') {
            $this->addError('user_create', 'Bạn phải chọn người tạo.');
            $this->addError('partner_payment_method_receipt', 'Bạn phải nhập mã tham chiếu.');
        }
        if (trim($this->user_create) != '') {
            $user = UserBusiness::getUserByUsername(trim($this->user_create));
            if ($user == null) {
                $this->addError('user_create', 'Không tìm thấy người dùng này.');
            }
        }
    }
}