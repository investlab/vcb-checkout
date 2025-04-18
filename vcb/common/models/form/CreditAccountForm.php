<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use common\components\utils\Validation;

class CreditAccountForm extends LanguageBasicForm
{
    public $merchant_id;
    public $account_number;
    public $branch_code;

    public function rules()
    {
        return [
            [['merchant_id'], 'integer'],
            [['account_number', 'branch_code'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['branch_code'], 'checkBranchCode'],
            [['account_number'], 'checkAccountNumber'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'merchant_id' => 'Merchant',
            'account_number' => 'Số tài khoản',
            'branch_code' => 'Mã chi nhánh',
        ];
    }

    public function checkAccountNumber($attribute, $param)
    {
        if (!is_numeric($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (chỉ được nhập chữ số)');
        } else if (!Validation::isBank($this->account_number)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (từ 4 - 18 số)');
        }
    }

    public function checkBranchCode($attribute, $params) {
        if (!is_numeric($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (chỉ được nhập chữ số)');
        } else {
            if (strlen($this->$attribute) > 6) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (không được nhập quá 6 chữ số)');
            }
        }
    }

} 