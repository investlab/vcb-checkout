<?php

namespace frontend\components\widgets;

use Yii;
use yii\base\Widget;

class HeaderErrorWidget extends Widget {

    public function init() {
        parent::init();
    }

    public function run() {
        return $this->render('header_error_widget', [
            'root_url' => ROOT_URL,
        ]);
    }

}
