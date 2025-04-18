<?php

namespace merchant\controllers;

use common\components\libs\FacebookApi;
use common\components\libs\GoogleApi;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\SendMailBussiness;
use common\models\business\UserLoginBusiness;
use common\models\db\Zone;
use common\models\form\ResetPasswordForm;
use common\models\form\UserChangePasswordForm;
use common\models\form\UserUpdatePasswordForm;
use merchant\components\MerchantBasicController;
use merchant\models\form\UserLoginForgetPasswordForm;
use merchant\models\form\UserLoginRequestForgetPassword;
use yii\filters\AccessControl;
use Yii;
use yii\web\Response;
use yii\web\Session;
use yii\widgets\ActiveForm;
use merchant\models\form\UserLoginForm;
use backend\assets\AppAsset;
use common\models\db\UserLogin;

class UserLoginController extends MerchantBasicController
{

    public $layout = 'user-login';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'captcha',
                            'request-reset-password',
                            'request-reset-password-success',
                            'reset-password',
                            'reset-password-success',
                            'forget-password',
                            'request-forget-password',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function () {
                    if (UserLogin::isLogin()) {
                        return $this->redirect(Yii::$app->getHomeUrl());
                    }
                },
            ],
        ];
    }

    public function actionIndex()
    {
        if (UserLogin::isLogin()) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('user-info/index', HTTP_CODE);
            return $this->redirect($url);
        }
        $error = '';
        $model = new UserLoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $form = Yii::$app->request->post("UserLoginForm");
            $result = UserLogin::checkLogin($form['username'], $form['password'], $error, $user_login_info);
            if ($result != false) {
                UserLogin::login($user_login_info);
                $current_url = Yii::$app->session->get('current_url');
                if (trim($current_url) == '') {
                    $current_url = Yii::$app->urlManager->createAbsoluteUrl('user-info/index', HTTP_CODE);
                }
                $this->redirect($current_url);
            }
        }
        return $this->render('index', [
                "model" => $model,
                "error" => $error,
            ]
        );
    }

    // =================THÔNG TIN TÀI KHOẢN===========================

    public function actionUserInfo()
    {
//        UserAsset::register(Yii::$app->view);
        $this->layout = 'user-login';

        $user_login_id = Yii::$app->user->getId();
        $user_login = null;
        if (intval($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", "id = " . $user_login_id);
            if ($user_login) {
                $ship_address_id = $user_login['ship_address_id'];
                if (intval($ship_address_id) > 0) {
                    $ship_address = Tables::selectOneDataTable("address", "id = " . $ship_address_id);
                    $user_login['ship_address_id'] = $ship_address_id;
                    $user_login['ship_address'] = $ship_address['address'];
                    $user_login['ship_fullname'] = $ship_address['fullname'];
                    $user_login['ship_mobile'] = $ship_address['mobile'];
                    $user_login['ship_address_name'] = Zone::getNameByZoneId($ship_address['zone_id']);
                }

                $payment_address_id = $user_login['payment_address_id'];
                if (intval($payment_address_id) > 0) {
                    $payment_address = Tables::selectOneDataTable("address", "id = " . $payment_address_id);
                    $user_login['payment_address_id'] = $payment_address_id;
                    $user_login['payment_address'] = $payment_address['address'];
                    $user_login['payment_fullname'] = $payment_address['fullname'];
                    $user_login['payment_mobile'] = $payment_address['mobile'];
                    $user_login['payment_address_name'] = Zone::getNameByZoneId($payment_address['zone_id']);
                }
            }
        }

        return $this->render('user-info', [
            'user_login' => $user_login
        ]);
    }

    // =================CẬP NHẬT THÔNG TIN CÁ NHÂN===========================
    public function actionUserInfoUpdate()
    {
//        UserAsset::register(Yii::$app->view);
        $this->layout = 'user-login';

        $user_login_id = Yii::$app->user->getId();
        $user_login = null;
        if (intval($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", "id = " . $user_login_id);
        }
        return $this->render('user-info-update', [
            'user_login' => $user_login
        ]);
    }

    // =================LẤY LẠI MẬT KHẨU===========================

    public function actionRequestResetPassword()
    {
        $this->layout = 'user-login';
        $error = null;
        /*$model = new RequestResetPasswordForm();
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post("RequestResetPasswordForm");
            $inputs = array(
                'email' => $form['email']
            );
            $result = UserLoginBusiness::requestResetPasswordByEmail($inputs);

            if ($result['error_message'] == '') {
                $url = Yii::$app->urlManager->createAbsoluteUrl(['user-login/request-reset-password-success', 'email' => $form['email']]);
                $this->redirect($url);
            } else {
                $error = $result['error_message'];
            }
        }*/
        return $this->render('request-reset-password', [
            //'model' => $model,
            'error' => $error
        ]);
    }

    public function actionForgetPassword()
    {
        $error = '';
        if (isset($_GET['email'])){
            $data = [
                'email' => $_GET['email'],
                'name' => $_GET['name'],
                'time_updated' =>$_GET['time_update'],
            ];
            $dataUser = UserLogin::findBySql("SELECT * FROM user_login WHERE email = '" . $data['email'] . "' AND time_updated = " . $data['time_updated'])->one();
            if (isset($dataUser)) {
                $model = new UserLoginForgetPasswordForm();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->validate()) {
                        $inputs = array(
                            'email' => $data['email'],
                            'time_updated' => $data['time_updated'],
                            'new_password' => $model->new_password,
                        );
                        $result = UserLoginBusiness::changeForgetPassword($inputs);
                        if ($result['error_message'] == '') {
                            $message = 'Cập nhật mật khẩu thành công!';
                            $url = Yii::$app->urlManager->createAbsoluteUrl(['user-info/index'], HTTP_CODE);
                            Weblib::showMessage($message, $url);
                        } else {
                            $error = $result['error_message'];
                        }
                    }
                }
                return $this->render('forget-password', [
                    'error' => $error,
                    'model' => $model,
                    'data' => $data
                ]);
            }else{
                $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['user-login/index'], HTTP_CODE));
            }
        }else{
            $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['user-login/index'], HTTP_CODE));
        }
    }

    public function actionRequestResetPasswordSuccess()
    {
//        UserAsset::register(Yii::$app->view);
//        $this->layout = 'product';
        $this->layout = 'user-login';
        $email = ObjInput::get('email', 'str', '');

        return $this->render('request-reset-password-success', [
            'email' => $email
        ]);
    }

    public function actionResetPassword()
    {
//        UserAsset::register(Yii::$app->view);
//        $this->layout = 'product';
        $this->layout = 'user-login';
        $id = ObjInput::get('id', 'int', 0);
        $code = ObjInput::get('code', 'str', '');
        $checksum = ObjInput::get('checksum', 'str', '');

        $model = new ResetPasswordForm();
        $model->id = $id;
        $model->code = $code;
        $model->checksum = $checksum;
        $user_login = null;
        $error = null;

        if (intval($id) > 0) {
            $user_login = Tables::selectOneDataTable('user_login', ['id = :id', 'id' => $id]);
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $form = Yii::$app->request->post("ResetPasswordForm");
                $user_login = Tables::selectOneDataTable('user_login', 'id = ' . $form['id']);
                $get_checksum = UserLogin::getChecksumForUrlVerifyEmail($form['id'], $form['code']);
                if ($get_checksum == $form['checksum']) {
                    $param = array(
                        'user_login_id' => $form['id'],
                        'otp' => $form['code'],
                        'new_password' => $form['password']
                    );

                    $result = UserLoginBusiness::verifyResetPasswordByEmail($param);
                    if ($result['error_message'] == '') {
                        $url = Yii::$app->urlManager->createAbsoluteUrl(['user-login/reset-password-success', 'email' => $user_login['email']], HTTP_CODE);
                        $this->redirect($url);
                    } else {
                        $error = $result['error_message'];
                    }
                } else {
                    $error = "Tài khoản ko hợp lệ.";
                }
            }
        }


        return $this->render('reset-password', ['model' => $model,
            'user_login' => $user_login,
            'error' => $error]);
    }

    public function actionResetPasswordSuccess()
    {
//        UserAsset::register(Yii::$app->view);
//        $this->layout = 'product';
        $this->layout = 'user-login';
        $email = Yii::$app->request->get('email');
        return $this->render('reset-password-success', [
            'email' => $email
        ]);
    }

    // =================ĐỔI MẬT KHẨU===========================

    public function actionChangePassword()
    {

//        UserAsset::register(Yii::$app->view);
//        $this->layout = 'product';
        $this->layout = 'user-login';
        $user_login_id = Yii::$app->user->getId();
        $user_login = null;
        if (intval($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", "id = " . $user_login_id);
        }

        $model = new UserChangePasswordForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo 'agent error : \n';
                die;
            }
            $form = Yii::$app->request->post("UserChangePasswordForm");

            $param = array(
                'user_login_id' => $user_login_id,
                'password' => $form['password'],
                'new_password' => $form['new_password']
            );
            $result = UserLoginBusiness::changePassword($param);
            if ($result['error_message'] == '') {
                $message = 'Đổi mật khẩu tài khoản thành công.';
                $url = Yii::$app->urlManager->createAbsoluteUrl('default/index', HTTP_CODE);
            } else {
                $message = $result['error_message'];
                $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/change-password', HTTP_CODE);
            }
            Weblib::showMessage($message, $url);
        }

        return $this->render('change-password', [
            'model' => $model,
            'user_login' => $user_login
        ]);
    }

    // =================ĐỔI MẬT KHẨU===========================

    public function actionUpdatePassword()
    {

//        UserAsset::register(Yii::$app->view);
//        $this->layout = 'product';
        $this->layout = 'user-login';
        $user_login_id = Yii::$app->user->getId();
        $user_login = null;
        if (intval($user_login_id) > 0) {
            $user_login = Tables::selectOneDataTable("user_login", "id = " . $user_login_id);
        }

        $model = new UserUpdatePasswordForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                $message = 'Tham số đầu vào không hợp lệ';
                $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/update-password', HTTP_CODE);
            }
            $form = Yii::$app->request->post("UserUpdatePasswordForm");

            $param = array(
                'user_login_id' => $user_login_id,
                'new_password' => $form['password']
            );
            $result = UserLoginBusiness::updatePassword($param);
            if ($result['error_message'] == '') {
                $message = 'Khai báo mật khẩu tài khoản thành công.';
                $url = Yii::$app->urlManager->createAbsoluteUrl('default/index', HTTP_CODE);
            } else {
                $message = $result['error_message'];
                $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/update-password', HTTP_CODE);
            }

            Weblib::showMessage($message, $url);
        }

        return $this->render('update-password', [
            'model' => $model,
            'user_login' => $user_login
        ]);
    }

    public function actionLoginGooglePhp()
    {
        $redirectURL = GOOGLE_REDIRECT_URL;
        $check_code = GoogleApi::checkCode($redirectURL);
        if ($check_code['check_gg']) {
            $this->redirect($check_code['url']);
        }

        $user = GoogleApi::getUser($redirectURL);
        if ($user['check']) {
            if (trim(@$user['data']['gender']) == 'male') {
                $gender = 1;
            } else {
                $gender = 2;
            }
            $param = [
                'social_network_id' => 2,
                'social_network_account_id' => $user['data']['id'],
                'fullname' => $user['data']['name'],
                'email' => $user['data']['email'],
                'mobile' => '',
                'birthday' => 0,
                'gender' => $gender,
            ];
            $user_login = UserLoginBusiness::addBySocialNetworkAccount($param);
            if ($user_login['error_message'] == '') {
                $user_login_info = UserLoginBusiness::getById($user_login['id']);
                UserLogin::login($user_login_info);
                $url = Yii::$app->urlManager->createUrl('default/index');
                $this->redirect($url);
            } else {
                $url = Yii::$app->urlManager->createUrl('user-login/index');
                $this->redirect($url);
            }
        }
    }

    public function actionLoginFacebookPhp()
    {
        $check_return = ObjInput::get('check_return', 'int');
        $facebook_login = FacebookApi::getUser();
        if ($facebook_login['check_code']) {
            $this->redirect($facebook_login['url']);
        }

        if ($facebook_login['check_data']) {
            if (trim($facebook_login['data']['gender']) == 'male') {
                $gender = 1;
            } else {
                $gender = 2;
            }
            $param = [
                'social_network_id' => 1,
                'social_network_account_id' => $facebook_login['data']['id'],
                'fullname' => $facebook_login['data']['name'],
                'email' => $facebook_login['data']['email'],
                'mobile' => '',
                'birthday' => 0,
                'gender' => $gender,
            ];
//            var_dump($param);die;
            $user_login = UserLoginBusiness::addBySocialNetworkAccount($param);
            if ($user_login['error_message'] == '') {
                $user_login_info = UserLoginBusiness::getById($user_login['id']);
                UserLogin::login($user_login_info);
                if ($check_return == 1) {
                    $url = Yii::$app->urlManager->createUrl('bill/create');
                } else {
                    $url = Yii::$app->urlManager->createUrl('default/index');
                }
                $this->redirect($url);
            } else {
                if ($check_return == 1) {
                    $url = Yii::$app->urlManager->createUrl('bill/login');
                } else {
                    $url = Yii::$app->urlManager->createUrl('user-login/index');
                }
                $this->redirect($url);
            }
        }
    }

    public function actionRequestForgetPassword(){
        $this->layout = 'user-login';
        $error = null;
        $model = new UserLoginRequestForgetPassword();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->validate()) {
                        $email = $model->email;
                        $user_info = Tables::selectOneDataTable("user_login", "email = '" . $email."'");
                        if ($user_info != false){
                            SendMailBussiness::send($email, Translate::get('Xác thực yêu cầu lấy mật khẩu'), 'forget_password',[
                                'fullname' => $user_info['fullname'],
                                'link' => ROOT_URL.'vi/merchant/user-login/forget-password?email='.$email.'&name='.$user_info['fullname'].'&time_update='.$user_info['time_updated'],
                            ]);
                            Yii::$app->urlManager->createAbsoluteUrl(['user-login/verify-email'], HTTP_CODE);
                            return $this->render('verify-email', [
                                'email' => $email,
                                'index' => ROOT_URL.'merch/user-login/index',
                            ]);
                        }else{
                            $error = "Email chưa được kích hoạt. Vùi lòng liên hệ bộ phận hỗ trợ để đăng ký tài khoản";
                        }
                    }
                }
        return $this->render('request-forget-password', [
            'error' => $error,
            'model' => $model
        ]);
    }

}
