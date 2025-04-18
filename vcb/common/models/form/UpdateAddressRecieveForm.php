<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UpdateAddressRecieveForm extends LanguageBasicForm
{
    public $id;
    public $address;
    public $district_id;
    public $zone_id;

//    public $verifyCode;

    public function rules()
    {
        return array(
            array(array('address'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
//            array(array('verifyCode','address'), 'required','message' => 'Bạn phải nhập {attribute}.'),
            array(array('id'), 'integer'),
            array(array('district_id', 'zone_id'), 'integer', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'),
//            array('verifyCode', 'captcha','captchaAction'=>'bill/captcha','message' => '{attribute} không đúng.'),
        );
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'district_id' => 'Quận / Huyện',
            'zone_id' => 'Tỉnh / Thành Phố',
            'address' => 'Địa chỉ',
//            'verifyCode' => 'Mã xác thực'
        ];
    }
} 