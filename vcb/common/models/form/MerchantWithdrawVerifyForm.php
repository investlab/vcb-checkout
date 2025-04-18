<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/11/2018
 * Time: 15:21
 */

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;

class MerchantWithdrawVerifyForm extends LanguageBasicForm
{
    public $cashout_id;
    public $verifyCode;

    public function rules()
    {
        return array(
            array(array('verifyCode'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array(array('cashout_id'), 'integer'),
            array('verifyCode', 'captcha', 'captchaAction' => 'checkout-order/captcha', 'message' => '{attribute} không đúng.'),
        );
    }

    public function attributeLabels()
    {
        return [
            'cashout_id' => 'cancel_id',
            'verifyCode' => 'Mã xác thực'
        ];
    }

} 