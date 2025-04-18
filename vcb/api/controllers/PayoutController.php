<?php
namespace api\controllers;

use Yii;
use api\components\ApiController;
use common\api\PayoutVersion1_0StaticApi;
use common\components\utils\ObjInput;

class PayoutController extends ApiController
{

    public function actionVersion_1_0()
    {
        $has_encrypt = false;
        $function = ObjInput::get('function', 'str', '');
        $obj = new PayoutVersion1_0StaticApi();       
        $obj->writeLog($function . '[post]:' . json_encode(@$_POST));
        $result = $obj->process($function, $has_encrypt);
        $this->_setHeader(200);
        echo $result;       
        exit();
    }

}
