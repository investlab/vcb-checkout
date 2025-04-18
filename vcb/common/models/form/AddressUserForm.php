<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class AddressUserForm extends LanguageBasicForm
{

    public $refer_type;
    public $refer_id;
    public $zone_id;
    public $district_id;
    public $wards_id;
    public $address;
    public $fullname;
    public $mobile;

    public $address_id;

    public function rules()
    {
        return [
            [['address', 'fullname', 'mobile'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['zone_id', 'district_id', 'wards_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => 'Số điện thoại không hợp lệ.', 'tooShort' => 'Số điện thoại không hợp lệ.'],
            [['refer_type', 'refer_id', 'address_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'refer_type' => 'refer_type',
            'refer_id' => 'Mã khách hàng',
            'zone_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'wards_id' => 'Phường/Xã',
            'address' => 'Địa chỉ',
            'address_id' => 'ID',
            'fullname' => 'Họ và tên',
            'mobile' => 'Số điện thoại'
        ];
    }


} 