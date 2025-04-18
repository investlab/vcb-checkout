<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\Translate;
use common\models\input\ReasonSearch;
use common\models\db\Reason;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ReasonController extends BackendController
{

    public function actionIndex()
    {
        $search = new ReasonSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $reason_status = Reason::getStatus();
        $reason_type = Reason::getType();

        $model = new Reason();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'reason_status' => $reason_status,
            'reason_type' => $reason_type,
            'model' => $model
        ]);

    }

    public function actionCreate()
    {
        $model = new Reason();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo '\n reason error : \n';
                var_dump($model->errors);
                die;
            }
            $form = Yii::$app->request->post("Reason");

            $model->type = $form['type'];
            $model->name = $form['name'];
            $model->description = $form['description'];
            $model->status = 1;
            $model->time_created = time();
            $model->user_created = Yii::$app->user->getId();

            if ($model->save()) {
                $message = Translate::get('Thêm lý do thành công');
            } else {
                $message = Translate::get('Thêm lý do thất bại');
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["reason/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $model = Reason::findOne(['id' => $id]);
            $data = $model->toArray();
            return json_encode($data);
        }
    }

    public function actionEdit()
    {
        $model = new Reason();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $form = Yii::$app->request->post("Reason");
        if ($model->load(Yii::$app->request->post())) {

            if (!$model->validate()) {
                echo '\n Reason error : \n';
                var_dump($model->errors);
                die;
            }
            $id = $form['id'];
            $model_reason = Reason::findOne(['id' => $id]);

            $model_reason->type = $form['type'];
            $model_reason->name = $form['name'];
            $model_reason->description = $form['description'];
            $model_reason->time_updated = time();
            $model_reason->user_updated = Yii::$app->user->getId();

            if ($model_reason->save()) {
                $message = Translate::get('Cập nhật lý do hủy thành công');
            } else {
                $message = Translate::get('Cập nhật lý do hủy thất bại');
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["reason/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionLock()
    {
        $message = null;
        $search = ['reason/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = Reason::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = 2;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
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

    // Mở khóa
    public function actionUnlock()
    {
        $message = null;
        $search = ['reason/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = Reason::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = 1;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
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


} 