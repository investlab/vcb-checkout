<?php
namespace checkout\controllers;
use checkout\components\MerchantCheckoutController;
use common\models\db\CheckoutOrder;
use Yii;
use yii\web\Controller;

class AjaxWriteLogController extends Controller
{

    public function actionWriteLogCbs3ds2(){
        $result='Không có dữ liệu !';
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->get()){
                $log = Yii::$app->request->get();
//                var_dump($log);die();
                $OrderNumber = $log['OrderNumber'];
                $ErrorDescription = $log['ErrorDescription'];
                if(self::writeLog($OrderNumber,$ErrorDescription)){
                    $result = 'ok';
                }
                $result = 'Lỗi ghi log';
            }
        }
        return $result;
    }

    public function writeLog($OrderNumber,$ErrorDescription)
    {
        $now = time();
        $file_name = LOG_PATH . '3ds2x' . DS . 'cardinal' . DS . 'validated' . DS . date('Ymd', $now) . '.txt';
        $fp = fopen($file_name, 'a');
        if ($fp) {
            $line = date("[H:i:s, d/m/Y]: ", $now). "[".$OrderNumber."]" . json_encode($ErrorDescription) . " \r\n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }

}
