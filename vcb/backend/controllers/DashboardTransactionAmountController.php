<?php

namespace backend\controllers;

use Yii;
use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\Translate;
use common\models\business\DashboardTransactionAmountBusiness;

class DashboardTransactionAmountController extends BackendController {
    
    public function actionIndex() {
        if (Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = DashboardTransactionAmountBusiness::getData([
                'time_search' => Yii::$app->request->post('time_search'),
                'merchant_id' => Yii::$app->request->post('merchant_id'),
            ]);
            return $data;
        }
        $branch_id  = Yii::$app->user->getIdentity()->branch_id;
        if (!empty($branch_id)){
            $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'branch_id ='.$branch_id, Translate::get('Chọn merchant'), true, 'name ASC');
        }else{
            $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');

        }
            return $this->render('index', [
            'merchant_search_arr' => $merchant_search_arr
        ]);
    }
    
}