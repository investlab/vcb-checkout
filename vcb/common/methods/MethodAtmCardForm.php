<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\methods;

use Yii;
use yii\base\Model;

class MethodAtmCardForm extends MethodBasicForm
{

    public function getMethodCode()
    {
        return 'ATM-CARD';
    }
}
