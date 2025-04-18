<?php

namespace common\components\libs;

use Yii;

class LocationApplication extends \yii\web\Application {

    public $location = 'vi';

    function __construct($config = array()) {
        $this->_setLocation($config);
        parent::__construct($config);
    }
    
    protected function _setLocation($config) {    
        $request_uri = $_SERVER['REQUEST_URI'];
        if (preg_match('/([^\/]+)\/' . $config['id'] . '\//', $request_uri, $part)) {
            $this->location = $part[1];
        }
        $_GET['location'] = $this->location;
    }
}
