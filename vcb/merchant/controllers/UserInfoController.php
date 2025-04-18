<?php

namespace merchant\controllers;

use merchant\models\form\UserLoginChangePasswordForm;
use Yii;
use merchant\components\MerchantBasicController;
use common\models\business\UserLoginBusiness;

class UserInfoController extends MerchantBasicController
{

    public function actionIndex()
    {
        return $this->render('index', []);
    }

    public function actionChangePassword()
    {
        $error = '';
        $model = new UserLoginChangePasswordForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $inputs = array(
                    'user_login_id' => Yii::$app->user->getId(),
                    'password' => $model->password,
                    'new_password' => $model->new_password,
                );
                $result = UserLoginBusiness::changePassword($inputs);
                if ($result['error_message'] == '') {
                    $url = Yii::$app->urlManager->createAbsoluteUrl(['user-info/change-password-success'], HTTP_CODE);
                    $this->redirect($url);
                } else {
                    $error = $result['error_message'];
                }
            }
        }
        return $this->render('change-password', [
            'error' => $error,
            'model' => $model,
        ]);
    }

    public function actionChangePasswordSuccess()
    {
        return $this->render('change-password-success', []);
    }
}
