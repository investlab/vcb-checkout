<?php

namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\UserBusiness;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserForm extends LanguageBasicForm
{
    public $id;
    public $user_group_id;
    public $user_group_code;
    public $fullname;
    public $birthday;
    public $gender;
    public $email;
    public $phone;
    public $mobile;
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['fullname', 'username', 'email', 'mobile'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['user_group_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'user_group_id', 'gender'], 'integer'],
            [['user_group_code', 'fullname', 'password'], 'string'],
            [['phone', 'mobile'], 'number', 'message' => '{attribute} không hợp lệ.'],
            [['phone', 'mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => '{attribute} không hợp lệ.', 'tooShort' => '{attribute} không hợp lệ.'],
            [['username', 'fullname'], 'checkString'],
            [['username'], 'uniqueCode'],
            [['birthday'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
            [['email'], 'email', 'message' => 'Email không hợp lệ.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_group_id' => 'Nhóm quyền',
            'user_group_code' => 'Code nhóm quyền',
            'fullname' => 'Họ và tên',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính',
            'email' => 'Email',
            'phone' => 'Số cố định',
            'mobile' => 'Số di động',
            'username' => 'Tên đăng nhập',
            'password' => 'Mật khẩu'
        ];
    }

    public function uniqueCode($attribute)
    {
        if ($this->id != null) {
            $user = UserBusiness::getByUsername($this->username);
            if ($user != null) {
                if ($user->username != $this->username) {
                    $username = UserBusiness::getByUsername($this->username);
                    if ($username != null) {
                        $this->addError($attribute, 'Tên đăng nhập đã tồn tại!');
                    }
                }
            }
        } else {
            $user = UserBusiness::getByUsername($this->username);
            if ($user != null) {
                $this->addError($attribute, 'Tên đăng nhập đã tồn tại!');
            }
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
            case "username":
                if (!Validation::isUserName($this->username)) {
                    $this->addError($attribute, 'Tên đăng nhập không hợp lệ.');
                }
                break;
            case "fullname":
                $fullname = Strings::_convertToSMS($this->fullname);

                if (!Validation::checkStringSpace($fullname)) {
                    $this->addError($attribute, 'Họ và tên không hợp lệ.');
                }
                break;
        }
    }

} 