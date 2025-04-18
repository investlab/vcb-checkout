<?php

namespace api\controllers;

use api\components\ApiController;
use common\api\CheckoutSeamless;
use common\components\utils\ObjInput;
use Firebase\JWT\JWT;
use yii\filters\VerbFilter;

class CheckoutSeamlessController extends ApiController
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post'],
                    'test' => ['post'],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        $has_encrypt = false;
        $function = ObjInput::get('function', 'str', '');
        $obj = new CheckoutSeamless();
        if (!empty($_POST)){
            $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        }
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;
        exit();
    }

    public function actionTest()
    {
        $jwt_in = ObjInput::get('jwt', 'str', '');
        $jwt = new JWT();
        $jwt::$leeway = 60;
        $decoded = $jwt->decode($jwt_in, "c0f9eb67-edd7-4303-a519-a9ccd1b11b27", array('HS256'));
        echo "<pre>";
        var_dump($decoded);
        die();

    }
}