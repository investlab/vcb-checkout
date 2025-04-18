<?php
namespace common\components\libs;

use yii\captcha\CaptchaValidator;

class MTQCaptchaValidator extends CaptchaValidator
{
    public $captchaAction = "captcha";

    /*
     * Kiem tra gia tri captcha
     * @input: captchaCode, controllers
     * 
     * return Boolean
     */
    public function validateValue($attribute, $controllers = '')
    {
        if (trim($controllers) != '') {
            $this->captchaAction = $controllers . DS . $this->captchaAction;

            $captcha = $this->createCaptchaAction();
            $valid = !is_array($attribute->verifyCode) && $captcha->validate($attribute->verifyCode, $this->caseSensitive);

            if (isset($valid) && $valid == true) {
                return true;
            }
        }

        return false;
    }
}