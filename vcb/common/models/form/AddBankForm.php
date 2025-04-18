<?php


namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BankBusiness;
use common\models\db\Bank;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class AddBankForm extends LanguageBasicForm
{

    public $id;
    public $code;
    public $trade_name;
    public $name;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['code', 'trade_name', 'name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'status'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['trade_name', 'name', 'description'], 'string', 'max' => 255],
            ['code', 'checkExits'],
            [['code', 'trade_name', 'name'], 'checkString']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã ngân hàng',
            'trade_name' => 'Tên thương mại',
            'name' => 'Tên đầy đủ',
            'description' => 'Ghi chú',
            'status' => 'Trạng thái'
        ];
    }

    public function checkExits($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if ($this->id != null) {
                    $bank = BankBusiness::getById($this->id);
                    if ($bank != null) {
                        if ($bank->code != $this->code) {
                            $bank_code = Bank::findOne(['code' => $this->code]);
                            if ($bank_code != null) {
                                $this->addError($attribute, 'Mã ngân hàng đã tồn tại.');
                            }
                        }
                    }
                } else {
                    $bank_code = Bank::findOne(['code' => $this->code]);
                    if ($bank_code != null) {
                        $this->addError($attribute, 'Mã ngân hàng đã tồn tại.');
                    }
                }
                break;
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {

            case "trade_name":
                $trade_name = Strings::_convertToSMS($this->trade_name);

                if (!Validation::checkStringAndNumberSpace($trade_name)) {
                    $this->addError($attribute, 'Tên thương mại không hợp lệ.');
                }
                break;
            case "name":
                $name = Strings::_convertToSMS($this->name);

                if (!Validation::checkStringAndNumberSpace($name)) {
                    $this->addError($attribute, 'Tên đầy đủ không hợp lệ.');
                }
                break;
            case "code":
                if (!Validation::checkContractCode($this->code)) {
                    $this->addError($attribute, 'Mã ngân hàng không hợp lệ.');
                }
                break;

        }
    }

}