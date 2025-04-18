<?php

namespace common\models\form;


use common\components\libs\Tables;
use common\components\utils\ObjInput;
use common\components\utils\Validation;
use common\models\db\Bank;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class PaymentMethodForm extends LanguageBasicForm
{
    public $id;
    public $bank_id;
    public $method_id;
    public $name;
    public $description;
    public $config;
    public $min_amount;

    public function rules()
    {
        return [
            [['id', 'min_amount'], 'integer'],
            [['name',], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['bank_id', 'method_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['description', 'config'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_id' => 'Ngân hàng/Ví điện tử',
            'method_id' => 'Nhóm phương thức',
            'name' => 'Tên',
            'description' => 'Mô tả',
            'image' => 'Ảnh',
            'config' => 'Cấu hình',
            'min_amount' => 'Số tiền tối thiểu',
            'token_key' => 'Mã token',
            'checksum_key' => 'Mã checksum',
        ];
    }

    public function getBanks()
    {
        $arr_bank = [];
        $banks = Tables::selectAllDataTable("bank", ['status' => Bank::STATUS_ACTIVE]);
        if (!empty($banks)) {
            foreach ($banks as $key => $bank) {
                $arr_bank[$bank['id']] = $bank['code'] .' - '. $bank['name'];
            }
        }

        return $arr_bank;
    }

    public function getMethods()
    {
        return \common\components\libs\Weblib::getArraySelectBoxForTable("method", "id", "name");
    }
} 