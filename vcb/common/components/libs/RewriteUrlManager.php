<?php

namespace common\components\libs;

use Yii;
use yii\web\UrlManager;

class RewriteUrlManager extends UrlManager {

    private $params = null;

    public function createUrl($params) {
        $this->params = $params;
        if (isset($params['location']) && !empty($params['location'])) {
            unset($params['location']);
        }
        return parent::createUrl($params);
    }

    public function getBaseUrl() {
        $url = parent::getBaseUrl();
        if (isset($this->params['location']) && !empty($this->params['location'])) {
            if ($this->params['location'] != Yii::$app->location) {
                $url = str_replace('/' . Yii::$app->location . '/', '/' . $this->params['location'] . '/', $url);
            }
        }
        return $url;
    }

}
