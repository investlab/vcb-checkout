<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;

class ShipPartnerUpdateProductCategoryForm extends \common\models\db\ShipPartner {
    public $product_category_ids = null;
    
    public function rules() {
        return [
            [['product_category_ids'], 'arrayOfInt'],
        ];
    }
    
    public function arrayOfInt($attributeName, $params)
    {
        $allowEmpty = false;
        if (isset($params['allowEmpty']) and is_bool($params['allowEmpty'])) {
            $allowEmpty = $params['allowEmpty'];
        }
        if (!is_array($this->$attributeName)) {
            $this->addError($attributeName, "$attributeName không hợp lệ.");
        }
        if (empty($this->$attributeName) && !$allowEmpty) {
            $this->addError($attributeName, "$attributeName yêu cầu phải chọn.");
        }
        foreach ($this->$attributeName as $key => $value) {
            if (!is_int($value)) {
                $this->addError($attributeName, "$attributeName chứa giá trị không hợp lệ");
            }
        }
    }
}
