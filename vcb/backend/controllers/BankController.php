<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 4/14/2016
 * Time: 2:49 PM
 */

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\FormatDateTime;
use common\models\business\BankBusiness;
use common\models\db\Bank;
use common\models\form\AddBankForm;
use common\models\form\BankForm;
use common\models\input\BankSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use DateTime;

class BankController extends BackendController
{

    public function actionIndex()
    {
        $search = new BankSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $model_bank = new AddBankForm();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model_bank' => $model_bank
        ]);
    }

    public function actionCreateBank()
    {
        $model = new AddBankForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                $message = $model->errors;
            } else {
                $form = Yii::$app->request->post("AddBankForm");

                $modelBank = new Bank();
                $modelBank->code = mb_strtoupper($form["code"]);
                $modelBank->trade_name = mb_strtoupper($form["trade_name"]);
                $modelBank->name = $form["name"];
                $modelBank->description = $form["description"];
                $modelBank->status = 1;
                $modelBank->time_created = time();
                $modelBank->user_created = Yii::$app->user->getId();

                if ($modelBank->save()) {
                    $message = 'Thêm ngân hàng thành công.';
                } else {
                    $message = 'Thêm ngân hàng thất bại.';
                }
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(["bank/index"], HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
    }

    // Lấy thông tin ngân hàng khi sửa
    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get("id");
            $model = BankBusiness::getByID($id);
            $data = $model->toArray();
            return json_encode($data);
        }
    }

    // Sửa ngân hàng
    public function actionEditBank()
    {
        $model = new AddBankForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $bankForm = Yii::$app->request->post("AddBankForm");
        if ($model->load(Yii::$app->request->post())) {

            if (!$model->validate()) {
                $message = $model->errors;
            } else {
                $id = $bankForm['id'];
                $model_bank = BankBusiness::getByID($id);

                $model_bank->code = mb_strtoupper($bankForm["code"]);
                $model_bank->trade_name = mb_strtoupper($bankForm["trade_name"]);
                $model_bank->name = $bankForm["name"];
                $model_bank->description = $bankForm["description"];
                $model_bank->time_updated = time();
                $model_bank->user_updated = Yii::$app->user->getId();

                if ($model_bank->save()) {
                    $message = 'Cập nhật ngân hàng thành công.';
                } else {
                    $message = 'Cập nhật ngân hàng thất bại.';
                }
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(["bank/index"], HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
    }
    //-------------------------------------------
    // Khóa
    public function actionLockBank()
    {
        $message = null;
        $search = ['bank/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $bank = BankBusiness::getByID($id);
            if ($bank != null) {
                $model = Bank::findOne(['id' => $id]);
                $model->status = 2;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save()) {
                    $message = 'Khóa tài khoản ngân hàng thành công.';
                } else {
                    $message = 'Khóa tài khoản ngân hàng thất bại.';
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
    public function actionUnlockBank()
    {
        $message = null;
        $search = ['bank/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }
            $alego_bank = BankBusiness::getByID($id);
            if ($alego_bank != null) {
                $model = Bank::findOne(['id' => $id]);
                $model->status = 1;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                if ($model->save()) {
                    $message = 'Mở khóa tài khoản ngân hàng thành công.';
                } else {
                    $message = 'Mở khóa tài khoản ngân hàng thất bại.';
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