<?php

namespace api\controllers;

use common\api\CancelPaymentVersion1_0StaticApi;
use Yii;
use api\components\ApiController;
use common\api\CheckoutVersion1_0StaticApi;
use common\components\utils\ObjInput;
use yii\filters\VerbFilter;

class CancelPaymentController extends ApiController
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        $has_encrypt = false;
        $function = ObjInput::get('function', 'str', '');
        $obj = new CancelPaymentVersion1_0StaticApi();
        if (!empty($_POST)){
            $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        }
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;
        exit();
    }

}
