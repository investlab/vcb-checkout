<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "payment_method".
 *
 * @property integer $id
 * @property integer $transaction_type_id
 * @property integer $bank_id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property string $image
 * @property double $min_amount
 * @property double $max_amount
 * @property string $enviroment
 * @property string $config
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PaymentMethod extends MyActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_method';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'config'], 'string'],
            [['min_amount', 'max_amount'], 'number'],
            [['transaction_type_id', 'bank_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'image', 'enviroment'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaction_type_id' => 'transaction_type_id',
            'bank_id' => 'bank_id',
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'image' => 'Image',
            'min_amount' => 'Min Amount',
            'max_amount' => 'Max Amount',
            'enviroment' => 'Enviroment',
            'config' => 'Config',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getModelFormName($partner_code, $method_code, $payment_method_code)
    {
        $partner_code_path = str_replace('-', '_', strtolower($partner_code));
        $path_class = 'common\payment_methods\\' . $partner_code_path . '\\';
        $path_file = ROOT_PATH . DS . 'common' . DS . 'payment_methods' . DS . $partner_code_path . DS;
        $class_name = self::_getClassName($partner_code, 'PaymentMethod' . self::_getClassName($method_code)) . self::_getClassName($payment_method_code) . 'Form';
        //echo $class_name.'<br>';
        $class_file = $class_name . '.php';
        $debug = Yii::$app->request->get("debug");
        $payment_method_code_debug = Yii::$app->request->get("payment_method_code_debug");

        if (YII_DEBUG && $debug == 'QuangNT' && !file_exists($path_file . $class_file) && $payment_method_code_debug == $payment_method_code) {
            echo "<pre>";
            var_dump("argument list");
            var_dump(func_get_args());
            var_dump("file_exists");
            var_dump($path_file . $class_file);
            var_dump(file_exists($path_file . $class_file));
            var_dump("REturn if true");
            var_dump($path_class . $class_name);
            die();
        }
        if (file_exists($path_file . $class_file)) {
            return $path_class . $class_name;
        } else {
            $class_name = self::_getClassName($partner_code, 'PaymentMethod' . self::_getClassName($method_code)) . 'Form';
            $class_file = $class_name . '.php';
            //echo $path_file.$class_file.'<br>';
            if ($debug == "QuangNT1") {
                echo "<pre>";
                var_dump("Return else true");
                var_dump(file_exists($path_file . $class_file));
                var_dump($path_file . $class_file);
                die();
            }
            if (file_exists($path_file . $class_file)) {
                return $path_class . $class_name;
            }
        }
        return false;
    }

    private static function _getClassName($code, $result = '')
    {
        $code = trim(strtolower($code));
        $temp = explode('-', $code);
        foreach ($temp as $item) {
            $result .= ucfirst($item);
        }
        return $result;
    }

    public static function hasSupportInstallment($payment_method_id)
    {
        return Tables::selectOneDataTable("method_payment_method", "payment_method_id = $payment_method_id AND method_id IN (SELECT method.id FROM method WHERE method.code = 'INSTALLMENT') ");
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa'
        );
    }

    public static function getPaymentMethodLimitForProduct($product_id)
    {
        $product_info = Tables::selectOneDataTable("product", "id = " . $product_id);
        if ($product_info != false) {
            $payment_method_rule_info = Tables::selectAllDataTable("payment_method_rule", "payment_method_rule_type_id IN (SELECT id FROM payment_method_rule_type WHERE code = 'PRODUCT_ID') "
                . "AND ("
                . "(id IN (SELECT payment_method_rule_id FROM payment_method_rule_value WHERE value = '$product_id') AND `option` = " . PaymentMethodRule::TERM_OUT . ") "
                . "OR "
                . "(id NOT IN (SELECT payment_method_rule_id FROM payment_method_rule_value WHERE value = '$product_id') AND `option` = " . PaymentMethodRule::TERM_IN . ") "
                . ") "
                . "AND status = " . PaymentMethodRule::STATUS_ACTIVE . " ");
            if ($payment_method_rule_info != false) {
                $payment_method_ids = array();
                foreach ($payment_method_rule_info as $row) {
                    $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
                }
                $payment_method_info = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") AND status = " . PaymentMethod::STATUS_ACTIVE);
                if ($payment_method_info != false) {
                    return $payment_method_info;
                }
            }
        }
        return false;
    }

    public static function checkPaymentMethodIdForSaleOrder($payment_method_id, $sale_order_id, $time_request)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", "id = '$payment_method_id' AND status = " . PaymentMethod::STATUS_ACTIVE);
        if ($payment_method_info != false) {
            $sale_order_info = Tables::selectOneDataTable("bill", "id = " . $sale_order_id);
            if ($sale_order_info != false) {
                $buyer_info = self::_getBuyerInfoBySaleOrder($sale_order_info);
                $product_list = self::_getProductListBySalerOrder($sale_order_info);
                foreach ($product_list as $row) {
                    $accept_product_ids[$row['product_id']] = $row['product_id'];
                }
                if (self::validateRules($payment_method_info, $buyer_info, $product_list, $time_request, $accept_product_ids)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function validateRules($payment_method_info, $buyer_info, $product_list, $time_request, &$accept_product_ids = array())
    {
        $payment_method_rule_info = Tables::selectAllDataTable("payment_method_rule", "payment_method_id = " . $payment_method_info['id'] . " AND status = " . PaymentMethodRule::STATUS_ACTIVE);
        if ($payment_method_rule_info != false) {
            foreach ($payment_method_rule_info as $rule) {
                if (!PaymentMethodRule::validateRule($rule, $buyer_info, $product_list, $time_request, $accept_product_ids)) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function _getBuyerInfoBySaleOrder($sale_order_info)
    {
        $buyer_info = array(
            'customer_id' => $sale_order_info['customer_id'],
            'buyer_fullname' => $sale_order_info['buyer_fullname'],
            'buyer_email' => $sale_order_info['buyer_email'],
            'buyer_mobile' => $sale_order_info['buyer_mobile'],
            'buyer_address' => $sale_order_info['buyer_address'],
            'buyer_zone_id' => $sale_order_info['buyer_zone_id'],
        );
        return $buyer_info;
    }

    private static function _getProductListBySalerOrder($sale_order_info)
    {
        $product_list = array();
        $bill_item_info = Tables::selectAllDataTable("bill_item", "bill_id = " . $sale_order_info['id'] . " ");
        if ($bill_item_info != false) {
            $product_ids = array();
            foreach ($bill_item_info as $row) {
                $product_ids[$row['product_id']] = $row['product_id'];
            }
            $product_info = Tables::selectAllDataTable("product", "id IN (" . implode(',', $product_ids) . ") ", "", "id");
            foreach ($bill_item_info as $row) {
                $product_list[] = array(
                    'product_id' => $row['product_id'],
                    'product_quantity' => $row['product_quantity'],
                    'product_category_id' => @$product_info[$row['product_id']]['product_category_id'],
                    'producer_id' => @$product_info[$row['product_id']]['producer_id'],
                    'amount' => $row['product_price'] - $row['product_discount'],
                );
            }
        }
        return $product_list;
    }

    public static function getProductListTotalAmount($product_list)
    {
        $total_amount = 0;
        foreach ($product_list as $product) {
            $total_amount += $product['amount'] * $product['product_quantity'];
        }
        return $total_amount;
    }

    public static function getRuleDescriptions($payment_method_id)
    {
        $result = array();
        $payment_method_rule_info = Tables::selectAllDataTable("payment_method_rule", "payment_method_id = " . $payment_method_id . " AND status = " . PaymentMethodRule::STATUS_ACTIVE);
        if ($payment_method_rule_info != false) {
            foreach ($payment_method_rule_info as $rule) {
                $result[] = PaymentMethodRule::getDescription($rule);
            }
        }
        return $result;
    }

    public static function getPaymentMethodIdByCode($code)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :code ", 'code' => $code]);
        if ($payment_method_info != false) {
            return $payment_method_info['id'];
        }
        return false;
    }

    public static function getMethodIdByPaymentMethodId($payment_method_id)
    {
        $method_payment_method_info = Tables::selectOneDataTable("method_payment_method", ["payment_method_id = :payment_method_id", "payment_method_id" => $payment_method_id]);
        if ($method_payment_method_info != false) {
            return $method_payment_method_info['method_id'];
        }
        return false;
    }

    public static function getPaymentMethodById($payment_method_id)
    {
        $method_payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $payment_method_id]);
        if ($method_payment_method_info != false) {
            return $method_payment_method_info;
        }
        return false;
    }

    public static function getPaymentMethodIdActiveByCode($code) {
        $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :code and status = :status", 'code' => $code, 'status' => self::STATUS_ACTIVE]);
        if ($payment_method_info != false) {
            return $payment_method_info['id'];
        }
        return false;
    }

    public static function isQrPayment($payment_method_code){
        if(stripos($payment_method_code, 'QR')){
            return true;
        }
        return false;
    }
}
