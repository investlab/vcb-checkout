<?php
namespace common\components\libs;

use common\models\business\ProductComboRuleBusiness;
use common\models\db\ProductArea;
use common\models\db\ProductCombo;
use Yii;
use common\components\libs\Tables;
use common\models\db\Product;

class Cart
{

    private static $items = null;
    private static $buyerInfo = null;

    public static function getItems()
    {
        self::load();
        return self::$items;
    }

    public static function getFirstItem()
    {
        self::load();
        if (self::$items != null) {
            foreach (self::$items as $item) {
                return $item;
            }
        }
        return false;
    }

    public static function getBuyerInfo()
    {
        self::load();
        return self::$buyerInfo;
    }

    public static function emptyItem()
    {
        self::load();
        self::$items = null;
        self::save();
        return true;
    }

    public static function addItem($item_id, $quantity, &$product_info = false, $combo_id = 0, &$combo_info = false)
    {
        $combo_info = Tables::selectOneDataTable("product_combo", "id = $combo_id AND status = " . ProductCombo::STATUS_ACTIVE);
        $product_info = Tables::selectOneDataTable("product", "id = $item_id AND status = " . Product::STATUS_ACTIVE . " AND publish = " . Product::PUBLISH);
        $session_zone_id = ProductArea::getSessionProductZone('id');
        $check_zone = false;
        $check_product = false;
        $check_has_product = false;

        if ($product_info != false) {
            self::load();
            Product::setRow($product_info);
            if ($session_zone_id != 0) {
                $check_zone = true;
                if (Product::hasSupportForZone($product_info, $session_zone_id, $product_quantity)) {
                    $check_has_product = true;
                    if ($product_quantity > 0) {
                        if (Product::hasBuyPriceForZone($product_info, $session_zone_id)) {
                            $check_product = true;
                        }
                    }
                }
            }

            if ($check_zone == false || $check_has_product == false || $check_product == false) {
                return false;
            }

            if (isset(self::$items[$item_id])) {
                self::$items[$item_id]['quantity'] += $quantity;
                self::$items[$item_id]['product_quantity'] += $quantity;
                self::$items[$item_id]['amount_payment'] += self::$items[$item_id]['buy_price'] * $quantity;
            } else {
                self::$items[$item_id] = $product_info;
                self::$items[$item_id]['quantity'] = $quantity;
                self::$items[$item_id]['product_quantity'] = $quantity;
                self::$items[$item_id]['amount_payment'] = self::$items[$item_id]['buy_price'] * $quantity;
            }

            self::save();
            return true;
        }
        return false;
    }

    public static function updateQuantity($item_id, $quantity)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            self::$items[$item_id]['quantity'] = $quantity;
            self::$items[$item_id]['product_quantity'] = $quantity;
            self::$items[$item_id]['amount_payment'] = self::$items[$item_id]['buy_price'] * $quantity;
            self::save();
            return true;
        }
        return false;
    }

    public static function plusQuantity($item_id)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            self::$items[$item_id]['quantity']++;
            self::$items[$item_id]['product_quantity']++;
            self::$items[$item_id]['amount_payment'] = self::$items[$item_id]['buy_price'] * self::$items[$item_id]['quantity'];
            self::save();
            return true;
        }
        return false;
    }

    public static function minusQuantity($item_id)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            self::$items[$item_id]['quantity']--;
            if (self::$items[$item_id]['quantity'] <= 0) {
                self::$items[$item_id]['quantity'] = 1;
                self::$items[$item_id]['product_quantity'] = 1;
                self::$items[$item_id]['amount_payment'] = self::$items[$item_id]['buy_price'] * self::$items[$item_id]['quantity'];
            }
            self::save();
            return true;
        }
        return false;
    }

    public static function getAmountpayment($item_id)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            return self::$items[$item_id]['amount_payment'];
        }
        return false;
    }

    public static function getTotalAmountPay()
    {
        self::load();
        $total_amount_payment = 0;
        foreach (self::$items as $key => $data) {
            $total_amount_payment += $data['amount_payment'];
        }
        return $total_amount_payment;
    }

    public static function removeItem($item_id)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            unset(self::$items[$item_id]);
            self::save();
            return true;
        }
        return false;
    }

    public static function updateBuyerInfo($buyer_info)
    {
        self::load();
        self::$buyerInfo = $buyer_info;
        self::save();
        return true;
    }

    public static function clear()
    {
        $session = Yii::$app->session;
        $session->set('CART', array(
            'items' => null,
            'buyer_info' => null,
        ));
    }

    public static function load()
    {
        $session = Yii::$app->session;
        $cart = $session->get('CART');
        self::$items = @$cart['items'];
        self::$buyerInfo = @$cart['buyer_info'];
    }

    public static function save()
    {
        $session = Yii::$app->session;
        $session->set('CART', array(
            'items' => self::$items,
            'buyer_info' => self::$buyerInfo,
        ));
        self::clearGiftCode();
    }

    public static function clearGiftCode()
    {
        $session = Yii::$app->session;
        $session->set('GIFT', array(
            'amount' => 0,
            'gift_code' => '',
            'buyer_fullname' => '',
            'buyer_email' => '',
            'buyer_mobile' => '',
            'buyer_address' => '',
            'buyer_zone_id' => 0,
        ));
    }

    /**
     *
     * @ci_buy_price : id, buy_price, quantity
     */
    public static function getComboData($ci_buy_price, $data = [])
    {
        $cart_item_ids = [];
        foreach ($ci_buy_price as $kBP => $vBP) {
            $cart_item_ids[] = $vBP['id'];
        }

        $product_combos = ProductComboRuleBusiness::getProductComboByProductId($cart_item_ids);

        foreach ($product_combos as $k => $v) {
            $check = self::checkCombo($v, $ci_buy_price);
            if ($check == false) {
                unset($product_combos[$k]);
            } else {
                $product_combos[$k]['amount'] = $check;
            }
        }
        foreach ($product_combos as $k1 => $v1) {
            if (!isset($data[$v1['id']])) {
                $data[$v1['id']] = [];
                $data[$v1['id']]['name'] = $v1['name'];
                $data[$v1['id']]['amount'] = $v1['amount'];
            } else {
                $data[$v1['id']]['amount'] += $v1['amount'];
            }
        }
        if ($ci_buy_price != null && $product_combos != null) {
            return self::getComboData($ci_buy_price, $data);
        }

        if ($data != null) {
            $value = [
                'isCombo' => true,
                'combo' => $data
            ];
        } else {
            $value = [
                'isCombo' => false,
                'combo' => []
            ];
        }

        return $value;
    }

    public static function checkCombo($product_combo, &$ci_buy_price, &$combo_price = 0)
    {
        if (!empty($product_combo['combo_rule'])) {
            $temp_products = $ci_buy_price;
            foreach ($product_combo['combo_rule'] as $row) {
                if (isset($ci_buy_price[$row['product_id']]) && !empty($ci_buy_price[$row['product_id']]) && $ci_buy_price[$row['product_id']]['quantity'] >= $row['product_quantity']) {
                    $combo_price += $ci_buy_price[$row['product_id']]['buy_price'] * $row['product_quantity'];

                    $ci_buy_price[$row['product_id']]['quantity'] = $ci_buy_price[$row['product_id']]['quantity'] - $row['product_quantity'];
                    if ($ci_buy_price[$row['product_id']]['quantity'] == 0) {
                        unset($ci_buy_price[$row['product_id']]);
                    }
                } else {
                    $ci_buy_price = $temp_products;
                    return false;
                }
            }
            $combo_discount = 0;
            if ($product_combo['discount_amount'] > 0) {
                $combo_discount = $product_combo['discount_amount'];
            } elseif ($product_combo['discount_percentage'] > 0) {
                $combo_discount = round($combo_price * $product_combo['discount_percentage'] / 100, 0);
            }
            return $combo_discount;
        }
        return false;
    }
}

