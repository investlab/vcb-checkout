<?php

namespace merchant\models\form;


use yii\base\Model;
use common\components\utils\Translate;
use Yii;

class LanguageBasicForm extends Model {

    public $message = null;
    
    public function addError($attribute, $error = '') {
        parent::addError($attribute, Translate::get($error));
    }

    public function getAttributeLabel($attribute) {
        $labels = $this->attributeLabels();
        return isset($labels[$attribute]) ? Translate::get($labels[$attribute]) : $this->generateAttributeLabel($attribute);
    }
    
    public function addMessage($value) {
        $this->message = $value;
        Yii::$app->session->set(get_class($this), $this->message);
    }
    
    public function getMessage() {
        if ($this->message == null) {
            $this->message = Yii::$app->session->get(get_class($this));
            Yii::$app->session->set(get_class($this), null);
        }       
        return $this->message;
    }
            
} 