<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/19/2016
 * Time: 11:25 AM
 */

namespace api\components;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;

class ApiController extends Controller
{

    public $layout = false;
    public $data = null;

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'version_1_0' => ['post', 'get'],
                    'version_seamless' => ['post', 'get'],
                    'refund-call-back' => ['post', 'get'],
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
            $this->_setHeader(400);
            echo json_encode(array('result_code' => '02', 'result_message' => 'Invalid data'));
            exit;
        }
        $this->_setHeader();
        //$this->data = file_get_contents('php://input');
        return true;
    }

    public function afterAction($action, $result)
    {
        exit();
    }

    protected function _setHeader()
    {
        header("Content-type: application/json");
        /*
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        $content_type = "application/json; charset=utf-8";        
        header($status_header);
        header('Content-type: ' . $content_type);
        header('X-Powered-By: ' . "Nintriva <nintriva.com>");
        
         */
    }

    protected function _getStatusCodeMessage($status)
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
}
