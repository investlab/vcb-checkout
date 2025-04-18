<?php

namespace api\controllers;

use Yii;
use yii\filters\VerbFilter;
use api\components\ApiController;
use common\api\CardTokenBasicApi;
use common\api\CardTokenVersion1_0Api;
use common\components\utils\ObjInput;

class CardTokenController extends ApiController {
        
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'version_1_0' => ['post'],
                ],
            ]
        ];
    }
    
    public function beforeAction($event)
    {
        $action = $event->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }
        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $this->setHeader(400);
            exit;
        }
        $this->setHeader(200);
        return true;
    }
    
    protected function setHeader($status)
    {
        header("Content-type: application/json; charset=utf-8");
        header('HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status));
    }

    protected function getStatusCodeMessage($status)
    {
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    public function actionVersion_1_0()
    {
        $log_id = uniqid() . time();
        $function = ObjInput::get('function', 'str', '');
        $obj = new CardTokenVersion1_0Api();
        CardTokenBasicApi::writeLog('[LOG_ID] ' . $log_id . ' [REQUEST] ' . json_encode(Yii::$app->request->post()));
        $response = $obj->process($function);
        CardTokenBasicApi::writeLog('[LOG_ID] ' . $log_id . ' [RESPONSE] ' . json_encode($response));
        $this->_setHeader(200);
        echo json_encode($response);       
        exit();
    }
    
}
