<?php


namespace backend\controllers;

use backend\components\BackendController;
use backend\models\form\CreditPartnerBranchForm;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\SendMailBussiness;
use common\models\business\UserAdminAccountBusiness;
use common\models\business\UserBusiness;
use common\models\business\UserRoleBusiness;
use common\models\business\ZoneBusiness;
use common\models\db\Right;
use common\models\db\User;
use common\models\db\UserAdminAccount;
use common\models\db\UserRight;
use common\models\form\LoginForm;
use common\models\form\UserAdminAccountForm;
use common\models\input\UserSearch;
use common\util\TextUtil;
use common\models\db\Branch;

use yii\filters\AccessControl;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\components\libs\Tables;
use common\models\db\UserGroup;
use common\models\form\UserAddForm;
use common\models\form\UserUpdateForm;
use common\models\db\Zone;

class   UserController extends BackendController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'captcha'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'logout', 'index',
                            'detail', 'lock',
                            'unlock', 'create',
                            'view-info-update',
                            'get-credit-partner-branch',
                            'update', 'reset-pass',
                            'roles', 'set-roles', 'login',
                            'search', 'search-affiliate',
                            'add-user-admin-account', 'update-user-admin-account',
                            'get-branch-by-partner', 'get-mtq-inventory'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    protected function _checkUser($user_id)
    {
        $sub_user_group_ids = User::getSubUserGroupIds(Yii::$app->user->getId(), $current_user_info);
        if ($current_user_info != false && $current_user_info['username'] == 'administrator') {
            $user_info = Tables::selectOneDataTable("user", "id = $user_id ");
            return $user_info;
        } else {
            if (!empty($sub_user_group_ids)) {
                $user_admin_account_info = Tables::selectOneDataTable("user_admin_account", "user_id = $user_id AND user_group_id IN (" . implode(',', $sub_user_group_ids) . ") ");
                if ($user_admin_account_info != false) {
                    $user_info = Tables::selectOneDataTable("user", "id = $user_id ");
                    return $user_info;
                } else {
                    $count = Tables::selectCountDataTable("user_admin_account", "user_id = $user_id ");
                    if ($count == 0) {
                        $user_info = Tables::selectOneDataTable("user", "id = $user_id ");
                        return $user_info;
                    }
                }
            }
        }
        return false;
    }

    protected function _getUserGroups($code = false, $result = array())
    {
        $user_group_info = false;
        $sub_user_group_ids = User::getSubUserGroupIds(Yii::$app->user->getId(), $current_user_info);
        if ($current_user_info != false && $current_user_info['username'] == 'administrator') {
            $user_group_info = Tables::selectAllDataTable("user_group", 1, "`left` ASC, id DESC ");
        } else {
            if (!empty($sub_user_group_ids)) {
                $user_group_info = Tables::selectAllDataTable("user_group", "id IN (" . implode(',', $sub_user_group_ids) . ") ", "`left` ASC, id DESC ");
            }
        }
        if ($user_group_info != false) {
            foreach ($user_group_info as $row) {
                if ($code) {
                    $result[$row['code']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
                } else {
                    $result[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
                }
            }
        }
        return $result;
    }

    public function actionIndex()
    {
        $search = new UserSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $user_group = $this->_getUserGroups(false, array(0 => '--- '.Translate::get('Nhóm người dùng').' ---'));
        $user_status = User::getStatus();
        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'user_group' => $user_group,
            'user_status' => $user_status
        ]);
    }

    public function actionLogin()
    {
        Yii::$app->language = 'en-US';

        $this->layout = 'login';
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(Yii::$app->getHomeUrl());
        }
        $error_message = '';
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {           
            if ($model->loginBackend($error_message)) {
                return $this->redirect(Yii::$app->getHomeUrl());
            }
        }
        return $this->render('login', [
                "model" => $model,
                "error" => $error_message,
            ]
        );
    }

    public function actionLogout()
    {

        Yii::$app->user->logout();
        return $this->redirect(Yii::$app->getHomeUrl());
    }

    public function actionDetail()
    {
        $user_id = Yii::$app->request->get('id');
        $user = $this->_checkUser($user_id);
        if ($user == false) {
            $message = Translate::get('Người dùng không tồn tại');
            $url = Yii::$app->urlManager->createUrl(['user/index']);
            Weblib::showMessage($message, $url);
        } else {
            $addressFull = null;
            // Lấy địa chỉ NCC
            $address = $user['address'];
            $zone_id = $user['zone_id'];

            if ($zone_id != null && $zone_id != 0 && $address != null) {
                $wardsList = ZoneBusiness::getByID($zone_id);
                if ($wardsList != null && $wardsList->level == 4) {
                    $district_id = $wardsList->parent_id;
                    $districtList = ZoneBusiness::getByID($district_id);
                    if ($districtList != null) {
                        $zone_id = $districtList->parent_id;
                        $city = ZoneBusiness::getByID($zone_id);
                        if ($city != null) {
                            $addressFull = $address . '-' . $wardsList->name . '-' . $districtList->name . '-' . $city->name;
                        } else {
                            $addressFull = $address . '-' . $wardsList->name . '-' . $districtList->name;
                        }
                    }
                } else if ($wardsList != null && $wardsList->level == 3) {
                    $zone_id = $wardsList->parent_id;
                    $city = ZoneBusiness::getByID($zone_id);
                    if ($city != null) {
                        $addressFull = $address . '-' . $wardsList->name . '-' . $city->name;
                    } else {
                        $addressFull = $address . '-' . $wardsList->name;
                    }
                }
            } elseif ($zone_id != null && $zone_id != 0 && $address == null) {
                $wardsList = ZoneBusiness::getByID($zone_id);
                if ($wardsList != null && $wardsList->level == 4) {
                    $district_id = $wardsList->parent_id;
                    $districtList = ZoneBusiness::getByID($district_id);
                    if ($districtList != null) {
                        $zone_id = $districtList->parent_id;
                        $city = ZoneBusiness::getByID($zone_id);
                        if ($city != null) {
                            $addressFull = $wardsList->name . '-' . $districtList->name . '-' . $city->name;
                        } else {
                            $addressFull = $wardsList->name . '-' . $districtList->name;
                        }
                    }
                } else if ($wardsList != null && $wardsList->level == 3) {
                    $zone_id = $wardsList->parent_id;
                    $city = ZoneBusiness::getByID($zone_id);
                    if ($city != null) {
                        $addressFull = $wardsList->name . '-' . $city->name;
                    } else {
                        $addressFull = $wardsList->name;
                    }
                }
            } elseif ($zone_id == null && $zone_id == 0 && $address != null) {
                $addressFull = $address;
            }
            return $this->render('detail', [
                'addressFull' => $addressFull,
                'user' => $user
            ]);
        }
    }

    public function actionLock()
    {
        $message = null;
        $search = ['user/index'];
        $commit = false;
        $transaction = User::getDb()->beginTransaction();
        if (Yii::$app->request->post()) {
            $user_id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }
            $user = $this->_checkUser($user_id);
            if ($user != false) {
                $model = User::findOne(['id' => $user_id]);
                if ($model != null) {
                    $model->status = 2;
                    $model->time_updated = time();
                    $user_admin_account = UserAdminAccountBusiness::getByUserId($model->id);
                    $all = false;
                    if ($user_admin_account != null) {
                        foreach ($user_admin_account as $k => $v) {
                            $params = array(
                                'id' => $v['id'],
                                'user_id' => Yii::$app->user->getId()
                            );
                            $result = UserAdminAccountBusiness::lock($params);
                            if ($result['error_message'] == '') {
                                $all = true;
                            }
                        }
                    } else {
                        $all = true;
                    }
                    if ($all) {
                        if ($model->save()) {
                            $commit = true;
                            $message = Translate::get('Khóa thành công');
                        } else {
                            $message = Translate::get('Khóa thất bại');
                        }
                    } else {
                        $message = Translate::get('Khóa thất bại, chưa khóa được tài khoản người dùng');
                    }
                } else {
                    $message = Translate::get('Người dùng không tồn tại');
                }
            } else {
                $message = Translate::get('Người dùng không tồn tại');
            }
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionUnlock()
    {
        $message = null;
        $search = ['user/index'];
        if (Yii::$app->request->post()) {
            $user_id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }
            $user = $this->_checkUser($user_id);
            if ($user != false) {
                $model = User::findOne(['id' => $user_id]);
                if ($model != null) {
                    $model->status = 1;
                    $model->time_updated = time();
                    if ($model->save()) {
                        $message = Translate::get('Mở khóa thành công');
                    } else {
                        $message = Translate::get('Mở khóa thất bại');
                    }
                } else {
                    $message = Translate::get('Người dùng không tồn tại');
                }
            } else {
                $message = Translate::get('Người dùng không tồn tại');
            }
        }
        if (Yii::$app->request->get()) {
            $search = $search + Yii::$app->request->get();
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    public function actionCreate()
    {
        $message = '';
        $model = new UserAddForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('UserAddForm');
            if ($model->validate()) {
                $inputs = array(
                    'fullname' => $model->fullname,
                    'username' => $model->username,
                    'password' => $model->password,
                    'email' => $model->email,
                    'mobile' => $model->mobile,
                    'phone' => $model->phone,
                    'gender' => $model->gender,
                    'birthday' => Yii::$app->formatter->asTimestamp($model->birthday),
                    'user_created' => Yii::$app->user->getId(),
                    'branch_id' => $form['branch_id']
                );
                $result = UserBusiness::addAndSendEmailPassword($inputs);
                if ($result['error_message'] == '') {
                    $message = Translate::get('Thêm thành công. Bạn vui lòng truy cập hòm thư ' . $model->email . ' để nhận mật khẩu mới');
                    $url = Yii::$app->urlManager->createAbsoluteUrl("user/index");
                    Weblib::showMessage($message, $url);
                } else {
                    $message = Translate::get($result['error_message']);
                }
            }
        }else {
            $branchs = Weblib::createComboTableArray('branch', 'id', 'name', ['status' => Branch::STATUS_ACTIVE], 'Chọn chi nhánh');
        }
        $user_gender = User::getGender();
        return $this->render('create', [
            'model' => $model,
            'user_gender' => $user_gender,
            'message' => $message,
            'branchs' => $branchs,
            'get_branch_url' => Yii::$app->urlManager->createAbsoluteUrl(['user/get-credit-partner-branch']),
        ]);
    }


    public function actionViewInfoUpdate()
    {
        $id = Yii::$app->request->get('id');
        $user = $this->_checkUser($id);
        $message = '';
        if ($user == false) {
            $message = Translate::get('Người dùng không tồn tại');
            $url = Yii::$app->urlManager->createUrl(['user/index']);
            Weblib::showMessage($message, $url);
        } else {
            $model = UserUpdateForm::findOne(['id' => $id]);
            if ($model != null) {
                if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }
                if ($model->load(Yii::$app->request->post())) {
                    $form = Yii::$app->request->post('UserUpdateForm');
                    if ($model->validate()) {
                        $inputs = array(
                            'id' => $model->id,
                            'fullname' => $model->fullname,
                            'email' => $model->email,
                            'mobile' => $model->mobile,
                            'phone' => $model->phone,
                            'gender' => $model->gender,
                            'birthday' => Yii::$app->formatter->asTimestamp($model->birthday),
                            'branch_id' => $form['branch_id'],
                            'user_updated' => Yii::$app->user->getId(),
                        );
                        $result = UserBusiness::update($inputs);
                        if ($result['error_message'] == '') {
                            $message = Translate::get('Cập nhật thành công');
                            $url = Yii::$app->urlManager->createAbsoluteUrl("user/index");
                            Weblib::showMessage($message, $url);
                        } else {
                            $message = Translate::get($result['error_message']);
                        }
                    }
                } else {
                    $model->birthday = date('d-m-Y', $model->birthday);
                    $branchs = Weblib::createComboTableArray('branch', 'id', 'name', ['status' => Branch::STATUS_ACTIVE], 'Chọn chi nhánh');

                }
                $user_gender = User::getGender();

                return $this->render('update', [
                    'model' => $model,
                    'user_gender' => $user_gender,
                    'message' => $message,
                    'branchs' => $branchs,
                    'get_branch_url' => Yii::$app->urlManager->createAbsoluteUrl(['user/get-credit-partner-branch']),
                ]);
            } else {
                $message = Translate::get('Người dùng không tồn tại');
                $url = Yii::$app->urlManager->createUrl(['user/index']);
                Weblib::showMessage($message, $url);
            }
        }
    }

    public function actionResetPass()
    {
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");
            $user = $this->_checkUser($id);
            if ($user == false) {
                $message = Translate::get('Người dùng không tồn tại');
                $url = Yii::$app->urlManager->createUrl(['user/index']);
                Weblib::showMessage($message, $url);
            } else {
                $model = User::findOne(['id' => $id]);
                if ($model != null) {
                    $email = $user['email'];
                    $username = $user['username'];
                    $password = TextUtil::generateRandomString(6);
                    $model->password = md5($password);
                    if ($model->save()) {
                        SendMailBussiness::send($email, Translate::get('Reset mật khẩu'), 'register_user', [
                            'username' => $username,
                            'password' => $password]);
                        $message = Translate::get('Reset mật khẩu thành công.  Bạn vui lòng truy cập hòm thư ' . $model->email . ' để nhận mật khẩu mới');
                    } else {
                        $message = Translate::get('Reset mật khẩu thất bại');
                    }
                } else {
                    $message = Translate::get('Người dùng không tồn tại');
                }
                $search = ['user/index'];
                if (Yii::$app->request->post("return_url")) {
                    $search = [Yii::$app->request->post("return_url")];
                }
                if (Yii::$app->request->get()) {
                    $search = $search + Yii::$app->request->get();
                }
                $url = Yii::$app->urlManager->createUrl($search);
                Weblib::showMessage($message, $url);
            }
        }
    }

    public function actionRoles()
    {
        $id = ObjInput::get("id", "int");
        $user_admin_account_id = ObjInput::get('admin_acc_id', 'int');
        $user = $this->_checkUser($id);
        if ($user == false) {
            $message = Translate::get('Người dùng không tồn tại');
            $url = Yii::$app->urlManager->createUrl(['user/index']);
            Weblib::showMessage($message, $url);
        } else {
            $right_id_user_list = [];
            $user_group_right_list = [];
            $user_admin_account = UserAdminAccountBusiness::getByUserId($id);
            foreach ($user_admin_account as $k => $v) {
                $user_group_id = $v['user_group_id'];
                $user_group_right = UserRoleBusiness::getUserGroupRightByGroup($user_group_id);
//                var_dump($user_group_id);
                foreach ($user_group_right as $keyG => $dataG) {
                    $right = Right::findOne(['id' => $dataG['right_id']]);
                    $user_group_right[$keyG]['parent_id'] = $right['parent_id'];
                    $user_group_right[$keyG]['right_name'] = $right['name'];
                    $user_group_right[$keyG]['level'] = $right['level'];
                }
                if (!isset($user_group_right_list[$v['id']])) {
                    $user_group_right_list[$v['id']] = self::_formatDataGroupRight($user_group_right);
                }
                // Các quyền đã cấp cho user
                $user_right = UserRight::find()
                    ->where(['user_id' => $id])
                    ->andWhere(['user_admin_account_id' => $v['id']])
                    ->all();

                $right_ids_user = array();
                foreach ($user_right as $key => $data) {
                    $right_ids_user[] = $data['right_id'];
                }
                if (!isset($right_id_user_list[$v['id']])) {
                    $right_id_user_list[$v['id']] = $right_ids_user;
                }
            }

            $model_user_admin_account = new UserAdminAccountForm();
            $list_user_group = Weblib::createComboTableArray('user_group', 'id', 'name', 'status = ' . UserGroup::STATUS_ACTIVE, '-- Chọn nhóm quyền --');
            $list_status = UserAdminAccount::getStatus();
            return $this->render('roles', [
                'user' => $user,
                'user_admin_account_id' => $user_admin_account_id,
                'user_admin_account' => $user_admin_account,
                'model_user_admin_account' => $model_user_admin_account,
                'list_status' => $list_status,
                'list_user_group' => $list_user_group,
                'right_id_user_list' => $right_id_user_list,
                'user_group_right_list' => $user_group_right_list
            ]);
        }
    }

    public function actionGetBranchByPartner()
    {
        $credit_partner_id = ObjInput::get('credit_partner_id', 'int');
        $branch_ids = ObjInput::get('branch_ids', 'str');
        $branchs = Weblib::createComboTableArray('credit_partner_branch', 'id', 'name', "`credit_partner_id` = '" . $credit_partner_id . "' AND `status` = 1 ", '', false, 'name ASC');

        $option = ' ';
        $list_branch_ids = explode(',', $branch_ids);
//        var_dump($branchs);die;
        if ($branchs) {
            foreach ($branchs as $c => $key) {
                $option .= '<tr>';
                $check = false;
                foreach ($list_branch_ids as $k => $v) {
                    if ($c == $v) {
                        $check = true;
                    }
                }

                if ($check) {
                    $option .= '<td><input type="checkbox" name="branch_ids[]" value="' . $c . '" checked class="noStyle"></td>';
                    $option .= '<td>' . $key . '</td>';
                } else {
                    $option .= '<td><input type="checkbox" name="branch_ids[]" value="' . $c . '" class="noStyle"></td>';
                    $option .= '<td>' . $key . '</td>';
                }
                $option .= '</tr>';
            }
        }

        echo $option;
    }



    protected function _formatDataGroupRight($user_group_right)
    {
        $lv1 = [];
        $lv2 = [];
        $lv3 = [];
//        $lv4 = [];
        foreach ($user_group_right as $k => $v) {
            if ($v['level'] == 1) {
                $lv1[] = $v;
            }
            if ($v['level'] == 2) {
                $lv2[] = $v;
            }
            if ($v['level'] == 3) {
                $lv3[] = $v;
            }
        }

        foreach ($lv2 as $k2 => $v2) {
            foreach ($lv3 as $k3 => $v3) {
//                var_dump($v3['parent_id']);
                if ($v3['parent_id'] == $v2['right_id']) {
                    $lv2[$k2]['lv3'][] = $v3;
                }
            }
        }
//        var_dump($lv2);die;
        foreach ($lv1 as $k1 => $v1) {
            foreach ($lv2 as $k2 => $v2) {
                if ($v2['parent_id'] == $v1['right_id']) {
                    $lv1[$k1]['lv2'][] = $v2;
                }
            }
        }
//        var_dump($lv1);die;
        return $lv1;
    }

    protected function _getRightIds($user_group_id)
    {
        $result = array();
        $right_ids = ObjInput::get('ids', 'def');
        if (!is_array($right_ids) || empty($right_ids)) {
            $id = intval(Yii::$app->request->get('id'));
            $right_ids = array($id);
        }
        if (is_array($right_ids) && !empty($right_ids)) {
            $group_right_ids = UserGroup::getRightIds($user_group_id);
            foreach ($right_ids as $right_id) {
                if (in_array($right_id, $group_right_ids)) {
                    $result[$right_id] = $right_id;
                }
            }
        }
        return $result;
    }

    public function actionSetRoles()
    {
        $user_id = ObjInput::get('user_id', 'int');
        $user_admin_account_id = ObjInput::get('user_admin_account_id', 'int');
        $user = $this->_checkUser($user_id);
        if ($user == false) {
            $message = Translate::get('Người dùng không tồn tại');
            $url = Yii::$app->urlManager->createUrl(['user/roles', 'id' => $user_id]);
            Weblib::showMessage($message, $url);
        } else {
            $user_admin_account = UserAdminAccountBusiness::getByIDToArray($user_admin_account_id);
            if ($user_admin_account == false) {
                $message = Translate::get('Tài khoản không tồn tại');
                $url = Yii::$app->urlManager->createUrl(['user/roles', 'id' => $user_id]);
                Weblib::showMessage($message, $url);
                die;
            }
            $right_ids = $this->_getRightIds($user_admin_account['user_group_id']);
            //-----------
            $transaction = UserRight::getDb()->beginTransaction();
            $user_right_list = UserRight::find()->where(['user_admin_account_id' => $user_admin_account_id]);
            if ($user_right_list != null) {
                $result = UserRight::deleteAll(['user_admin_account_id' => $user_admin_account_id]);
            }
            $all = true;
            if (!empty($right_ids)) {
                foreach ($right_ids as $right_id) {
                    $right = Right::findOne(['id' => intval($right_id)]);
                    if ($right != null) {
                        $user_right = new UserRight();
                        $user_right->user_id = $user_id;
                        $user_right->user_admin_account_id = $user_admin_account_id;
                        $user_right->right_id = $right_id;
                        $user_right->right_code = $right->code;
                        if (!$user_right->save()) {
                            //  $message = 'Không thêm được quyền ';
                            $all = false;
                            break;
                        }
                    }
                }
            }
            if ($all) {
                $message = Translate::get('Phân quyền thành công');
                $transaction->commit();
            } else {
                $message = Translate::get('Phân quyền thất bại');
                $transaction->rollBack();
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(['user/roles', 'id' => $user_id, 'admin_acc_id' => $user_admin_account_id]);
            Weblib::showMessage($message, $url);
        }
    }


    public function actionSearch()
    {
        $this->layout = false;
        $model = new UserSearch();
        $model->load(Yii::$app->request->get());
        $model->user_group_code = 'TSA_STAFF';
        $model->status = User::STATUS_ACTIVE;
        $dataPage = $model->search();
        $user_group = Weblib::createComboTableArray("user_group", "id", "name", "code LIKE 'TSA_STAFF%'", Translate::get('Chọn nhóm sales'));
        $data = $dataPage->data;

        return $this->render('search', [
            'model' => $model,
            'data' => $data,
            'user_group' => $user_group
        ]);
    }

    public function actionSearchAffiliate()
    {
        $this->layout = false;
        $model = new UserSearch();
        $model->load(Yii::$app->request->get());
        $model->user_group_code = 'SALES';
        $model->status = User::STATUS_ACTIVE;
        $dataPage = $model->search();
        $user_group = Weblib::createComboTableArray("user_group", "id", "name", "code LIKE 'SALES%'", Translate::get('Chọn nhóm kinh doanh'));
        $data = $dataPage->data;

        return $this->render('search_affiliate', [
            'model' => $model,
            'data' => $data,
            'user_group' => $user_group
        ]);
    }

    public function actionAddUserAdminAccount()
    {
        $model = new UserAdminAccountForm();
        $admin_acc_id = 0;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $params = array(
                    'name' => $model->name,
                    'user_group_id' => $model->user_group_id,
                    'user_id' => $model->user_id,
                    'status' => $model->status,
                    'user_create_id' => Yii::$app->user->getId()
                );
//            var_dump($params);
//            die;
                $result = UserAdminAccountBusiness::add($params);

                if ($result['error_message'] == '') {
                    $message = Translate::get('Thêm tài khoản thành công');
                    $admin_acc_id = $result['id'];
                } else {
                    $message = Translate::get($result['error_message']);
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl(["user/roles", 'id' => $model->user_id, 'admin_acc_id' => $admin_acc_id]);
                Weblib::showMessage($message, $url);
            }
        }
    }

    public function actionUpdateUserAdminAccount()
    {
        $model = new UserAdminAccountForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $params = array(
                    'id' => $model->id,
                    'name' => $model->name,
                    'status' => $model->status,
                    'user_create_id' => Yii::$app->user->getId()
                );
                $result = UserAdminAccountBusiness::update($params);

                if ($result['error_message'] == '') {
                    $message = Translate::get('Cập nhật tài khoản thành công');
                } else {
                    $message = Translate::get($result['error_message']);
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl(["user/roles", 'id' => $model->user_id, 'admin_acc_id' => $model->id]);
                Weblib::showMessage($message, $url);
            }
        }
    }
}
