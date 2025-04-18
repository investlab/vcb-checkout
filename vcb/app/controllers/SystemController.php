<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 12/6/2019
 * Time: 3:55 PM
 */

namespace app\controllers;
use app\components\ApiV2Controller;
use app\models\bussiness\SystemFlow;

class SystemController extends ApiV2Controller

{
    public $modelClass = 'api\modules\v2\models\form\SystemForm';
    private $_modelFlow;
    public function init() {

        parent::init();
        $this->no_validate = ['get-config'];
        $this->_modelFlow = new SystemFlow();
        //
    }

    public function actionGetConfig(){
        $result = $this->_modelFlow->getConfig();
        return $this->forward($result);

    }

}