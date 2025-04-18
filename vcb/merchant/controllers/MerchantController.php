<?php

namespace merchant\controllers;

use merchant\models\form\MerchantChangePasswordForm;
use merchant\models\form\MerchantUpdateForm;
use Yii;
use merchant\components\MerchantBasicController;
use common\models\business\MerchantBusiness;
use common\models\db\UserLogin;
use yii\web\UploadedFile;
use common\components\utils\Translate;

class MerchantController extends MerchantBasicController
{

    public function actionIndex()
    {
        return $this->render('index', []);
    }

    public function actionChangePassword()
    {
        $error = '';
        $model = new MerchantChangePasswordForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $inputs = array(
                'id' => UserLogin::get('merchant_id'),
                'new_password' => $model->new_password,
                'user_id' => 0,
            );
            $result = MerchantBusiness::changepassword($inputs);
            if ($result['error_message'] == '') {
                $url = Yii::$app->urlManager->createAbsoluteUrl(['merchant/change-password-success'], HTTP_CODE);
                $this->redirect($url);
            } else {
                $error = $result['error_message'];
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

    public function actionUpdate()
    {
        $error = '';
        $model = new MerchantUpdateForm();
        $model->name = UserLogin::get('merchant_name');
        $model->website = UserLogin::get('merchant_website');
        $model->email_notification = UserLogin::get('merchant_email_notification');
        $model->mobile_notification = UserLogin::get('merchant_mobile_notification');
        $model->url_notification = UserLogin::get('merchant_url_notification');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $file_name = null;
            $model->logo = UploadedFile::getInstance($model, 'logo');
            if ($model->logo != null) {
                $file_name = uniqid() . '.' . $model->logo->extension;
                if (!$model->logo->saveAs(IMAGES_MERCHANT_PATH . $file_name)) {
                    $model->addError('logo', Translate::get('Có lỗi khi upload logo'));
                }
            }
            if (!$model->hasErrors()) {
                $inputs = array(
                    'id' => UserLogin::get('merchant_id'),
                    'name' => $model->name,
                    'website' => $model->website,
                    'logo' => $file_name,
                    'email_notification' => $model->email_notification,
                    'mobile_notification' => $model->mobile_notification,
                    'url_notification' => $model->url_notification,
                    'user_id' => 0,
                );
                $result = MerchantBusiness::update($inputs);
                if ($result['error_message'] == '') {
                    $model->addMessage('Cập nhật thông tin merchant thành công');
                    $url = Yii::$app->urlManager->createAbsoluteUrl(['merchant/index'], HTTP_CODE);
                    $this->redirect($url);
                } else {
                    $error = $result['error_message'];
                }
            }
        }
        return $this->render('update', [
            'error' => $error,
            'model' => $model,
        ]);
    }

}
