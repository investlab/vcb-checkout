<?php

namespace common\models\business;


use common\models\db\PaymentMethodRule;

class PaymentMethodRuleBusiness
{

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethodRule::getDb()->beginTransaction();
        }
        $model = new PaymentMethodRule();
        $model->payment_method_id = $params['payment_method_id'];
        $model->payment_method_rule_type_id = $params['payment_method_rule_type_id'];
        $model->option = $params['option'];
//        $model->value = $params['value'];
        $model->status = PaymentMethodRule::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $all = true;
                if (trim($params['value']) != '') {
                    $value = explode(',', trim($params['value']));
                    foreach ($value as $item) {
                        $inputs = array(
                            'payment_method_rule_id' => $id,
                            'value' => $item,
                            'user_id' => $params['user_id'],
                        );
//                    var_dump($inputs);die();
                        $result = PaymentMethodRuleValueBusiness::add($inputs, false);
                        if ($result['error_message'] != '') {
                            $error_message = $result['error_message'];
                            $all = false;
                            break;
                        }
                    }
                }
                if ($all) {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Có lỗi khi thêm';
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

    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethodRule::getDb()->beginTransaction();
        }
        $model = PaymentMethodRule::findOne(['id' => $params['id']]);
        $model->payment_method_id = $params['payment_method_id'];
        $model->payment_method_rule_type_id = $params['payment_method_rule_type_id'];
        $model->option = $params['option'];
//        $model->value = $params['value'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $all = true;
                $rule_value = PaymentMethodRuleValueBusiness::getByRuleId($model->id);
                if ($rule_value != null) {
                    $sql = "DELETE FROM payment_method_rule_value "
                        . "WHERE payment_method_rule_id = " . $model->id . " ";
                    $connection = PaymentMethodRule::getDb();
                    $command = $connection->createCommand($sql);
                    $delete_result = $command->execute();
                    if ($delete_result) {
                        if (trim($params['value']) != '') {
                            $value = explode(',', trim($params['value']));
                            foreach ($value as $item) {
                                $inputs = array(
                                    'payment_method_rule_id' => $model->id,
                                    'value' => $item,
                                    'user_id' => $params['user_id'],
                                );
                                //var_dump($inputs);die();
                                $result = PaymentMethodRuleValueBusiness::add($inputs, false);
                                if ($result['error_message'] != '') {
                                    $error_message = $result['error_message'];
                                    $all = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        $error_message = 'Có lỗi khi cập nhật giới hạn';
                        $all = false;
                    }
                } else {
                    if (trim($params['value']) != '') {
                        $value = explode(',', trim($params['value']));
                        foreach ($value as $item) {
                            $inputs = array(
                                'payment_method_rule_id' => $model->id,
                                'value' => $item,
                                'user_id' => $params['user_id'],
                            );
                            //var_dump($inputs);die();
                            $result = PaymentMethodRuleValueBusiness::add($inputs, false);
                            if ($result['error_message'] != '') {
                                $error_message = $result['error_message'];
                                $all = false;
                                break;
                            }
                        }
                    }
                }

                if ($all) {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật';
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