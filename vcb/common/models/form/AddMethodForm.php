<?php

namespace common\models\form;

use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BankBusiness;
use common\models\business\MethodBusiness;
use common\models\db\Bank;
use common\models\db\Method;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class AddMethodForm extends LanguageBasicForm
{

    public $id;
    public $transaction_type_id;
    public $code;
    public $name;
    public $description;
    public $status;
    public $position;

    public function rules()
    {
        return [
            [['code', 'name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['transaction_type_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'status', 'position'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['name', 'description'], 'string', 'max' => 255],
            ['code', 'checkExits'],
            [['code'], 'checkString']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaction_type_id' => 'Loại giao dịch',
            'code' => 'Mã nhóm phương thức',
            'name' => 'Tên nhóm phương thức',
            'description' => 'Ghi chú',
            'position' => 'Vị trí',
            'status' => 'Trạng thái'
        ];
    }

    public function checkExits($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if ($this->id != null) {
                    $method = MethodBusiness::getById($this->id);
                    if ($method != null) {
                        if ($method->code != $this->code) {
                            $method_code = Method::findOne(['code' => $this->code]);
                            if ($method_code != null) {
                                $this->addError($attribute, 'Mã nhóm phương thức đã tồn tại.');
                            }
                        }
                    }
                } else {
                    $method_code = Method::findOne(['code' => $this->code]);
                    if ($method_code != null) {
                        $this->addError($attribute, 'Mã nhóm phương thức đã tồn tại.');
                    }
                }
                break;
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
//            case "name":
//                $name = String::_convertToSMS($this->name);
//
//                if (!Validation::checkStringAndNumberSpace($name)) {
//                    $this->addError($attribute, 'Tên phương thức không hợp lệ.');
//                }
//                break;
            case "code":
                if (!Validation::checkContractCode($this->code)) {
                    $this->addError($attribute, 'Mã nhóm phương thức không hợp lệ.');
                }
                break;

        }
    }

}