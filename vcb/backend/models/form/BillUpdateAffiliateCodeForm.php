<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class BillUpdateAffiliateCodeForm extends Model {

    public $code = null;

    public function rules() {
        return [
            [['code'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['code'], 'isAffiliateCode'],
        ];
    }

    public function attributeLabels() {
        return array(
            'code' => 'Mã hội viên',
        );
    }
    
    public function isAffiliateCode($attribute, $param){
        $affiliate_code_info = Tables::selectOneDataTable("affiliate_code", ["code = :code AND status = ".\common\models\db\AffiliateCode::STATUS_ACCEPT, "code" => $this->$attribute]);
        if ($affiliate_code_info == false) {
            $this->addError($attribute, 'Mã hội viên không hợp lệ.');
        }
    }
}
