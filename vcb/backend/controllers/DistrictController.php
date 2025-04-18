<?php

namespace backend\controllers;


use backend\components\BackendController;
use backend\models\form\ZoneAddForm;
use backend\models\form\ZoneUpdateForm;
use backend\models\form\ZoneUpdatePositionForm;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\db\Zone;
use common\models\input\DistrictSearch;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\web\Response;

class DistrictController extends BackendController
{

    public function actionIndex()
    {
        $search = new DistrictSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = 40;
        $page = $search->search();

        $status_arr = Zone::getStatus();
        $remote_arr = Zone::getRemote();
        $city_arr = Weblib::createComboTableArray('zone', 'id', 'name', '`status` = 1 && `level` = 2', 'Tỉnh/Thành phố', true);

        $model = new ZoneAddForm();
        $model_update = new ZoneUpdateForm();


        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'remote_arr' => $remote_arr,
            'city_arr' => $city_arr,
            'model' => $model,
            'model_update' => $model_update
        ]);
    }

    public function actionAdd()
    {
        $message = '';
        $model = new ZoneAddForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'ZoneAddForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            if ($model->load(Yii::$app->request->post(), 'ZoneAddForm')) {
                $model->zone_id = 0;
                $model->code = Zone::getCodeByName($model->name);
                if ($model->validate()) {
                    if ($model->save()) {
                        $message = "Thêm mới thành công.";
                    } else {
                        $message = 'Có lỗi khi cập nhật thông tin';
                    }
                }
            }
        }

        $url = Yii::$app->urlManager->createAbsoluteUrl('district/index');
        Weblib::showMessage($message, $url);
    }

    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = ObjInput::get('id', 'int');
            $data = Tables::selectOneDataTable("zone", ["id = :id", "id" => $id]);
            return json_encode($data);
        }
    }

    public function actionUpdate()
    {
        $message = '';

        $form = Yii::$app->request->post('ZoneUpdateForm');
        $id = $form['id'];
        $model = ZoneUpdateForm::findOne(['id' => $id]);
        if ($model == null) {
            $url = Yii::$app->urlManager->createAbsoluteUrl('district/index');
            Weblib::showMessage('Tham số đầu vào không hợp lệ, truy cập bị từ chối', $url);
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post(), 'ZoneUpdateForm')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }


        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    $message = "Cập nhật thành công.";
                } else {
                    $message = 'Có lỗi khi cập nhật thông tin';
                }
            }

        }

        $url = Yii::$app->urlManager->createAbsoluteUrl('district/index');
        Weblib::showMessage($message, $url);
    }


    public function actionUpdatePosition()
    {
        $model = new ZoneUpdatePositionForm();
        $result = $model->updatePosition(Yii::$app->request->post('ids'), Yii::$app->request->post('positions'));
        if ($result) {
            $message = 'Cập nhật thành công';
        } else {
            $message = 'Vị trí không hợp lệ';
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('district/index');
        Weblib::showMessage($message, $url);
        return;
    }

    public function actionActive()
    {
        $message = null;
        $search = ['district/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = Zone::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = Zone::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save()) {
                    $message = 'Kích hoạt thành công.';
                } else {
                    $message = 'Kích hoạt thất bại.';
                }
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }

    }

    public function actionLock()
    {
        $message = null;
        $search = ['district/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = Zone::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = Zone::STATUS_LOCK;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save()) {
                    $message = 'Khóa thành công.';
                } else {
                    $message = 'Khóa thất bại.';
                }
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }
} 