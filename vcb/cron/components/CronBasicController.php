<?php

namespace cron\components;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use common\components\utils\Logs;

class CronBasicController extends Controller
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
                    'index' => ['get'],
                    'post' => ['get'],
                    'doisoat' => ['post'],
                    'export-manual' => ['get'],
                    'xnc' => ['get'],
                    'quangninh' => ['get'],
                    'buudien' => ['get'],
                    'xanhpon' => ['get'],
                    'bcit' => ['get'],
                    'refund' => ['get'],
                    'update-by-merchant' => ['get'],
                    'get-transaction' => ['get'],
                    'update-notify-bo-cong-an' => ['get'],
                    'update-notify-bo-cong-an-handle' => ['get'],
                    'fubon' => ['get'],
                    'cahp' => ['get'],
                    'catb' => ['get'],
                    'cabt' => ['get'],
                    'cakt' => ['get'],
                    'catq' => ['get'],
                    'hub' => ['get'],
                    'cahcm' => ['get'],
                    'capt' => ['get'],
                    'med-th' => ['get'],
                    'med-bd' => ['get'],
                    'ca-dong-nai' => ['get'],
                    'ca-tien-giang' => ['get'],
                    'catth' => ['get'],
                    'xnyh-pos' => ['get'],
                    'update-cancel' => ['get'],
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
            exit;
        }
        $this->_setHeader(200);
        return true;
    }

    public function afterAction($action, $result)
    {
        exit();
    }

    protected function _setHeader($status)
    {
        header("Content-type: application/json; charset=utf-8");
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
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
    
    protected function writeLog($data) {
        $file_name = 'cron' . DS . $this->id . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }
    
}
