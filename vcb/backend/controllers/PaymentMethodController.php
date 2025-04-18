<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerPaymentBusiness;
use common\models\business\PaymentMethodBusiness;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\models\form\PaymentMethodForm;
use common\models\input\PaymentMethodSearch;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class PaymentMethodController extends BackendController
{

    public function actionIndex()
    {
        $search = new PaymentMethodSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = 30;
        $page = $search->search();

        $status_arr = PaymentMethod::getStatus();
        $transaction_type_arr = Weblib::createComboTableArray('transaction_type', 'id', 'name', 1, Translate::get('Chọn loại giao dịch'), true);
        $image_url = IMAGES_PAYMENT_METHOD_URL;

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'transaction_type_arr' => $transaction_type_arr,
            'image_url' => $image_url
        ]);
    }

    public function actionLock()
    {
        $message = null;
        $search = ['payment-method/index'];

        if (Yii::$app->request->post()) {

            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = PaymentMethod::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = PaymentMethod::STATUS_LOCK;
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

    public function actionUnlock()
    {
        $message = null;
        $search = ['payment-method/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $model = PaymentMethod::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = PaymentMethod::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();


                if ($model->save()) {
                    $message = Translate::get('Mở khóa thành công');
                } else {
                    $message = Translate::get('Mở khóa thất bại');
                }
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }

    }

    public function actionAdd()
    {
        $model = new PaymentMethodForm();
        $transaction_type_arr = Weblib::createComboTableArray('transaction_type', 'id', 'name', 1, Translate::get('Chọn loại giao dịch'), true);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('PaymentMethodForm');

            $params = array(
                'name' => $form['name'],
                'method_id' => $form['method_id'],
                'bank_id' => $form['bank_id'],
                'image' => '',
                'description' => $form['description'],
                'min_amount' => $form['min_amount'],
                'config' => $form['config'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = PaymentMethodBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm phương thức thành công thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('payment-method/index');
            Weblib::showMessage($message, $url);
        }

        return $this->render('add', [
            'model' => $model,
            'transaction_type_arr' => $transaction_type_arr
        ]);
    }

    public function actionViewEdit()
    {
        $id = Yii::$app->request->get('id');
        $model = new PaymentMethodForm();
        if (intval($id) > 0) {
            $payment_method = Tables::selectOneDataTable("payment_method", "id = " . $id);
            if ($payment_method) {
                $model->id = $payment_method['id'];
                $model->method_id = PaymentMethod::getMethodIdByPaymentMethodId($payment_method['id']);
                $model->bank_id = $payment_method['bank_id'];
                $model->name = $payment_method['name'];
                $model->description = $payment_method['description'];
                $model->config = $payment_method['config'];
                $model->min_amount = $payment_method['min_amount'];

            }
        }

        return $this->render('update', [
            'model' => $model,
            'payment_method' => $payment_method
        ]);
    }

    public function actionEdit()
    {
        $model = new PaymentMethodForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('PaymentMethodForm');
            $params = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'description' => $form['description'],
                'config' => $form['config'],
                'min_amount' => $form['min_amount'],
                'user_id' => Yii::$app->user->getId()
            );
            $result = PaymentMethodBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('payment-method/index');
            Weblib::showMessage($message, $url);
        }
    }

    public function actionGetPaymentMethodByMethodId()
    {
        $option = '<option selected="selected" value="0">'.Translate::get('Chọn tất cả phương thức').'</option>';

        $payment_method_ids = array();
        $method_id = ObjInput::get('method_id', 'int', '');
        $transaction_type_id = 0;

        if ($method_id > 0) {
            $method_payment_method = Tables::selectAllDataTable("method_payment_method", ['method_id = :method_id', 'method_id' => $method_id], "id ASC", "id");
            if ($method_payment_method != false) {
                foreach ($method_payment_method as $key => $data) {
                    $payment_method_ids[] = $data['payment_method_id'];
                }
                if (!empty($payment_method_ids)) {
                    $payment_method = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") AND status = " . PaymentMethod::STATUS_ACTIVE, "", "id");
                    if ($payment_method != false) {
                        foreach ($payment_method as $key => $data) {
                            $option .= '<option value="' . $data['id'] . '">' . $data['name'] . '</option>';
                        }
                    } else {
                        $option = '<option selected="selected" value="0">'.Translate::get('Không có phương thức thanh toán').'</option>';
                    }
                }
            }else{
                $option = '<option selected="selected" value="0">'.Translate::get('Không có phương thức thanh toán').'</option>';
            }
            $method = Tables::selectOneDataTable("method", ["id = :id", "id" => $method_id]);
            if ($method) {
                $transaction_type_id = $method['transaction_type_id'];
            }
        } else {
            $option = '<option selected="selected" value="0">'.Translate::get('Không có phương thức thanh toán').'</option>';
        }
        $data = array(
            'transaction_type_id' => $transaction_type_id,
            'option' => $option
        );

        return json_encode($data);
//        echo $option;
    }

    public function actionLockAll()
    {
        $arr_method_id = explode(',', Yii::$app->request->get('arr_method_id'));
        $message = null;
        $search = ['payment-method/index'];
        $flag = false;
        if (!empty($arr_method_id)) {
            if ($arr_method_id[0] == 0) {
                $condition = ['status' => PaymentMethod::STATUS_ACTIVE];
            } else {
                $condition = ['id' => $arr_method_id, 'status' => PaymentMethod::STATUS_ACTIVE];
            }
            $payment_methods = PaymentMethod::findAll($condition);
            if ($payment_methods != null) {
                foreach ($payment_methods as $key => $payment_method) {
                    $payment_method->status = PaymentMethod::STATUS_LOCK;
                    $payment_method->time_updated = time();
                    $payment_method->user_updated = Yii::$app->user->getId();

                    if ($payment_method->save()) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                }
            }

            if ($flag) {
                $message = Translate::get('Khóa thành công');
            } else {
                $message = Translate::get('Khóa thất bại');
            }

            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }

    }

} 