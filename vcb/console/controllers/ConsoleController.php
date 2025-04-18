<?php

namespace console\controller;

use yii\console\Controller;
use Yii;

class ConsoleController extends Controller
{

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }
} 