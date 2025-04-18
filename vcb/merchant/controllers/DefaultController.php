<?php

namespace merchant\controllers;

use Yii;
use merchant\components\MerchantController;

class DefaultController extends MerchantController
{

    public function actionIndex()
    {


        return $this->render('index', [
        ]);
    }

}
