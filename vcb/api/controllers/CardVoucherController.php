<?php

namespace api\controllers;

use api\components\ApiController;
use common\api\CardVoucherApi;
use common\components\utils\ObjInput;
use yii\filters\VerbFilter;

class CardVoucherController extends ApiController
{
    public function behaviors(): array
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
        $ip = get_client_ip();

        if (in_array($ip, [
            '::1' ,
            '172.26.0.1' ,

            '103.109.32.66',
            '103.109.32.68',
            '183.91.7.129',
            '14.177.239.192',
            '101.99.7.132',
            '101.99.7.213',
            '14.177.239.244',
//            '183.91.4.105',
            '14.177.239.203',

            '103.109.32.92', // partner
            '171.244.53.212', // partner
            '171.244.53.222', // partner
            '18.143.216.8', // partner
            '18.142.214.241', // partner
        ])) {
            $obj = new CardVoucherApi();
            if (!empty($_POST)){
                $obj->writeLog($function . '[post][' . $ip . ']:' . json_encode(@$_POST));
            }
            $result = $obj->process($function, $has_encrypt);
            $this->_setHeader(200);
            echo $result;
            exit();
        } else {
            echo "<pre>";
            var_dump("Invalid IP");
            die();
        }


    }
}
