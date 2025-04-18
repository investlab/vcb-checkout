<?php

namespace merchant\controllers;


use common\models\db\UserLogin;
use merchant\components\MerchantBasicController;
use Yii;

class UserLogoutController extends MerchantBasicController
{

    public function actionIndex()
    {
        UserLogin::logout();
        $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/index');
        return $this->redirect($url);
    }

} 