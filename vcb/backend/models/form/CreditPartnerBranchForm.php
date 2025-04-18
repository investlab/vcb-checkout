<?php

namespace backend\models\form;


use common\components\libs\Tables;
use yii\base\Model;

class CreditPartnerBranchForm extends Model
{
    public $credit_partner_branch_id;
    public $credit_product_id;

    public function rules()
    {
        return [
            [['credit_partner_branch_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['credit_product_id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'credit_partner_branch_id' => 'Chi nhánh',
            'credit_product_id' => 'Mã sản phẩm'
        ];
    }


} 