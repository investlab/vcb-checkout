<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/19/2016
 * Time: 9:22 AM
 */

namespace merchant\controllers;

use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;
use merchant\assets\AppAsset;
use common\components\utils\Strings;

class ErrorController extends Controller
{
    public $layout = 'error';
    protected $_pageTitleDefault = 'XPay';
    protected $_pageTitle = null;
    protected $_fieldName = '';
    protected $_pageDescription = 'Mô tả';
    protected $_pageKeyword = '';
    public $staticClient;

    public function actionIndex()
    {
        //echo $this->base64url_encode('Địa chỉ truy cập không hợp lệ');
        //ErrorAppAsset::register(Yii::$app->view);
        $error_message = ObjInput::get('error_message', 'str', '', 'GET');
        $error_message = base64_decode(base64_decode($error_message));
        if ($error_message == '') {
            $error_message = 'Địa chỉ trang truy cập không tồn tại';
        }
        $back_url = Yii::$app->request->get('back_url');
        return $this->render('index', array(
                'error_message' => $error_message,
                'back_url' => $back_url,
            )
        );
    }
}