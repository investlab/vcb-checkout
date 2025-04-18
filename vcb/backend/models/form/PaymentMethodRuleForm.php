<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 04/01/2018
 * Time: 10:54 SA
 */
namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\PaymentMethodRule;

class PaymentMethodRuleForm extends PaymentMethodRule
{
    public $value = null;

    public function rules()
    {
        return [
            [['value'], 'isValue'],
        ];
    }

    public function isValue()
    {
        $value = str_replace(' ', '', $this->value);
        $value_arr = explode(',', $value);
        $check_value = true;

        foreach ($value_arr as $k => $v) {
            if (is_int(intval($v)) == false) {
//                var_dump(is_int($v));
                $check_value = false;
            }
        }
//        die;
        if ($check_value == false) {
            $this->addError('value', 'Giá trị không hợp lệ');
        }
    }

    public function attributeLabels()
    {
        return array(
            'value' => 'Giá trị',
        );
    }
}