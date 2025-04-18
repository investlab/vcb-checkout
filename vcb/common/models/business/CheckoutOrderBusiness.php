<?php

namespace common\models\business;

use checkout\controllers\Version_1_0Controller;
use common\components\libs\NotifySystem;
use common\components\utils\Logs;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\db\CheckoutOrderCallback;
use common\models\db\CurrencyExchange;
use common\models\db\MerchantInstallmentFeeOnlineV2;
use common\payments\VCB;
use Yii;
use common\models\db\CheckoutOrder;
use common\components\libs\Tables;
use common\models\db\PaymentTransaction;
use common\models\db\Bill;
use common\models\db\Merchant;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;

class CheckoutOrderBusiness
{

    /**
     *
     * @param type $params : version, language_id, merchant_id, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function checkOrderByTransaction($params)
    {
        $error_message = 'Lỗi không xác định';
        $error_code = '-100';
        $transaction_id_pay_gate = '';
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE, 'id' => $params['merchant_id']]);
        if ($merchant_info != false) {
            $result = Tables::selectOneDataTable("transaction", ["bank_refer_code = ':bank_refer_code'", "bank_refer_code" => $params['transaction_id']]);
            if ($result) {
                $error_message = 'Giao địch dã tồn tại trên hệ thống';
                $error_code = '004';
                $transaction_id_pay_gate = $result['id'];
            } else {
                $error_message = '';
                $error_code = '000';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
            $error_code = '003';
        }
        return array(
            'error_message' => $error_message,
            'error_code' => $error_code,
            'transaction_id_pay_gate' => $transaction_id_pay_gate,
        );
    }

    static function addAndRequestPayment($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $checkout_order_id = null;
        $transaction_id = null;
        $token_code = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE, 'id' => $params['merchant_id']]);
        if ($merchant_info != false) {
            $inputs = array(
                'version' => $params['version'],
                'language_id' => $params['language_id'],
                'merchant_id' => $params['merchant_id'],
                'order_code' => $params['order_code'],
                'order_description' => $params['order_description'],
                'amount' => $params['amount'],
                'currency' => $params['currency'],
                'return_url' => $params['return_url'],
                'cancel_url' => $params['cancel_url'],
                'notify_url' => $params['notify_url'],
                'time_limit' => $params['time_limit'],
                'buyer_fullname' => $params['buyer_fullname'],
                'buyer_mobile' => $params['buyer_mobile'],
                'buyer_email' => $params['buyer_email'],
                'buyer_address' => $params['buyer_address'],
                'user_id' => $params['user_id'],
            );

            if (!empty($params['time_created'])) {
                $inputs['time_created'] = $params['time_created'];
            }

            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $checkout_order_id = $result['id'];
                $token_code = $result['token_code'];
                $inputs = array(
                    'checkout_order_id' => $checkout_order_id,
                    'payment_method_id' => $params['payment_method_id'],
                    'partner_payment_id' => $params['partner_payment_id'],
                    'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                    'user_id' => $params['user_id'],
                );
                $result = self::requestPayment($inputs, false);
                if ($result['error_message'] == '') {
                    $transaction_id = $result['transaction_id'];
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message,
            'checkout_order_id' => $checkout_order_id,
            'transaction_id' => $transaction_id,
            'token_code' => $token_code
        );
    }

    //Áp dụng cho các giao dịch được đồng bộ từ Ngân lượng về cổng
    static function addAndRequestPaymentForCallBack($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $checkout_order_id = null;
        $transaction_id = null;
        $token_code = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE, 'id' => $params['merchant_id']]);
        if ($merchant_info != false) {
            $inputs = array(
                'version' => $params['version'],
                'language_id' => $params['language_id'],
                'merchant_id' => $params['merchant_id'],
                'order_code' => $params['order_code'],
                'order_description' => $params['order_description'],
                'amount' => $params['amount'],
                'currency' => $params['currency'],
                'return_url' => $params['return_url'],
                'cancel_url' => $params['cancel_url'],
                'notify_url' => $params['notify_url'],
                'time_limit' => $params['time_limit'],
                'buyer_fullname' => $params['buyer_fullname'],
                'buyer_mobile' => $params['buyer_mobile'],
                'buyer_email' => $params['buyer_email'],
                'buyer_address' => $params['buyer_address'],
                'user_id' => $params['user_id'],
            );

            if (!empty($params['time_created'])) {
                $inputs['time_created'] = $params['time_created'];
            }
            if (isset($params['orginal_amount'])) {
                $inputs['orginal_amount'] = $params['orginal_amount'];
            }

            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $checkout_order_id = $result['id'];
                $token_code = $result['token_code'];
                $inputs = array(
                    'checkout_order_id' => $checkout_order_id,
                    'payment_method_id' => $params['payment_method_id'],
                    'partner_payment_id' => $params['partner_payment_id'],
                    'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                    'user_id' => $params['user_id'],
                    'time_created' => isset($params['time_created']) ? $params['time_created'] : '',
                );
                $result = self::requestPaymentForCallBack($inputs, false);
                if ($result['error_message'] == '') {
                    $transaction_id = $result['transaction_id'];
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message,
            'checkout_order_id' => $checkout_order_id,
            'transaction_id' => $transaction_id,
            'token_code' => $token_code
        );
    }

    /**
     *
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function requestPayment($params, $rollback = true)
    {
        //TODO tạo đơn hàng.
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }

        $version = $params['version'] ?? 1;
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_NEW . " ", array('checkout_order_id' => intval($params['checkout_order_id'])))->one();
        if ($model != null) {
            $card_owner_percent_instalment_fee = 0;
            if ($version == 3) {
                if (isset($params['installment_fee_bearer']) && isset($params['installment_fee']) && isset($params['payment_fee'])) {
                    $amount_order = $params['payment_amount'];
                    $sender_flat_fee = $params['payment_fee']['sender_flat_fee'];
                    $sender_percent_fee = $params['payment_fee']['sender_percent_fee'];
                    $card_owner_percent_instalment_fee = $params['installment_fee']['card_owner_percent_fee'];
                    $card_owner_flat_instalment_fee = $params['installment_fee']['card_owner_fixed_fee'];
                    $merchant_percent_instalment_fee = $params['installment_fee']['merchant_percent_fee'];
                    $merchant_flat_instalment_fee = $params['installment_fee']['merchant_fixed_fee'];
                    switch ($params['installment_fee_bearer']) {
                        case MerchantInstallmentFeeOnlineV2::FEE_BEARER_MERCHANT:
                            //Xử lý lại phí cho merchant
                            $installment_fee_buyer = 0;
                            $result_installment_fee = CheckoutOrder::getInstallmentFeeMerchantVer3(
                                $amount_order,
                                $sender_flat_fee,
                                $sender_percent_fee,
                                $merchant_percent_instalment_fee,
                                $merchant_flat_instalment_fee,
                                $card_owner_flat_instalment_fee,
                                $card_owner_percent_instalment_fee
                            );
                            $installment_fee_merchant = $result_installment_fee['amount_fee'];
                            break;
                        case MerchantInstallmentFeeOnlineV2::FEE_BEARER_CARD_OWNER:
                            //Xử lý lại phí cho card owner
                            $installment_fee_merchant = 0;
                            $result_installment_fee = CheckoutOrder::getInstallmentFeeCardOwnerVer3(
                                $amount_order,
                                $sender_percent_fee,
                                $card_owner_flat_instalment_fee,
                                $card_owner_percent_instalment_fee
                            );

                            $installment_fee_buyer = $result_installment_fee['amount_fee'];
                            break;
                        case MerchantInstallmentFeeOnlineV2::FEE_BEARER_BOTH:
                            //Xử lý lại phí cho card owner && merchant
                            $result_installment_fee = CheckoutOrder::getInstallmentFeeCardOwnerVer3(
                                $amount_order,
                                $sender_percent_fee,
                                $card_owner_flat_instalment_fee,
                                $card_owner_percent_instalment_fee
                            );
                            $installment_fee_buyer = $result_installment_fee['amount_fee'];

                            $result_installment_fee = CheckoutOrder::getInstallmentFeeMerchantVer3(
                                $amount_order,
                                $sender_flat_fee,
                                $sender_percent_fee,
                                $merchant_percent_instalment_fee,
                                $merchant_flat_instalment_fee,
                                $card_owner_flat_instalment_fee,
                                $card_owner_percent_instalment_fee
                            );
                            $installment_fee_merchant = $result_installment_fee['amount_fee'];
                            break;
                    }
                }
            } else {
                if (isset($params['installment_fee_bearer']) && isset($params['installment_fee'])) {
                    switch ($params['installment_fee_bearer']) {
                        case 1:
                            $installment_fee_merchant = $model->amount * (float)$params['installment_fee'] / 100;
                            $installment_fee_buyer = 0;
                            break;
                        case 2:
                            $installment_fee_merchant = 0;
                            $installment_fee_buyer = $model->amount * (float)$params['installment_fee'] / 100;
                            break;
                        case 3:
                            $installment_fee_merchant = ($model->amount * ((float)$params['installment_fee'] / 2)) / 100;
                            $installment_fee_buyer = ($model->amount * ((float)$params['installment_fee'] / 2)) / 100;
                            break;
                    }
                }
            }

            if (isset($params['installment_mpos'])) {
                $installment_fee_merchant = $params['installment_fee_merchant'];
                $installment_fee_buyer = $params['installment_fee_buyer'];
                $total_buyer_amount = $model->amount + $installment_fee_buyer;
                $roundedValue = ceil($total_buyer_amount / 1000) * 1000;
                // Tính số tiền chênh lệch
                $installment_fee_buyer = $installment_fee_buyer + ($roundedValue - $total_buyer_amount);
            }
            //TODO lưu thong tin client
            /** @var $model CheckoutOrder */
            $info = [
                'ip_address' => get_client_ip(),
                'user_agent' => "fix",
                'controller_action' => (!empty(Yii::$app->controller->id) && !empty(Yii::$app->controller->action->id)) ? (Yii::$app->controller->id . '/' . Yii::$app->controller->action->id) : ''
            ];
            $model->client_info = json_encode($info);

            $inputs = array(
                'version' => $version,
                'checkout_order_id' => $model->id,
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $model->amount,
                'currency' => $model->currency,
                'user_id' => $params['user_id'],
                'installment_conversion' => isset($params['installment_conversion']) ? $params['installment_conversion'] : "",
                'installment_fee' => isset($params['installment_fee']) ? (float)$params['installment_fee'] : "",
                'installment_fee_merchant' => isset($installment_fee_merchant) ? (float)$installment_fee_merchant : '',
                'installment_fee_buyer' => isset($installment_fee_buyer) ? (float)$installment_fee_buyer : '',
                'card_owner_percent_instalment_fee' => isset($card_owner_percent_instalment_fee) ? (float)$card_owner_percent_instalment_fee : '',
            );
            //TODO cần chuyền card_owner percent.


            if (!empty($params['transaction_type_id']))
                $inputs['transaction_type_id'] = $params['transaction_type_id'];
            $result = TransactionBusiness::addPaymentTransaction($inputs, false);
            if ($result['error_message'] == '') {
                $transaction_id = $result['id'];
                $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
                if ($transaction_info != false) {
                    //-------
                    $model->transaction_id = $transaction_id;
                    $model->cashin_amount = $model->amount + $transaction_info['sender_fee'] + (int)(isset($transaction_info['installment_fee_buyer']) ? $transaction_info['installment_fee_buyer'] : 0);
                    $model->cashout_amount = $model->amount - $transaction_info['receiver_fee'] - $transaction_info['partner_payment_sender_fee'] - (int)(isset($transaction_info['installment_fee_merchant']) ? $transaction_info['installment_fee_merchant'] : 0);
                    $model->time_updated = time();
                    $model->user_updated = $params['user_id'];
                    $model->sender_fee = $transaction_info['sender_fee'];
                    $model->receiver_fee = $transaction_info['receiver_fee'];
                    if ($model->validate() && $model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
                    }
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại hoặc trạng thái không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'transaction_id' => $transaction_id);
    }

    //Áp dụng cho giao dịch được đồng bộ từ Ngân lượng về cổng
    static function requestPaymentForCallBack($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_NEW . " ", array('checkout_order_id' => intval($params['checkout_order_id'])))->one();
        if ($model != null) {
            $inputs = array(
                'checkout_order_id' => $model->id,
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $model->amount,
                'currency' => $model->currency,
                'user_id' => $params['user_id'],
                'time_created' => $params['time_created'],
            );
            if (!empty($params['transaction_type_id']))
                $inputs['transaction_type_id'] = $params['transaction_type_id'];
            $result = TransactionBusiness::addPaymentTransactionForCallBack($inputs, false);
            if ($result['error_message'] == '') {
                $transaction_id = $result['id'];
                $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
                if ($transaction_info != false) {
                    //-------
                    $model->transaction_id = $transaction_id;
                    $model->cashin_amount = $model->amount + $transaction_info['sender_fee'];
                    $model->cashout_amount = $model->amount - $transaction_info['receiver_fee'];
                    $model->time_updated = time();
                    $model->user_updated = $params['user_id'];
                    if ($model->validate() && $model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
                    }
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại hoặc trạng thái không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'transaction_id' => $transaction_id);
    }

    /**
     *
     * @param type $params : checkout_order_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelRequestPayment($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAYING . " ", array('checkout_order_id' => $params['checkout_order_id']))->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_CANCEL;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'transaction_id' => $model->transaction_id,
                    'reason_id' => $params['reason_id'],
                    'reason' => $params['reason'],
                    'user_id' => $params['user_id']
                );
                $result = TransactionBusiness::cancel($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelRequestPaymentV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN ("
            . CheckoutOrder::STATUS_PAYING . "," . CheckoutOrder::STATUS_NEW . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_CANCEL;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                if (!empty($model->transaction_id)) {
                    //Neu da ton tai transaction
                    $inputs = array(
                        'transaction_id' => $model->transaction_id,
                        'reason_id' => $params['reason_id'],
                        'reason' => $params['reason'],
                        'user_id' => $params['user_id']
                    );
                    $result = TransactionBusiness::cancel($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    //Neu chua ton tai transaction
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, receipt, bin_code, card_type, request_token, user_id
     * @param type $rollback
     * @return type
     */
    static function reviewRequestPayment($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $payment_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = :checkout_order_id AND status = " . CheckoutOrder::STATUS_PAYING . " ", array('checkout_order_id' => $params['checkout_order_id']))->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_REVIEW;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'id' => $model->transaction_id,
                    'receipt' => $params['receipt'],
                    'bin_code' => $params['bin_code'],
                    'card_type' => $params['card_type'],
                    'request_token' => $params['request_token'],
                    'user_id' => $params['user_id']
                );
                $result = TransactionBusiness::review($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : version, language_id, merchant_id, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $token_code = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE . " ", 'id' => $params['merchant_id']]);
        if ($merchant_info) {
            $model = new CheckoutOrder();
            $model->token_code = uniqid() . rand();
            $model->version = $params['version'];
            $model->language_id = $params['language_id'];
            $model->partner_id = $merchant_info['partner_id'];
            $model->merchant_id = $params['merchant_id'];
            $model->order_code = trim($params['order_code']);
            $model->order_description = $params['order_description'];
            $model->amount = $params['amount'];
            $model->currency = $params['currency'];
            $model->return_url = $params['return_url'];
            $model->cancel_url = $params['cancel_url'];
            $model->notify_url = $params['notify_url'];
            $model->time_limit = $params['time_limit'];
            $model->buyer_fullname = $params['buyer_fullname'];
            $model->buyer_email = $params['buyer_email'];
            $model->buyer_mobile = $params['buyer_mobile'];
            $model->buyer_address = $params['buyer_address'];
            $model->object_code = $params['object_code'] ?? '';
            $model->object_name = $params['object_name'] ?? '';
            $model->status = CheckoutOrder::STATUS_NEW;
            $model->callback_status = CheckoutOrder::CALLBACK_STATUS_NEW;
            $model->currency_exchange = $params['currency_exchange'] ?? '';
            $model->link_card = $params['link_card'] ?? 0;
            $model->customer_field = isset($params['customer_field']) && $params['customer_field'] != '' ? $params['customer_field'] : null;

            if (isset($params['seamless_info']) && $params['seamless_info'] != '') {
                $model->seamless_info = json_encode($params['seamless_info']);
            }
            if (!empty($params['time_created'])) {
                $model->time_created = $params['time_created'];
                $model->time_updated = $params['time_created'];
            } else {
                $model->time_created = time();
                $model->time_updated = time();
            }
            if (!empty($params['orginal_amount'])) {
                $model->orginal_amount = $params['orginal_amount'];
            }

            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $token_code = CheckoutOrder::getTokenCode($id);
                    $model->token_code = $token_code;
                    if ($model->save()) {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = 'Có lỗi khi thêm yêu cầu thanh toán';
                    }
                } else {
                    $error_message = 'Có lỗi khi thêm yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'token_code' => $token_code);
    }

    /**
     *
     * @param type $params : checkout_order_id, transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusPaying($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ") ")->one();
        if ($model != null) {
            $model->transaction_id = $params['transaction_id'];
            $model->status = CheckoutOrder::STATUS_PAYING;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, transaction_id, sender_fee, receiver_fee, time_paid, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusPaid($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_PAYING)->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'] - $params['partner_payment_sender_fee'];
            $model->status = CheckoutOrder::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
//            if (in_array($model->merchant_id,$GLOBALS['MERCHANT_XNC'] )){
//                $receipt_url = ReceiptBussiness::processMakeBillUrl($model,$model->order_code);
//                if ($receipt_url['error_message'] == 'Success'){
//                    $model->receipt_url = $receipt_url['url'];
//                }
//            }
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            @NotifySystem::send("ERROR ADD Callback transaction ID : " . $params['transaction_id'] . "|" . $result['error_message']);
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateStatusPaidHandle($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'])->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
//            $model->cashout_amount = $model->amount - $params['receiver_fee'] - $params['partner_payment_sender_fee'];
            $model->status = CheckoutOrder::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {

                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $params['checkout_order_id']]);

                        if ($checkout_order_callback == null) {
                            $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                            if ($result['error_message'] == '') {
                                $commit = true;
                                $error_message = '';
                            } else {
                                @NotifySystem::send("ERROR ADD Callback transaction ID : " . $params['transaction_id'] . "|" . $result['error_message']);
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $commit = true;
                            $error_message = '';
                        }

                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {

                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateStatusPaidVcbQrGateway($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_PAYING)->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'] - $params['partner_payment_sender_fee'];
            $model->status = CheckoutOrder::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $commit = true;
                        $error_message = '';
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, transaction_id, sender_fee, receiver_fee, time_paid, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusReview($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_PAYING)->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'];
            $model->status = CheckoutOrder::STATUS_REVIEW;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {

                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $result = CheckoutOrderCallbackBusiness::addReview($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateStatusInstallMent($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND (status = " . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " OR status = " . CheckoutOrder::STATUS_REVIEW . " )")->one();
        if ($model != null) {
            $notify_url = trim($model['notify_url']);
            $model->status = $params['status'];
            $model->time_paid = time();
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                            'status' => CheckoutOrder::STATUS_INSTALLMENT_WAIT,
                        );
                        $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }

        return array(
            'error_message' => $error_message
        );
    }

    static function updateStatusInstallMentPaid($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $notify_url = '';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND (status = " . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " OR status = " . CheckoutOrder::STATUS_REVIEW . " )")->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'];
            $model->status = CheckoutOrder::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            $model->installment_cycle = $params['month'];
            $model->installment_info = $params['installmet_info'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $params['checkout_order_id'], 'status' => CheckoutOrderCallback::STATUS_ERROR]);
                        if (!empty($checkout_order_callback)) {
                            $result = CheckoutOrderCallbackBusiness::updateStatus($inputs, false);
                        } else {
                            $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                        }

                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array(
            'error_message' => $error_message,
            'notify_url' => $notify_url,
        );
    }


    static function updateStatusInstallMentCancel($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_INSTALLMENT_WAIT)->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'];
            $model->status = CheckoutOrder::STATUS_CANCEL;

            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            $model->installment_cycle = $params['month'];
            $model->installment_info = $params['installmet_info'];
            if ($model->save()) {
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    public static function updateStatusAcceptReview($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_REVIEW)->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_INSTALLMENT_WAIT;
            $model->time_updated = time();
            if ($model->save()) {
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán cho thẻ review';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusCancel($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_INSTALLMENT_WAIT . "," . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_CANCEL;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }


    static function updateStatusWaitInstallment($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ")")->one();
        if ($model != null) {
            $notify_url = trim($model['notify_url']);
            $model->status = CheckoutOrder::STATUS_INSTALLMENT_WAIT;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            $model->installment_cycle = $params['month'];
            $model->installment_info = $params['payment_info'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }


    /**
     *
     * @param type $params : checkout_order_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCallbackStatusError($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " ")->one();
        if ($model != null) {
            $model->callback_status = CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCallbackStatusSuccess($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " ")->one();
        if ($model != null) {
            $model->callback_status = CheckoutOrder::CALLBACK_STATUS_SUCCESS;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : merchant_id, currency, time_begin, time_end, time_request, cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCashoutId($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order "
            . "SET cashout_id = " . $params['cashout_id'] . ", "
            . "status = " . CheckoutOrder::STATUS_WAIT_WIDTHDAW . ", "
            . "time_withdraw = " . $params['time_request'] . ", "
            . "user_withdraw = " . $params['user_id'] . " "
            . "WHERE merchant_id = " . $params['merchant_id'] . " "
            . "AND time_created >= " . $params['time_begin'] . " "
            . "AND time_created <= " . $params['time_end'] . " "
            . "AND currency = '" . $params['currency'] . "' "
            . "AND status = " . CheckoutOrder::STATUS_PAID . " "
            . "AND cashout_id = 0 ";
        $command = CheckoutOrder::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "INSERT INTO cashout_checkout_order(cashout_id, checkout_order_id, time_created, user_created) "
                . "SELECT cashout_id, id, " . $params['time_request'] . ", " . $params['user_id'] . " FROM checkout_order "
                . "WHERE cashout_id = " . $params['cashout_id'] . " "
                . "AND status = " . CheckoutOrder::STATUS_WAIT_WIDTHDAW;
            $command = CheckoutOrder::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật phiếu chi';
            }
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function removeCashoutId($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order "
            . "SET cashout_id = " . 0 . ", "
            . "status = " . CheckoutOrder::STATUS_PAID . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CheckoutOrder::STATUS_WAIT_WIDTHDAW . " ";
        $command = CheckoutOrder::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "DELETE FROM cashout_checkout_order WHERE cashout_id = " . $params['cashout_id'] . " ";
            $command = CheckoutOrder::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật phiếu chi';
            }
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusWithdrawByCashout($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order "
            . "SET status = " . CheckoutOrder::STATUS_WIDTHDAW . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CheckoutOrder::STATUS_WAIT_WIDTHDAW . " ";
        $command = CheckoutOrder::getDb()->createCommand($sql);
        if ($command->execute()) {
            $error_message = '';
            $commit = true;
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusWaitRefund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_PAID . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_WAIT_REFUND;
            $model->time_refund = time();
            $model->user_refund = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'payment_transaction_id' => $model->transaction_id,
                        'payment_method_id' => $params['payment_method_id'],
                        'partner_payment_id' => $params['partner_payment_id'],
                        'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                        'currency' => $model->currency,
                        'user_id' => $params['user_id'],
                    );
                    $result = TransactionBusiness::addRefundTransaction($inputs, false);
                    if ($result['error_message'] == '') {
                        $refund_transaction_id = $result['id'];
                        $model->refund_transaction_id = $refund_transaction_id;
                        if ($model->validate() && $model->save()) {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
    }

    /**
     *
     * @param type $params : checkout_order_id, time_paid,  bank_refer_code, receiver_fee, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusRefund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_WAIT_REFUND . ")")->one();
        if ($model != null) {
            $refund_transaction = CheckoutOrder::findBySql("select * from transaction "
                . "where refer_transaction_id = " . $model->transaction_id . " "
                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "and status = " . Transaction::STATUS_NEW)->asArray()->one();
            $transaction_payment = Transaction::findBySql("SELECT * FROM transaction WHERE id =" . $model['transaction_id'])->one();
            if (!is_null($refund_transaction)) {
                $refund_transaction_amount = doubleval($refund_transaction['amount']);
                $refund_rate = $refund_transaction_amount / (doubleval($model->amount + $model->sender_fee + ((!empty($transaction_payment['installment_fee_buyer']) && $transaction_payment['installment_conversion'] == 1) ? $transaction_payment['installment_fee_buyer'] : 0)));
                if ($refund_rate == 1) {
                    $model->status = CheckoutOrder::STATUS_REFUND;
                } else {
                    $model->status = CheckoutOrder::STATUS_REFUND_PARTIAL;
                }
                $model->time_updated = time();
                $model->user_updated = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $inputs = array(
                            'transaction_id' => $model->refund_transaction_id,
                            'time_paid' => $params['time_paid'],
                            'bank_refer_code' => $params['bank_refer_code'],
                            'partner_payment_receiver_fee' => $params['receiver_fee'],
                            'user_id' => $params['user_id'],
                            'refund_rate' => $refund_rate
                        );
                        $result = TransactionBusiness::paid($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                            if (!empty($model->refund_callback_url)) {
                                $params_callback = [
                                    'ref_code_refund' => isset($model->ref_code_refund) ? $model->ref_code_refund : '',
                                    'amount' => $refund_transaction['amount'],
                                    'transaction_status' => $refund_transaction['status'],
                                    'transaction_refund_id' => $refund_transaction['id'],
                                    'token_code' => $model->token_code,
                                    'checksum' => hash('sha256', $model->ref_code_refund . ' ' . $model->token_code . ' ' . $refund_transaction['id'] . ' ' . Merchant::getApiKey($model->merchant_id)),
                                ];

                                self::CallbackRefundToMerchant($params, $model->refund_callback_url);
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = 'Có lỗi khi cập nhật yêu cầu hoàn tiền';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            }
        } else {
            $error_message = 'Yêu cầu hoàn tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /** phuc vu hoan Onus Hasaki */
    static function updateStatusRefundV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_WAIT_REFUND . ")")->one();
        if ($model != null) {
            $refund_transaction = CheckoutOrder::findBySql("select * from transaction "
                . "where refer_transaction_id = " . $model->transaction_id . " "
                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "and status = " . Transaction::STATUS_NEW)->asArray()->one();
            if (!is_null($refund_transaction)) {
                $refund_transaction_amount = doubleval($refund_transaction['amount']);
                $refund_rate = $refund_transaction_amount / (doubleval($model->amount + $model->sender_fee));
                if ($refund_rate == 1) {
                    $model->status = CheckoutOrder::STATUS_REFUND;
                } else {
                    $model->status = CheckoutOrder::STATUS_REFUND_PARTIAL;
                }
                $model->time_updated = time();
                $model->user_updated = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $inputs = array(
                            'transaction_id' => $model->refund_transaction_id,
                            'time_paid' => $params['time_paid'],
                            'bank_refer_code' => $params['bank_refer_code'],
                            'partner_payment_receiver_fee' => $params['receiver_fee'],
                            'user_id' => $params['user_id'],
                            'refund_rate' => $refund_rate
                        );
                        $result = TransactionBusiness::paidOnusHasaki($inputs, false);
                        if ($result['error_message'] == '') {
                            self::writeRefundLog('[UPDATE_STATUS_REFUND][HASAKI]: TRANSACTION::PAID - CHECKOUT SUCCESS');
                            $commit = true;
                            $error_message = '';
                            if (!empty($model->refund_callback_url)) {
                                $params_callback = [
                                    'ref_code_refund' => isset($model->ref_code_refund) ? $model->ref_code_refund : '',
                                    'amount' => $refund_transaction['amount'],
                                    'transaction_status' => $refund_transaction['status'],
                                    'transaction_refund_id' => $refund_transaction['id'],
                                    'token_code' => $model->token_code,
                                    'checksum' => hash('sha256', $model->ref_code_refund . ' ' . $model->token_code . ' ' . $refund_transaction['id'] . ' ' . Merchant::getApiKey($model->merchant_id)),
                                ];

                                self::CallbackRefundToMerchant($params, $model->refund_callback_url);
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = 'Có lỗi khi cập nhật yêu cầu hoàn tiền';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            }
        } else {
            $error_message = 'Yêu cầu hoàn tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelWaitRefund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrder::STATUS_WAIT_REFUND . ")")->one();
        if ($model != null) {
            $refund_transaction = CheckoutOrder::findBySql("select * from transaction "
                . "where refer_transaction_id = " . $model->transaction_id . " "
                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "and status = " . Transaction::STATUS_NEW)->asArray()->one();
            if (!is_null($refund_transaction)) {
                $refund_transaction_amount = doubleval($refund_transaction['amount']);
                $refund_rate = $refund_transaction_amount / (doubleval($model->amount + $model->sender_fee));
                $model->status = CheckoutOrder::STATUS_PAID;
                $model->time_updated = time();
                $model->user_updated = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $inputs = array(
                            'transaction_id' => $model->refund_transaction_id,
                            'reason_id' => $params['reason_id'],
                            'reason' => $params['reason'],
                            'user_id' => $params['user_id'],
                            'refund_rate' => $refund_rate
                        );
                        $result = TransactionBusiness::cancel($inputs, false);
                        if ($result['error_message'] == '') {
                            $refund_transaction = CheckoutOrder::findBySql("select count(id) as count_refund_success from transaction "
                                . "where refer_transaction_id = " . $model->transaction_id . " "
                                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                                . "and status = " . Transaction::STATUS_PAID)->asArray()->one();
                            if (!is_null($refund_transaction) && $refund_transaction['count_refund_success'] > 0) {
                                $model->status = CheckoutOrder::STATUS_REFUND_PARTIAL;
                                $model->time_updated = time();
                                $model->user_updated = $params['user_id'];
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $commit = true;
                                        $error_message = '';
                                    } else {
                                        $error_message = 'Có lỗi khi hủy yêu cầu hoàn tiền';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $commit = true;
                                $error_message = '';
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = 'Có lỗi khi hủy yêu cầu hoàn tiền';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            }
        } else {
            $error_message = 'Yêu cầu hoàn tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
    }

    static function updateCheckoutOrderStatusReview($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_PAYING)->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_REVIEW;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            $model->installment_cycle = isset($params['month']) ? $params['month'] : '';
            $model->installment_info = isset($params['payment_info']) ? $params['payment_info'] : '';
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateCheckoutOrderStatusFailure($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . "   AND status IN (" . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_FAILURE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateCheckoutOrderStatusRevert_QrVCBGateway($params, $rollback = true)
    { // update thành công trên cổng trc, nếu MC trả về TB thì ms update lại cổng về TB
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_PAID)->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_REVERT;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    static function updateCheckoutOrderStatusFailureBCA($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . "   AND status IN (" . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_FAILURE;
            $model->time_updated = time();
//            $model->user_updated = $params['user_id'];
            $model->transaction_timeout = true;  // đối vs BCA
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }


    static function CallbackRefundToMerchant($params, $url)
    {

        self::writeRefundLog('[URL]' . $url);
        self::writeRefundLog('[INPUT-CALLBACK]' . json_encode($params));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        self::writeRefundLog('[STATUS-CODE]' . $httpCode);
        self::writeRefundLog('[ERROR-CURL]' . curl_error($ch));
        self::writeRefundLog('[OUTPUT-CALLBACK]' . $result);
        //  var_dump(json_encode($params)); var_dump($result);var_dump($httpCode);
        if ($httpCode == 200) {
            return json_decode($result, true);
        }
        return false;
    }

    static function processRequestRefund($params)
    {
        $error_message = 'Lỗi không xác định';
        $refund_transaction_id = null;
        $refund_status = $GLOBALS['REFUND_STATUS']['FAIL'];
        $checkout_order = $params['checkout_order'];

        $payment_transaction_info = Tables::selectOneDataTable("transaction", [
            "id = :id AND transaction_type_id in (:type) AND status = :status ",
            "id" => $checkout_order['transaction_id'],
            "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(),
            "status" => Transaction::STATUS_PAID
        ]);
        if ($payment_transaction_info) {
            $payment_transaction = Transaction::setRow($payment_transaction_info);
            $payment_method_code = $payment_transaction['payment_method_info']['code']; // lay ma phuong thuc thanh toan
            $delimiter_pos = strpos($payment_method_code, '-');
            $refund_payment_method_code = substr($payment_method_code, 0, $delimiter_pos) //lay ma phuong thuc hoan tien
                . '-REFUND'
                . substr($payment_method_code, $delimiter_pos);
            $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                "payment_method_code" => $refund_payment_method_code,
                "transaction_type_id" => TransactionType::getRefundTransactionTypeId(),
                "status" => PaymentMethod::STATUS_ACTIVE
            ]);
            if ($refund_payment_method_info) {
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($refund_partner_payment_info) {
                    $result_update_status_wait_refund = self::updateStatusWaitRefundByType([
                        'checkout_order_id' => $checkout_order['id'],
                        'payment_method_id' => $refund_payment_method_info['id'],
                        'partner_payment_id' => $refund_partner_payment_info['partner_payment_id'],
                        'partner_payment_method_refer_code' => 'REFUND' . $payment_transaction['id'] . '-' . uniqid(),
                        'user_id' => $params['user_id'],
                        'refund_amount' => $params['refund_amount'],
                        'refund_reason' => $params['refund_reason'],
                        'refund_type' => $params['refund_type']
                    ]);
                    if ($result_update_status_wait_refund['error_message'] == '') {
                        $refund_partner_payment_info_code = $refund_partner_payment_info['partner_payment_code'];
                        $refund_transaction_id = $result_update_status_wait_refund['refund_transaction_id'];
//                        $file_name = 'vcb' .DS. date("Ymd", time()) . ".txt";
//                        $pathinfo = pathinfo($file_name);
//                        Logs::create($pathinfo['dirname'], $pathinfo['basename'], json_encode($refund_partner_payment_info));

//                        if ($refund_partner_payment_info_code == 'NGANLUONG' || $refund_partner_payment_info_code == 'VCB') {
                        if ($refund_partner_payment_info_code == 'NGANLUONG') {
                            $result_process_refund_by_partner_payment = self::processRefundByPartnerPayment([
                                'refund_transaction_id' => $refund_transaction_id,
                                'refund_partner_payment_info_code' => $refund_partner_payment_info_code,
                                'payment_transaction_info' => $payment_transaction_info
                            ]);
                            if ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                                $refund_status = $GLOBALS['REFUND_STATUS']['SUCCESS'];
                                $params_update_success = array(
                                    'checkout_order_id' => $checkout_order['id'],
                                    'time_paid' => time(),
                                    'bank_refer_code' => $result_process_refund_by_partner_payment['refund_transaction_id'],
                                    'receiver_fee' => 0,
                                    'user_id' => Yii::$app->user->getId()
                                );
                                $result_update_success = CheckoutOrderBusiness::updateStatusRefund($params_update_success);
                                if ($result_update_success['error_message'] == '') {
                                    $error_message = 'Hoàn tiền thành công';
                                } else {
                                    $error_message = $result_update_success['error_message'];
                                }
                            } elseif ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT']) {
                                $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                                $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            } else {
                                $error_message = $result_process_refund_by_partner_payment['error_message'];
                                $params_update_cancel = array(
                                    'checkout_order_id' => $checkout_order['id'],
                                    'reason_id' => 0,
                                    'reason' => $error_message,
                                    'user_id' => Yii::$app->user->getId()
                                );
                                $result_update_cancel = CheckoutOrderBusiness::cancelWaitRefund($params_update_cancel);
                                if ($result_update_cancel['error_message'] == '') {
                                    $error_message = $result_process_refund_by_partner_payment['error_message'];
                                } else {
                                    $error_message = $result_update_cancel['error_message'];
                                }
                            }
                        } else {
                            $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                        }
                    } else {
                        $error_message = $result_update_status_wait_refund['error_message'];
                    }
                } else {
                    $error_message = 'Kênh phương thức hoàn tiền chưa được cấu hình';
                }
            } else {
                $error_message = 'Phương thức hoàn tiền chưa được cấu hình';
            }
        } else {
            $error_message = 'Không tìm thấy giao dịch hoặc trạng thái không hợp lệ';
        }
        return ['error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id, 'refund_status' => $refund_status];
    }

    static function processRequestRefundVcb($params)
    {
        $error_message = 'Lỗi không xác định';
        $refund_transaction_id = null;
        $refund_status = $GLOBALS['REFUND_STATUS']['FAIL'];
        $checkout_order = $params['checkout_order'];

        $payment_transaction_info = Tables::selectOneDataTable("transaction", [
            "id = :id AND transaction_type_id in (:type) AND status = :status ",
            "id" => $params['transaction_id'],
            "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(),
            "status" => Transaction::STATUS_PAID
        ]);
        if ($payment_transaction_info) {
            $payment_transaction = Transaction::setRow($payment_transaction_info);
            $payment_method_code = $payment_transaction['payment_method_info']['code']; // lay ma phuong thuc thanh toan
            $delimiter_pos = strpos($payment_method_code, '-');
            $refund_payment_method_code = substr($payment_method_code, 0, $delimiter_pos) //lay ma phuong thuc hoan tien
                . '-REFUND'
                . substr($payment_method_code, $delimiter_pos);
            $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                "payment_method_code" => $refund_payment_method_code,
                "transaction_type_id" => TransactionType::getRefundTransactionTypeId(),
                "status" => PaymentMethod::STATUS_ACTIVE
            ]);
            if ($refund_payment_method_info) {
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($refund_partner_payment_info) {
                    $result_update_status_wait_refund = self::updateStatusWaitRefundByType([
                        'checkout_order_id' => $checkout_order['id'],
                        'payment_method_id' => $refund_payment_method_info['id'],
                        'partner_payment_id' => $refund_partner_payment_info['partner_payment_id'],
                        'partner_payment_method_refer_code' => 'REFUND' . $payment_transaction['id'] . '-' . uniqid(),
                        'user_id' => $params['user_id'],
                        'refund_amount' => $params['refund_amount'],
                        'refund_reason' => @$params['refund_reason'],
                        'refund_type' => @$params['refund_type']
                    ]);
                    if ($result_update_status_wait_refund['error_message'] == '') {
                        $refund_partner_payment_info_code = $refund_partner_payment_info['partner_payment_code'];
                        $refund_transaction_id = $result_update_status_wait_refund['refund_transaction_id'];
//                        $file_name = 'vcb' .DS. date("Ymd", time()) . ".txt";
//                        $pathinfo = pathinfo($file_name);
//                        Logs::create($pathinfo['dirname'], $pathinfo['basename'], json_encode($refund_partner_payment_info));

//                        if ($refund_partner_payment_info_code == 'NGANLUONG' || $refund_partner_payment_info_code == 'VCB') {
                        if ($refund_partner_payment_info_code == 'VCB') {
                            $result_process_refund_by_partner_payment = self::processRefundByPartnerPayment([
                                'refund_transaction_id' => $refund_transaction_id,
                                'refund_partner_payment_info_code' => $refund_partner_payment_info_code,
                                'payment_transaction_info' => $payment_transaction_info
                            ]);
                            if ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                                $refund_status = $GLOBALS['REFUND_STATUS']['SUCCESS'];
                                $params_update_success = array(
                                    'checkout_order_id' => $checkout_order['id'],
                                    'time_paid' => time(),
                                    'bank_refer_code' => $result_process_refund_by_partner_payment['refund_transaction_id'],
                                    'receiver_fee' => 0,
                                    'user_id' => $params['user_id']
                                );
                                $result_update_success = CheckoutOrderBusiness::updateStatusRefund($params_update_success);
                                if ($result_update_success['error_message'] == '') {
                                    $error_message = 'Hoàn tiền thành công';
                                } else {
                                    $error_message = $result_update_success['error_message'];
                                }
                            } elseif ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT']) {
                                $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                                $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            } else {
                                $error_message = $result_process_refund_by_partner_payment['error_message'];
                                $params_update_cancel = array(
                                    'checkout_order_id' => $checkout_order['id'],
                                    'reason_id' => 0,
                                    'reason' => $error_message,
                                    'user_id' => Yii::$app->user->getId()
                                );
                                $result_update_cancel = CheckoutOrderBusiness::cancelWaitRefund($params_update_cancel);
                                if ($result_update_cancel['error_message'] == '') {
                                    $error_message = $result_process_refund_by_partner_payment['error_message'];
                                } else {
                                    $error_message = $result_update_cancel['error_message'];
                                }
                            }
                        } else {
                            $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                        }
                    } else {
                        $error_message = $result_update_status_wait_refund['error_message'];
                    }
                } else {
                    $error_message = 'Kênh phương thức hoàn tiền chưa được cấu hình';
                }
            } else {
                $error_message = 'Phương thức hoàn tiền chưa được cấu hình';
            }
        } else {
            $error_message = 'Không tìm thấy giao dịch hoặc trạng thái không hợp lệ';
        }
        return ['error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id, 'refund_status' => $refund_status];
    }

    static function processRequestRefundVcbV2($params)
    {
        $error_message = 'Lỗi không xác định';
        $refund_transaction_id = null;
        $refund_status = $GLOBALS['REFUND_STATUS']['FAIL'];
        $checkout_order = $params['checkout_order'];

        $payment_transaction_info = Tables::selectOneDataTable("transaction", [
            "id = :id AND transaction_type_id in (:type) AND status = :status ",
            "id" => $params['transaction_id'],
            "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(),
            "status" => Transaction::STATUS_PAID
        ]);
        if ($payment_transaction_info) {
            $payment_transaction = Transaction::setRow($payment_transaction_info);
            $payment_method_code = $payment_transaction['payment_method_info']['code']; // lay ma phuong thuc thanh toan
            $delimiter_pos = strpos($payment_method_code, '-');
            $refund_payment_method_code = substr($payment_method_code, 0, $delimiter_pos) //lay ma phuong thuc hoan tien
                . '-REFUND'
                . substr($payment_method_code, $delimiter_pos);
            $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                "payment_method_code" => $refund_payment_method_code,
                "transaction_type_id" => TransactionType::getRefundTransactionTypeId(),
                "status" => PaymentMethod::STATUS_ACTIVE
            ]);
            if ($refund_payment_method_info) {
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($refund_partner_payment_info) {
                    $result_update_status_wait_refund = self::updateStatusWaitRefundByTypeV2([
                        'checkout_order_id' => $checkout_order['id'],
                        'payment_method_id' => $refund_payment_method_info['id'],
                        'partner_payment_id' => $refund_partner_payment_info['partner_payment_id'],
                        'partner_payment_method_refer_code' => 'REFUND' . $payment_transaction['id'] . '-' . uniqid(),
                        'user_id' => $params['user_id'],
                        'refund_amount' => $params['refund_amount'],
                        'refund_reason' => @$params['refund_reason'],
                        'refund_type' => @$params['refund_type']
                    ]);
                    if ($result_update_status_wait_refund['error_message'] == '') {
                        $refund_partner_payment_info_code = $refund_partner_payment_info['partner_payment_code'];
                        $refund_transaction_id = $result_update_status_wait_refund['refund_transaction_id'];
//                        $file_name = 'vcb' .DS. date("Ymd", time()) . ".txt";
//                        $pathinfo = pathinfo($file_name);
//                        Logs::create($pathinfo['dirname'], $pathinfo['basename'], json_encode($refund_partner_payment_info));

//                        if ($refund_partner_payment_info_code == 'NGANLUONG' || $refund_partner_payment_info_code == 'VCB') {
                        if ($refund_partner_payment_info_code == 'VCB') {
                            $result_process_refund_by_partner_payment = self::processRefundByPartnerPayment([
                                'refund_transaction_id' => $refund_transaction_id,
                                'refund_partner_payment_info_code' => $refund_partner_payment_info_code,
                                'payment_transaction_info' => $payment_transaction_info
                            ]);
//                            var_dump($result_process_refund_by_partner_payment['refund_status']);
//                            var_dump($GLOBALS['REFUND_STATUS']['WAIT']);die();

                            if ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                                $refund_status = $GLOBALS['REFUND_STATUS']['SUCCESS'];
                                // Bo UpdateStatusRefund o day do order + transaction se duoc update tien + status ben BACKEND !!!
//                                $params_update_success = array(
//                                    'checkout_order_id' => $checkout_order['id'],
//                                    'time_paid' => time(),
//                                    'bank_refer_code' => $result_process_refund_by_partner_payment['refund_transaction_id'],
//                                    'receiver_fee' => 0,
////                                    'user_id' => 0
//                                    'user_id' => $params['user_id']
//                                );
////                                $result_update_success = CheckoutOrderBusiness::updateStatusRefund($params_update_success);
//                                $result_update_success = CheckoutOrderBusiness::updateStatusRefundV2($params_update_success);
//                                if ($result_update_success['error_message'] == '') {
//                                    $error_message = 'Hoàn tiền thành công';
//                                } else {
//                                    $error_message = $result_update_success['error_message'];
//                                }
                                $error_message = 'Hoàn tiền thành công';
                            } elseif ($result_process_refund_by_partner_payment['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT']) {
                                $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                                $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            } else {
                                $error_message = $result_process_refund_by_partner_payment['error_message'];
                                $params_update_cancel = array(
                                    'checkout_order_id' => $checkout_order['id'],
                                    'reason_id' => 0,
                                    'reason' => $error_message,
                                    'user_id' => Yii::$app->user->getId()
                                );
                                $result_update_cancel = CheckoutOrderBusiness::cancelWaitRefund($params_update_cancel);
                                if ($result_update_cancel['error_message'] == '') {
                                    $error_message = $result_process_refund_by_partner_payment['error_message'];
                                } else {
                                    $error_message = $result_update_cancel['error_message'];
                                }
                            }
                        } else {
                            $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                            $error_message = 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý';
                        }
                    } else {
                        $error_message = $result_update_status_wait_refund['error_message'];
                    }
                } else {
                    $error_message = 'Kênh phương thức hoàn tiền chưa được cấu hình';
                }
            } else {
                $error_message = 'Phương thức hoàn tiền chưa được cấu hình';
            }
        } else {
            $error_message = 'Không tìm thấy giao dịch hoặc trạng thái không hợp lệ';
        }
        return ['error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id, 'refund_status' => $refund_status];
    }

    static function processRequestRefundAPI($params)
    {
        $error_message = 'Lỗi không xác định';
        $refund_transaction_id = null;
        $refund_status = $GLOBALS['REFUND_STATUS']['FAIL'];
        $checkout_order = $params['checkout_order'];

        $payment_transaction_info = Tables::selectOneDataTable("transaction", [
            "id = :id AND transaction_type_id in (:type) AND status = :status ",
            "id" => $checkout_order['transaction_id'],
            "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(),
            "status" => Transaction::STATUS_PAID
        ]);
        if ($payment_transaction_info) {
            $payment_transaction = Transaction::setRow($payment_transaction_info);
            $payment_method_code = $payment_transaction['payment_method_info']['code']; // lay ma phuong thuc thanh toan
            $delimiter_pos = strpos($payment_method_code, '-');
            $refund_payment_method_code = substr($payment_method_code, 0, $delimiter_pos) //lay ma phuong thuc hoan tien
                . '-REFUND'
                . substr($payment_method_code, $delimiter_pos);
            $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                "payment_method_code" => $refund_payment_method_code,
                "transaction_type_id" => TransactionType::getRefundTransactionTypeId(),
                "status" => PaymentMethod::STATUS_ACTIVE
            ]);
            if ($refund_payment_method_info) {
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($refund_partner_payment_info) {
                    $result_update_status_wait_refund = self::updateStatusWaitRefundByType([
                        'checkout_order_id' => $checkout_order['id'],
                        'payment_method_id' => $refund_payment_method_info['id'],
                        'partner_payment_id' => $refund_partner_payment_info['partner_payment_id'],
                        'partner_payment_method_refer_code' => 'REFUND' . $payment_transaction['id'] . '-' . uniqid(),
                        'user_id' => $params['user_id'],
                        'refund_amount' => $params['refund_amount'],
                        'refund_reason' => $params['refund_reason'],
                        'refund_type' => $params['refund_type'],
                        'callback' => $params['callback'],
                        'ref_code_refund' => $params['ref_code_refund'],

                    ]);
                    if ($result_update_status_wait_refund['error_message'] == '') {
                        $refund_partner_payment_info_code = $refund_partner_payment_info['partner_payment_code'];
                        $refund_transaction_id = $result_update_status_wait_refund['refund_transaction_id'];
                        $error_message = '';
                        $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
//                        $file_name = 'vcb' .DS. date("Ymd", time()) . ".txt";
//                        $pathinfo = pathinfo($file_name);
//                        Logs::create($pathinfo['dirname'], $pathinfo['basename'], json_encode($refund_partner_payment_info));

//                        if ($refund_partner_payment_info_code == 'NGANLUONG' || $refund_partner_payment_info_code == 'VCB') {

                    } else {
                        $error_message = $result_update_status_wait_refund['error_message'];
                    }
                } else {
                    $error_message = 'Kênh phương thức hoàn tiền chưa được cấu hình';
                }
            } else {
                $error_message = 'Phương thức hoàn tiền chưa được cấu hình';
            }
        } else {
            $error_message = 'Không tìm thấy giao dịch hoặc trạng thái không hợp lệ';
        }
        return ['error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id, 'refund_status' => $refund_status];
    }

    static function updateStatusWaitRefundByType($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        if ($params['refund_type'] == $GLOBALS['REFUND_TYPE']['PARTIAL']) {
            $checkout_order_status = CheckoutOrder::STATUS_PAID . "," . CheckoutOrder::STATUS_REFUND_PARTIAL;
        } else {
            $checkout_order_status = CheckoutOrder::STATUS_PAID;
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id']
            . " AND status IN (" . $checkout_order_status . ")")->one();
        $transaction_payment = Transaction::findBySql("SELECT * FROM transaction WHERE id =" . $model['transaction_id'])->one();
        if ($model) {
            $total_amount_refund = $model->amount + $model->sender_fee + ((!empty($transaction_payment['installment_fee_buyer']) && $transaction_payment['installment_conversion'] == 1) ? $transaction_payment['installment_fee_buyer'] : 0); // tong so tien co the hoan
            $get_total_transaction_refund_amount = CheckoutOrder::findBySql("select sum(amount) as refund_amount from transaction " // tong so tien da hoan thanh cong
                . "where refer_transaction_id = " . $model->transaction_id . " "
                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "and status = " . Transaction::STATUS_PAID)->asArray()->one();
            if (!is_null($get_total_transaction_refund_amount)) {
                $total_transaction_refund_amount = empty($get_total_transaction_refund_amount['refund_amount']) ? 0 : $get_total_transaction_refund_amount['refund_amount'];
                $refund_rate = doubleval($params['refund_amount']) / doubleval($model->amount);
                $refund_payment_sender_fee = $refund_rate * doubleval($model->sender_fee);
                $refund_payment_installment_merchant = (!empty($transaction_payment['installment_fee_buyer']) && $transaction_payment['installment_conversion'] == 1) ? $refund_rate * doubleval($transaction_payment['installment_fee_merchant']) : 0;
                $refund_payment_installment_buyer = (!empty($transaction_payment['installment_fee_buyer']) && $transaction_payment['installment_conversion'] == 1) ? $refund_rate * doubleval($transaction_payment['installment_fee_buyer']) : 0;

                if ((doubleval($params['refund_amount']) + $refund_payment_sender_fee + $refund_payment_installment_buyer + doubleval($total_transaction_refund_amount)) <= doubleval($total_amount_refund)) {
                    if (isset($params['callback']) && !empty($params['callback'])) {
                        $model->refund_callback_url = $params['callback'];
                    }
                    if (isset($params['ref_code_refund']) && !empty($params['ref_code_refund'])) {
                        $model->ref_code_refund = $params['ref_code_refund'];
                    }
                    $model->status = CheckoutOrder::STATUS_WAIT_REFUND;
                    $model->time_refund = time();
                    $model->user_refund = $params['user_id'];

                    if ($model->validate()) {
                        if ($model->save()) {
                            $inputs = array(
                                'payment_transaction_id' => $model->transaction_id,
                                'payment_method_id' => $params['payment_method_id'],
                                'partner_payment_id' => $params['partner_payment_id'],
                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                'currency' => $model->currency,
                                'user_id' => $params['user_id'],
                                'refund_amount' => $params['refund_amount']
                            );
                            $result = TransactionBusiness::addRefundTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $refund_transaction_id = $result['id'];
                                $model->refund_transaction_id = $refund_transaction_id;
                                if ($model->validate() && $model->save()) {
                                    $commit = true;
                                    $error_message = '';
                                } else {
                                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                        }
                    } else {
                        $error_message = 'Tham số đầu vào không hợp lệ';
                    }
                } else {
                    $error_message = 'Số tiền hoàn lại không hợp lệ';
                }
            }
        } else {
            $error_message = 'Trạng thái đơn hàng không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
    }

    static function updateStatusWaitRefundByTypeV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        if ($params['refund_type'] == $GLOBALS['REFUND_TYPE']['PARTIAL']) {
            $checkout_order_status = CheckoutOrder::STATUS_PAID . "," . CheckoutOrder::STATUS_REFUND_PARTIAL . "," . CheckoutOrder::STATUS_WAIT_REFUND;
        } else {
            $checkout_order_status = CheckoutOrder::STATUS_PAID . "," . CheckoutOrder::STATUS_WAIT_REFUND;
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id']
            . " AND status IN (" . $checkout_order_status . ")")->one();
        self::writeRefundLog('[DEBUG-1]');
        if ($model) {
            /** @var $model CheckoutOrder */
            self::writeRefundLog('[MODEL-AMOUNT] ' . $model->amount);
            self::writeRefundLog('[MODEL-SENDER_FEE] ' . $model->sender_fee);

            $total_amount_refund = $model->amount + $model->sender_fee; // tong so tien co the hoan
            $get_total_transaction_refund_amount = CheckoutOrder::findBySql("select sum(amount) as refund_amount from transaction " // tong so tien da hoan thanh cong
                . "where refer_transaction_id = " . $model->transaction_id . " "
                . "and transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "and status = " . Transaction::STATUS_PAID)->asArray()->one();
            self::writeRefundLog('[get_total_transaction_refund_amount] ' . json_encode($get_total_transaction_refund_amount));
            if (!is_null($get_total_transaction_refund_amount)) {
                $total_transaction_refund_amount = empty($get_total_transaction_refund_amount['refund_amount']) ? 0 : $get_total_transaction_refund_amount['refund_amount'];

                //update
                $refund_amount_to_check = doubleval($params['refund_amount']) - doubleval($model->sender_fee);

//                $refund_rate = doubleval($params['refund_amount']) / doubleval($model->amount);
                $refund_rate = doubleval($refund_amount_to_check) / doubleval($model->amount);

                $refund_payment_sender_fee = $refund_rate * doubleval($model->sender_fee);
                self::writeRefundLog('[refund_amount_to_check] ' . $refund_amount_to_check);
                self::writeRefundLog('[refund_payment_sender_fee] ' . $refund_payment_sender_fee);
                self::writeRefundLog('[total_transaction_refund_amount] ' . $total_transaction_refund_amount);
                self::writeRefundLog('[total_amount_refund] ' . $total_amount_refund);

//                if ((doubleval($params['refund_amount']) + $refund_payment_sender_fee + doubleval($total_transaction_refund_amount)) <= doubleval($total_amount_refund)) {
                if (($refund_amount_to_check + $refund_payment_sender_fee + doubleval($total_transaction_refund_amount)) <= doubleval($total_amount_refund)) {
                    self::writeRefundLog('[DEBUG-2222]]');

                    if (isset($params['callback']) && !empty($params['callback'])) {
                        $model->refund_callback_url = $params['callback'];
                    }
                    if (isset($params['ref_code_refund']) && !empty($params['ref_code_refund'])) {
                        $model->ref_code_refund = $params['ref_code_refund'];
                    }
                    $model->status = CheckoutOrder::STATUS_WAIT_REFUND;
                    $model->time_refund = time();
                    $model->user_refund = $params['user_id'];

                    if ($model->validate()) {
                        if ($model->save()) {
                            // BO ADD REFUND TRANSACTION(DO TRUOC DO DA TAO GD HOAN (STATUS: NEW) TU YEU CAU HOAN
//                            $inputs = array(
//                                'payment_transaction_id' => $model->transaction_id,
//                                'payment_method_id' => $params['payment_method_id'],
//                                'partner_payment_id' => $params['partner_payment_id'],
//                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
//                                'currency' => $model->currency,
//                                'user_id' => $params['user_id'],
//                                'refund_amount' => $params['refund_amount']
//                            );
//                            $result = TransactionBusiness::addRefundTransaction($inputs, false);
//                            if ($result['error_message'] == '') {
//                                $refund_transaction_id = $result['id'];
//                                $model->refund_transaction_id = $refund_transaction_id;
//                                if ($model->validate() && $model->save()) {
                            $commit = true;
                            $error_message = '';
//                                } else {
//                                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
//                                }
//                            } else {
//                                $error_message = $result['error_message'];
//                            }
                        } else {
                            $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                        }
                    } else {
                        $error_message = 'Tham số đầu vào không hợp lệ';
                    }
                } else {
                    $error_message = 'Số tiền hoàn lại không hợp lệ';
                }
            }
        } else {
            $error_message = 'Trạng thái đơn hàng không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
//        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
        return array('error_message' => $error_message, 'refund_transaction_id' => $model->refund_transaction_id);
    }

    static function processRefundByPartnerPayment($params)
    {
        $error_message = 'Lỗi không xác định';
        $refund_status = $GLOBALS['REFUND_STATUS']['FAIL'];
        $refund_transaction_id = null;

        $payment_transaction_info = $params['payment_transaction_info'];
        $refund_transaction_id = $params['refund_transaction_id'];
        $refund_partner_payment_info_code = $params['refund_partner_payment_info_code'];
        $refund_transaction_info = Tables::selectOneDataTable("transaction", [
            "id = :id AND transaction_type_id = :type AND status = :status ",
            "id" => $refund_transaction_id,
            "type" => TransactionType::getRefundTransactionTypeId(),
            "status" => Transaction::STATUS_NEW
        ]);
        if ($refund_transaction_info) {
            $merchant_id = $refund_transaction_info['merchant_id'];
            $partner_payment_id = $refund_transaction_info['partner_payment_id'];
            $partner_payment_account_id = $refund_transaction_info['partner_payment_account_id'];

            $partner_payment_account_info = Tables::selectOneDataTable("partner_payment_account", [
                "id = :partner_payment_account_id AND status = :status ",
                "partner_payment_account_id" => $partner_payment_account_id,
                "status" => PartnerPaymentAccount::STATUS_ACTIVE]);
            if ($partner_payment_account_info) {
                if ($refund_partner_payment_info_code == 'NGANLUONG') {
                    $nganluong_multi_refund = new \common\payments\NganLuongMultiRefund($merchant_id, $partner_payment_id);
                    $result_refund = $nganluong_multi_refund->setRefundRequest([
                        'ref_code_refund' => $refund_transaction_info['partner_payment_method_refer_code'],
                        'amount' => $refund_transaction_info['amount'],
                        'transaction_id' => $payment_transaction_info['bank_refer_code'],
                        'reason' => (empty($refund_transaction_info['reason'])) ?
                            'VCB hoàn tiền GD ' . $payment_transaction_info['id'] : $refund_transaction_info['reason'],
                    ]);
                    if (!empty($result_refund)) {
                        if ($result_refund['error_message'] == '') {
                            $nl_refund_data = $result_refund['data']['data'];
                            if ($nl_refund_data['transaction_status'] == '00') { // thành công
                                $refund_status = $GLOBALS['REFUND_STATUS']['SUCCESS'];
                                $refund_transaction_id = $nl_refund_data['transaction_refund_id'];
                                $error_message = '';
                            } elseif ($nl_refund_data['transaction_status'] == '01') { // đang chờ xử lý
                                $refund_status = $GLOBALS['REFUND_STATUS']['WAIT'];
                                $error_message = '';
                            } else {
                                $error_message = 'Lỗi hoàn tiền thất bại trên Ngân Lượng';
                            }
                        } else {
                            $error_message = 'Lỗi hoàn tiền Ngân Lượng: ' . $result_refund['error_message'];
                        }
                    }
                } elseif ($refund_partner_payment_info_code == 'VCB') {

                    $params_rf_vcb = [
                        'trans_id' => $payment_transaction_info['bank_refer_code'],
//                        'trans_id' => $payment_transaction_info['trans_id'],
                        'amount' => $refund_transaction_info['amount'],
                        'ref_trans_id' => 'NL' . $refund_transaction_id,
                    ];
                    $merchant_id = $payment_transaction_info['merchant_id'];
                    $partner_payment_id = $payment_transaction_info['partner_payment_id'];
                    $result = VCB::refund($params_rf_vcb, $merchant_id, $partner_payment_id);
// gia lap refun thanh cong
//                    $result = [
//                        'status' => true,
//                        'error_code' => '1',
//                        'message' => '1: giao dịch thành công',
//                        'data' => [
//                            'trans_id' => 'PAYGATE_VCB_'.$refund_transaction_info['id'],
//                        ],
//                    ];
//end gia lap
                    if (!empty($result)) {
                        if ($result['status']) {
                            if ($result['error_code'] == '1') { // thành công
                                $refund_status = $GLOBALS['REFUND_STATUS']['SUCCESS'];
                                $refund_transaction_id = $result['data']['trans_id'];
                                $error_message = '';
                            } else {
                                $error_message = 'Lỗi hoàn tiền thất bại trên VCB: ' . $result['message'];
                            }
                        } else {
                            $error_message = 'Lỗi hoàn tiền thất bại trên VCB: ' . $result['message'];
                        }
                    }
                }
            } else {
                $error_message = 'Tài khoản kênh thanh toán không tồn tại';
            }
        } else {
            $error_message = 'Giao dịch hoàn tiền không hợp lệ';
        }
        return ['error_message' => $error_message, 'refund_status' => $refund_status, 'refund_transaction_id' => $refund_transaction_id];
    }

    public function makeTransID($trans_id)
    {
        $lenght = strlen($trans_id);
        $prefix = 'NL';
        $after_fix = '';
        if ($lenght == 1) {
            $after_fix = '00000';
        } elseif ($lenght == 2) {
            $after_fix = '0000';
        } elseif ($lenght == 3) {
            $after_fix = '000';
        } elseif ($lenght == 4) {
            $after_fix = '00';
        } elseif ($lenght == 5) {
            $after_fix = '0';
        } else {
            $after_fix = '';
        }
        return $prefix . $after_fix . $trans_id;
    }

    static function updateStatusInstallMentPaidMPOS($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $notify_url = '';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql("SELECT * FROM checkout_order WHERE id = " . $params['checkout_order_id'] . " AND (status = " . CheckoutOrder::STATUS_INSTALLMENT_WAIT . " OR status = " . CheckoutOrder::STATUS_REVIEW . " )")->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
//            $model->cashin_amount = $model->amount + $model->sender_fee;
//            $model->cashout_amount = $model->amount - $model->receiver_fee;
            $model->status = CheckoutOrder::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrder::CALLBACK_STATUS_PROCESSING : CheckoutOrder::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            $model->installment_cycle = $params['month'];
            $model->installment_info = $params['installment_info'];
            if ($model->validate()) {
                if ($model->save()) {
                    $transaction_info = Transaction::findOne($params['transaction_id']);
                    $transaction_info->installment_conversion = Transaction::InstallmentConversion_PAID;
                    $transaction_info->save();


                    $params_add_installment = [
                        'checkout_order_id' => $params['checkout_order_id'],
                        'user_created' => Yii::$app->user->getId()
                    ];
                    $add_installment = InstallmentBusiness::add($params_add_installment);
                    if ($add_installment['error_message'] == '') {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = $add_installment['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array(
            'error_message' => $error_message,
            'notify_url' => $notify_url,
        );
    }

    public static function writeRefundLog($data)
    {
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'refund' . DS . 'callback' . DS;
        if (is_dir($log_path) || mkdir($log_path, 0777, true)) {
            $log_file = date('Ymd') . '.txt';
            $file = fopen($log_path . $log_file, 'a+');
            if ($file) {
                fwrite($file, '[' . date('H:i:s, d/m/Y') . '] ' . $data . "\n");
                fclose($file);
                return true;
            }
        }
        return false;
    }

    public static function updateSuccess($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $bank_trans_id, $authorizationCode): array
    {
        $checkout_order_inputs = array(
            'checkout_order_id' => $checkout_order_info->id,
            'payment_method_id' => $payment_method_info['id'],
            'partner_payment_id' => $payment_method_info['partner_payment_id'],
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );
//        Is installment?
        if ($payment_method_info['transaction_type_id'] == TransactionType::getInstallmentTransactionTypeId()) {
            $checkout_order_inputs['transaction_type_id'] = TransactionType::getInstallmentTransactionTypeId();
        }

        $result_request_payment = CheckoutOrderBusiness::requestPayment($checkout_order_inputs);
        if ($result_request_payment['error_message'] == '') {
            $transaction_id = $result_request_payment['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info) {
                $cardInfo = [
                    'card_fullname' => $last_name . " " . $first_name,
                    'card_number' => Strings::encodeCreditCardNumber($data['custommer_info']['card_number']),
                    'card_month' => $data['custommer_info']['expiration_month'],
                    'card_year' => $data['custommer_info']['expiration_year'],
                ];
                Transaction::insertCardInfo($transaction_id, $cardInfo);
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'partner_payment_method_refer_code' => '',
                    'user_id' => 0,
                );
                $result = TransactionBusiness::paying($inputs);
                if ($result['error_message'] == '') {
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'time_paid' => time(),
                        'bank_refer_code' => $bank_trans_id,
                        'authorizationCode' => $authorizationCode,
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paid($inputs);
                    if ($result['error_message'] === '') {
                        $result = [
                            'status' => false,
                            'redirect' => Version_1_0Controller::_getUrlSuccess($checkout_order_info->token_code),
                        ];
                    } else {
                        $result = [
                            'status' => false,
                            'error_message' => Translate::get("Không tìm thấy giao dịch"),
                        ];
                    }
                } else {
                    $result = [
                        'status' => false,
                        'error_message' => Translate::get("Không tìm thấy giao dịch"),
                    ];
                }
            } else {
                $result = [
                    'status' => false,
                    'error_message' => Translate::get("Không tìm thấy giao dịch"),
                ];
            }
        } else {
            $result = [
                'status' => false,
                'error_message' => Translate::get($result_request_payment['error_message'] == ''),
            ];
        }
        return $result;
    }

    public static function updateSeamlessInfo($checkout_order_id, $seamless_info): bool
    {
        $model = CheckoutOrder::findOne($checkout_order_id);
        if ($model) {
            $model->seamless_info = json_encode($seamless_info);
            if ($model->save()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    static function updateCheckoutOrderStatusFailureV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrder::getDb()->beginTransaction();
        }
        $model = CheckoutOrder::findBySql('SELECT * FROM checkout_order WHERE id = ' . $params['checkout_order_id'] . '   AND status IN (' . CheckoutOrder::STATUS_NEW . ',' . CheckoutOrder::STATUS_PAYING . ',' . CheckoutOrder::STATUS_PAID . ')')->one();
        if ($model != null) {
            $model->status = CheckoutOrder::STATUS_FAILURE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

}
