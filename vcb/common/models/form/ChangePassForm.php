<?php

namespace common\models\form;

use common\models\business\UserBusiness;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use Yii;

class ChangePassForm extends LanguageBasicForm
{
    public $password;
    public $newPass;
    public $rePass;
    public $time_updated;

    public function rules()
    {
        return [
            [['password', 'newPass', 'rePass'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['time_updated'], 'integer'],
            [['password'], 'checkPass'],
            ['rePass', 'compare', 'compareAttribute' => 'newPass', 'message' => 'Mật khẩu mới và mật khẩu xác nhận không trùng.'],
            [['newPass', 'rePass'], 'string', 'min' => 8, 'max' => 20, 'tooShort' => 'Mật khẩu không đúng định dạng(8 - 20 ký tự, chữ hoa, số, kí tự đặc biệt)',
                'tooLong' => 'Mật khẩu không đúng định dạng(8 - 20 ký tự, chữ hoa, số, kí tự đặc biệt)'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => 'Mật khẩu cũ',
            'newPass' => 'Mật khẩu mới',
            'rePass' => 'Xác nhận mật khẩu mới',
            'time_updated' => 'Ngày cập nhật'
        ];
    }

    public function checkPass($attribute)
    {
        $password = md5($this->password);
        $user = UserBusiness::getByUsername(Yii::$app->user->identity->username);
        if ($user != null) {
            if ($password != $user->password) {
                $this->addError($attribute, 'Mật khẩu cũ không đúng.');
            }
        }
    }
}