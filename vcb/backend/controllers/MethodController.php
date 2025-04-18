<?php
namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\MethodBusiness;
use common\models\business\MethodPaymentMethodBusiness;
use common\models\db\Method;
use common\models\db\TransactionType;
use common\models\form\AddMethodForm;
use common\models\form\EditMethodPaymentMethodForm;
use common\models\input\MethodSearch;
use common\util\TextUtil;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class MethodController extends BackendController
{
    public function actionIndex()
    {
        $search = new MethodSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status = $page->status;
        $model_method = new AddMethodForm();
        $transaction_type_arr = Weblib::createComboTableArray('transaction_type', 'id', 'name', 1, Translate::get('Chọn loại giao dịch'), true);

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model_method' => $model_method,
            'status' => $status,
            'transaction_type_arr' => $transaction_type_arr,
        ]);
    }

    // danh sách phương thức thanh toán
    public function actionPaymentMethod()
    {
        $method_id = ObjInput::get('id', 'int');
        $method_name = MethodBusiness::getNameById($method_id);
        $method_pMethod = MethodPaymentMethodBusiness::getByMethodId($method_id);
        $method = Tables::selectOneDataTable("method", ["id = :id", "id" => $method_id]);
        $payment_method = Weblib::createComboTableArray('payment_method', 'id', 'name',
            'id NOT IN (SELECT payment_method_id FROM method_payment_method WHERE method_id !=' . $method_id .
            ') AND transaction_type_id = ' . $method['transaction_type_id'], '', false);

        $url = Yii::$app->urlManager->createAbsoluteUrl(["method/index"]);
        $model = new EditMethodPaymentMethodForm();

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

            $params = [
                'method_id' => $method_id,
                'payment_method_id' => $model->payment_id,
                'user_id' => Yii::$app->user->getId()
            ];

            $modelMethod = MethodPaymentMethodBusiness::update($params);
            if ($modelMethod['error_message'] == '') {
                $message = Translate::get('Cập nhật phương thức thanh toán thành công');
                Weblib::showMessage($message, $url);
            } else {
                $message = $modelMethod['error_message'];
                Weblib::showMessage($message, $url);
            }


        }
        if ($method_pMethod != null) {
            foreach ($method_pMethod as $key => $data) {
                $model->payment_id[] = $data['payment_method_id'];
            }
        }

        return $this->render('payment-method', [
            'model' => $model,
            'payment_method' => $payment_method,
            'method_name' => $method_name,
            'index_url' => Yii::$app->urlManager->createAbsoluteUrl('method/index'),
        ]);
    }

    // Thêm phương thức
    public function actionAdd()
    {
        $model = new AddMethodForm();
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
            $params = [
                'transaction_type_id' => $model->transaction_type_id,
                'name' => $model->name,
                'code' => strtoupper($model->code),
                'description' => $model->description,
                'position' => $model->position,
                'user_id' => Yii::$app->user->getId()
            ];

            $modelMethod = MethodBusiness::add($params);

            if ($modelMethod['error_message'] == '') {
                $message = Translate::get('Thêm nhóm phương thức thanh toán thành công');
            } else {
                $message = Translate::get($modelMethod['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["method/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    // Lấy thông tin phương thức khi sửa
    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $data = MethodBusiness::getByIdToArray($id);
            return json_encode($data);
        }
    }

    // Sửa phương thức
    public function actionEdit()
    {
        $model = new AddMethodForm();
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
            $params = [
                'id' => $model->id,
                'name' => $model->name,
                'code' => strtoupper($model->code),
                'description' => $model->description,
                'position' => $model->position,
                'user_id' => Yii::$app->user->getId()
            ];

            $modelMethod = MethodBusiness::edit($params);

            if ($modelMethod['error_message'] == '') {
                $message = Translate::get('Sửa nhóm phương thức thanh toán thành công');
            } else {
                $message = Translate::get($modelMethod['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["method/index"]);
            Weblib::showMessage($message, $url);
        }
    }
    //-------------------------------------------
    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['method/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            $method = MethodBusiness::getByID($id);
            if ($method != null) {
                $method->status = 2;
                $method->time_updated = time();
                $method->user_updated = Yii::$app->user->getId();
                if ($method->save()) {
                    $message = Translate::get('Khóa nhóm phương thức thanh toán thành công');
                } else {
                    $message = Translate::get('Khóa nhóm phương thức thanh toán thất bại');
                }
            } else {
                $message = Translate::get('Không tìm thấy nhóm phương thức thanh toán này');
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
        $search = ['method/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            $method = MethodBusiness::getByID($id);
            if ($method != null) {
                $method->status = 1;
                $method->time_updated = time();
                $method->user_updated = Yii::$app->user->getId();
                if ($method->save()) {
                    $message = Translate::get('Mở khóa nhóm phương thức thanh toán thành công');
                } else {
                    $message = Translate::get('Mở khóa nhóm phương thức thanh toán thất bại');
                }
            } else {
                $message = Translate::get('Không tìm thấy nhóm phương thức thanh toán này');
            }
        }
        if (Yii::$app->request->get()) {
            $search = $search + Yii::$app->request->get();
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }
} 