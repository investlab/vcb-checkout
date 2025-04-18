<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use common\models\db\MethodPaymentMethod;
use common\util\TextUtil;
use Yii;

class MethodPaymentMethodBusiness
{
    public static function getByMethodId($id)
    {
        return MethodPaymentMethod::find()->andWhere(['=', 'method_id', $id])->asArray()->all();
    }

    /**
     *
     * @param type $params : method_id, payment_method_id, user_id
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true)
    {
        $commit = true;
        $error_message = '';
        //------------
        if ($rollback) {
            $transaction = MethodPaymentMethod::getDb()->beginTransaction();
        }
        $method_pMethod = MethodPaymentMethodBusiness::getByMethodId($params['method_id']);
        if ($method_pMethod != null) {
            $delete = MethodPaymentMethod::deleteAll('method_id = ' . $params['method_id']);
        }
        if ($params['payment_method_id'] != null) {
            foreach ($params['payment_method_id'] as $key => $value) {
                $model = new MethodPaymentMethod();
                $model->method_id = $params['method_id'];
                $model->payment_method_id = $value;
                $model->time_created = time();
                $model->user_created = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $error_message = '';
                    } else {
                        $commit = false;
                        $error_message = 'Có lỗi khi cập nhật phương thức thanh toán';
                        break;
                    }
                } else {
                    $commit = false;
                    $error_message = 'Tham số đầu vào không hợp lệ';
                    break;
                }
            }
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }
}