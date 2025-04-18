<?php

namespace common\models\form;


use common\components\utils\Translate;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class PartnerPaymentAccountForm extends LanguageBasicForm {
    public $id;
    public $merchant_id;
    public $partner_payment_id;
    public $partner_payment_account;
    public $token_key;
    public $checksum_key;
    public $partner_merchant_password	;
    public $partner_merchant_id	;

    public function rules()
    {
        return [
            [['merchant_id','partner_payment_id'], 'number', 'min' => 1, 'tooSmall' => Translate::get('Bạn phải chọn'). ' {attribute}'],
            [['id'], 'integer'],
//            [['partner_payment_account'], 'email', 'message' => '{attribute} '.Translate::get('không đúng định dạng email')],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant',
            'partner_payment_id' => Translate::get('Kênh thanh toán'),
            'partner_payment_account' => Translate::get('Tài khoản kênh thanh toán'),
            'token_key' => Translate::get('Token key'),
            'checksum_key' => Translate::get('Mã checksum'),
            'partner_merchant_password' => Translate::get('Mật khẩu kết nối'),
            'partner_merchant_id' => Translate::get('Mã merchant đối tác'),
        ];
    }

} 