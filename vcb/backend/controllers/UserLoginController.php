<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\UserLoginBusiness;
use common\models\db\Merchant;
use common\models\db\Right;
use common\models\db\UserLogin;
use common\models\db\UserRightMerchant;
use common\models\form\UserLoginForm;
use common\models\form\UserLoginUpdateForm;
use common\models\form\UserLoginUpdateIPForm;
use common\models\input\UserLoginSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class UserLoginController extends BackendController
{

    // Danh sách
    public function actionIndex()
    {
        $search = new UserLoginSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = UserLogin::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'check_all_operators' => UserLogin::getOperatorsForCheckAll(),
        ]);
    }

    // Thêm mới
    public function actionAdd()
    {
        $model = new UserLoginForm();
        $model->load(Yii::$app->request->get(), '');
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $gender_arr = UserLogin::getGender();
        $error = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('UserLoginForm');

            $params = array(
                'merchant_id' => $form['merchant_id'],
                'fullname' => $form['fullname'],
                'email' => $form['email'],
                'mobile' => $form['mobile'],
                'password' => UserLogin::encryptPassword(trim($form['password'])),
                'gender' => $form['gender'],
                'ips' => $form['ips'],
                'birthday' => Yii::$app->formatter->asTimestamp($form['birthday'])
            );

            $result = UserLoginBusiness::add($params);
            
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm tài khoản merchant thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/index');
                Weblib::showMessage($message, $url);
            } else {
                $error = Translate::get($result['error_message']);
            }
        }
        return $this->render('add', [
            'model' => $model,
            'error' => $error,
            'merchant_arr' => $merchant_arr,
            'gender_arr' => $gender_arr
        ]);


    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['user-login/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'user_login_id' => $id
            ];
            $result = UserLoginBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = 'Khóa tài khoản Merchant thành công.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản Merchant';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    // Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['user-login/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'user_login_id' => $id
            ];
            $result = UserLoginBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = 'Mở khóa tài khoản Merchant thành công.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản Merchant';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Cập nhật
    public function actionViewUpdate()
    {
        $model = new UserLoginUpdateForm();
        $gender_arr = UserLogin::getGender();
        $id = ObjInput::get('id', 'int');
        $user_login = null;
        if (intval($id) > 0) {
            $user_login_info = Tables::selectOneDataTable("user_login", ["id = :id ", "id" => $id]);
            $user_login = UserLogin::setRow($user_login_info);
            if ($user_login) {
                $model->id = $user_login['id'];
                $model->fullname = $user_login['fullname'];
                if (intval($user_login['birthday']) > 0) {
                    $model->birthday = date('d-m-Y', $user_login['birthday']);
                }
                $model->gender = $user_login['gender'];
            }
        }
        return $this->render('update', [
            'model' => $model,
            'user_login' => $user_login,
            'gender_arr' => $gender_arr
        ]);
    }

    public function actionUpdate()
    {
        $model = new UserLoginUpdateForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('UserLoginUpdateForm');
            $params = array(
                'user_login_id' => $form['id'],
                'fullname' => $form['fullname'],
                'gender' => $form['gender'],
                'birthday' => Yii::$app->formatter->asTimestamp($form['birthday']),
                'user_id' => Yii::$app->user->getId()
            );

            $result = UserLoginBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật tài khoản Merchant thành công .';
            } else {
                $message = $result['error_message'];
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('user-login/index');
            Weblib::showMessage($message, $url);
        }
    }

    // Reset mật khẩu
    public function actionResetPassword()
    {
        $message = null;
        $search = ['user-login/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'user_login_id' => $id,
            ];
            $result = UserLoginBusiness::resetPassword($params);
            if ($result['error_message'] == '') {
                $message = 'Reset tài khoản Merchant thành công. Bạn vui lòng truy cập hòm thư ' . $result['send_email_to'] . ' để nhận mật khẩu mới.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản Merchant';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Cập nhật dải IP
    public function actionUpdateIp()
    {
        $id = ObjInput::get('id', 'int');
        $model = null;
        $errors = null;
        $user_login = null;
        if (isset($id) && intval($id) > 0) {
            $model = UserLoginUpdateIPForm::findBySql("SELECT * FROM user_login WHERE id = $id ")->one();
            $user_login_info = Tables::selectOneDataTable('user_login', ['id = :id', 'id' => $id]);
            $user_login = UserLogin::setRow($user_login_info);
            $errors = null;
            if ($model == null) {
                Weblib::showMessage('Tài khoản không hợp lệ', Yii::$app->urlManager->createAbsoluteUrl(['user-login/index']));
            } else {
                $model->load(Yii::$app->request->get());
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->validate()) {
                        $params = array(
                            'user_login_id' => $model->id,
                            'ips' => $model->ips
                        );
                        $result = UserLoginBusiness::updateIP($params);
                        if ($result['error_message'] == '') {
                            Weblib::showMessage('Cập nhật dải IP thành công', Yii::$app->urlManager->createAbsoluteUrl(['user-login/index']), false);
                            die();
                        } else {
                            $errors = $result['error_message'];
                        }
                    }
                }
            }
        }
        return $this->render('update-ip', [
            'model' => $model,
            'error_message' => $errors,
            'user_login' => $user_login
        ]);
    }

    public function actionRoles()
    {
        $user_id = Yii::$app->request->get('id');
        $user_merchant = UserLogin::findOne(['id' => $user_id]);
        $right_merchant = Right::find()->where(['type' => Right::TYPE_MERCHANT])->addOrderBy('left')->all();
        $user_group_right = UserRightMerchant::find()->where(['user_id' => $user_id])->all();
        $right_ids = array();
        foreach ($user_group_right as $key => $data) {
            $right_ids[] = $data['right_id'];
        }

        return $this->render('roles', [
            'user_merchant' => $user_merchant,
            'right_merchant' => $right_merchant,
            'right_ids' => $right_ids
        ]);
    }

    public function actionSetRoles()
    {
        $right_ids = $this->_getRightIds();
//        var_dump($right_ids);die();
        $user_merchant_id = Yii::$app->request->post('user_merchant_id');
        $error = array();

        $right_merchant = UserRightMerchant::getDb()->beginTransaction();
        $user_right_list = UserRightMerchant::find()->where(['user_id' => $user_merchant_id]);
        if ($user_right_list != null) {
            $result = UserRightMerchant::deleteAll(['user_id' => $user_merchant_id]);
        }

        foreach ($right_ids as $id) {
            if ($id != null) {
                $right_id = null;
                $right_code = null;
                $right = Right::findOne(['id' => $id]);

                if ($right != null) {
                    $right_id = $id;
                    $right_code = $right['code'];
                }

                $user_right = new UserRightMerchant();
                $user_right->user_id = $user_merchant_id;
                $user_right->user_admin_account_id = 0;
                $user_right->right_id = $right_id;
                $user_right->right_code = $right_code;

                if (!$user_right->save()) {
                    $error[] = Translate::get('Không thêm được quyền') . $right_code;
                }
            } else {
                continue;
            }
        }

        if ($error != null) {
            $message = Translate::get('Phân quyền thất bại');
            $right_merchant->rollBack();
        } else {
            $message = Translate::get('Phân quyền thành công');
            $right_merchant->commit();
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl(['user-login/roles', 'id' => $user_merchant_id]);
        Weblib::showMessage($message, $url);
    }

    protected function _getRightIds()
    {
        $right_ids = Yii::$app->request->post('ids');

        if (!is_array($right_ids) || empty($right_ids)) {
            $id = intval(Yii::$app->request->get('id'));
            $right_ids = array($id);
        }
        return $right_ids;
    }
} 