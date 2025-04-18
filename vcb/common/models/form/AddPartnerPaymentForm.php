<?php


namespace common\models\form;

use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BankBusiness;
use common\models\business\MethodBusiness;
use common\models\business\PartnerPaymentBusiness;
use common\models\db\Bank;
use common\models\db\Method;
use common\models\db\PartnerPayment;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class AddPartnerPaymentForm extends LanguageBasicForm
{

    public $id;
    public $code;
    public $name;
    public $description;
    public $status;
    public $token_key;
    public $checksum_key;

    public function rules()
    {
        return [
            [['code', 'name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'status'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['name', 'description'], 'string', 'max' => 255],
            ['code', 'checkExits'],
            [['code', 'name'], 'checkString']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã kênh',
            'name' => 'Tên kênh',
            'description' => 'Ghi chú',
            'status' => 'Trạng thái',
            'token_key' => 'Mã token',
            'checksum_key' => 'Mã checksum'
        ];
    }

    public function checkExits($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if ($this->id != null) {
                    $partner_payment = PartnerPaymentBusiness::getById($this->id);
                    if ($partner_payment != null) {
                        if ($partner_payment->code != $this->code) {
                            $partner_payment_code = PartnerPayment::findOne(['code' => $this->code]);
                            if ($partner_payment_code != null) {
                                $this->addError($attribute, 'Mã kênh đã tồn tại.');
                            }
                        }
                    }
                } else {
                    $partner_payment_code = PartnerPayment::findOne(['code' => $this->code]);
                    if ($partner_payment_code != null) {
                        $this->addError($attribute, 'Mã kênh đã tồn tại.');
                    }
                }
                break;
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
            case "name":
                $name = Strings::_convertToSMS($this->name);

                if (!Validation::checkStringAndNumberSpace($name)) {
                    $this->addError($attribute, 'Tên kênh không hợp lệ.');
                }
                break;
            case "code":
                if (!Validation::checkContractCode($this->code)) {
                    $this->addError($attribute, 'Mã kênh không hợp lệ.');
                }
                break;

        }
    }

}