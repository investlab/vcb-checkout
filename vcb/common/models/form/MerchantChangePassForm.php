<?php

namespace common\models\form;


use common\models\db\Merchant;
use yii\base\Model;

class MerchantChangePassForm extends Merchant
{
    public $merchant_id;
    public $new_password;

    public function rules()
    {
        return [
            [['merchant_id'], 'integer'],
            [['new_password'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'merchant_id' => 'Merchant',
            'new_password' => 'Mật khẩu mới'
        ];
    }

} 