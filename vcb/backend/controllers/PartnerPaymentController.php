<?php
namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\Translate;
use common\models\business\PartnerPaymentBusiness;
use common\models\form\AddPartnerPaymentForm;
use common\models\input\PartnerPaymentSearch;
use common\util\TextUtil;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerPaymentController extends BackendController
{
    public function actionIndex()
    {
        $search = new PartnerPaymentSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status = $page->status;
        $model = new AddPartnerPaymentForm();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model' => $model,
            'status' => $status,
        ]);
    }

    // Thêm kênh thanh toán
    public function actionAdd()
    {
        $model = new AddPartnerPaymentForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo '\n Partner Payment error : \n';
                var_dump($model->errors);
                die;
            }
            $params = [
                'name' => $model->name,
                'code' => $model->code,
                'description' => $model->description,
                'user_id' => Yii::$app->user->getId()
            ];

            $modelMethod = PartnerPaymentBusiness::add($params);

            if ($modelMethod['error_message'] == '') {
                $message = Translate::get('Thêm kênh thanh toán thành công');
            } else {
                $message = Translate::get($modelMethod['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-payment/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    // Lấy thông tin phương thức khi sửa
    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $data = PartnerPaymentBusiness::getByIdToArray($id);
            return json_encode($data);
        }
    }

    // Sửa phương thức
    public function actionEdit()
    {
        $model = new AddPartnerPaymentForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                echo '\n Method error : \n';
                var_dump($model->errors);
                die;
            }
            $data = Yii::$app->request->post()['AddPartnerPaymentForm'];
            $params = [
                'id' => $model->id,
                'name' => $model->name,
                'code' => $model->code,
                'description' => $model->description,
                'token_key' => $data['token_key'],
                'checksum_key' => $data['checksum_key'],
                'user_id' => Yii::$app->user->getId()
            ];

            $modelMethod = PartnerPaymentBusiness::edit($params);

            if ($modelMethod['error_message'] == '') {
                $message = Translate::get('Sửa kênh thanh toán thành công');
            } else {
                $message = Translate::get($modelMethod['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-payment/index"]);
            Weblib::showMessage($message, $url);
        }
    }
    //-------------------------------------------
    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['partner-payment/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            $method = PartnerPaymentBusiness::getByID($id);
            if ($method != null) {
                $method->status = 2;
                $method->time_updated = time();
                $method->user_updated = Yii::$app->user->getId();
                if ($method->save()) {
                    $message = Translate::get('Khóa kênh thanh toán thành công');
                } else {
                    $message = Translate::get('Khóa kênh thanh toán thất bại');
                }
            } else {
                $message = Translate::get('Không tìm thấy kênh thanh toán này');
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
        $search = ['partner-payment/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            $method = PartnerPaymentBusiness::getByID($id);
            if ($method != null) {
                $method->status = 1;
                $method->time_updated = time();
                $method->user_updated = Yii::$app->user->getId();
                if ($method->save()) {
                    $message = Translate::get('Mở khóa kênh thanh toán thành công');
                } else {
                    $message = Translate::get('Mở khóa kênh thanh toán thất bại');
                }
            } else {
                $message = Translate::get('Không tìm thấy kênh thanh toán này');
            }
        }
        if (Yii::$app->request->get()) {
            $search = $search + Yii::$app->request->get();
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }
}