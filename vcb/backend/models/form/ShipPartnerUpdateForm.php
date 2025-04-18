<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;

class ShipPartnerUpdateForm extends \common\models\db\ShipPartner{
    public function beforeSave($insert) {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
