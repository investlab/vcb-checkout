<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;

class CallCustomerBasicForm extends Model {
    
    public $call_history_info = null;
    public $customer_info = null;
    public $call_follow_up_pool_info = null;
    public $call_plan_pool_info = null;
    public $customer_id = null;
    public $title = '';
    public $key = null;
    public $error = null;
    public $message = null;
    
    public function rules() {
        return [
            
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            
        );
    }
    
    public function loadRequest($data, $formName = null) {
        $this->_setMessage();
        parent::load($data, $formName);        
        $this->_afterLoadRequest();
    }
    
    protected function _afterLoadRequest() {
        
    }
    
    final protected function _setMessage() {
        if (isset($_SESSION[$this->key]['message']) && !empty($_SESSION[$this->key]['message'])) {
            $this->message = $_SESSION[$this->key]['message'];
            unset($_SESSION[$this->key]['message']);
        }
    }
    
    final public function addMessage($message) {
        $this->message = $message;
        $_SESSION[$this->key]['message'] = $message;
    }
}