<?php

namespace common\components\libs;

use common\components\utils\Utilities;
use yii\captcha\CaptchaAction;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Response;
use yii\captcha\Captcha;

class MTQCaptchaAction extends CaptchaAction {
    
    public function init() {
        parent::init();
    }

    public function run() {

        if (Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR) !== null) {
            
        } else {
            if (ob_get_contents()) ob_end_clean();
            $this->setHttpHeaders();
            Yii::$app->response->format = Response::FORMAT_RAW;
            return $this->renderImage($this->getVerifyCode());
        }
    }

    public function getVerifyCode($regenerate = false) {
        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }

        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey();
        if ($session[$name] === null || $regenerate) {
            $session[$name] = $this->generateVerifyCode();
            $session[$name . 'count'] = 1;
        }

        return $session[$name];
    }

    protected function generateVerifyCode() {
        $code = Utilities::generateRandomUppercaseCaptchar(3);
        return $code;
    }

}
