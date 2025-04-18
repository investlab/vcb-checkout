<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\BranchBusiness;
use common\models\db\Branch;
use common\models\form\BranchForm;
use common\models\input\BranchSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class BranchController extends BackendController
{
    public function actionIndex()
    {
        $search = new BranchSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $model = new BranchForm();
        $status_arr = Branch::getStatus();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'model' => $model,
            'check_all_operators' => Branch::getOperatorsForCheckAll(),
        ]);
    }

    // Thêm mới
    public function actionAdd()
    {
        $model = new BranchForm();
        $model->scenario = BranchForm::SCENARIO_ADD;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('BranchForm');

            $params = array(
                'name' => $form['name'],
                'city' => $form['city'],
                'user_id' => Yii::$app->user->getId(),
            );

            $result = BranchBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm chi nhánh thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('branch/index', HTTP_CODE);
            Weblib::showMessage($message, $url);
        }
        return $this->render('add', [
            'model' => $model
        ]);
    }

    // Cập nhật
    public function actionViewUpdate()
    {
        $model = new BranchForm();
        $id = ObjInput::get('id', 'int');
        $branch = null;
        if (intval($id) > 0) {
            $branch = Tables::selectOneDataTable("branch", ["id = :id ", "id" => $id]);
            if ($branch) {
                $model->id = $branch['id'];
                $model->name = $branch['name'];
                $model->city = $branch['city'];
            }
        }

        return $this->render('update', [
            'model' => $model,
            'branch' => $branch
        ]);
    }

    public function actionUpdate()
    {
        $model = new BranchForm();
        $model->scenario = BranchForm::SCENARIO_UPDATE;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('BranchForm');

            $branch = Tables::selectOneDataTable('branch', "id = " . $form['id']);

            $params = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'city' => $form['city'],
                'user_id' => Yii::$app->user->getId(),
            );

            $result = BranchBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật chi nhánh thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('branch/index');
            Weblib::showMessage($message, $url);
        }
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['branch/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = BranchBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa chi nhánh thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại chi nhánh');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    //  Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['branch/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = BranchBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa chi nhánh thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tồn tại chi nhánh');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    //Xem chi tiết
    public function actionDetail() {
        $message = null;
        $search = ['branch/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $result = BranchBusiness::viewDetail($id);
            $status_arr = Branch::getStatus();

            if ($result['error_message'] == '') {
                return $this->render('detail', [
                    'branch' => $result['data'],
                    'status_arr' => $status_arr
                ]);
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tồn tại chi nhánh');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }
}