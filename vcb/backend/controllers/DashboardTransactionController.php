<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\input\DashboardTransactionSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class DashboardTransactionController extends BackendController
{
    public function actionIndex()
    {
        $search = new DashboardTransactionSearch();
        $end_date = date('d-m-Y', time());
        $start_date = date('d-m-Y', strtotime("-6 day", time()));
        $search->setAttributes([
            'start_time' => '00:00:00 '. $start_date,
            'end_time' => '23:59:59 '. $end_date,
        ]);
        $result = $search->search();
        $branch_id  = Yii::$app->user->getIdentity()->branch_id;
        if (!empty($branch_id)){
            $merchants = Weblib::createComboTableArray('merchant', 'id', 'name', 'branch_id ='.$branch_id, Translate::get('Chọn merchant'), true, 'name ASC');
        }else{
            $merchants = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');

        }



        return $this->render('index', [
            'merchants' => $merchants,
            'data_search' => $result
        ]);
    }

    public function actionGetDataChartTransaction() {
        $search = new DashboardTransactionSearch();
        $search->setAttributes(Yii::$app->request->post());
        $result = $search->search();

        return json_encode($result);
    }


}