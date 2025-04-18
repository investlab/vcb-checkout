<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/23/2016
 * Time: 3:18 PM
 */

namespace common\models\business;

use common\models\db\PartnerPaymentMethod;
use Yii;
use common\models\db\PaymentMethod;
use common\models\db\PaymentMethodFee;
use common\components\libs\Tables;
use common\models\db\Merchant;

class PaymentMethodBusiness
{
    const BORDER_AMOUNT = 300000; // nho hon thi chay MSB-VA, lon hon thi chay BIDV-VA

    public static function getByIDToArray($id)
    {
        $data = PaymentMethod::findOne(['id' => $id]);
        if ($data != null) {
            return $data->toArray();
        }
        return $data;
    }
    /**
     *
     * @param type $payment_amount
     * @param type $time
     * @return type
     */
    /*public static function getListByPaymentAmount($payment_amount, $time) {
        $enviroment = Yii::$app->controller->module->id;
        $sql = "SELECT payment_method.*, payment_method_fee.flat_fee, payment_method_fee.percentage_fee "
                . "FROM payment_method INNER JOIN payment_method_fee ON payment_method.id = payment_method_fee.payment_method_id "
                . "WHERE payment_method.status = :payment_method_status AND payment_method_fee.status = :payment_method_fee_status "
                . "AND payment_method.min_amount <= :payment_amount AND (payment_method.max_amount >= :payment_amount OR payment_method.max_amount = 0) "
                . "AND payment_method_fee.time_begin <= :time AND (payment_method_fee.time_end >= :time OR payment_method_fee.time_end = 0)"
                . "AND payment_method.enviroment LIKE '%$enviroment%' ";
        $command = Yii::$app->db->createCommand($sql);
        $command->bindValues([
            ':payment_method_status' => PaymentMethod::STATUS_ACTIVE,
            ':payment_method_fee_status' => PaymentMethodFee::STATUS_ACTIVE,
            ':payment_amount' => $payment_amount,
            ':time' => $time]);
        $data = $command->queryAll();
        return $data;
    }*/

    public static function getListByPaymentAmountAndMethodCode($payment_amount, $time, $method_code, $enviroment = '')
    {
        $method_info = Tables::selectOneDataTable("method", "code = '$method_code' ");
        if ($method_info != false) {
            $method_payment_method_info = Tables::selectAllDataTable("method_payment_method", "method_id = " . $method_info['id']);
            if ($method_payment_method_info != false) {
                $payment_method_ids = array();
                foreach ($method_payment_method_info as $row) {
                    $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
                }
                $sql = "SELECT payment_method.*, partner_payment_method.partner_payment_code, partner_payment_method.partner_payment_id "
                    . "FROM payment_method INNER JOIN partner_payment_method ON payment_method.id = partner_payment_method.payment_method_id "
                    . "WHERE payment_method.id IN (" . implode(',', $payment_method_ids) . ") "
                    . "AND payment_method.status = :payment_method_status "
                    . "AND payment_method.min_amount <= :payment_amount AND (payment_method.max_amount >= :payment_amount OR payment_method.max_amount = 0) "
                    . "AND partner_payment_method.enviroment = '$enviroment' "
                    . "AND partner_payment_method.status = " . \common\models\db\PartnerPaymentMethod::STATUS_ACTIVE . " ORDER BY partner_payment_method.position ASC ";
                $command = Yii::$app->db->createCommand($sql);
                $command->bindValues([
                    ':payment_method_status' => PaymentMethod::STATUS_ACTIVE,
                    ':payment_method_fee_status' => PaymentMethodFee::STATUS_ACTIVE,
                    ':payment_amount' => $payment_amount]);
                //echo $command->getRawSql().'<br><br>';
                $data = $command->queryAll();
                return $data;
            }
        }
        return array();
    }

    public static function getInfoByPaymentMethodCode($payment_method_code, $environment, $merchant_id = false)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :code AND status = :status ", "code" => $payment_method_code, "status" => PaymentMethod::STATUS_ACTIVE]);
        if ($payment_method_info) {
            $partner_payment_method_info = false;
            if ($merchant_id) {
                $partner_payment_method_info = Tables::selectOneDataTable("merchant_partner_payment_method",
                    ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status AND merchant_id = :merchant_id",
                        "payment_method_id" => $payment_method_info['id'],
                        "enviroment" => $environment,
                        "status" => PartnerPaymentMethod::STATUS_ACTIVE,
                        "merchant_id" => $merchant_id
                    ], "position ASC, id DESC "); // Get merchant partner payment config
            }
            if (!$partner_payment_method_info) {
                $partner_payment_method_info = Tables::selectOneDataTable("partner_payment_method", ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status", "payment_method_id" => $payment_method_info['id'], "enviroment" => $environment, "status" => PartnerPaymentMethod::STATUS_ACTIVE], "position ASC, id DESC ");
            }
            if ($partner_payment_method_info) {
                $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["id = :id AND status = :status ", "id" => $partner_payment_method_info['partner_payment_id'], "status" => \common\models\db\PartnerPayment::STATUS_ACTIVE]);
                if ($partner_payment_info) {
                    $method_info = Tables::selectOneDataTable("method", ["id IN (SELECT method_id FROM method_payment_method WHERE payment_method_id = :payment_method_id) AND status = :status", "payment_method_id" => $payment_method_info['id'], "status" => \common\models\db\Method::STATUS_ACTIVE]);
                    if ($method_info) {
                        $payment_method_info['partner_payment_id'] = $partner_payment_info['id'];
                        $payment_method_info['partner_payment_code'] = $partner_payment_info['code'];
                        $payment_method_info['method_code'] = $method_info['code'];
                        return $payment_method_info;
                    }
                }
            }
        }
        return false;
    }

    public static function getInfoByPaymentMethodCodeV3($payment_method_code, $environment, $merchant_id = false)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :code AND status = :status ", "code" => $payment_method_code, "status" => PaymentMethod::STATUS_ACTIVE]);
        if ($payment_method_info) {
            $partner_payment_method_info = false;
            if ($merchant_id) {
                $partner_payment_method_info = Tables::selectOneDataTable("merchant_partner_payment_method",
                    ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status AND merchant_id = :merchant_id",
                        "payment_method_id" => $payment_method_info['id'],
                        "enviroment" => $environment,
                        "status" => PartnerPaymentMethod::STATUS_ACTIVE,
                        "merchant_id" => $merchant_id
                    ], "position ASC, id DESC "); // Get merchant partner payment config
            }
            if (!$partner_payment_method_info) {
                $partner_payment_method_info = Tables::selectOneDataTable("partner_payment_method", ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status", "payment_method_id" => $payment_method_info['id'], "enviroment" => $environment, "status" => PartnerPaymentMethod::STATUS_ACTIVE], "position ASC, id DESC ");
            }
            if ($partner_payment_method_info) {
                $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["id = :id AND status = :status ", "id" => $partner_payment_method_info['partner_payment_id'], "status" => \common\models\db\PartnerPayment::STATUS_ACTIVE]);
                if ($partner_payment_info) {
                    $method_info = Tables::selectOneDataTable("method", ["id IN (SELECT method_id FROM method_payment_method WHERE payment_method_id = :payment_method_id) AND status = :status", "payment_method_id" => $payment_method_info['id'], "status" => \common\models\db\Method::STATUS_ACTIVE]);
                    if ($method_info) {
                        $payment_method_info['partner_payment_id'] = $partner_payment_info['id'];
                        $payment_method_info['partner_payment_code'] = $partner_payment_info['code'];
                        $payment_method_info['method_code'] = $method_info['code'];
                        return $payment_method_info;
                    }
                }
            }
        }
        return false;
    }

    /** clone function getInfoByPaymentMethodCode() + thêm đoạn check MOMO-QR-CODE */
    public static function getInfoByPaymentMethodCodeV2($payment_method_code, $environment, $merchant_id = false,$amount=false)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :code AND status = :status ", "code" => $payment_method_code, "status" => PaymentMethod::STATUS_ACTIVE]);
        if ($payment_method_info) {
            // check kenh momo
            $result_switch = self::checkPartnerSwitchAsAmount($payment_method_code, $merchant_id, $amount);

            // GIA LAP // CMT KHI DAY LIVE
//            $result_switch = [
//                'is_switch' => true,
//                'result_bank' => 'BIDV-VA'
//            ];

            if($result_switch['is_switch']){
                // lay kenh moi
                $partner_payment_code = $result_switch['result_bank']; // GAN KENH MOI !!!
                $partner_payment_method_info = Tables::selectOneDataTable("partner_payment_method", ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status", "payment_method_id" => $payment_method_info['id'], "enviroment" => $environment, "status" => PartnerPaymentMethod::STATUS_ACTIVE], "position ASC, id DESC ");
                if ($partner_payment_method_info) {
                    $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["code = :code AND status = :status ", "code" => $partner_payment_code, "status" => \common\models\db\PartnerPayment::STATUS_ACTIVE]);
                    if ($partner_payment_info) {
                        $method_info = Tables::selectOneDataTable("method", ["id IN (SELECT method_id FROM method_payment_method WHERE payment_method_id = :payment_method_id) AND status = :status", "payment_method_id" => $payment_method_info['id'], "status" => \common\models\db\Method::STATUS_ACTIVE]);
                        if ($method_info) {
                            $payment_method_info['partner_payment_id'] = $partner_payment_info['id'];
                            $payment_method_info['partner_payment_code'] = $partner_payment_info['code'];
                            $payment_method_info['method_code'] = $method_info['code'];
                            return $payment_method_info;
                        }
                    }
                }

            }else{
                // CODE CU LAY $partner_payment_method_info
                $partner_payment_method_info = false;
                if ($merchant_id) {
                    $partner_payment_method_info = Tables::selectOneDataTable("merchant_partner_payment_method",
                        ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status AND merchant_id = :merchant_id",
                            "payment_method_id" => $payment_method_info['id'],
                            "enviroment" => $environment,
                            "status" => PartnerPaymentMethod::STATUS_ACTIVE,
                            "merchant_id" => $merchant_id
                        ], "position ASC, id DESC "); // Get merchant partner payment config
                }
                if (!$partner_payment_method_info) {
                    $partner_payment_method_info = Tables::selectOneDataTable("partner_payment_method", ["payment_method_id = :payment_method_id AND enviroment = :enviroment AND status = :status", "payment_method_id" => $payment_method_info['id'], "enviroment" => $environment, "status" => PartnerPaymentMethod::STATUS_ACTIVE], "position ASC, id DESC ");
                }
                if ($partner_payment_method_info) {
                    $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["id = :id AND status = :status ", "id" => $partner_payment_method_info['partner_payment_id'], "status" => \common\models\db\PartnerPayment::STATUS_ACTIVE]);
                    if ($partner_payment_info) {
                        $method_info = Tables::selectOneDataTable("method", ["id IN (SELECT method_id FROM method_payment_method WHERE payment_method_id = :payment_method_id) AND status = :status", "payment_method_id" => $payment_method_info['id'], "status" => \common\models\db\Method::STATUS_ACTIVE]);
                        if ($method_info) {
                            $payment_method_info['partner_payment_id'] = $partner_payment_info['id'];
                            $payment_method_info['partner_payment_code'] = $partner_payment_info['code'];
                            $payment_method_info['method_code'] = $method_info['code'];
                            return $payment_method_info;
                        }
                    }
                }
            }
        }
        return false;
    }

    public static function checkPartnerSwitchAsAmount($payment_method_code, $merchant_id, $amount){
        $is_switch = false;
        $result_bank = '';
        if($payment_method_code == 'MOMO-QR-CODE'){
            $merchant_info = Merchant::find()->where(['id' => $merchant_id])->asArray()->one();
            if($merchant_info){
                if(isset($merchant_info['partner_switch']) && $merchant_info['partner_switch'] == Merchant::MERCHANT_PARTNER_SWITCH_AS_AMOUNT_ON){
                    // CHECK XEM VCB CON SONG HAY KO
                    // CHECK AMOUNT -> CHON KENH MSB HOAC BIDV
                    if($amount){
                        if(intval($amount) < self::BORDER_AMOUNT){
                            $result_bank = 'MSB-VA';
                        }else{
                            $result_bank = 'BIDV-VA';
                        }
                        $is_switch = true;
                    }

                }
            }
        }
        return ['is_switch' => $is_switch, 'result_bank' => $result_bank];
    }

    public static function getInfoByPaymentMethodId($payment_method_id, $partner_payment_id)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id AND status = :status ", "id" => $payment_method_id, "status" => PaymentMethod::STATUS_ACTIVE]);
        if ($payment_method_info != false) {
            $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["id = :id AND status = :status ", "id" => $partner_payment_id, "status" => \common\models\db\PartnerPayment::STATUS_ACTIVE]);
            if ($partner_payment_info != false) {
                $method_info = Tables::selectOneDataTable("method", ["id IN (SELECT method_id FROM method_payment_method WHERE payment_method_id = :payment_method_id) AND status = :status", "payment_method_id" => $payment_method_info['id'], "status" => \common\models\db\Method::STATUS_ACTIVE]);
                if ($method_info != false) {
                    $payment_method_info['partner_payment_id'] = $partner_payment_info['id'];
                    $payment_method_info['partner_payment_code'] = $partner_payment_info['code'];
                    $payment_method_info['method_code'] = $method_info['code'];
                    return $payment_method_info;
                }
            }
        }
        return false;
    }

    public static function getInfoByPaymentTransactionInfo($payment_transaction_info)
    {
        $sql = "SELECT method.code AS method_code, payment_method.*, partner_payment_method.partner_payment_code, partner_payment_method.partner_payment_id "
            . "FROM (payment_method INNER JOIN (method_payment_method INNER JOIN method ON method_payment_method.method_id = method.id) ON payment_method.id = method_payment_method.payment_method_id) INNER JOIN partner_payment_method ON payment_method.id = partner_payment_method.payment_method_id "
            . "WHERE payment_method.id = " . $payment_transaction_info['payment_method_id'] . " AND partner_payment_method.partner_payment_id = " . $payment_transaction_info['partner_payment_id'] . " ";
        $command = Yii::$app->db->createCommand($sql);
        $data = $command->queryOne();
        return $data;
    }

    public static function getInfoByPaymentMethodAndPartnerPayment($payment_method_id, $partner_payment_id)
    {
        $sql = "SELECT method.code AS method_code, payment_method.*, partner_payment_method.partner_payment_code, partner_payment_method.partner_payment_id "
            . "FROM (payment_method INNER JOIN (method_payment_method INNER JOIN method ON method_payment_method.method_id = method.id) ON payment_method.id = method_payment_method.payment_method_id) INNER JOIN partner_payment_method ON payment_method.id = partner_payment_method.payment_method_id "
            . "WHERE payment_method.id = " . $payment_method_id . " AND partner_payment_method.partner_payment_id = " . $partner_payment_id . " ";
        $command = Yii::$app->db->createCommand($sql);
        $data = $command->queryOne();
        return $data;
    }

    public static function getCodeById($payment_method_id, &$payment_method_info = false)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", "id = $payment_method_id ");
        if ($payment_method_info != false) {
            return $payment_method_info['code'];
        }
        return false;
    }

    public static function getById($id)
    {
        return PaymentMethod::findOne(['id' => $id]);
    }

    /**
     *
     * @param type $params : transaction_type_id, bank_id, method_id, name, image, description, min_amount, config, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethod::getDb()->beginTransaction();
        }
        $bank_info = Tables::selectOneDataTable("bank", ["id = :id AND status = :status", "id" => $params['bank_id'], "status" => \common\models\db\Bank::STATUS_ACTIVE]);
        if ($bank_info != false) {
            $method_info = Tables::selectOneDataTable("method", ["id = :id AND status = :status", "id" => $params['method_id'], 'status' => \common\models\db\Method::STATUS_ACTIVE]);
            if ($method_info != false) {
                $code = $bank_info['code'] . '-' . $method_info['code'];
                $check = Tables::selectOneDataTable("payment_method", ["code = :code", "code" => $code]);
                if ($check) {
                    $error_message = 'Phương thức thanh toán đã tồn tại';

                } else {
                    $model = new PaymentMethod();
                    $model->transaction_type_id = $method_info['transaction_type_id'];
                    $model->bank_id = $bank_info['id'];
                    $model->code = $code;
                    $model->name = $params['name'];
                    $model->image = $params['image'];
                    $model->min_amount = $params['min_amount'];
                    $model->description = $params['description'];
                    $model->config = $params['config'];
                    $model->status = PaymentMethod::STATUS_ACTIVE;
                    $model->time_created = time();
                    $model->time_updated = time();
                    $model->user_created = $params['user_id'];
                    if ($model->validate()) {
                        if ($model->save()) {
                            $id = $model->getDb()->getLastInsertID();
                            //------
                            $model_method = new \common\models\db\MethodPaymentMethod();
                            $model_method->method_id = $method_info['id'];
                            $model_method->payment_method_id = $id;
                            $model_method->time_created = time();
                            $model_method->user_created = $params['user_id'];
                            if ($model_method->validate() && $model_method->save()) {
                                $commit = true;
                                $error_message = '';
                            } else {
                                $error_message = 'Có lỗi khi thêm phương thức thanh toán';
                            }
                        } else {
                            $error_message = 'Có lỗi khi thêm phương thức thanh toán';
                        }
                    } else {
                        $error_message = 'Tham số đầu vào không hợp lệ';
                    }
                }

            } else {
                $error_message = 'Nhóm phương thức không hợp lệ';
            }
        } else {
            $error_message = 'Ngân hàng không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : name, config, min_amount, description
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethod::getDb()->beginTransaction();
        }
        $model = PaymentMethod::findOne(['id' => $params['id']]);
        if ($model) {
            $model->name = $params['name'];
            $model->min_amount = $params['min_amount'];
            $model->description = $params['description'];
            $model->config = $params['config'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Phương thức thanh toán không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    public static function getPaymentMethodName($payment_method_id)
    {
        $payment_method = PaymentMethod::findOne(['id' => $payment_method_id]);
        if ($payment_method != false && $payment_method->name) {
            return $payment_method->name;
        }

        return '';

    }

}
