<?php

namespace common\models\form;


use common\components\libs\Tables;
use common\models\db\UserLogin;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserLoginForm extends LanguageBasicForm
{
    public $id;
    public $merchant_id;
    public $fullname;
    public $email;
    public $mobile;
    public $password;
    public $gender;
    public $birthday;
    public $ips;

    public function rules()
    {
        return [
            [['fullname', 'email', 'mobile', 'password'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['merchant_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'gender'], 'integer'],
            [['password'], 'string', 'min' => 6, 'tooShort' => '{attribute} không hợp lệ (6-50 kí tự)', 'tooLong' => '{attribute} không hợp lệ (6-50 kí tự)'],
            [['mobile'], 'number', 'message' => '{attribute} không hợp lệ.'],
            [['mobile'], 'string', 'min' => 10, 'max' => 11,
                'tooLong' => '{attribute} không hợp lệ.',
                'tooShort' => '{attribute} không hợp lệ.'],
            [['ips', 'birthday'], 'safe'],
            [['birthday'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
            [['email'], 'email', 'message' => '{attribute} không hợp lệ.'],
            [['email', 'mobile', 'ips'], 'checkValidate'],
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
            'fullname' => 'Họ và tên',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính',
            'email' => 'Email login',
            'mobile' => 'Số điện thoại',
            'password' => 'Mật khẩu đăng nhập',
            'ips' => 'Dải IP'
        ];
    }

    public function checkValidate($attribute, $param)
    {
        switch ($attribute) {
            case "email":
                if ($this->id != null) {
                    $user_login = Tables::selectOneDataTable("user_login", ['email = :email', 'email' => $this->email]);
                    if ($user_login != null) {
                        if ($user_login['email'] != $this->email) {
                            $user_login_email = Tables::selectOneDataTable("user_login", ['email = :email', 'email' => $this->email]);
                            if ($user_login_email != null) {
                                $this->addError($attribute, 'Email login đã tồn tại!');
                            }
                        }
                    }
                } else {
                    $user_login = Tables::selectOneDataTable("user_login", ['email = :email', 'email' => $this->email]);
                    if ($user_login != null) {
                        $this->addError($attribute, 'Email login đã tồn tại!');
                    }
                }
                break;
            case "mobile":
                if ($this->id != null) {
                    $user_login = Tables::selectOneDataTable("user_login", ['mobile = :mobile', 'mobile' => $this->mobile]);
                    if ($user_login != null) {
                        if ($user_login['mobile'] != $this->mobile) {
                            $user_login_mobile = Tables::selectOneDataTable("user_login", ['mobile = :mobile', 'mobile' => $this->mobile]);
                            if ($user_login_mobile != null) {
                                $this->addError($attribute, 'Số điện thoại đã tồn tại!');
                            }
                        }
                    }
                } else {
                    $user_login = Tables::selectOneDataTable("user_login", ['mobile = :mobile', 'mobile' => $this->mobile]);
                    if ($user_login != null) {
                        $this->addError($attribute, 'Số điện thoại đã tồn tại!');
                    }
                }
                break;
            case "ips":
                $ips = str_replace(' ', '', $this->ips);
                $ips_arr = explode(',', $ips);
                $check_ips = true;

                foreach ($ips_arr as $k => $v) {
                    if (is_int(intval($v)) == false) {
                        $check_ips = false;
                    }
                }
                if ($check_ips == false) {
                    $this->addError('ips', 'Dải IP không hợp lệ');
                }
                break;
        }
    }

}