<?php

namespace checkout\components\widgets;

use Yii;
use yii\helpers\Url;
use yii\base\Widget;
use yii\helpers\Html;
use common\components\libs\Weblib;
use common\components\libs\Tables;

class HeaderVersion_1_0Widget extends Widget{    
    public function init() {
        parent::init();
    }
    
    public function run() {
        return $this->render('header_version_1_0_widget',[
            'root_url' => ROOT_URL,
        ]);
    }
}