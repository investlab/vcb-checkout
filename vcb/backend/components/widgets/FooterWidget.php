<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\components\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class FooterWidget extends Widget{
    public function init() {
        parent::init();
    }
    
    public function run() {
        return $this->render('footer_widget',[
            'root_url'=>ROOT_URL,
            'frontend_url'=>\Yii::$app->homeUrl,
            'page_config' => $GLOBALS['FRONTEND_PAGE'],
        ]);
    }
}