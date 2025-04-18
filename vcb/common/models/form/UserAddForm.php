<?php

namespace common\models\form;

use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\UserBusiness;
use yii\base\Model;
use common\components\libs\Tables;
use Yii;

class UserAddForm extends \common\models\db\User
{

    public $city_id;
    public $district_id;

    public function rules()
    {
        $rules = [
            [['fullname', 'username',  'email', 'mobile','password'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
//            [['city_id', 'district_id', 'zone_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'gender', 'city_id', 'zone_id'], 'integer'],
            [['address', 'fullname', 'password'], 'string'],
            [['phone', 'mobile'], 'number', 'message' => '{attribute} không hợp lệ.'],
            [['phone', 'mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => '{attribute} không hợp lệ.', 'tooShort' => '{attribute} không hợp lệ.'],
            [['username'], 'isUsername'],
            [['birthday'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
            [['password'], 'string', 'min' => 6, 'tooShort' => '{attribute} không hợp lệ (6-50 kí tự)', 'tooLong' => '{attribute} không hợp lệ (6-50 kí tự)'],
            [['email'], 'email', 'message' => 'Email không hợp lệ.'],
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Họ và tên',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính',
            'email' => 'Email',
            'phone' => 'Số cố định',
            'mobile' => 'Số di động',
            'city_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'zone_id' => 'Phường/Xã',
            'address' => 'Địa chỉ',
            'username' => 'Tên đăng nhập',
            'password' => 'Mật khẩu',
            'branch_id' => 'Chi nhánh',
        ];
    }

    public function isUsername($attribute, $param)
    {
        if (!Validation::isUserName($this->$attribute)) {
            $this->addError($attribute, 'Tên đăng nhập không hợp lệ!');
        } else {
            if ($this->id != null) {
                $user_info = Tables::selectOneDataTable("user", "username = '" . $this->$attribute . "' AND id != " . $this->id);
            } else {
                $user_info = Tables::selectOneDataTable("user", "username = '" . $this->$attribute . "' ");
            }
            if ($user_info != false) {
                $this->addError($attribute, 'Tên đăng nhập đã tồn tại!');
            }
        }
    }

//    public function setUserGroup($form_name)
//    {
//        $post = Yii::$app->request->post();
//        if ($post) {
//            $user_group_info = Tables::selectOneDataTable("user_group", "code = '" . $post[$form_name]['user_group_code'] . "' ");
//            if ($user_group_info != false) {
//                $this->user_group_id = @$user_group_info['id'];
//                $this->user_group_code = $post[$form_name]['user_group_code'];
//            }
//        }
//    }
}
