<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\components\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\components\libs\Weblib;

class HeaderWidget extends Widget{
    public function init() {
        parent::init();
    }
    
    public function run() {
        $usersLogined = Weblib::getUserLogined();
        return $this->render('header_widget',[
            'root_url'=>ROOT_URL,
            'backend_url'=>Yii::$app->homeUrl,
            'users'=>$usersLogined,
            'logout_url'=>Yii::$app->urlManager->createAbsoluteUrl('user/logout'),
        ]);
    }
}