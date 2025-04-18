<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;

class ShipPartnerAddForm extends \common\models\db\ShipPartner {
    public function beforeSave($insert) {
        $this->delivery_status  = 2;
        $this->time_created = time();
        $this->user_created = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
