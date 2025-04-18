<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 8/12/2016
 * Time: 9:52 AM
 */

namespace api\controllers;

use common\api\CheckoutVersionSeamlessStaticApi;
use Yii;
use api\components\ApiController;
use common\api\CheckoutVersion1_0StaticApi;
use common\components\utils\ObjInput;

class CheckoutController extends ApiController
{

    public function actionVersion_1_0()
    {
        $has_encrypt = false;
        $function = ObjInput::get('function', 'str', '');
        $obj = new CheckoutVersion1_0StaticApi();
        if (!empty($_POST)){
            $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        }
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;       
        exit();
    }

    public function actionVersion_seamless()
    {
        $has_encrypt = false;
        $function = ObjInput::get('function', 'str', '');
        $obj = new CheckoutVersionSeamlessStaticApi();
        if (!empty($_POST)){
            $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        }
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;
        exit();
    }

}
