<?php


namespace backend\controllers;


use common\components\utils\ObjInput;
use common\models\business\BinAcceptBusiness;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use backend\components\BackendController;
use common\components\libs\Weblib;
use common\models\db\BinAccept;
use common\models\form\BinAcceptForm;
use common\models\input\BinAcceptSearch;
use common\components\utils\Translate;

class BinAcceptController extends BackendController
{
    public function actionIndex()
    {
        $search = new BinAcceptSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $model_bin = new BinAcceptForm();
        $card_type = BinAccept::getCardType();
        $status = BinAccept::getStatus();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model' => $model_bin,
            'card_type' => $card_type,
            'status' => $status,
        ]);
    }

    public function actionCreateBin() {
        $model = new BinAcceptForm();
        $model->scenario = BinAcceptForm::SCENARIO_ADD;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('BinAcceptForm');

            $params = array(
                'bin_code' => $form['bin_code'],
                'card_type' => $form['card_type'],
                'status' => $form['status'],
                'user_id' => Yii::$app->user->getId(),
            );

            $result = BinAcceptBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm đầu bin thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('bin-accept/index');
            Weblib::showMessage($message, $url);
        }
    }

    public function actionViewUpdate() {
        $id = ObjInput::get('id', 'int');
        $error = true;
        $message = '';
        $data = [];

        if (!empty($id)) {
            $bin_accept = BinAcceptBusiness::getById($id);

            if (!empty($bin_accept)) {
                $error = false;
                $message = 'Thành công';
                $data = [
                    'id' => $bin_accept->id,
                    'bin_code' => $bin_accept->bin_code,
                    'card_type' => $bin_accept->card_type,
                    'status' => $bin_accept->status,
                ];
            } else {
                $message = 'Không tìm thấy dữ liệu';
            }
        } else {
            $message = 'Không tìm thấy dữ liệu';
        }

        $data_return = [
            'error' => $error,
            'message' => $message,
            'data' => $data
        ];

        return json_encode($data_return);
    }

    public function actionUpdateBin() {
        $model = new BinAcceptForm();
        $model->scenario = BinAcceptForm::SCENARIO_UPDATE;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('BinAcceptForm');

            $params = array(
                'id' => $form['id'],
                'bin_code' => $form['bin_code'],
                'card_type' => $form['card_type'],
                'status' => $form['status'],
                'user_id' => Yii::$app->user->getId(),
            );

            $result = BinAcceptBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật đầu bin thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('bin-accept/index');
            Weblib::showMessage($message, $url);
        }
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['bin-accept/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = BinAcceptBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa đầu bin thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tìm thấy dữ liệu');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    //  Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['bin-accept/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = BinAcceptBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa đầu bin thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tìm thấy dữ liệu');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }
}