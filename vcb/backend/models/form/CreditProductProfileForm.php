<?php

namespace backend\models\form;


use yii\base\Model;

class CreditProductProfileForm extends Model {

    public $file_type_id;
    public $credit_product_id;
    public $required;

    public function rules()
    {
        return [
            [['file_type_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['credit_product_id','required'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'file_type_id' => 'Loại hồ sơ',
            'credit_product_id' => 'Mã sản phẩm',
            'required' => 'Bắt buộc'
        ];
    }

} 