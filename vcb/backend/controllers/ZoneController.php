<?php

namespace backend\controllers;

use backend\components\BackendController;
use backend\models\form\ZoneListForm;
use backend\models\form\ZoneUpdateForm;
use backend\models\form\ZoneAddForm;
use backend\models\form\ZoneDeleteForm;
use common\components\libs\Weblib;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\Zone;
use common\components\libs\Tables;

class ZoneController extends BackendController
{

    public function actionActive()
    {
        $message = '';
        //---------
        $model = ZoneUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Zone::STATUS_LOCK]);
        if ($model == null) {
            $message = 'Tham số đầu vào không hợp lệ, truy cập bị từ chối';
        } else {
            $model->status = Zone::STATUS_ACTIVE;
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
        $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionAdd()
    {
        $error_message = '';
        $model = new ZoneAddForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'ZoneAddForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post(), 'ZoneAddForm')) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
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
//            'home_array' => $model->getHomeName(),
            'parent_id_array' => $this->_getCategories(),
        ]);
    }

    protected function _getCategories()
    {
        $categories = array(0 => '---- Danh mục gốc ----');
        $result = Tables::selectAllDataTable("zone", "1 ", "`left` ASC ");
        if ($result != false) {
            foreach ($result as $row) {
                $categories[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
        }
        return $categories;
    }

    public function actionDelete()
    {
        $message = '';
        //---------
        $model = ZoneDeleteForm::findOne(['id' => Yii::$app->request->get('id')]);
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
        $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionIndex()
    {
        $model = new ZoneListForm();
        $model->load(Yii::$app->request->get(), 'ZoneListForm');
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
        $model = ZoneUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Zone::STATUS_ACTIVE]);
        if ($model == null) {
            $message = 'Tham số đầu vào không hợp lệ, truy cập bị từ chối';
        } else {
            $model->status = Zone::STATUS_LOCK;
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
        $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionUpdate()
    {
        $error_message = '';
        $model = ZoneUpdateForm::findOne(['id' => Yii::$app->request->get('id')]);
        if ($model == null) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
            Weblib::showMessage('Tham số đầu vào không hợp lệ, truy cập bị từ chối', $url);
            die();
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'ZoneUpdateForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
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
        $model = new \backend\models\form\ZoneUpdatePositionForm();
        $result = $model->updatePosition(Yii::$app->request->post('ids'), Yii::$app->request->post('positions'));
        if ($result) {
            $message = 'Cập nhật thành công';
        } else {
            $message = 'Vị trí không hợp lệ';
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('zone/index');
        Weblib::showMessage($message, $url);
        return;
    }
}
