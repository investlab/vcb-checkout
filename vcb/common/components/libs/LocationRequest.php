<?php

namespace common\components\libs;

use Yii;

class LocationRequest extends \yii\web\Request {

    private $_baseUrl = null;
    public $location = null;
    
    public function init() {
        parent::init();
        $this->initLocation();
    }
    
    public function initLocation() {
        $this->location = $_GET['location'];
    }

    public function getBaseUrl() {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
            $this->_baseUrl = str_replace(\Yii::$app->id . '/web', $this->location . '/' . \Yii::$app->id, $this->_baseUrl);
        }
        return $this->_baseUrl;
    }

}
