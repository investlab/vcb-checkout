<?php

namespace common\models\form;

use common\components\utils\Translate;
use common\components\libs\Tables;
use common\models\db\OtpUser;
use common\models\db\UserLogin;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use Yii;

class UserChangePasswordForm extends LanguageBasicForm {

    public $password;
    public $new_password;
    public $rep_password;

    public function rules() {
        return [
            [['password', 'new_password', 'rep_password'], 'required', 'message' => Translate::get('Bạn phải nhập') . '{attribute}.'],
            [['password'], 'checkPass'],
            [['new_password'], 'string', 'min' => 6, 'tooShort' => Translate::get('Mật khẩu gồm 6 ký tự.')],
            ['rep_password', 'compare', 'compareAttribute' => 'new_password', 'message' => Translate::get('Mật khẩu mới và mật khẩu xác nhận không trùng.')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'password' => 'Mật khẩu hiện tại',
            'new_password' => 'Mật khẩu mới',
            'rep_password' => 'Xác nhận mật khẩu mới'
        ];
    }

    public function checkPass($attribute) {
        $password = UserLogin::encryptPassword($this->password);
        $user_login_id = Yii::$app->user->getId();
        if (intval($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", "id = " . $user_login_id);
            if ($user_login) {
                if ($user_login != null) {
                    if ($password != $user_login['password']) {
                        $this->addError($attribute, Translate::get('Mật khẩu cũ không đúng.'));
                    }
                }
            }
        } else {
            $this->addError($attribute, Translate::get('Mật khẩu cũ không đúng.'));
        }
    }

}
