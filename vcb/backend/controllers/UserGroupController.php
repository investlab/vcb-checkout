<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\Translate;
use common\models\business\UserBusiness;
use common\models\business\UserRoleBusiness;
use common\models\db\Right;
use common\models\db\UserGroup;
use common\models\db\UserGroupRight;
use common\models\input\UserGroupSearch;
use Yii;
use yii\db\Transaction;
use yii\web\Response;
use yii\widgets\ActiveForm;

class UserGroupController extends BackendController
{

    public function actionIndex()
    {
        $search = new UserGroupSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();
        $group_status = UserGroup::getStatus();
        $parent_id_array = UserGroup::getUserGroup(array(0 => '----'.Translate::get('Danh mục gốc').' ----'));

        $model = new UserGroup();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'group_status' => $group_status,
            'parent_id_array' => $parent_id_array,
            'model' => $model
        ]);
    }

    public function actionLock()
    {
        $message = null;
        $search = ['user-group/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = UserGroup::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = 2;
                if ($model->save()) {
                    $message = Translate::get('Khóa thành công');
                } else {
                    $message = Translate::get('Khóa thất bại');
                }
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
        $search = ['user-group/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = UserGroup::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = 1;
                if ($model->save()) {
                    $message = Translate::get('Mở khóa thành công');
                } else {
                    $message = Translate::get('Mở khóa thất bại');
                }
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
        $model = new UserGroup();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        $message = Translate::get('Thêm nhóm quyền thất bại');
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo '\n group error : \n';
                var_dump($model->errors);
                die;
            }
            $form = Yii::$app->request->post("UserGroup");

            $model->code = $form['code'];
            $model->name = $form['name'];
            $model->parent_id = $form['parent_id'];
            $model->status = 1;

            if ($form['position'] != null) {
                $model->position = $form['position'];
            } else {
                $model->position = 1;
            }

            $model->right = 1;
            $model->left = 1;
            $model->level = UserGroup::getLevel($form['parent_id']);

            if ($model->save()) {
                $result = UserBusiness::_updateIndexUserGroup('user_group');
                if ($result) {
                    $message = Translate::get('Thêm nhóm quyền thành công');
                }
            }

        }

        $url = Yii::$app->urlManager->createAbsoluteUrl('user-group/index');
        Weblib::showMessage($message, $url);
    }

    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $model = UserGroup::findOne(['id' => $id]);
            $data = $model->toArray();
            return json_encode($data);
        }
    }

    public function actionEdit()
    {
        $model = new UserGroup();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $message = Translate::get('Cập nhật nhóm quyền thất bại');
        $form = Yii::$app->request->post("UserGroup");
        if ($model->load(Yii::$app->request->post())) {

            if (!$model->validate()) {
                echo '\n group error : \n';
                var_dump($model->errors);
                die;
            }
            $id = $form['id'];
            $model_group = UserGroup::findOne(['id' => $id]);

            $model_group->code = $form['code'];
            $model_group->name = $form['name'];
            $model_group->parent_id = $form['parent_id'];
            $model_group->status = 1;

            if ($form['position'] != null) {
                $model_group->position = $form['position'];
            } else {
                $model_group->position = 1;
            }
            $model->right = 1;
            $model->left = 1;
            $model->level = UserGroup::getLevel($form['parent_id']);

            if ($model_group->save()) {
                //$result = UserBusiness::_updateIndexUserGroup('user_group');
                //if ($result) {
                $message = Translate::get('Cập nhật nhóm quyền thành công');
                //}
            }


            $url = Yii::$app->urlManager->createAbsoluteUrl(["user-group/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionRoles()
    {
        $group_id = Yii::$app->request->get('id');
        $user_group = UserGroup::findOne(['id' => $group_id]);
        $right = UserRoleBusiness::getListRole();
        $user_group_right = UserGroupRight::find()->where(['user_group_id' => $group_id])->all();
        $right_ids = array();
        foreach ($user_group_right as $key => $data) {
            $right_ids[] = $data['right_id'];
        }
        $post_ids = Yii::$app->request->post('ids');
        if (!empty($post_ids)) {
            $this->actionSetRoles();
        }

        return $this->render('roles', [
            'user_group' => $user_group,
            'right' => $right,
            'right_ids' => $right_ids
        ]);
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

    public function actionSetRoles()
    {
        $right_ids = $this->_getRightIds();
        $group_id = Yii::$app->request->post('group_id');
        $error = array();

        $transaction = UserGroupRight::getDb()->beginTransaction();
        $user_group_right_list = UserGroupRight::find()->where(['user_group_id' => $group_id]);
        if ($user_group_right_list != null) {
            $result = UserGroupRight::deleteAll(['user_group_id' => $group_id]);
        }

        foreach ($right_ids as $id) {
            if ($id != null) {
                $right = Right::findOne(['id' => $id]);

                $right_id = null;
                $right_code = null;

                if ($right != null) {
                    $right_id = $id;
                    $right_code = $right['code'];
                }

                $user_group_right = new UserGroupRight();
                $user_group_right->user_group_id = $group_id;
                $user_group_right->right_id = $right_id;
                $user_group_right->right_code = $right_code;

                if (!$user_group_right->save()) {
                    $error[] = Translate::get('Không thêm được quyền') . $right_code;
                }
            } else {
                continue;
            }
        }


        if ($error != null) {
            $message = Translate::get('Phân quyền thất bại');
            $transaction->rollBack();
        } else {
            $message = Translate::get('Phân quyền thành công');
            $transaction->commit();
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl(['user-group/roles', 'id' => $group_id]);
        Weblib::showMessage($message, $url);

    }

} 