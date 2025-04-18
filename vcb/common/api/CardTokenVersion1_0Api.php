<?php

namespace common\api;

use common\api\CardTokenBasicApi;
use common\components\utils\Encryption;
use common\components\utils\ObjInput;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\models\business\CardDeclineBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\Merchant;
use common\models\db\LinkCard;
use common\models\db\Transaction;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\LinkCardBusiness;
use common\models\business\TransactionBusiness;
use common\payments\CyberSource;
use common\payments\CyberSourceVcb;
use common\payments\CyberSourceVcb3ds2;
use common\util\Helpers;
use PHPUnit\Framework\Constraint\IsJson;

class CardTokenVersion1_0Api extends CardTokenBasicApi
{

    private $merchant_info;

    protected function getVersion()
    {
        return '1.0';
    }

    protected function isFunction($function)
    {
        if (in_array($function, ['CheckOrder', 'checkOrder'])) {
            $function_name = 'CheckOrder';
        } else {
            $function_name = $function;
        }

        return in_array($function_name, ['create', 'payment', 'cancel', 'CheckOrder', 'CheckToken']);
    }

    public function getRequest($function_name)
    {
        if (in_array($function_name, ['checkOrder', 'CheckOrder'])) {
            $function = 'CheckOrder';
        } else {
            $function = $function_name;
        }
        switch ($function) {
            case 'create':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', ''),
                    'merchant_id' => ObjInput::get('merchant_id', 'int', 0),
                    'customer_id' => ObjInput::get('customer_id', 'str', ''),
                    'first_name' => ObjInput::get('first_name', 'str', ''),
                    'last_name' => ObjInput::get('last_name', 'str', ''),
                    'street' => ObjInput::get('street', 'str', ''),
                    'city' => ObjInput::get('city', 'str', ''),
                    'state' => ObjInput::get('state', 'str', ''),
                    'postal_code' => ObjInput::get('postal_code', 'str', ''),
                    'email' => ObjInput::get('email', 'str', ''),
                    'phone' => ObjInput::get('phone', 'str', ''),
                    'return_url' => ObjInput::get('return_url', 'str', ''),
                    'notify_url' => ObjInput::get('notify_url', 'str', ''),
                    'customer_field' => ObjInput::get('customer_field', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                    'language' => ObjInput::get('language', 'str', 'vi'),
                ];
                break;
            case 'payment':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', ''),
                    'merchant_id' => ObjInput::get('merchant_id', 'int', 0),
                    'token_merchant' => ObjInput::get('token_merchant', 'str', ''),
                    'currency' => ObjInput::get('currency', 'str', ''),
                    'amount' => ObjInput::get('amount', 'int', 0),
                    'order_code' => ObjInput::get('order_code', 'str', ''),
                    'order_description' => ObjInput::get('order_description', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                    'notify_url' => ObjInput::get('notify_url', 'str', ''),
                    'return_url' => ObjInput::get('return_url', 'str', ''),
                    'cancel_url' => ObjInput::get('cancel_url', 'str', ''),
                    'customer_field' => ObjInput::get('customer_field', 'str', ''),
                    'language' => ObjInput::get('language', 'str', 'vi'),
                ];
                break;
            case 'cancel':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', ''),
                    'merchant_id' => ObjInput::get('merchant_id', 'int', 0),
                    'token_merchant' => ObjInput::get('token_merchant', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                ];
                break;
            case 'CheckOrder':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', ''),
                    'merchant_id' => ObjInput::get('merchant_id', 'int', 0),
                    'token_code' => ObjInput::get('token_code', 'str', ''),
                    'token_merchant' => ObjInput::get('token_merchant', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                    'function_name' => $function_name,
                ];
                break;
            case 'CheckToken':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', ''),
                    'merchant_id' => ObjInput::get('merchant_id', 'int', 0),
                    'customer_id' => ObjInput::get('customer_id', 'str', ''),
                    'token_merchant' => ObjInput::get('token_merchant', 'str', ''),
                    'first_name' => ObjInput::get('first_name', 'str', ''),
                    'last_name' => ObjInput::get('last_name', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                ];
                break;
            default:
                $request = [];
        }
        return $request;
    }

    protected function validateRequest($request)
    {
        $error_code = '0001';
        $method_name = 'validate' . ucfirst($request['function']);
        if (method_exists($this, $method_name)) {
            $validate = $this->$method_name($request, $error_code);
            if ($validate) {
                $error_code = '0000';
            }
        }
        return ['error_code' => $error_code];
    }

    protected function processRequest($request)
    {
        $error_code = '0001';
        $data = [];
        $method_name = 'process' . ucfirst($request['function']);
        if (method_exists($this, $method_name)) {
            $process = $this->$method_name($request);
            $error_code = $process['error_code'];
            $data = $process['data'];
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Success', '0001' => 'Undefined Error', '0002' => 'Invalid Function name', '0003' => 'Invalid merchant_site_code ', '0004' => 'Invalid version', '0005' => 'Invalid order_code', '0006' => 'Invalid order_description', '0007' => 'Invalid amount format', '0008' => 'Invalid currency', '0009' => 'Invalid buyer_fullname', '0010' => 'Invalid buyer_email', '0011' => 'Invalid buyer_mobile', '0012' => 'Invalid buyer_address', '0013' => 'Invalid return_url', '0014' => 'Invalid cancel_url', '0015' => 'Invalid notify_url', '0016' => 'Invalid time_limit', '0017' => 'Invalid checksum', '0018' => 'Invalid token_code', '0101' => 'Request params are ok, but could not create the order for this merchant.', '0102' => 'Unlinked card',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    protected function processCheckOrder($params)
    {
        $checkout_order_info = '';
        $error_code = '0001';
        $result_data = null;
        //-------------
        $error_code = '0000';
        if ($this->_validateTokenCode($params['token_code'], $checkout_order_info)) {
            if ($checkout_order_info['merchant_id'] == $params['merchant_id']) {
                $params['checkout_order_info'] = $checkout_order_info;
                $error_code = '0000';
            } else {
                $error_code = '0018';
            }
        } else {
            $error_code = '0018';
        }
        $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);

        return array('error_code' => $error_code, 'data' => $result_data['result_data']);
    }

    private function processCreate($request)
    {
        $error_code = '0001';
        $data = [];
        $add_link_card = LinkCardBusiness::add([
            'merchant_id' => $request['merchant_id'],
            'card_holder' => trim($request['last_name']) . ' ' . trim($request['first_name']),
            'customer_email' => $request['email'],
            'customer_mobile' => $request['phone'],
            'customer_field' => $request['customer_field'],
            'info' => json_encode([
                'version' => $request['version'],
                'street' => $request['street'],
                'city' => $request['city'],
                'state' => $request['state'],
                'postal_code' => $request['postal_code'],
                'notify_url' => $request['notify_url'],
                'return_url' => $request['return_url'] ?? '',
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'customer_id' => $request['customer_id'],
                'customer_field' => $request['customer_field'],
                'language' => $request['language'],
            ]),
        ]);
        if ($add_link_card['error_message'] == '') {
            $error_code = '0000';
            $data = ['redirect_url' => LinkCard::getLinkCardUrl($add_link_card['token'])];
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    private function processCheckToken($request)
    {
        $error_code = '0001';
        $data = [];
        $check_token = LinkCardBusiness::checkTokenMerchant([
            'merchant_id' => $request['merchant_id'],
            'customer_id' => $request['customer_id'],
            'token_merchant' => $request['token_merchant'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'checksum' => $request['checksum'],
        ]);
        if ($check_token['error_message'] == '') {
            $error_code = '0000';
            $data = $check_token['result'];
        } else if ($check_token['error_message'] == 'customer_id error') {
            $error_code = '0026';
            $data = [];
        } else if ($check_token['error_message'] == 'first_name error') {
            $error_code = '0008';
            $data = [];
        } else if ($check_token['error_message'] == 'last_name error') {
            $error_code = '0009';
            $data = [];
        } else if ($check_token['error_message'] == 'Thẻ chưa được liên kết') {
            $error_code = '0102';
            $data = [];
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    private function processPayment($request)
    {
        $error_code = '0001';
        $data = [];
        $card_token = LinkCard::getByTokenMerchant($request['token_merchant']);
//        if ($request['merchant_id'] == 19) { // FWD chặn thanh toán- Quang yc hot fix ngay 11/9
//            if ($request['order_code'] != "2736312") {
//                $error_code = '0101';
//                return ['error_code' => '0101', 'data' => Translate::get("Lỗi xử lý thanh toán")];
//            }
//        }

        if ($card_token && $request['merchant_id'] == $card_token['merchant_id']) {
            $card_token_info = json_decode($card_token['info'], true);
            $payment_method_code = LinkCard::getBankCodeByCardType($card_token['card_type']) . '-TOKENIZATION';
            $payment_method_id = PaymentMethod::getPaymentMethodIdActiveByCode($payment_method_code);
            if ($payment_method_id) {
                $partner_payment_method = PartnerPaymentMethod::getByPaymentMethodId($payment_method_id);
                if ($partner_payment_method) {
                    $partner_payment = PartnerPayment::getById($partner_payment_method['partner_payment_id']);
                    if ($partner_payment && $partner_payment['id'] == $card_token['partner_payment_id']) {
                        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($request['merchant_id'], $partner_payment['id']);
                        if ($partner_payment_account_info) {
                            // Add and Request Payment CheckoutOrder
                            $add_request_payment_checkout_order = CheckoutOrderBusiness::addAndRequestPayment([
                                'merchant_id' => $request['merchant_id'],
                                'version' => '1.0',
                                'language_id' => '1', //vi
                                'order_code' => $request['order_code'],
                                'order_description' => $request['order_description'],
                                'amount' => $request['amount'],
                                'currency' => $request['currency'],
                                'return_url' => $request['return_url'],
                                'cancel_url' => $request['cancel_url'],
                                'notify_url' => $request['notify_url'],
                                'time_limit' => strtotime(date('c', time() + 7 * 86400)),
                                'buyer_fullname' => $card_token['card_holder'],
                                'buyer_mobile' => $card_token['customer_mobile'],
                                'buyer_email' => $card_token['customer_email'],
                                'buyer_address' => $card_token_info['street'] . $card_token_info['city'],
                                'user_id' => 0,
                                'payment_method_id' => $payment_method_id,
                                'partner_payment_id' => $partner_payment['id'],
                                'partner_payment_method_refer_code' => '',
                            ]);
                            if ($add_request_payment_checkout_order['error_message'] == '') {
                                $merchant_info = Merchant::findOne(['id' => $card_token['merchant_id']]);
                                $checkout_order_id = $add_request_payment_checkout_order['checkout_order_id'];
                                $transaction = Transaction::findOne(['id' => $add_request_payment_checkout_order['transaction_id']]);
                                $transaction->card_token_id = $card_token['id'];
                                $transaction->save();

                                $card_number_check = substr($card_token['card_number_mask'], 0, 6) . Helpers::addZeroPrefix(substr($card_token['card_number_mask'], -4), 10);
                                if (CardDeclineBusiness::checkCard($card_number_check)) {
                                    if ($partner_payment_method['partner_payment_code'] == "CYBER-SOURCE-VCB-3DS2" && $merchant_info->active3D == Merchant::ST_ACTIVE) {
                                        $error_code = "0000";
                                        $data = [
//                                        "data" => [
                                            "redirect_url" => LinkCard::getAuthenticateUrl($add_request_payment_checkout_order['token_code']),
                                            "token_code" => ($add_request_payment_checkout_order['token_code'])
//                                        ]
                                        ];

                                    } else {
                                        $checkout_order_id = $add_request_payment_checkout_order['checkout_order_id'];
                                        $checkout_order_token_code = $add_request_payment_checkout_order['token_code'];
                                        $transaction_id = $add_request_payment_checkout_order['transaction_id'];
                                        $process_cybersource = $this->processCybersource([
                                            'transaction_id' => $transaction_id,
                                            'merchant_id' => $request['merchant_id'],
                                            'partner_payment_id' => $card_token['partner_payment_id'],
                                            'partner_payment_code' => $partner_payment['code'],
                                            'token_cybersource' => $card_token['token_cybersource'],
                                            'iv' => $card_token['iv'],
                                            'order_code' => $request['order_code'],
                                            'card_token' => $card_token
                                        ]);
                                        $result_code = $process_cybersource['result_code'];
                                        if ($result_code == 'ACCEPT') {
                                            $inputs_paying = array(
                                                'transaction_id' => $transaction_id,
                                                'partner_payment_method_refer_code' => '',
                                                'partner_payment_info' => json_encode($process_cybersource),
                                                'user_id' => 0,
                                            );
                                            $result_paying = TransactionBusiness::paying($inputs_paying);
                                            if ($result_paying['error_message'] == '') {
                                                $authorizationCode = $process_cybersource['authorizationCode'];
                                                $inputs_paid = array(
                                                    'transaction_id' => $transaction_id,
                                                    'bank_refer_code' => $process_cybersource['bank_trans_id'],
                                                    'authorizationCode' => $authorizationCode,
                                                    'time_paid' => time(),
                                                    'user_id' => 0,
                                                );
                                                $result_paid = TransactionBusiness::paid($inputs_paid);
                                                if ($result_paid['error_message'] == '') {
                                                    $error_code = '0000';
                                                    $params = [
                                                        'merchant_advice_code' => $process_cybersource['merchant_advice_code']
                                                    ];
                                                    $data = $this->getResponsePayment($checkout_order_id, $params);
                                                } else {
                                                    $error_code = '0101';
                                                    $data = Translate::get("Lỗi xử lý thanh toán");
                                                }
                                            }
                                        } elseif ($result_code == 'REVIEW') {
                                            $inputs_paying = array(
                                                'transaction_id' => $transaction_id,
                                                'partner_payment_method_refer_code' => '',
                                                'partner_payment_info' => json_encode($process_cybersource),
                                                'user_id' => 0,
                                            );
                                            $result_paying = TransactionBusiness::paying($inputs_paying);
                                            if ($result_paying['error_message'] == '') {
                                                $inputs_review = array(
                                                    'transaction_id' => $transaction_id,
                                                    'bank_refer_code' => $process_cybersource['bank_trans_id'],
                                                    'user_id' => 0,
                                                );
                                                $result_review = TransactionBusiness::updateReview($inputs_review);
                                                if ($result_review['error_message'] == '') {
                                                    $error_code = '0000';
                                                    $params = [
                                                        'merchant_advice_code' => $process_cybersource['merchant_advice_code']
                                                    ];
                                                    $data = $this->getResponsePayment($checkout_order_id, $params);
                                                    $data['status'] = 'REVIEW';
                                                }
                                            }
                                        } elseif ($result_code == 'REJECT') {
                                            $partner_payment_info = [
                                                'result_code' => $result_code,
//                                'reasonCode' => $result['reasonCode'],
                                                'bank_trans_id' => $process_cybersource['bank_trans_id'],
                                            ];

                                            $inputs = array(
                                                'transaction_id' => $transaction_id,
                                                'reason_id' => $process_cybersource['result_id'],
                                                'reason' => CyberSourceVcb3ds2::getErrorMessage($process_cybersource['result_id']),
                                                'partner_payment_info' => json_encode($partner_payment_info),
                                                'user_id' => 0,
                                            );
                                            $cancel = TransactionBusiness::failure($inputs);

                                            if ($cancel['error_message'] == '') {
                                                $inputs = array(
                                                    'checkout_order_id' => $checkout_order_id,
                                                    'user_id' => '0',
                                                );
                                                $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);

                                                if ($result['error_message'] === '') {
                                                    $error_code = '0102';


                                                    $params = [
                                                        'merchant_advice_code' => $request['amount'] == '8888' ? '03' : $request['amount'] == '9999' ? '21' : $process_cybersource['merchant_advice_code']
                                                    ];
                                                    $data = $this->getResponsePayment($checkout_order_id, $params);
                                                }
                                            } else {
                                                $error_code = '0101';
                                                $data = Translate::get("Lỗi xử lý thanh toán");
                                            }
                                        } else {
                                            $error_code = '17';
                                        }
                                    }

                                } else {
                                    $partner_payment_info = [
                                        'result_code' => "699",
                                        'bank_trans_id' => '',
                                    ];

                                    $inputs = array(
                                        'transaction_id' => $transaction->id,
                                        'reason_id' => "699",
                                        'reason' => "Thẻ tạm từ chối xử lý",
                                        'partner_payment_info' => json_encode($partner_payment_info),
                                        'user_id' => 0,
                                    );
                                    $cancel = TransactionBusiness::failure($inputs);

                                    if ($cancel['error_message'] == '') {
                                        $inputs = array(
                                            'checkout_order_id' => $checkout_order_id,
                                            'user_id' => '0',
                                        );
                                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                        if ($result['error_message'] === '') {
                                            $error_code = '0102';
                                            $params = [
                                                'merchant_advice_code' => null
                                            ];
                                            $data = $this->getResponsePayment($checkout_order_id, $params);
                                        }
                                    } else {
                                        $error_code = '0101';
                                        $data = Translate::get("Lỗi xử lý thanh toán");
                                    }
                                }
                            } else {
                                if (YII_DEBUG) {
                                    echo "<pre>";
                                    var_dump($add_request_payment_checkout_order);
                                    die();
                                }
                            }
                        } else {
                            $error_code = '0023';
                        }
                    } else {
                        $error_code = '0023';
                    }
                } else {
                    $error_code = '0023';
                }
            } else {
                $error_code = '0023';
            }
        } else {
            $error_code = '0018';
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    private function processCancel($request)
    {
        $error_code = '0001';
        $data = [];
        CyberSource::_writeLog('INPUT_CANCEL: ' . json_encode($request));
        $card_token = LinkCard::getByTokenMerchant($request['token_merchant']);
        CyberSource::_writeLog('OUTPUT_CANCEL: ' . json_encode($card_token));
        if ($card_token != false && $request['merchant_id'] == $card_token['merchant_id']) {
            $partner_payment = PartnerPayment::getById($card_token['partner_payment_id']);
            if (!empty($partner_payment)) {
                $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($card_token['merchant_id'], $partner_payment['id']);
                if (!empty($partner_payment_account_info)) {
                    $token_cybersource = Encryption::decryptAES($card_token['token_cybersource'], $GLOBALS['AES_KEY'], $card_token['iv']);
                    if ($partner_payment['code'] == 'CYBER-SOURCE') {
                        $cybersource = new CyberSource($card_token['merchant_id'], $card_token['partner_payment_id']);
                        $cancel_card_token = $cybersource->cancelAuthorizeCard([
                            'token' => $token_cybersource
                        ]);
                    } elseif ($partner_payment['code'] == 'CYBER-SOURCE-VCB') {
                        $cybersource = new CyberSourceVcb($card_token['merchant_id'], $card_token['partner_payment_id']);
                        $cancel_card_token = $cybersource->cancelAuthorizeCard([
                            'token' => $token_cybersource
                        ]);
                    } elseif ($partner_payment['code'] == 'CYBER-SOURCE-VCB-3DS2') {
                        $partner_payment_account_info_cp['partner_payment_account_info'] = $partner_payment_account_info;
                        $cbs_3ds2 = new CyberSourceVcb3ds2($partner_payment_account_info_cp);

                        $cancel_card_token = $cbs_3ds2->cancelAuthorizeCard([
                            'token' => $token_cybersource
                        ]);
                    } else {
                        $cancel_card_token['error'] = 'Lỗi không xác định';
                    }
                    if ($cancel_card_token['error'] == '') {
                        $update_cancel = LinkCardBusiness::updateCancel([
                            'id' => $card_token['id']
                        ]);
                        if ($update_cancel['error_message'] == '') {
                            $error_code = '0000';
                            $data = [
                                'token_status' => '3',
                            ];
                        }
                    } else {
                        $error_code = '18';
                    }
                } else {
                    $error_code = '18';
                }
            } else {
                $error_code = '18';
            }
        } else {
            $error_code = '0018';
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    /**
     * @throws \SoapFault
     */
    private function processCybersource($params)
    {

        $error_message = 'Lỗi không xác định';
        $result_code = null;
        $bank_trans_id = null;
        $merchant_advice_code = null;
        $result_id = null;
        $authorizationCode = null;

        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $params['transaction_id']]);
        if ($transaction_info) {
            $data_init['partner_payment_account_info'] = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($params['merchant_id'], $params['partner_payment_id']);
            $cybersource3ds2 = new CyberSourceVcb3ds2($data_init);
            $authorize_subcription = $cybersource3ds2->authorizeSubcription([
                'cashin_id' => $params['order_code'],
                'cashin_amount' => Transaction::getPartnerPaymentAmount($transaction_info),
                'token' => Encryption::decryptAES($params["token_cybersource"], $GLOBALS['AES_KEY'], $params['iv']),
            ], true);


            $result_id = $authorize_subcription->reasonCode;
            if ($authorize_subcription->decision == 'ACCEPT' && $authorize_subcription->reasonCode == '100' && $authorize_subcription->ccAuthReply->authorizationCode != "") {
                $error_message = '';
                $bank_trans_id = $authorize_subcription->requestID;
                $authorizationCode = $authorize_subcription->ccAuthReply->authorizationCode;
                $result_code = 'ACCEPT';
                $merchant_advice_code = $authorize_subcription->ccAuthReply->merchantAdviceCode ?? null;
            } else {
                if (CyberSourceVcb3ds2::checkVisaReviewForCardToken($authorize_subcription)) {
                    $error_message = '';
                    $bank_trans_id = $authorize_subcription->requestID;
                    $merchant_advice_code = $authorize_subcription->ccAuthReply->merchantAdviceCode ?? null;
                    $result_code = 'REVIEW';
                } elseif (CyberSourceVcb3ds2::isReject($authorize_subcription)) {
                    $error_message = CyberSourceVcb3ds2::getErrorMessage($authorize_subcription->reasonCode);
                    $result_code = 'REJECT';
                    $bank_trans_id = $authorize_subcription->requestID;
                    $merchant_advice_code = $authorize_subcription->ccAuthReply->merchantAdviceCode ?? null;

                    @CardDeclineBusiness::addDeclineResponse($authorize_subcription, [
                        'card_type' => strtolower(LinkCard::getBankCodeByCardType($params['card_token']['card_type'])),
                        'account_number' => $params['card_token']['card_number_mask']
                    ], $params['transaction_id']);

                } else {
                    $error_message = CyberSourceVcb3ds2::getErrorMessage($authorize_subcription->reasonCode);
                    $result_code = '3D';
                }
            }
        }
        return [
            'error_message' => $error_message,
            'result_code' => $result_code,
            'bank_trans_id' => $bank_trans_id,
            'merchant_advice_code' => $merchant_advice_code,
            'result_id' => $result_id,
            'authorizationCode' => $authorizationCode,
        ];
    }

    private function getResponsePayment($checkout_order_id, $params): array
    {
        $response = [];
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", [
            "id = :id", "id" => $checkout_order_id
        ]);
        if ($checkout_order_info) {
            if (intval($checkout_order_info['transaction_id']) != 0) {
                $transaction_info = Tables::selectOneDataTable("transaction", [
                    "id = :id AND checkout_order_id = :checkout_order_id ",
                    "id" => $checkout_order_info['transaction_id'],
                    "checkout_order_id" => $checkout_order_id
                ]);
            }
            $merchant_password = Merchant::getApiKey($checkout_order_info['merchant_id']);
            $checkout_order_info['function'] = 'return';
            $response = [
                'token_code' => $checkout_order_info['token_code'],
                'version' => strval($checkout_order_info['version']),
                'order_code' => $checkout_order_info['order_code'],
                'order_description' => $checkout_order_info['order_description'],
                'amount' => $checkout_order_info['amount'],
                'sender_fee' => floatval($checkout_order_info['sender_fee']),
                'receiver_fee' => floatval($checkout_order_info['receiver_fee']),
//                'currency' => $checkout_order_info['currency'],
//                'return_url' => $checkout_order_info['return_url'],
//                'cancel_url' => $checkout_order_info['cancel_url'],
//                'notify_url' => $checkout_order_info['notify_url'],
                'status' => intval($checkout_order_info['status']),
//                'payment_method_code' => @$payment_method_info['code'],
//                'payment_method_name' => @$payment_method_info['name'],
                'checksum' => $this->makeSignature($checkout_order_info, $merchant_password),
                'merchant_defined' => [
                    'merchant_advice_code' => $params['merchant_advice_code'],
                ]
            ];
        }
        return $response;
    }

    private function _validateTokenCode($value, &$checkout_order_info = false)
    {
        return CheckoutOrder::checkTokenCode($value, $checkout_order_info);
    }

    private function validateCreate($request, &$error_code = '0001')
    {
        if (!$this->validateVersion($request['version'])) {
            $error_code = '0004';
            return false;
        }
        $merchant_password = Merchant::getApiKey($request['merchant_id'], $this->merchant_info);
        if (!$merchant_password) {
            $error_code = '0003';
            return false;
        }
        if (!$this->validateFirstName($request['first_name'])) {
            $error_code = '0008';
            return false;
        }
        if (!$this->validateLastName($request['last_name'])) {
            $error_code = '0009';
            return false;
        }
        if (!$this->validateStreet($request['street'])) {
            $error_code = '0012';
            return false;
        }
        if (!$this->validateCity($request['city'])) {
            $error_code = '0020';
            return false;
        }
        if (!$this->validateState($request['state'])) {
            $error_code = '0021';
            return false;
        }
        if (!$this->validatePostalCode($request['postal_code'])) {
            $error_code = '0022';
            return false;
        }
        if (!$this->validateEmail($request['email'])) {
            $error_code = '0010';
            return false;
        }
        if (!$this->validatePhone($request['phone'])) {
            $error_code = '0011';
            return false;
        }
        if (!$this->validateUrl($request['notify_url'])) {
            $error_code = '0015';
            return false;
        }
        if (!$this->validateCustomerId($request['customer_id'])) {
            $error_code = "0026";
            return false;
        }
        if (!$this->validateChecksum($request, $merchant_password)) {
            $error_code = '0017';
            return false;
        }
        return true;
    }

    private function validateCheckToken($request, &$error_code = '0001')
    {
        if (!$this->validateVersion($request['version'])) {
            $error_code = '0004';
            return false;
        }
        $merchant_password = Merchant::getApiKey($request['merchant_id'], $this->merchant_info);
        if (!$merchant_password) {
            $error_code = '0003';
            return false;
        }
        if (!$this->validateTokenMerchant($request['token_merchant'])) {
            $error_code = '0018';
            return false;
        }
        if (!$this->validateFirstName($request['first_name'])) {
            $error_code = '0008';
            return false;
        }
        if (!$this->validateLastName($request['last_name'])) {
            $error_code = '0009';
            return false;
        }
        if (!$this->validateCustomerId($request['customer_id'])) {
            $error_code = "0026";
            return false;
        }
        if (!$this->validateChecksum($request, $merchant_password)) {
            $error_code = '0017';
            return false;
        }
        return true;
    }

    protected function validateCheckOrder($request, &$error_code = '0001')
    {
        if (!$this->validateVersion($request['version'])) {
            $error_code = '0004';
            return false;
        }
        $merchant_password = Merchant::getApiKey($request['merchant_id'], $this->merchant_info);
        if (!$merchant_password) {
            $error_code = '0003';
            return false;
        }
        if (!$this->validateTokenCode($request['token_code'])) {
            $error_code = "0019";
            return false;
        }
        if (!$this->validateChecksum($request, $merchant_password)) {
            $error_code = '0017';
            return false;
        }
        return true;
    }

    private function validatePayment($request, &$error_code = '0001')
    {
        if (!$this->validateVersion($request['version'])) {
            $error_code = '0004';
            return false;
        }
        $merchant_password = Merchant::getApiKey($request['merchant_id'], $this->merchant_info);
        if (!$merchant_password) {
            $error_code = '0003';
            return false;
        }
        if (!$this->validateTokenMerchant($request['token_merchant'])) {
            $error_code = '0018';
            return false;
        }
        if (!$this->validateCurrency($request['currency'])) {
            $error_code = '0027';
            return false;
        }
        if (!$this->validateAmount($request['amount'])) {
            $error_code = '0007';
            return false;
        }
        if (!$this->validateOrderCode($request['order_code'])) {
            $error_code = '0005';
            return false;
        }
        if (!$this->validateChecksum($request, $merchant_password)) {
            $error_code = '0017';
            return false;
        }
        return true;
    }

    private function validateCancel($request, &$error_code = '0001')
    {
        if (!$this->validateVersion($request['version'])) {
            $error_code = '0004';
            return false;
        }
        $merchant_password = Merchant::getApiKey($request['merchant_id'], $this->merchant_info);
        if (!$merchant_password) {
            $error_code = '0003';
            return false;
        }
        if (!$this->validateTokenMerchant($request['token_merchant'])) {
            $error_code = '0018';
            return false;
        }
        if (!$this->validateChecksum($request, $merchant_password)) {
            $error_code = '0017';
            return false;
        }
        return true;
    }

    private function validateChecksum($data, $key)
    {
        $checksum = $data['checksum'];
        $data_check = $data;
        unset($data_check['checksum']);
        $checksum_correct = $this->makeSignature($data_check, $key);
        self::writeLog('[LOG SIGNATURE][INPUT] ' . $data['checksum'] . " [OUTPUT] " . $checksum_correct);
        if ($checksum == $checksum_correct) {
            return true;
        }
        return false;
    }

    private function makeSignature($data, $hash_key)
    {
        if ($data['function'] == 'CheckOrder') {
            $data['function'] = $data['function_name'];
            unset($data['function_name']);
        }
        switch ($data['function']) {

            case 'create':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'customer_id',
                    'first_name',
                    'last_name',
                    'street',
                    'city',
                    'state',
                    'postal_code',
                    'email',
                    'phone',
                    'notify_url'
                ];
                break;
            case 'payment':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'token_merchant',
                    'amount',
                    'order_code'
                ];
                break;
            case 'cancel':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'token_merchant',
                ];
                break;
            case 'return':
                $list_data = [
                    'token_code',
                    'version',
                    'order_code',
                    'order_description',
                    'amount',
                    'sender_fee',
                    'receiver_fee',
                ];
                break;
            case 'CheckToken':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'token_merchant',
                    'customer_id',
                    'first_name' ? 'first_name' : '',
                    'last_name' ? 'last_name' : '',
                ];
                break;
            case 'CheckOrder':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'token_merchant',
                    'token_code'
                ];
                break;
            case 'checkOrder':
                $list_data = [
                    'function',
                    'version',
                    'merchant_id',
                    'token_merchant',
                    'token_code'
                ];
                break;
        }
        $hash_data = '';
        $is_first_key = false;
        foreach ($list_data as $key) {
            if ($is_first_key) {
                $hash_data .= '&' . $key . '=' . $data[$key];
            } else {
                $hash_data .= $key . '=' . $data[$key];
                $is_first_key = true;
            }
        }
//        var_dump(Encryption::hashHmacSHA256($hash_data, $hash_key));die;
        return Encryption::hashHmacSHA256($hash_data, $hash_key);
    }

}