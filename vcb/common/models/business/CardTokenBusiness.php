<?php

namespace common\models\business;

use common\components\libs\Tables;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\payments\CyberSourceVcb3ds2;

class CardTokenBusiness
{
    protected static function updateFailure($checkout_order_info, $payment_method_info, $last_name, $first_name, $data, $reason = false): array
    {
        $checkout_order_inputs = array(
            'checkout_order_id' => $checkout_order_info->id,
            'payment_method_id' => $payment_method_info['id'],
            'partner_payment_id' => $payment_method_info['partner_payment_id'],
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );

        if ($payment_method_info['transaction_type_id'] == TransactionType::getInstallmentTransactionTypeId()) {
            $checkout_order_inputs['transaction_type_id'] = TransactionType::getInstallmentTransactionTypeId();
        }

        $result_request_payment = CheckoutOrderBusiness::requestPayment($checkout_order_inputs);
        if ($result_request_payment['error_message'] == '') {
            $transaction_id = $result_request_payment['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
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
                    'partner_payment_info' => '',
                );
                $paying = TransactionBusiness::paying($inputs);
                if ($paying['error_message'] == "") {
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'reason_id' => "666",
                        'reason' => "Giao dịch bị từ chối bởi ngân hàng phát hành thẻ",
                        'user_id' => 0,
                    );
                    if ($reason && !in_array($reason->reasonCode, ["475", "100"])) {
                        $inputs['reason_id'] = $reason->reasonCode;
                        $inputs['reason'] = CyberSourceVcb3ds2::getErrorMessage($reason->reasonCode);
                    }
                    if (isset($reason->invalidField) && $reason->invalidField != "") {
                        $invalid_filed = CyberSourceVcb3ds2::getInvalidField($reason);
                        $inputs['reason'] .= " Field Invalid: ";
                        foreach ($invalid_filed as $item) {
                            $inputs['reason'] .= $item . " ";
                        }
                    }

                    if (isset($data['enrrol_checked']) && $data['enrrol_checked']) {
                        $inputs['reason_id'] = "476";
                        $inputs['reason'] = CyberSourceVcb3ds2::getErrorMessage("476") . "(user cancel)";
                    }

                    $failure = TransactionBusiness::failure($inputs);
                    if ($failure['error_message'] === '') {
                        $inputs = array(
                            'checkout_order_id' => $checkout_order_info->id,
                            'user_id' => '0',
                        );
                        $update_checkout_order_failure = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                        if ($update_checkout_order_failure['error_message'] === '') {
                            $inputs_callback = [
                                'checkout_order_id' => $checkout_order_info->id,
                                'notify_url' => $checkout_order_info->notify_url,
                                'time_process' => time(),
                            ];
                            if (true) {
                                $add_callback = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                            }
                            if ($add_callback['error_message'] == '') {
                                $result = [
                                    'status' => false,
                                    'redirect' => self::_getUrlFailure($checkout_order_info->token_code),
                                ];
                            } else {
                                $result = [
                                    'status' => false,
                                    'error_message' => Translate::get($add_callback['error_message']),
                                ];
                            }
                        } else {
                            $result = [
                                'status' => false,
                                'error_message' => Translate::get($update_checkout_order_failure['error_message']),
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'error_message' => Translate::get($failure['error_message']),
                        ];
                    }
                } else {
                    $result = [
                        'status' => false,
                        'error_message' => Translate::get($paying['error_message']),
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

}