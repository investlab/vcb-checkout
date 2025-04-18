<?php

namespace  app\models\form;
use common\components\utils\Translate;
use common\components\utils\Validation;
use yii\base\Model;

class UserLoginForm extends Model
{
    public $email;
    public $password;

    public function rules(){
        return  [
            [['email','password'], 'required', 'on' => 'login'],
            [['email'], 'checkEmail', 'on' => 'login'],


        ];
    }
    public function checkEmail()
    {
        if ($this->email) {
            if (!Validation::isEmail($this->email)) {
                $this->addError($this->email, Translate::get('Email không đúng định dạng'));
                return false;
            }
        }
    }
}