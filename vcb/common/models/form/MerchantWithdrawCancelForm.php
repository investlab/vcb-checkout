<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/11/2018
 * Time: 15:08
 */

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;

class MerchantWithdrawCancelForm extends LanguageBasicForm
{
    public $cashout_id;
    public $reason_id;
    public $reason;
    public $verifyCode;


    public function rules()
    {
        return [
            [['verifyCode'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['reason_id', 'cashout_id'], 'integer'],
            [['reason'], 'string'],
            [['verifyCode'], 'captcha', 'captchaAction' => 'checkout-order/captcha', 'message' => '{attribute} không đúng.'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'cashout_id' => 'ID',
            'reason_id' => 'Lý do',
            'reason' => 'Mô tả lý do',
            'verifyCode' => 'Mã xác thực'
        ];
    }

} 