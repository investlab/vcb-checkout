<?php


namespace backend\controllers;

use backend\components\BackendController;
use backend\models\form\RightMerchantListForm;
use backend\models\form\RightUpdateForm;
use backend\models\form\RightAddForm;
use backend\models\form\RightDeleteForm;
use common\components\libs\Weblib;
use common\components\utils\Translate;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\db\Right;

class RightMerchantController extends BackendController
{
    public function actionIndex()
    {
        $model = new RightMerchantListForm();
        $model->load(Yii::$app->request->get(), 'RightMerchantListForm');
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

    public function actionAdd()
    {
        $error_message = '';
        $model = new RightAddForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'RightAddForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post(), 'RightAddForm')) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
                    Weblib::showMessage(Translate::get('Cập nhật thành công'), $url);
                    return;
                } else {
                    $error_message = Translate::get('Có lỗi khi cập nhật thông tin');
                }
            }
        }
        return $this->render('add', [
            'model' => $model,
            'error_message' => $error_message,
            'status_array' => $model->getStatus(),
            'parent_id_array' => Right::getCategoryMerchants(array(0 => '----'. Translate::get('Danh mục gốc').' ----')),
        ]);
    }

    public function actionUpdate()
    {
        $error_message = '';
        $model = RightUpdateForm::findOne(['id' => Yii::$app->request->get('id')]);
        if ($model == null) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
            Weblib::showMessage(Translate::get('Tham số đầu vào không hợp lệ, truy cập bị từ chối'), $url);
            die();
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'RightUpdateForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
                    Weblib::showMessage(Translate::get('Cập nhật thành công'), $url);
                    return;
                } else {
                    $error_message = Translate::get('Có lỗi khi cập nhật thông tin');
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
        $model = new \backend\models\form\RightUpdatePositionForm();
        $result = $model->updatePosition(Yii::$app->request->post('ids'), Yii::$app->request->post('positions'));
        if ($result) {
            $message = Translate::get('Cập nhật thành công');
        } else {
            $message = Translate::get('Vị trí không hợp lệ');
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
        Weblib::showMessage($message, $url);
        return;
    }

    public function actionActive()
    {
        $message = '';
        //---------
        $model = RightUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Right::STATUS_LOCK, 'type'=> Right::TYPE_MERCHANT]);
        if ($model == null) {
            $message = Translate::get('Tham số đầu vào không hợp lệ, truy cập bị từ chối');
        } else {
            $model->status = Right::STATUS_ACTIVE;
            if ($model->validate()) {
                if ($model->save()) {
                    $message = Translate::get('Cập nhật thành công');
                } else {
                    $message = Translate::get('Có lỗi khi cập nhật');
                }
            } else {
                $message = Translate::get('Tham số đầu vào không hợp lệ');
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionLock()
    {
        $message = '';
        //---------
        $model = RightUpdateForm::findOne(['id' => Yii::$app->request->get('id'), 'status' => Right::STATUS_ACTIVE, 'type' => Right::TYPE_MERCHANT]);
        if ($model == null) {
            $message = Translate::get('Tham số đầu vào không hợp lệ, truy cập bị từ chối');
        } else {
            $model->status = Right::STATUS_LOCK;
            if ($model->validate()) {
                if ($model->save()) {
                    $message = Translate::get('Cập nhật thành công');
                } else {
                    $message = Translate::get('Có lỗi khi cập nhật');
                }
            } else {
                $message = Translate::get('Tham số đầu vào không hợp lệ');
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }

    public function actionDelete()
    {
        $message = '';
        //---------
        $model = RightDeleteForm::findOne(['id' => Yii::$app->request->get('id'), 'type' => Right::TYPE_MERCHANT]);
        if ($model == null) {
            $message = Translate::get('Tham số đầu vào không hợp lệ, truy cập bị từ chối');
        } else {
            if ($model->delete()) {
                $message = Translate::get('Xóa thành công');
            } else {
                if (isset($model->errors['delete']) && !empty($model->errors['delete'])) {
                    $message = $model->errors['delete'][0];
                } else {
                    $message = Translate::get('Có lỗi trong quá trình xử lý');
                }
            }
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('right-merchant/index', HTTP_CODE);
        echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
        Weblib::showMessage($message, $url);
        die();
    }
}