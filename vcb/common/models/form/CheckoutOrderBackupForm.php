<?php

namespace common\models\form;


use common\components\libs\Tables;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CheckOrderBackupForm extends LanguageBasicForm
{
    public $mobile;

    public function rules()
    {
        return [
            [['mobile'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => 'Số điện thoại không hợp lệ.', 'tooShort' => 'Số điện thoại không hợp lệ.'],
            [['mobile'], 'checkBill']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => 'Số điện thoại'
        ];
    }

    public function checkBill($attribute, $param)
    {
        switch ($attribute) {
            case "mobile":
                if ($this->mobile != null) {
                    $bill = Tables::selectAllDataTable("bill", "buyer_mobile = '" . $this->mobile . "' AND bill.customer_id = 0 OR bill.customer_id = null");
                    if ($bill == false) {
                        $this->addError($attribute, 'Không tìm thấy đơn hàng.');
                    }
                }
                break;
        }
    }
} 