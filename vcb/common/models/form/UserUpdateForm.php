<?php

namespace common\models\form;

use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\UserBusiness;
use common\models\db\UserSupplierInventory;
use yii\base\Model;
use common\components\libs\Tables;
use Yii;

class UserUpdateForm extends UserAddForm
{

    public function rules()
    {
        $rules = [
            [['fullname', 'email', 'mobile'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
//            [['city_id', 'district_id', 'zone_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'gender', 'city_id', 'zone_id'], 'integer'],
            [['address', 'username', 'fullname', 'password'], 'string'],
            [['phone', 'mobile'], 'number', 'message' => '{attribute} không hợp lệ.'],
            [['phone', 'mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => '{attribute} không hợp lệ.', 'tooShort' => '{attribute} không hợp lệ.'],
            [['birthday'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
            [['email'], 'email', 'message' => 'Email không hợp lệ.'],
        ];
        return $rules;
    }
}
