<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\models\business\UserBusiness;
use common\models\business\ZoneBusiness;
use common\models\db\Contact;
use common\models\db\User;
use common\models\form\ChangePassForm;
use common\models\form\UserForm;
use yii\web\Response;
use Yii;
use yii\widgets\ActiveForm;

class DefaultController extends BackendController
{

    public function actionIndex()
    {
        $count = 0;
        //$contact = Contact::find()->where('status =' . Contact::STATUS_UNREAD)->all();
        //$count = count($contact);
        return $this->render('index', [
            'root_url' => ROOT_URL,
            'count' => $count
        ]);
    }

    public function actionDetail()
    {
        $model = new UserForm();

        $model_pass = new ChangePassForm();
        $user_id = Yii::$app->user->getId();
        $user = UserBusiness::getByID($user_id);

        $model->id = $user_id;
        $model->fullname = $user['fullname'];
        $model->gender = $user['gender'];
        if ($user['birthday'] != null) {
            $model->birthday = date('d-m-Y', $user['birthday']);
        }
        $model->username = $user['username'];
        $model->email = $user['email'];
        $model->mobile = $user['mobile'];
        $model->phone = $user['phone'];


        $user_group = Weblib::createComboTableArray('user_group', 'id', 'name', 1, 'Chọn nhóm quyền', true);
        $user_gender = User::getGender();


        return $this->render('detail', [
            'users' => $user,
            'model' => $model,
            'user_group' => $user_group,
            'user_gender' => $user_gender,
            'model_pass' => $model_pass,
        ]);
    }

    public function actionUpdate()
    {
        $message = 'Cập nhật thất bại!';
        $form = new UserForm();
        if (Yii::$app->request->isAjax && $form->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($form);
        }

        $user_form = Yii::$app->request->post("UserForm");
        $user_id = Yii::$app->user->getId();

        $user = UserBusiness::getByID($user_id);

        if ($user != null) {
            $user->fullname = $user_form['fullname'];
            $user->email = $user_form['email'];
            $user->mobile = $user_form['mobile'];
            $user->phone = $user_form['phone'];
            $user->gender = $user_form['gender'];
            if ($user_form['birthday'] != null) {
                $user->birthday = Yii::$app->formatter->asTimestamp($user_form['birthday']);
            }
            $user->time_updated = time();

            if ($user->validate()) {
                if ($user->save()) {
                    $message = 'Cập nhật thành công!';
                }
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(['default/detail'], HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
    }

    // thay đổi mật khẩu
    public function actionChangePass()
    {
        $users_id = Yii::$app->getUser()->getId();
        $users = UserBusiness::getByID($users_id);

        $model = new ChangePassForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo 'agent error : \n';
                die;
            }
            $form = Yii::$app->request->post("ChangePassForm");

            $users->password = md5($form['newPass']);
            $users->time_updated = time();

            if ($users->save()) {
                $message = 'Cập nhật thành công.';
            } else {
                $message = 'Cập nhật thất bại.';
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(['default/detail'], HTTP_CODE);
            Weblib::showMessage($message, $url);;
        }
    }


}
