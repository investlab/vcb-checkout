<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;

class ErrorController extends Controller {
    
    public $layout = 'error';
    
    public function actionIndex() {
        $error_message_encode = Yii::$app->request->get('error_message');
        $error_message = base64_decode(base64_decode($error_message_encode));
        return $this->render('index', array(
                'error_message' => $error_message,
            )
        );
    }
    
}

