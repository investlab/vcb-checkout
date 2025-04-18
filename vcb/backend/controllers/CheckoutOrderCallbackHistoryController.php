<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\models\db\CheckoutOrderCallbackHistory;
use common\models\input\CheckoutOrderCallbackHistorySearch;
use Yii;

class CheckoutOrderCallbackHistoryController extends BackendController
{

    //    Danh sách
    public function actionIndex()
    {
        $search = new CheckoutOrderCallbackHistorySearch();
        $search->setAttributes(Yii::$app->request->get());
        if (empty($search->time_request_from)) {
            $search->time_request_from = date('d-m-Y');
        }
        if (empty($search->time_request_to)) {
            $search->time_request_to = date('d-m-Y');
        }
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = CheckoutOrderCallbackHistory::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'merchant_search_arr' => $merchant_search_arr,
            'status_arr' => $status_arr
        ]);
    }
} 