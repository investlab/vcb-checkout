<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 04/01/2018
 * Time: 11:04 SA
 */

namespace common\models\business;

use common\models\db\PaymentMethodRuleValue;
use Yii;

class PaymentMethodRuleValueBusiness
{
    public static function getByID($id)
    {
        return PaymentMethodRuleValue::find()->andWhere(['=', 'id', $id])->one();
    }

    public static function getByRuleId($rule_id)
    {
        return PaymentMethodRuleValue::find()->andWhere(['=', 'payment_method_rule_id', $rule_id])->asArray()->all();
    }

    /**
     *
     * @param type $params : payment_method_rule_id, value, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PaymentMethodRuleValue::getDb()->beginTransaction();
        }
        $model = new PaymentMethodRuleValue();
        $model->payment_method_rule_id = $params['payment_method_rule_id'];
        $model->value = $params['value'];
        $model->time_created = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thêm giá trị giới hạn';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }
}