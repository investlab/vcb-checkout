<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CardTypeBusiness;
use common\models\db\CardType;
use common\models\form\CardTypeForm;
use common\models\input\CardTypeSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class CardTypeController extends BackendController
{
    public function actionIndex()
    {
        $search = new CardTypeSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $model = new CardTypeForm();
        $status_arr = CardType::getStatus();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model' => $model,
            'status_arr' => $status_arr
        ]);
    }

    public function actionAdd()
    {
        $model = new CardTypeForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {

            $form = Yii::$app->request->post("CardTypeForm");
            $params = array(
                'name' => $form['name'],
                'code' => strtoupper($form['code']),
                'user_id' => Yii::$app->user->getId()
            );

            $result = CardTypeBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = 'Thêm loại thẻ thành công';
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["card-type/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = ObjInput::get('id', 'int');
            $data = Tables::selectOneDataTable('card_type', ['id = :id', 'id' => $id]);
            return json_encode($data);
        }
    }

    public function actionUpdate()
    {
        $model = new CardTypeForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $form = Yii::$app->request->post("CardTypeForm");
            $params = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'code' => strtoupper($form['code']),
                'user_id' => Yii::$app->user->getId()
            );

            $result = CardTypeBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật loại thẻ thành công';
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["card-type/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionLock()
    {
        $message = null;
        $search = ['card-type/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $params = array(
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = CardTypeBusiness::lock($params);
            if ($result['error_message'] == '') {
                $message = 'Khóa loại thẻ thành công';
            } else {
                $message = Translate::get($result['error_message']);
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }


    }

    public function actionActive()
    {
        $message = null;
        $search = ['card-type/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $params = array(
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = CardTypeBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = 'Mở khóa loại thẻ thành công';
            } else {
                $message = $result['error_message'];
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }

} 