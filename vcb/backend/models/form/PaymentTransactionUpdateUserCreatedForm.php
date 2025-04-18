<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\PaymentTransaction;
use common\components\libs\Tables;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPaymentMethod;
use common\components\libs\Weblib;

class PaymentTransactionUpdateUserCreatedForm extends PaymentTransaction {

    public $username = null; 
    public $user_id = null;

    public function rules() {
        return [
            [['username'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['username'], 'string', 'max' => 50, 'message' => '{attribute} không hợp lệ'],           
            [['username'], 'isUserName', 'message' => '{attribute} không hợp lệ.'],
        ];
    }

    public function attributeLabels() {
        return array(
            'username' => 'Username người dùng',
        );
    }
    
    public function isUserName($attribute, $params) {
        $user_info = Tables::selectOneDataTable("user", ["username = :username", 'username' => $this->$attribute]);
        if ($user_info == false) {
            $this->addError($attribute, 'Username không tồn tại');
        } else {
            $this->user_id = $user_info['id'];
        }
    }
}
