<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/19/2016
 * Time: 9:22 AM
 */

namespace checkout\controllers;

use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;

class ErrorController extends Controller
{
    public $layout = 'error';
    protected $_pageTitleDefault = 'Order cancellation';
    protected $_pageTitle = null;
    protected $_fieldName = '';
    protected $_pageDescription = 'Description';
    protected $_pageKeyword = '';
    public $staticClient;

    public function actionIndex()
    {
        $error_message = Yii::$app->request->get('error_message');
        $error_message = base64_decode(base64_decode($error_message));
        $back_url = Yii::$app->request->get('back_url');
        return $this->render('index', array(
                'error_message' => $error_message,
                'back_url' => $back_url,
            )
        );
    }


}