<?php

namespace backend\controllers;

use backend\components\BackendController;
use backend\models\form\MenuListForm;
use backend\models\form\MenuUpdateForm;
use backend\models\form\MenuAddForm;
use backend\models\form\MenuDeleteForm;
use common\components\libs\Weblib;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\Menu;
use common\components\libs\Tables;

class MenuController extends BackendController
{

    public function actionMakeCache()
    {
        $message = 'Tạo cache thành công';
        Menu::updateMenuProductCategory();
        $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionActive()
    {
        $message = '';
        //---------
        $model = MenuUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Menu::STATUS_LOCK]);
        if ($model == null) {
            $message = 'Tham số đầu vào không hợp lệ, truy cập bị từ chối';
        } else {
            $model->status = Menu::STATUS_ACTIVE;
            if ($model->validate()) {
                if ($model->save()) {
                    $message = 'Cập nhật thành công';
                } else {
                    $message = 'Có lỗi khi cập nhật';
                }
            } else {
                $message = 'Tham số đầu vào không hợp lệ';
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionAdd()
    {
        $error_message = '';
        $model = new MenuAddForm();
        /*if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'MenuAddForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }*/
        if ($model->load(Yii::$app->request->post(), 'MenuAddForm')) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
                    Weblib::showMessage('Cập nhật thành công', $url);
                    return;
                } else {
                    $error_message = 'Có lỗi khi cập nhật thông tin';
                }
            }
        }
        return $this->render('add', [
            'model' => $model,
            'error_message' => $error_message,
            'status_array' => $model->getStatus(),
            'parent_id_array' => Menu::getCategories(array(0 => '---- Danh mục gốc ----')),
        ]);
    }

    public function actionDelete()
    {
        $message = '';
        //---------
        $model = MenuDeleteForm::findOne(['id' => Yii::$app->request->get('id')]);
        if ($model == null) {
            $message = 'Tham số đầu vào không hợp lệ, truy cập bị từ chối';
        } else {
            if ($model->delete()) {
                $message = 'Xóa thành công';
            } else {
                if (isset($model->errors['delete']) && !empty($model->errors['delete'])) {
                    $message = $model->errors['delete'][0];
                } else {
                    $message = 'Có lỗi trong quá trình xử lý';
                }
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionIndex()
    {
        $model = new MenuListForm();
        $model->load(Yii::$app->request->get(), 'MenuListForm');
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        $data = array();
        $data = $model->search();
        return $this->render('index', [
            'model' => $model,
            'data' => $data,
        ]);
    }

    public function actionLock()
    {
        $message = '';
        //---------
        $model = MenuUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Menu::STATUS_ACTIVE]);
        if ($model == null) {
            $message = 'Tham số đầu vào không hợp lệ, truy cập bị từ chối';
        } else {
            $model->status = Menu::STATUS_LOCK;
            if ($model->validate()) {
                if ($model->save()) {
                    $message = 'Cập nhật thành công';
                } else {
                    $message = 'Có lỗi khi cập nhật';
                }
            } else {
                $message = 'Tham số đầu vào không hợp lệ';
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionUpdate()
    {
        $error_message = '';
        $model = MenuUpdateForm::findOne(['id' => Yii::$app->request->get('id')]);
        if ($model == null) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
            Weblib::showMessage('Tham số đầu vào không hợp lệ, truy cập bị từ chối', $url);
            die();
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'MenuUpdateForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
                    Weblib::showMessage('Cập nhật thành công', $url);
                    return;
                } else {
                    $error_message = 'Có lỗi khi cập nhật thông tin';
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
            'error_message' => $error_message,
            'status_array' => $model->getStatus(),
        ]);
    }

    public function actionUpdatePosition()
    {
        $model = new \backend\models\form\MenuUpdatePositionForm();
        $result = $model->updatePosition(Yii::$app->request->post('ids'), Yii::$app->request->post('positions'));
        $url = Yii::$app->urlManager->createAbsoluteUrl('menu/index');
        Weblib::showMessage('Cập nhật thành công', $url);
        return;
    }
}
