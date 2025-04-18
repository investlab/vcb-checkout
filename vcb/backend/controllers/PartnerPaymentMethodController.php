<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerPaymentMethodBusiness;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\util\TextUtil;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerPaymentMethodController extends BackendController
{
    // Điều kênh thanh toán
    public function actionIndex()
    {
        $code = ObjInput::get('code', 'str', '');
        $payment_method_arr = Tables::selectAllDataTable('payment_method',
            "status = " . PaymentMethod::STATUS_ACTIVE . " AND transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() .
            " AND code LIKE '%" . $code . "%'"
        );

        $partner_payment_method_arr = Tables::selectAllDataTable("partner_payment_method",
            'payment_method_id IN (SELECT id FROM payment_method WHERE transaction_type_id = ' . TransactionType::getPaymentTransactionTypeId() . ")");
        $enviroment_arr = array();
        foreach ($partner_payment_method_arr as $key => $data) {
            if (!in_array($data['enviroment'], $enviroment_arr)) {
                $enviroment_arr[] = $data['enviroment'];
            }

            $partner_payment_id = $data['partner_payment_id'];
            if (intval($partner_payment_id) > 0) {
                $partner_payment = Tables::selectOneDataTable('partner_payment', ['id = :id', "id" => $partner_payment_id]);
                $partner_payment_method_arr[$key]['partner_payment_name'] = !empty($partner_payment['name']) ? $partner_payment['name'] : '';
            }
        }

        return $this->render('index', [
            'code' => $code,
            'partner_payment_method_arr' => $partner_payment_method_arr,
            'enviroment_arr' => $enviroment_arr,
            'payment_method_arr' => $payment_method_arr,
        ]);


    }


    // Khóa
    public function actionLock()
    {
        $message = null;
        $id = ObjInput::get('id', 'int', 0);
        if (isset($id) && intval($id) > 0) {
            $params = array(
                'partner_payment_method_id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerPaymentMethodBusiness::lock($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa kênh thanh toán thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl('partner-payment-method/index');
            Weblib::showMessage($message, $url);
        }
    }

    // Mở khóa
    public function actionActive()
    {
        $message = null;

        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = array(
                'partner_payment_method_id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerPaymentMethodBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa kênh thanh toán thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl('partner-payment-method/index');
            Weblib::showMessage($message, $url);
        }
    }


    // DANH SÁCH KÊNH THEO PHƯƠNG THỨC THANH TOÁN
    public function actionListByPaymentMethod()
    {
        $id = ObjInput::get('id', 'int');
        $partner_payment_method = null;
        if (intval($id)) {
            $partner_payment_method = Tables::selectAllDataTable("partner_payment_method", "payment_method_id = " . $id);
            if ($partner_payment_method) {
                foreach ($partner_payment_method as $key => $data) {
                    $partner_payment_id = $data['partner_payment_id'];
                    if (intval($partner_payment_id) > 0) {
                        $partner_payment = Tables::selectOneDataTable("partner_payment", "id = " . $partner_payment_id);
                        if ($partner_payment) {
                            $partner_payment_method[$key]['partner_payment_name'] = $partner_payment['name'];
                        }
                    }
                }
            }

        }

        $model = new PartnerPaymentMethod();
        $model->payment_method_id = $id;
        $partner_payment_arr = Weblib::createComboTableArray("partner_payment", "id", "name", "status = " . PartnerPayment::STATUS_ACTIVE, "Chọn kênh thanh toán", true);

        $enviroments_arr = $GLOBALS['ENVIROMENTS'];
        return $this->render('list-by-payment-method', [
            'partner_payment_method' => $partner_payment_method,
            'model' => $model,
            'partner_payment_arr' => $partner_payment_arr,
            'enviroments_arr' => $enviroments_arr
        ]);
    }

    public function actionLockInPaymentMethod()
    {
        $message = null;
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");
            $payment_method_id = Yii::$app->request->post("payment_method_id");
            $model = PartnerPaymentMethod::findOne(['id' => $id]);
            if ($model != null) {
                $model->status = PartnerPaymentMethod::STATUS_LOCK;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save(false)) {
                    $message = Translate::get('Khóa thành công');
                } else {
                    $message = Translate::get('Khóa thất bại');
                }
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-payment-method/list-by-payment-method", "id" => $payment_method_id]);
            Weblib::showMessage($message, $url);
        }

    }

    public function actionUnlock()
    {
        $message = null;
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");
            $payment_method_id = Yii::$app->request->post("payment_method_id");
            $model = PartnerPaymentMethod::findOne(['id' => $id]);

            if ($model != null) {
                $model->status = PartnerPaymentMethod::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save(false)) {
                    $message = Translate::get('Mở khóa thành công');
                } else {
                    $message = Translate::get('Mở khóa thất bại');
                }
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-payment-method/list-by-payment-method", "id" => $payment_method_id]);
            Weblib::showMessage($message, $url);
        }
    }

    public function actionAdd()
    {
        $model = new PartnerPaymentMethod();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $partner_payment_code = null;
            $form = Yii::$app->request->post('PartnerPaymentMethod');
            $partner_payment_id = $form['partner_payment_id'];
            if (intval($partner_payment_id) > 0) {
                $partner_payment = Tables::selectOneDataTable("partner_payment", "id = " . $partner_payment_id);
                if ($partner_payment) {
                    $partner_payment_code = $partner_payment['code'];
                }
            }
            $params = array(
                'partner_payment_id' => $partner_payment_id,
                'partner_payment_code' => $partner_payment_code,
                'payment_method_id' => $form['payment_method_id'],
                'enviroment' => $form['enviroment'],
                'position' => $form['position'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = PartnerPaymentMethodBusiness::add($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm thành công thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(['partner-payment-method/list-by-payment-method', 'id' => $form['payment_method_id']]);
            Weblib::showMessage($message, $url);
        }

    }

    // Lấy thông tin khi sửa
    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $data = Tables::selectOneDataTable("partner_payment_method", "id = " . $id);
            return json_encode($data);
        }
    }

    public function actionEdit()
    {
        $model = new PartnerPaymentMethod();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $partner_payment_code = null;
            $form = Yii::$app->request->post('PartnerPaymentMethod');
            $partner_payment_id = $form['partner_payment_id'];
            if (intval($partner_payment_id) > 0) {
                $partner_payment = Tables::selectOneDataTable("partner_payment", "id = " . $partner_payment_id);
                if ($partner_payment) {
                    $partner_payment_code = $partner_payment['code'];
                }
            }
            $params = array(
                'id' => $form['id'],
                'partner_payment_id' => $partner_payment_id,
                'partner_payment_code' => $partner_payment_code,
                'payment_method_id' => $form['payment_method_id'],
                'enviroment' => $form['enviroment'],
                'position' => $form['position'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = PartnerPaymentMethodBusiness::update($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(['partner-payment-method/list-by-payment-method', 'id' => $form['payment_method_id']]);
            Weblib::showMessage($message, $url);
        }

    }

} 
