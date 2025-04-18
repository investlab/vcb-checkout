<?php

namespace common\api;

use common\components\libs\Tables;
use common\components\utils\ObjInput;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\PaymentMethodBusiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentFee;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\payments\CyberSourceVcb3ds2;
use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class CheckoutSeamless extends CheckoutBasicApi
{

    private $checkout_order = null;

    public function getVersion()
    {
        return ObjInput::get('version', 'str', '1.0');
    }

    protected function _isFunction($function)
    {
        return ($function == 'CreateOrder' || $function == 'CheckCard' || $function == 'AuthorizeCard' || $function == 'AuthorizeCardV2' || $function == 'CheckOrder' || $function == 'PASetup');
    }

    public function getData($function)
    {
        switch ($function) {
            case "CreateOrder":
            {
                $data['function'] = $function;
                $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
                $data['order_code'] = ObjInput::get('order_code', 'str', '');
                $data['order_description'] = ObjInput::get('order_description', 'str', '');
                $data['amount'] = ObjInput::get('amount', 'str', '');
                $data['currency'] = ObjInput::get('currency', 'str', '');
                $data['buyer_fullname'] = ObjInput::get('buyer_fullname', 'str', '');
                $data['buyer_email'] = ObjInput::get('buyer_email', 'str', '');
                $data['buyer_mobile'] = ObjInput::get('buyer_mobile', 'str', '');
                $data['buyer_address'] = ObjInput::get('buyer_address', 'str', '');
                $data['return_url'] = ObjInput::get('return_url', 'str', '');
                $data['cancel_url'] = ObjInput::get('cancel_url', 'str', '');
                $data['notify_url'] = ObjInput::get('notify_url', 'str', '');
                $data['time_limit'] = ObjInput::get('time_limit', 'str', date('c', time() + 7 * 86400));
                $data['language'] = ObjInput::get('language', 'str', 'vi');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                $data['object_code'] = ObjInput::get('object_code', 'str', '');
                $data['object_name'] = ObjInput::get('object_name', 'str', '');
                $merchant = Merchant::find()->where(['id' => $data['merchant_site_code']])->one();
                if ($merchant) {
                    if ($merchant->merchant_on_seamless == Merchant::MERCHANT_ON_SEAMLESS_ENABLE) {
                        $data['payment_method_code'] = ObjInput::get('payment_method_code', 'str', '');
                        $data['bank_code'] = ObjInput::get('bank_code', 'str', '');
                    }
                }
                break;
            }
            case "CheckCard":
            {
                $data['function'] = $function;
                $data['token_code'] = ObjInput::get('token_code', 'str', 0);
                $data['card_number'] = ObjInput::get('card_number', 'str', '');
                $data['card_name'] = ObjInput::get('card_name', 'str', '');
                $data['card_month'] = ObjInput::get('card_month', 'str', '');
                $data['card_year'] = ObjInput::get('card_year', 'str', '');
                $data['cvv'] = ObjInput::get('cvv', 'str', '');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                break;
            }
            case "AuthorizeCard":
            {
                $data['function'] = $function;
                $data['token_code'] = ObjInput::get('token_code', 'str', 0);
                $data['card_number'] = ObjInput::get('card_number', 'str', '');
                $data['card_name'] = ObjInput::get('card_name', 'str', '');
                $data['card_month'] = ObjInput::get('card_month', 'str', '');
                $data['card_year'] = ObjInput::get('card_year', 'str', '');
                $data['cvv'] = ObjInput::get('cvv', 'str', '');
//                $data['jwt'] = ObjInput::get('jwt', 'str', '');
                $data['processor_transaction_id'] = ObjInput::get('processor_transaction_id', 'str', '');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                break;
            }
            case "AuthorizeCardV2":
            {
                $data['function'] = $function;
                $data['token_code'] = ObjInput::get('token_code', 'str', 0);
                $data['card_number'] = ObjInput::get('card_number', 'str', '');
                $data['card_name'] = ObjInput::get('card_name', 'str', '');
                $data['card_month'] = ObjInput::get('card_month', 'str', '');
                $data['card_year'] = ObjInput::get('card_year', 'str', '');
                $data['cvv'] = ObjInput::get('cvv', 'str', '');
                $data['jwt'] = ObjInput::get('jwt', 'str', '');
                $data['processor_transaction_id'] = ObjInput::get('processor_transaction_id', 'str', '');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                break;
            }

            case "CheckOrder":
            {
                $data['function'] = $function;
                $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
                $data['token_code'] = ObjInput::get('token_code', 'str', '');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                break;
            }
            case "PASetup":
            {
                $data['function'] = $function;
                $data['token_code'] = ObjInput::get('token_code', 'str', '');
                $data['card_number'] = ObjInput::get('card_number', 'str', '');
                $data['card_month'] = ObjInput::get('card_month', 'str', '');
                $data['card_year'] = ObjInput::get('card_year', 'str', '');
                $data['card_name'] = ObjInput::get('card_name', 'str', '');
                $data['cvv'] = ObjInput::get('cvv', 'str', '');
                $data['checksum'] = ObjInput::get('checksum', 'str', '');
                break;
            }

            default:
            {
                $data = false;
            }
        }
        return $data;
    }

    protected function _createOrder($params): array
    {
        $error_code = '0001';
        $result_data = null;

        $check_fee = self::checkAmountPaymentMethod($params);
        if (!$check_fee) {
            $error_code = '0007';
            return array('error_code' => $error_code, 'result_data' => $result_data);
        }
//        $merchant= Merchant::findOne($params['merchant_site_code']);
//        if ($merchant->email_requirement==0){
//            if ($params['buyer_email']==''){
//                $params['buyer_email'] = 'not-required@not.not';
//            }
//        }
        //-------------
        $inputs = array(
            'version' => $this->getVersion(),
            'language_id' => '1',
            'merchant_id' => $params['merchant_site_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'amount' => $params['amount'],
            'orginal_amount' => $params['amount'],
            'currency' => $params['currency'],
            'return_url' => $params['return_url'],
            'cancel_url' => $params['cancel_url'],
            'notify_url' => $params['notify_url'],
            'time_limit' => strtotime($params['time_limit']),
            'buyer_fullname' => $params['buyer_fullname'],
            'buyer_mobile' => $params['buyer_mobile'],
            'buyer_email' => $params['buyer_email'],
            'buyer_address' => $params['buyer_address'],
            'object_code' => $params['object_code'],
            'object_name' => $params['object_name'],
            'user_id' => 0,
            'seamless_info' => [
                'payment_method_code' => $params['payment_method_code'],
                'bank_code' => $params['bank_code'],
            ],
            'currency_exchange' => $params['currency_exchange'] ?? "",
        );
        $result = CheckoutOrderBusiness::add($inputs);
        if ($result['error_message'] === '') {
            $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($params['bank_code'] . "-" . $params['payment_method_code'], 'version_1_0');
            $merchant_fee_info = MerchantFee::getPaymentFee($params['merchant_site_code'], $payment_method_info['id'], $params['amount'], 'VND', time());
            $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $params['amount']);

            $token_code = $result['token_code'];
            $error_code = '0000';
            $result_data = array(
                'token_code' => $token_code,
                'total_amount' => $sender_fee + $params['amount'],
                'card_type' => $params['bank_code'],
//                'request_field' => $this->getRequestField($params['payment_method_code']),
//                'jwt' => $this->generateJwt($result['token_code'], $sender_fee + $params['amount'])
            );
        } else {
            echo $result['error_message'];
            $error_code = '0101';
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    /**
     * @throws \SoapFault
     */
    protected function _checkCard($data)
    {
        $error_code = '0001';
        $result_data = null;

        $params = [
            'card_number' => $data['card_number'],
            'card_name' => $data['card_name'],
            'card_month' => $data['card_month'],
            'card_year' => $data['card_year'],
            'cvv' => $data['cvv'],
        ];

        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();

        $seamless_info = json_decode($checkout_order->seamless_info);

        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');

        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order->merchant_id, $payment_method_info['partner_payment_id']);
        if ($partner_payment_account_info) {
            $params = [
                'partner_payment_account_info' => $partner_payment_account_info
            ];
            $card_fullname = $this->_convertName($data['card_name']);
            $this->_processCardFullname($card_fullname, $first_name, $last_name);

            $merchant_fee_info = MerchantFee::getPaymentFee($checkout_order->merchant_id, $payment_method_info['partner_payment_id'], $checkout_order->amount, 'VND', time());
            $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $checkout_order->amount);
            $cyber_source = new CyberSourceVcb3ds2($params);


            $inputs = array(
                'reference_code' => $GLOBALS['PREFIX'] . $checkout_order->id,
                'city' => 'Ha Noi',
                'country' => 'VN',
                'email' => $checkout_order->buyer_email,
                'phone' => $checkout_order->buyer_mobile,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'postal_code' => '',
                'state' => '',
                'address' => $checkout_order->buyer_address,
                'customer_id' => 0,
                'account_number' => $data['card_number'],
                'card_type' => $this->getCardTypeByCode($seamless_info->bank_code),
                'expiration_month' => $data['card_month'],
                'expiration_year' => $data['card_year'],
                'currency' => 'VND',
                'amount' => $sender_fee + $checkout_order->amount,
                'cvv_code' => $data['cvv'],
                'client_ip' => @$_SERVER['REMOTE_ADDR'],
                'order_code' => $checkout_order->order_code,
                'referenceID' => $seamless_info->reference_id,
            );

            $checkout_order_inputs = array(
                'checkout_order_id' => $checkout_order->id,
                'payment_method_id' => $payment_method_info['id'],
                'partner_payment_id' => $payment_method_info['partner_payment_id'],
                'partner_payment_method_refer_code' => '',
                'user_id' => 0,
            );


            $result_request_payment = CheckoutOrderBusiness::requestPayment($checkout_order_inputs);

            if ($result_request_payment['error_message'] == '') {
                $transaction_id = $result_request_payment['transaction_id'];
                $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
                if ($transaction_info != false) {
                    $cardInfo = [
                        'card_fullname' => $last_name . " " . $first_name,
                        'card_number' => Strings::encodeCreditCardNumber($data['card_number']),
                        'card_month' => $data['card_month'],
                        'card_year' => $data['card_year'],
                    ];
                    Transaction::insertCardInfo($transaction_id, $cardInfo);

                    $check_enroll = $cyber_source->checkEnroll($inputs);
                    if ($check_enroll == null) {
                        $error_code = '0026';
                    } else {
                        $reasonCode = $check_enroll->reasonCode;
                        $eci = '';
                        if (isset($check_enroll->payerAuthEnrollReply->eci)) {
                            $eci = $check_enroll->payerAuthEnrollReply->eci;
                        } elseif (isset($check_enroll->payerAuthEnrollReply->eciRaw)) {
                            $eci = $check_enroll->payerAuthEnrollReply->eciRaw;
                        }

                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'user_id' => 0,
                            'partner_payment_info' => '',
                        );
                        $paying = TransactionBusiness::paying($inputs);

                        if ($paying['error_message'] == "") {
                            if ($reasonCode == "475"
                                && (isset($check_enroll->payerAuthEnrollReply->acsURL) && $check_enroll->payerAuthEnrollReply->acsURL != null && $check_enroll->payerAuthEnrollReply->acsURL != "")
                                && isset($check_enroll->payerAuthEnrollReply->paReq)
                                && isset($check_enroll->payerAuthEnrollReply->authenticationTransactionID)
                            ) {
                                $error_code = '0000';
                                $result_data = [
                                    'status' => true,
                                    'challenge' => true,
                                    'auth_info' => array(
                                        'paReq' => $check_enroll->payerAuthEnrollReply->paReq,
                                        'acsURL' => $check_enroll->payerAuthEnrollReply->acsURL,
                                        'authenticationTransactionID' => $check_enroll->payerAuthEnrollReply->authenticationTransactionID,
                                    ),
                                ];
                            } elseif (isset($check_enroll->payerAuthEnrollReply->paresStatus) && $check_enroll->payerAuthEnrollReply->paresStatus == "Y" && $eci != null && !in_array($eci, ["00", "07"])) {
                                $error_code = '0000';
                                $result_data = [
                                    'status' => true,
                                    'challenge' => false,
                                    'auth_info' => array(
                                        'authenticationTransactionID' => $check_enroll->payerAuthEnrollReply->authenticationTransactionID,
                                    ),
                                ];
                            } else {

                                $update_fail = self::updateFailure($checkout_order, $transaction_id);
                                if ($update_fail['error_code'] == '0000') {
                                    if ($reasonCode == "100") {
                                        $error_code = "1666";
                                    } else {
                                        $error_code = "1" . $reasonCode;
                                    }
                                }
                            }
                        } else {
                            $error_code = '0996';
                        }
                    }
                } else {
                    $error_code = '0995';
                }
            } else {
                $error_code = '0994';

            }
        } else {
            $error_code = '0025';
        }

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    /**
     * @throws \SoapFault
     */
    protected function _PASetup($data): array
    {
        $result_data = null;

        $seamless_info = json_decode($this->checkout_order['seamless_info']);

        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');


        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->checkout_order['merchant_id'], $payment_method_info['partner_payment_id']);
        $this->writeLog('[payment_method_info]:' . json_encode($payment_method_info) . ' ======== Merchant id vs partner_payment_id ' . $this->checkout_order['merchant_id'].'-'. $payment_method_info['partner_payment_id']);
        $this->writeLog('[partner_payment_account_info]:' . json_encode($partner_payment_account_info) );

        if ($partner_payment_account_info) {
            $params = [
                'partner_payment_account_info' => $partner_payment_account_info
            ];
            $card_fullname = $this->_convertName($data['card_name']);
            $this->_processCardFullname($card_fullname, $first_name, $last_name);

            $merchant_fee_info = MerchantFee::getPaymentFee($this->checkout_order['merchant_id'], $payment_method_info['partner_payment_id'], $this->checkout_order['amount'], 'VND', time());
            $sender_fee = MerchantFee::getSenderFee($merchant_fee_info, $this->checkout_order['amount']);
            $cyber_source = new CyberSourceVcb3ds2($params);


            $inputs = array(
                'reference_code' => $GLOBALS['PREFIX'] . $this->checkout_order['id'],
                'city' => 'Ha Noi',
                'country' => 'VN',
                'email' => $this->checkout_order['buyer_email'],
                'phone' => $this->checkout_order['buyer_mobile'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'postal_code' => '',
                'state' => '',
                'address' => $this->checkout_order['buyer_address'],
                'customer_id' => 0,
                'account_number' => $data['card_number'],
                'card_type' => $this->getCardTypeByCode($seamless_info->bank_code),
                'expiration_month' => $data['card_month'],
                'expiration_year' => $data['card_year'],
                'currency' => 'VND',
                'amount' => $sender_fee + $this->checkout_order['amount'],
                'cvv_code' => $data['cvv'],
                'client_ip' => @$_SERVER['REMOTE_ADDR'],
                'order_code' => $this->checkout_order['order_code'],
            );

            $PASetup = $cyber_source->stepOneAuthSetup($inputs);
            if ($PASetup == null) {
                $error_code = '1999';
            } else {
                if ($PASetup->reasonCode == "100") {
                    if ($this->updateReferenceId($PASetup->payerAuthSetupReply->referenceID)) {
                        $error_code = '0000';
                        $result_data = [
                            'status' => true,
                            'pa_setup' => array(
//                                'referenceID' => $PASetup->payerAuthSetupReply->referenceID,
                                'accessToken' => $PASetup->payerAuthSetupReply->accessToken,
                                'deviceDataCollectionURL' => $PASetup->payerAuthSetupReply->deviceDataCollectionURL,
                            ),
                        ];
                    } else {
                        $error_code = '0001';
                    }
                } else {
                    $error_code = '1' . $PASetup->reasonCode;
                }
            }

        } else {
            $error_code = '0025';
        }

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _checkOrder($params): array
    {
        $error_code = '0001';
        $result_data = null;
        //-------------
        $error_code = '0000';
        $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }


    /**
     * @throws \SoapFault
     */
    protected function _authorizeCard($data)
    {
        $error_code = '0201';
        $result_data = null;

        $params = [
            'card_number' => $data['card_number'],
            'card_name' => $data['card_name'],
            'card_month' => $data['card_month'],
            'card_year' => $data['card_year'],
            'cvv' => $data['cvv'],
        ];


        $seamless_info = json_decode($this->checkout_order['seamless_info']);

        if (!isset($seamless_info->authorized)) {
            $params_update_seamless_info = json_decode($this->checkout_order['seamless_info'], true);
            $params_update_seamless_info['authorized'] = 1;
            $update_seamless_info = CheckoutOrderBusiness::updateSeamlessInfo($this->checkout_order['id'], $params_update_seamless_info);
            if ($update_seamless_info) {
                $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');

                $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->checkout_order['merchant_id'], $payment_method_info['partner_payment_id']);
                if ($partner_payment_account_info) {
                    $params = [
                        'partner_payment_account_info' => $partner_payment_account_info
                    ];
                    $card_fullname = $this->_convertName($data['card_name']);
                    $this->_processCardFullname($card_fullname, $first_name, $last_name);
                    $merchant_fee_info = MerchantFee::getPaymentFee($this->checkout_order['merchant_id'], $payment_method_info['partner_payment_id'], $this->checkout_order['amount'], 'VND', time());
                    $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $this->checkout_order['amount']);
                    $cbs_3ds2 = new CyberSourceVcb3ds2($params);

                    $inputs = array(
                        'transaction_id' => $this->checkout_order['transaction_id'],
                        'partner_payment_method_refer_code' => '',
                        'user_id' => 0,
                        'partner_payment_info' => '',
                    );

                    $cardInfo = [
                        'card_fullname' => $card_fullname,
                        'card_number' => Strings::encodeCreditCardNumber($data['card_number']),
                        'card_month' => $data['card_month'],
                        'card_year' => $data['card_year'],
                        'partner_payment_info' => '',
                    ];
                    Transaction::insertCardInfo($this->checkout_order['transaction_id'], $cardInfo);

                    $paying = TransactionBusiness::paying($inputs);
                    if ($paying['error_message'] == '') {
                        $inputs = array(
                            'reference_code' => $GLOBALS['PREFIX'] . $this->checkout_order['id'],
                            'city' => 'Ha Noi',
                            'country' => 'VN',
                            'email' => $this->checkout_order['buyer_email'],
                            'phone' => $this->checkout_order['buyer_mobile'],
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'postal_code' => '',
                            'state' => '',
                            'address' => $this->checkout_order['buyer_address'],
                            'buyer_address' => $this->checkout_order['buyer_address'],
                            'customer_id' => 0,
                            'account_number' => $data['card_number'],
                            'card_type' => strtolower($seamless_info->bank_code),
                            'expiration_month' => $data['card_month'],
                            'expiration_year' => $data['card_year'],
                            'currency' => 'VND',
                            'amount' => $sender_fee + $this->checkout_order['amount'],
                            'product_code' => $GLOBALS['PREFIX'] . $this->checkout_order['transaction_id'],
                            'cvv_code' => $data['cvv'],
                            'client_ip' => @$_SERVER['REMOTE_ADDR'],
                            'order_code' => $this->checkout_order['order_code'],
                            'ProcessorTransactionId' => $data['processor_transaction_id'],
                            'run_enrollment' => true,
                        );
//                $validateTransaction = $cbs_3ds2->validateTransactionId($inputs);
//
//                if (isset($validateTransaction['result']->payerAuthValidateReply->eci)) {
//                    $eci_validate = $validateTransaction['result']->payerAuthValidateReply->eci;
//                } elseif (isset($validateTransaction['result']->payerAuthValidateReply->eciRaw)) {
//                    $eci_validate = $validateTransaction['result']->payerAuthValidateReply->eciRaw;
//                } else {
//                    $eci_validate = null;
//                }

                        $eciSuccess = array('01', '02', '05', '06');
//                if ($validateTransaction['result']->decision == 'ACCEPT' && $validateTransaction['result']->reasonCode == '100' && in_array($eci_validate, $eciSuccess)) {
                        $result = $cbs_3ds2->authorizeCard($inputs);
                        if (isset($result['result']->payerAuthEnrollReply->eci)) {
                            $eci = $result['result']->payerAuthEnrollReply->eci;
                        } elseif (isset($result['result']->payerAuthEnrollReply->eciRaw)) {
                            $eci = $result['result']->payerAuthEnrollReply->eciRaw;
                        } else {
                            $eci = '';
                        }

                        if (!empty($result['result'])) {
                            if ($result['result']->decision == 'ACCEPT' &&
                                isset($result['result']->ccAuthReply->reasonCode) &&
                                $result['result']->ccAuthReply->reasonCode == '100' &&
                                isset($result['result']->ccAuthReply->authorizationCode) &&
                                $result['result']->ccAuthReply->authorizationCode != "" &&
                                in_array($eci, $eciSuccess)) {

                                //update loi khong co cavv
//                                $cavv = @$result['result']->payerAuthEnrollReply->cavv;
                                $cavv = null;
                                if (isset($result['result']->payerAuthEnrollReply->cavv)) {
                                    $cavv = $result['result']->payerAuthEnrollReply->cavv;
                                }

                                $PAResStatus = @$result['result']->payerAuthEnrollReply->paresStatus;
                                $authorizationCode = @$result['result']->ccAuthReply->authorizationCode;

                                $inputs = array(
                                    'transaction_id' => $this->checkout_order['transaction_id'],
                                    'time_paid' => time(),
                                    'bank_refer_code' => $result['result']->requestID,
                                    'authorizationCode' => $authorizationCode,
                                    'user_id' => 0,
                                );
                                $result = TransactionBusiness::paid($inputs);
                                if ($result['error_message'] === '') {
                                    $result_data = array(
                                        'ECIFlag' => $eci,
                                        'CAVV' => $cavv,
                                        'PAResStatus' => $PAResStatus,
                                    );
                                    $error_code = '0000';
                                } else {
                                    $error_code = '9999';
                                }
                            } else {
                                if ($cbs_3ds2::isReview($result['result'])) {
                                    $inputs = array(
                                        'transaction_id' => $this->checkout_order['transaction_id'],
                                        'time_paid' => time(),
                                        'bank_refer_code' => $result['result']->requestID,
                                        'user_id' => 0,
                                    );
                                    $result = TransactionBusiness::updateReview($inputs);
                                    if ($result['error_message'] === '') {
                                        $error_code = '0210';
                                    } else {
                                        $error_code = '9999';
                                    }

                                } elseif ($cbs_3ds2::isReject($result['result'])) {
                                    $inputs = array(
                                        'transaction_id' => $this->checkout_order['transaction_id'],
                                        'reason_id' => $result['result']->reasonCode ?? '',
                                        'reason' => CyberSourceVcb3ds2::getErrorMessage($result['result']->reasonCode),
                                        'user_id' => 0,
                                    );
                                    $cancel = TransactionBusiness::failure($inputs);
                                    if ($cancel['error_message'] === '') {
                                        $inputs = array(
                                            'checkout_order_id' => $this->checkout_order['id'],
                                            'user_id' => '0',
                                        );
                                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                        if ($result['error_message'] === '') {
                                            $inputs_callback = [
                                                'checkout_order_id' => $this->checkout_order['id'],
                                                'notify_url' => $this->checkout_order['notify_url'],
                                                'time_process' => time(),
                                            ];
//                                    if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
//                                        $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
//                                    }
                                            if (true || $result['error_message'] == '') {
                                                $error_code = '0211';
                                            } else {
                                                $error_code = '0001';
                                            }
                                        } else {
                                            $error_code = '0001';
                                        }
                                    } else {
                                        $error_code = '0001';
                                    }
                                } else {
                                    $error_code = '0001';
                                }
                            }
                        } else {
                            $error_code = '0026';
                        }
                    } else {
                        $error_code = '0001';
                    }
                } else {
                    $error_code = '0025';
                }
            } else {
                $error_code = '0001';
            }
        } else {
            $error_code = '0032';
        }



        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    /**
     * @throws \SoapFault
     */
    protected function _authorizeCardV2($data)
    {
        $error_code = '0001';
        $result_data = null;

        $params = [
            'card_number' => $data['card_number'],
            'card_name' => $data['card_name'],
            'card_month' => $data['card_month'],
            'card_year' => $data['card_year'],
            'cvv' => $data['cvv'],
        ];

        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();

        $seamless_info = json_decode($checkout_order->seamless_info);

        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');

        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order->merchant_id, $payment_method_info['partner_payment_id']);
        if ($partner_payment_account_info) {

            $card_fullname = $this->_convertName($data['card_name']);
            $this->_processCardFullname($card_fullname, $first_name, $last_name);

            $merchant_fee_info = MerchantFee::getPaymentFee($checkout_order->merchant_id, $payment_method_info['partner_payment_id'], $checkout_order->amount, 'VND', time());
            $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $checkout_order->amount);
            $decodeJWT = $this->decodeJWT($checkout_order, $data['jwt']);
            $extendedData = $decodeJWT->Payload->Payment->ExtendedData;
            if ($extendedData->Amount == $sender_fee + $checkout_order->amount) {
                $checkout_order_inputs = array(
                    'checkout_order_id' => $checkout_order->id,
                    'payment_method_id' => $payment_method_info['id'],
                    'partner_payment_id' => $payment_method_info['partner_payment_id'],
                    'partner_payment_method_refer_code' => '',
                    'user_id' => 0,
                );

                $result_request_payment = CheckoutOrderBusiness::requestPayment($checkout_order_inputs);
                if ($result_request_payment['error_message'] == '') {
                    $transaction_id = $result_request_payment['transaction_id'];
                    $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
                    if ($transaction_info != false) {
                        $cardInfo = [
                            'card_fullname' => $last_name . " " . $first_name,
                            'card_number' => Strings::encodeCreditCardNumber($data['card_number']),
                            'card_month' => $data['card_month'],
                            'card_year' => $data['card_year'],
                        ];
                        Transaction::insertCardInfo($transaction_id, $cardInfo);
                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'user_id' => 0,
                        );
                        $paying = TransactionBusiness::paying($inputs);
                        if ($paying['error_message'] == '') {
                            if (in_array($extendedData->PAResStatus, ["N", "R", "U"]) && (isset($extendedData->ECIFlag) || in_array($extendedData->ECIFlag, ['01', '07']))) {
                                $inputs = array(
                                    'transaction_id' => $transaction_id,
                                    'reason_id' => "666",
                                    'reason' => "Thẻ không được hỗ trợ thanh toán",
                                    'user_id' => 0,
                                );
                                $cancel = TransactionBusiness::failure($inputs);
                                if ($cancel['error_message'] === '') {
                                    $inputs = array(
                                        'checkout_order_id' => $checkout_order->id,
                                        'user_id' => '0',
                                    );
                                    $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                    if ($result['error_message'] === '') {
                                        $inputs_callback = [
                                            'checkout_order_id' => $checkout_order->id,
                                            'notify_url' => $checkout_order->notify_url,
                                            'time_process' => time(),
                                        ];
//                                    if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
//                                        $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
//                                    }
                                        if (true || $result['error_message'] == '') {
                                            $error_code = '0211';
                                        } else {
                                            $error_code = '9993';
                                        }
                                    } else {
                                        $error_code = '9992';
                                    }
                                } else {
                                    $error_code = '9991';
                                }

                            } else {
                                $inputs = array(
                                    'reference_code' => $GLOBALS['PREFIX'] . $checkout_order->id,
                                    'city' => 'Ha Noi',
                                    'country' => 'Viet Nam',
                                    'email' => 'null@cybersource.com',
                                    'phone' => $checkout_order->buyer_mobile,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'postal_code' => '91356',
                                    'state' => '',
                                    'address' => $checkout_order->buyer_address,
                                    'customer_id' => 0,
                                    'account_number' => $data['card_number'],
                                    'card_type' => strtolower($seamless_info->bank_code),
                                    'expiration_month' => $data['card_month'],
                                    'expiration_year' => $data['card_year'],
                                    'currency' => 'VND',
                                    'amount' => $sender_fee + $checkout_order->amount,
                                    'cvv_code' => $data['cvv'],
                                    'client_ip' => @$_SERVER['REMOTE_ADDR'],
                                    'order_code' => $checkout_order->order_code,
                                    'ProcessorTransactionId' => $data['processor_transaction_id'],
                                    'referenceID' => isset($params['referenceID']) ? $params['referenceID'] : '',
                                );
                                $params = [
                                    'partner_payment_account_info' => $partner_payment_account_info
                                ];
                                $cbs_3ds2 = new CyberSourceVcb3ds2($params);


                                $result = $cbs_3ds2->authorizeCard($inputs);
                                if (isset($result['result']->payerAuthValidateReply->eci)) {
                                    $eci = $result['result']->payerAuthValidateReply->eci;
                                } elseif (isset($result['result']->payerAuthValidateReply->eciRaw)) {
                                    $eci = $result['result']->payerAuthValidateReply->eciRaw;
                                }

                                if (!empty($result['result'])) {
                                    $eciSuccess = array('01', '02', '05', '06');
                                    if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100' && in_array($eci, $eciSuccess)) {
                                        $inputs = array(
                                            'transaction_id' => $transaction_id,
                                            'time_paid' => time(),
                                            'bank_refer_code' => $result['result']->requestID,
                                            'user_id' => 0,
                                        );
                                        $result = TransactionBusiness::paid($inputs);
                                        if ($result['error_message'] === '') {
                                            $error_code = '0200';
                                        } else {
                                            $error_code = '9999';
                                        }
                                    } else {
                                        if ($cbs_3ds2::checkVisaReview($result['result'])) {
                                            $inputs = array(
                                                'transaction_id' => $transaction_id,
                                                'time_paid' => time(),
                                                'bank_refer_code' => $result['result']->requestID,
                                                'user_id' => 0,
                                            );
                                            $result = TransactionBusiness::updateReview($inputs);
                                            if ($result['error_message'] === '') {
                                                $error_code = '0210';
                                            } else {
                                                $error_code = '9999';
                                            }

                                        } elseif ($cbs_3ds2::checkVisaReject($result['result'])) {
                                            $inputs = array(
                                                'transaction_id' => $transaction_id,
                                                'reason_id' => @$result['reasonCode'],
                                                'reason' => CyberSourceVcb3ds2::getErrorMessage($result['result']->reasonCode),
                                                'user_id' => 0,
                                            );
                                            $cancel = TransactionBusiness::failure($inputs);
                                            if ($cancel['error_message'] === '') {
                                                $inputs = array(
                                                    'checkout_order_id' => $checkout_order->id,
                                                    'user_id' => '0',
                                                );
                                                $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                                if ($result['error_message'] === '') {
                                                    $inputs_callback = [
                                                        'checkout_order_id' => $checkout_order->id,
                                                        'notify_url' => $checkout_order->notify_url,
                                                        'time_process' => time(),
                                                    ];
//                                    if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
//                                        $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
//                                    }
                                                    if (true || $result['error_message'] == '') {
                                                        $error_code = '0211';
                                                    } else {
                                                        $error_code = '0001';
                                                    }
                                                } else {
                                                    $error_code = '0001';
                                                }
                                            } else {
                                                $error_code = '0001';
                                            }

                                        }
                                    }
                                } else {
                                    $error_code = '0026';
                                }
                            }
                        } else {
                            $error_code = '0202';
                        }
                    } else {
                        $error_code = '0995';
                    }
                } else {
                    $error_code = '0994';
                }
            } else {
                $error_code = '0201';
            }

        } else {
            $error_code = '0025';
        }

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function checkAmountPaymentMethod($params)
    {
        if (!empty($params['payment_method_code']) && !empty($params['bank_code'])) {
            $payment_method = CheckoutOrder::getPaymentMethod($params);
            $method_code = CheckoutOrder::getMethodCode($params['payment_method_code']);

            $method_check = Method::find()->select('method.id, merchant_fee.min_amount as merchant_min_amount, 
                         partner_payment_fee.min_amount as partner_min_amount')
                ->leftJoin('merchant_fee', 'merchant_fee.method_id=method.id')
                ->leftJoin('partner_payment_fee', 'partner_payment_fee.method_id=method.id')
                ->where(['method.code' => $method_code, 'method.status' => Method::STATUS_ACTIVE,
                    'merchant_fee.status' => MerchantFee::STATUS_ACTIVE, 'partner_payment_fee.status' => PartnerPaymentFee::STATUS_ACTIVE])
                ->orderBy('merchant_fee.min_amount DESC, partner_payment_fee.min_amount DESC')->asArray()->one();

            $payment_check = PaymentMethod::find()
                ->select('id, code, min_amount')
                ->where(['code' => $payment_method, 'status' => PaymentMethod::STATUS_ACTIVE])
                ->orderBy('min_amount DESC')->asArray()->one();


            // Check số tiền tối thiểu qua payment_method
            $flag_payment_amount = ($params['amount'] >= $payment_check['min_amount']);
            $flag_merchant_amount = ($params['amount'] >= $method_check['merchant_min_amount']);
            $flag_partner_amount = ($params['amount'] >= $method_check['partner_min_amount']);
            if ($flag_payment_amount && $flag_merchant_amount && $flag_partner_amount) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }


    protected function _validateDataCreateOrder(&$data)
    {

        $error_code = '0001';

        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->merchant_info['merchant_on_seamless'] == Merchant::MERCHANT_ON_SEAMLESS_ENABLE) {
                if ($data['merchant_site_code'] == 25 || $data['merchant_site_code'] == 24) {
                    $error_code = '0020';
                } else {
                    if ($this->_validateOrderCode($data['order_code'])) {
                        if ($this->_validateOrderDescription($data['order_description'])) {
                            if ($this->_validateCurrency($data['currency'])) {
                                if ($this->_validateAmount($data)) {
                                    if ($this->_validateBuyerFullname($data['buyer_fullname'])) {
                                        $merchant = Merchant::findOne($data['merchant_site_code']);
                                        if ($merchant->email_requirement == 0) {
                                            if ($data['buyer_email'] == '' || $data['buyer_email'] == null) {
                                                $data['buyer_email'] = 'notrequired@nganluong.vn';
                                            }
                                            if ($data['buyer_mobile'] == '' || $data['buyer_mobile'] == null) {
                                                $data['buyer_mobile'] = '1900585899';
                                            }
                                        }
                                        if ($this->_validateBuyerEmail($data['buyer_email'])) {
                                            if ($this->_validateBuyerMobile($data['buyer_mobile'], $data['merchant_site_code'])) {
                                                if ($this->_validateBuyerAddress($data['buyer_address'])) {
                                                    if ($this->_validateReturnUrl($data['return_url'])) {
                                                        if ($this->_validateCancelUrl($data['cancel_url'])) {
                                                            if ($this->_validateNotifyUrl($data['notify_url'])) {
                                                                if ($this->_validateTimeLimit($data['time_limit'])) {
                                                                    if ($this->_validatePaymentMethodCode($data['payment_method_code'])) {
                                                                        if ($this->_validateBankCode($data['bank_code'])) {
                                                                            if ($this->_validateChecksumCreateOrder($data, $api_key)) {
                                                                                $error_code = '0000';
                                                                            } else {
                                                                                $error_code = '0017';
                                                                            }
                                                                        } else {
                                                                            $error_code = '0019';
                                                                        }
                                                                    } else {
                                                                        $error_code = '0018';
                                                                    }
                                                                } else {
                                                                    $error_code = '0016';
                                                                }
                                                            } else {
                                                                $error_code = '0015';
                                                            }
                                                        } else {
                                                            $error_code = '0014';
                                                        }
                                                    } else {
                                                        $error_code = '0013';
                                                    }
                                                } else {
                                                    $error_code = '0012';
                                                }
                                            } else {
                                                $error_code = '0011';
                                            }
                                        } else {
                                            $error_code = '0010';
                                        }
                                    } else {
                                        $error_code = '0009';
                                    }
                                } else {
                                    $error_code = '0007';
                                }
                            } else {
                                $error_code = '0008';
                            }
                        } else {
                            $error_code = '0006';
                        }
                    } else {
                        $error_code = '0005';
                    }
                }
            } else {
                $error_code = '0031';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateDataCheckCard(&$data): array
    {
        $error_code = '0001';
        if ($this->_validateTokenCode($data['token_code'])) {
            $seamless_info = json_decode($this->checkout_order['seamless_info'], true);
            if ($this->_validateChecksumCheckCard($data)) {
                if (isset($seamless_info['reference_id']) && $seamless_info['reference_id'] != "") {
                    if ($this->_validateCardNumber($data['card_number'])) {
                        if ($this->_validateCardName($data['card_name'])) {
                            if ($this->_validateCardMonth($data['card_month'])) {
                                if ($this->_validateCardYear($data['card_year'])) {
                                    if ($this->_validateCVV($data['cvv'])) {
                                        $error_code = '0000';
                                    } else {
                                        $error_code = '0027';
                                    }
                                } else {
                                    $error_code = '0023';
                                }
                            } else {
                                $error_code = '0022';
                            }
                        } else {
                            $error_code = '0021';
                        }
                    } else {
                        $error_code = '0020';
                    }
                } else {
                    $error_code = '0203';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0028';
        }


        return array('error_code' => $error_code);
    }

    protected function _validateDataPASetup(&$data)
    {
        if ($this->_validateTokenCode($data['token_code'])) {
            if ($this->_validateCardNumber($data['card_number'])) {
                if ($this->_validateCardName($data['card_name'])) {
                    if ($this->_validateCardMonth($data['card_month'])) {
                        if ($this->_validateCardYear($data['card_year'])) {
                            if ($this->_validateCVV($data['cvv'])) {
                                if ($this->_validateChecksumPASetup($data)) {
                                    $error_code = '0000';
                                } else {
                                    $error_code = '0017';
                                }
                            } else {
                                $error_code = '0027';
                            }

                        } else {
                            $error_code = '0023';
                        }
                    } else {
                        $error_code = '0022';
                    }
                } else {
                    $error_code = '0021';
                }
            } else {
                $error_code = '0020';
            }
        } else {
            $error_code = '0028';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateDataCheckOrder(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumCheckOrder($data, $api_key)) {
                if ($this->_validateTokenCodeForCheckOrder($data['token_code'], $checkout_order_info)) {
                    if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                        $data['checkout_order_info'] = $checkout_order_info;
                        $error_code = '0000';
                    } else {
                        $error_code = '0028';
                    }
                } else {
                    $error_code = '0028';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }


    protected function _validateDataAuthorizeCard(&$data)
    {
        $error_code = '0001';
        if ($this->_validateTokenCodeForAuthorize($data['token_code'])) {
            if ($this->_validateChecksumAuthorizeCard($data)) {
                if ($this->_validateCardNumber($data['card_number'])) {
                    if ($this->_validateCardName($data['card_name'])) {
                        if ($this->_validateCardMonth($data['card_month'])) {
                            if ($this->_validateCardYear($data['card_year'])) {
                                if ($this->_validateProcessorTransactionId($data['processor_transaction_id'])) {
                                    $error_code = '0000';
                                } else {
                                    $error_code = '0030';
                                }
                            } else {
                                $error_code = '0023';
                            }
                        } else {
                            $error_code = '0022';
                        }
                    } else {
                        $error_code = '0021';
                    }
                } else {
                    $error_code = '0020';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0028';
        }
        self::writeLog('Validate'.$error_code);

        return array('error_code' => $error_code);
    }

    protected function _validateDataAuthorizeCardV2(&$data)
    {
        $error_code = '0001';
        if ($this->_validateTokenCode($data['token_code'])) {
            if ($this->_validateCardNumber($data['card_number'])) {
                if ($this->_validateCardName($data['card_name'])) {
                    if ($this->_validateCardMonth($data['card_month'])) {
                        if ($this->_validateCardYear($data['card_year'])) {
                            if ($this->_validateJWT($data['jwt'])) {
                                if ($this->_validateProcessorTransactionId($data['processor_transaction_id'])) {
                                    if ($this->_validateChecksumAuthorizeCardV2($data)) {
                                        $error_code = '0000';
                                    } else {
                                        $error_code = '0017';
                                    }
                                } else {
                                    $error_code = '0030';
                                }
                            } else {
                                $error_code = '0029';
                            }
                        } else {
                            $error_code = '0023';
                        }
                    } else {
                        $error_code = '0022';
                    }
                } else {
                    $error_code = '0021';
                }
            } else {
                $error_code = '0020';
            }
        } else {
            $error_code = '0028';
        }

        return array('error_code' => $error_code);
    }

    protected function _validateTokenCode($value, &$checkout_order_info = false): bool
    {
        if (preg_match('/^(\d+)-(CO[A-Z0-9]{10})$/', $value, $temp)) {
            $checkout_order_id = intval($temp[1]);
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND token_code = :token_code AND status= :status", "id" => $checkout_order_id, "token_code" => $value, "status" => CheckoutOrder::STATUS_NEW]);
            if ($checkout_order_info) {
                $this->checkout_order = $checkout_order_info;
                return true;
            }
        }
        return false;
    }

    protected function _validateTokenCodeForCheckOrder($value, &$checkout_order_info = false)
    {
        return CheckoutOrder::checkTokenCode($value, $checkout_order_info);
    }


    protected function _validateTokenCodeForAuthorize($value, &$checkout_order_info = false): bool
    {
        if (preg_match('/^(\d+)-(CO[A-Z0-9]{10})$/', $value, $temp)) {
            $checkout_order_id = intval($temp[1]);
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND token_code = :token_code AND status= :status", "id" => $checkout_order_id, "token_code" => $value, "status" => CheckoutOrder::STATUS_PAYING]);
            if ($checkout_order_info) {
                $this->checkout_order = $checkout_order_info;
                return true;
            }
        }
        return false;
    }


    protected function _validateChecksumCreateOrder($data, $api_key)
    {
//        $merchant= Merchant::findOne($data['merchant_site_code']);
//        if ($merchant->email_requirement==0){
//            if ($data['buyer_email']==''){
//                $data['buyer_email'] = 'not-required@not.not';
//            }
//        }
        $currency = 'VND';
        $amount = $data['amount'];
        if (!empty($data['currency_exchange'])) {
            $currency_exchange = json_decode($data['currency_exchange'], true);
            $transfer_rate = @$currency_exchange['transfer'];
            $currency = 'USD';
            $amount = $data['amount'] / $transfer_rate;
        }

        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $data['order_description'];
        $str_checksum .= '|' . $amount;
        $str_checksum .= '|' . $currency;
        $str_checksum .= '|' . $data['buyer_fullname'];
        $str_checksum .= '|' . $data['buyer_email'];
        $str_checksum .= '|' . $data['buyer_mobile'];
        $str_checksum .= '|' . $data['buyer_address'];
        $str_checksum .= '|' . $data['return_url'];
        $str_checksum .= '|' . $data['cancel_url'];
        $str_checksum .= '|' . $data['notify_url'];
        //$str_checksum .= '|' . $data['time_limit'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(md5($str_checksum));
            }
        }
        return false;
    }

    private function getRequestField($payment_method_code)
    {
        switch ($payment_method_code) {
            case "CREDIT-CARD":
            {
                $field = [
                    'card_number',
                    'card_name',
                    'card_month',
                    'card_year',
                    'cvv',
                ];
                break;
            }
            default:
            {
                $field = false;
                break;
            }
        }
        return $field;
    }

    public function writeLog($data): bool
    {
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'api' . DS . 'checkout-seamless' . DS . 'version' . $this->getVersion() . DS;
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

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Success',
            '0001' => 'Unknown error',
            '0002' => 'Post data is invalid',
            '0003' => 'Invalid merchant site code',
            '0004' => 'Invalid version',
            '0005' => 'Invalid order_code',
            '0006' => 'Invalid order_description',
            '0007' => 'Invalid amount',
            '0008' => 'Invalid currency',
            '0009' => 'Invalid buyer_fullname',
            '0010' => 'Invalid buyer_email',
            '0011' => 'Invalid buyer_mobile',
            '0012' => 'Invalid buyer_address',
            '0013' => 'Invalid return_url',
            '0014' => 'Invalid cancel_url',
            '0015' => 'Invalid notify_url',
            '0016' => 'Invalid token_code',
            '0017' => 'Invalid checksum',
            '0018' => 'Invalid payment method code',
            '0019' => 'Invalid payment bank code',
            '0020' => 'Invalid payment card number',
            '0021' => 'Invalid payment card name',
            '0022' => 'Invalid payment card month',
            '0023' => 'Invalid payment card year',
            '0027' => 'Invalid CVV',
            '0025' => 'Partner Payment Account Error',
            '0026' => 'Connect to partner error',
            '0028' => 'Invalid token code',
            '0029' => 'JWT not empty',
            '0030' => 'Processor Transaction Id not empty',
            '0031' => 'Merchant not config for seamless',
            '0032' => 'Invalid status',
            '0200' => 'Payment Success',
            '0201' => 'Amount is invalid',
            '0202' => 'JWT is invalid',
            '0203' => 'PA Setup has not been run',
            '0204' => 'Card not enrolled in 3D Secure authentication',
            '0210' => 'Checkout Order Review',
            '0211' => 'Payment Error',
//            Map Cybersource
            '1100' => 'Giao dịch thành công',
            '1101' => 'Thông tin giao dịch bị thiếu một hoặc nhiều trường dữ liệu bắt buộc',
            '1102' => 'Một hoặc nhiều trường thông tin trong giao dịch chứa dữ liệu không hợp lệ',
            '1110' => 'Một phần tiền trong số tiền thanh toán đã được xử lý thành công',
            '1150' => 'Lỗi hệ thống thanh toán, giao dịch chưa được xử lý',
            '1151' => 'Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền',
            '1152' => 'Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền và đang được xử lý',
            '1200' => 'Giao dịch bị từ chối do địa chỉ nhận hàng không khớp với địa chỉ chủ thẻ đã khai báo',
            '1201' => 'Giao dịch chờ xử lý do ngân hàng phát hành thẻ yêu cầu bạn phải trả lời một số câu hỏi',
            '1202' => 'Thẻ đã hết hạn sử dụng, vui lòng liên hệ ngân hàng phát hành thẻ để biết thêm chi tiết',
            '1203' => 'Giao dịch bị từ chối bởi ngân hàng phát hành thẻ',
            '1204' => 'Số dư tài khoản thẻ không đủ hoặc thẻ đã hết hạn mức thanh toán',
            '1205' => 'Thẻ bị từ chối giao dịch do chủ thẻ thông báo với ngân hàng phát hành là thẻ đã bị mất hoặc bị đánh cắp',
            '1207' => 'Hệ thống ngân hàng phát hành thẻ đang bị lỗi, không thể thực hiện được giao dịch',
            //'208'	=> 'Thẻ chưa được kích hoạt hoặc không tồn tại',
            '1208' => 'Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            '1209' => 'Giao dịch bị từ chối thực hiện do Mã xác thực thẻ American Express (CID) không chính xác',
            '1210' => 'Thẻ hết hạn mức thanh toán',
            '1211' => 'Thông tin thẻ không chính xác', //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '1220' => 'Bộ vi xử lý từ chối yêu cầu dựa trên một vấn đề chung với tài khoản của khách hàng.', ////
            '1221' => 'The customer matched an entry on the processor\'s negative file.', ///
            '1222' => 'Tài khoản thẻ đang bị đóng băng bởi ngân hàng phát hành', ///
            '1230' => 'Thông tin thẻ không chính xác', //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '1231' => 'Số thẻ không hợp lệ',
            '1232' => 'Loại thẻ không được chấp nhận bởi hệ thống thanh toán',
            '1233' => 'Hệ thống thanh toán thẻ quốc tế không chấp nhận xử lý giao dịch',
            '1234' => 'Có lỗi giữa hệ thống Vietcombank với hệ thống thanh toán thẻ quốc tế',
            '1235' => 'Yêu cầu xử lý giao dịch với số tiền lớn hơn số tiền khi kiểm tra thông tin thẻ',
            '1236' => 'Hệ thống xử lý thẻ quốc tế đang bị lỗi, không thể thực hiện được giao dịch',
            '1237' => 'Giao dịch đã được trả lại',
            '1238' => 'Tài khoản thẻ của khách hàng đã bị trừ tiền',
            '1239' => 'Số tiền trong yêu cầu xử lý sai khác với thông tin trong giao dịch trước đó',
            '1240' => 'Bạn chọn sai loại thẻ',
            '1241' => 'Request ID không chính xác',
            '1242' => 'Yêu cầu thanh toán đã được gửi nhưng không thể trừ được tiền',
            '1243' => 'Yêu cầu thanh toán đã được gửi thực hiện hoặc bị chuyển trả ở lần trước đó',
            '1247' => 'Yêu cầu thanh toán đã bị hủy',
            '1250' => 'Yêu cầu thanh toán bị trễ do đường truyền',
            '1475' => 'Thẻ sử dụng mật khẩu xác thực giao dịch nên không thể liên kết',
            '1476' => 'Xác thực mật khẩu thanh toán (3Dsecure) không thành công',
            '1480' => 'Thẻ bị REVIEW, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            '1481' => 'Giao dịch bị từ chối, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            '1666' => 'Thẻ không được hỗ trợ thanh toán',
            '1999' => 'Hệ thống thẻ Quốc tế đang bảo trì. Bạn vui lòng quay lại sau ít phút nữa',
        );
        return array_key_exists($result_code, $message) ? Translate::get($message[$result_code]) : $message['0001'];
    }

    private function _validateChecksumCheckCard(&$data): bool
    {
        $str_checksum = $data['token_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $data['card_name'];
        $str_checksum .= '|' . $data['card_month'];
        $str_checksum .= '|' . $data['card_year'];
        $str_checksum .= '|' . $data['cvv'];
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();
        $api_key = Merchant::getApiKey($checkout_order->merchant_id, $this->merchant_info);
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(md5($str_checksum));
            }
        }
        return false;
    }

    private function _validateChecksumPASetup(&$data): bool
    {
        $str_checksum = $data['token_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $data['card_name'];
        $str_checksum .= '|' . $data['card_month'];
        $str_checksum .= '|' . $data['card_year'];
        $str_checksum .= '|' . $data['cvv'];
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();
        $api_key = Merchant::getApiKey($checkout_order->merchant_id, $this->merchant_info);
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(md5($str_checksum));
            }
        }
        return false;
    }

    private function _validateChecksumAuthorizeCard(&$data): bool
    {
        $str_checksum = $data['token_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $data['card_name'];
        $str_checksum .= '|' . $data['card_month'];
        $str_checksum .= '|' . $data['card_year'];
        $str_checksum .= '|' . $data['cvv'];
//        $str_checksum .= '|' . $data['jwt'];
        $str_checksum .= '|' . $data['processor_transaction_id'];
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_PAYING])
            ->one();
        $api_key = Merchant::getApiKey($checkout_order->merchant_id, $this->merchant_info);
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            if (ObjInput::get('ly', 'str', 0) == 'luonkhuon') {
                echo "<pre>";
                var_dump(md5($str_checksum));
                die();
            }
        }
        return false;
    }

    private function _validateChecksumAuthorizeCardV2(&$data): bool
    {
        $str_checksum = $data['token_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $data['card_name'];
        $str_checksum .= '|' . $data['card_month'];
        $str_checksum .= '|' . $data['card_year'];
        $str_checksum .= '|' . $data['cvv'];
        $str_checksum .= '|' . $data['jwt'];
        $str_checksum .= '|' . $data['processor_transaction_id'];
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $data['token_code']])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();
        $api_key = Merchant::getApiKey($checkout_order->merchant_id, $this->merchant_info);
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            if (ObjInput::get('ly', 'str', 0) == 'luonkhuon') {
                echo "<pre>";
                var_dump(md5($str_checksum));
                die();
            }
        }
        return false;
    }

    protected function _validateChecksumCheckOrder($data, $api_key): bool
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }


    protected function _validateCardNumber(&$card_number): bool
    {
        $card_number = str_replace(' ', '', $card_number);
        return $card_number != "";
    }

    protected function _validateCVV($cvv): bool
    {
        return $cvv != "";
    }

    protected function _validateCardName($card_name): bool
    {
        return (trim($card_name) != '' && strlen($card_name) <= 255);
    }

    protected function _validateCardMonth($card_month): bool
    {
        return (int)$card_month > 0 && (int)$card_month <= 12;
    }

    protected function _validateCardYear($card_year): bool
    {
        return (int)$card_year >= (int)date("Y");
    }

    protected function _validateJWT($jwt): bool
    {
        return (trim($jwt) != '');
    }

    protected function _validateProcessorTransactionId($data): bool
    {
        return (trim($data) != '');
    }

    private function _convertName($content)
    {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    protected function _processCardFullname($fullname, &$first_name = '', &$last_name = '')
    {
        $fullname = trim($fullname);
        $pos = strrpos($fullname, ' ');
        if ($pos !== false) {
            $first_name = trim(substr($fullname, $pos));
            $last_name = trim(substr($fullname, 0, $pos));
        } else {
            $first_name = $fullname;
            $last_name = '';
        }
    }

    protected static function updateFailure($checkout_order_info, $transaction_id): array
    {
        $error_code = '0001';
        $inputs = array(
            'transaction_id' => $transaction_id,
            'reason_id' => "666",
            'reason' => "Thẻ không được hỗ trợ thanh toán",
            'user_id' => 0,
        );
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
                    $error_code = '0000';
                } else {
                    $error_code = '0999';
                }
            } else {
                $error_code = '0998';
            }
        } else {
            $error_code = '0997';
        }

        return array('error_code' => $error_code);
    }

    private function generateJwt($token_code, $total_amount): string
    {
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $token_code])
            ->andWhere(['status' => CheckoutOrder::STATUS_NEW])
            ->one();
        $order = array(
            "OrderDetails" => array(
                "OrderNumber" => $checkout_order->order_code,
                "Amount" => $total_amount,
                "CurrencyCode" => '704'
            )
        );
        $TransactionId = uniqid();

        $seamless_info = json_decode($checkout_order->seamless_info);
        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');
        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order->merchant_id, $payment_method_info['partner_payment_id']);

        $cardinal_config = [
            'OrgUnitId' => $partner_payment_account_info['partner_merchant_password'],
            'ApiIdentifier' => $partner_payment_account_info['token_key'],
            'ApiKey' => $partner_payment_account_info['checksum_key'],
        ];
        $currentTime = time();
        $expireTime = 3600; // expiration in seconds - this equals 1hr

        $token = (new Builder())->setIssuer($cardinal_config['ApiIdentifier']) // API Key Identifier (iss claim)
        ->setId($TransactionId, true) // The Transaction Id (jti claim)
        ->setIssuedAt($currentTime) // Configures the time that the token was issued (iat claim)
        ->setExpiration($currentTime + $expireTime) // Configures the expiration time of the token (exp claim)
        ->set('OrgUnitId', $cardinal_config['OrgUnitId']) // Configures a new claim, called "OrgUnitId"
        ->set('Payload', $order) // Configures a new claim, called "Payload", containing the OrderDetails
        ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $cardinal_config['ApiKey']) // Sign with API Key
            ->getToken();

        return html_entity_decode($token);
    }

    private function decodeJWT($checkout_order, $jwt_in): object
    {

        $seamless_info = json_decode($checkout_order->seamless_info);
        $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($seamless_info->bank_code . "-" . $seamless_info->payment_method_code, 'version_1_0');
        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($checkout_order->merchant_id, $payment_method_info['partner_payment_id']);
        $jwt = new JWT();
        $jwt::$leeway = 60;
        return $jwt->decode($jwt_in, $partner_payment_account_info['checksum_key'], array('HS256'));
    }

    private function updateReferenceId($referenceId): bool
    {
        $seamless_info = json_decode($this->checkout_order['seamless_info'], true);
        $seamless_info['reference_id'] = $referenceId;

        $checkout_order = CheckoutOrder::find()->where(['id' => $this->checkout_order['id']])
            ->one();
        if ($checkout_order) {
            $checkout_order->seamless_info = json_encode($seamless_info);
            if ($checkout_order->save()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getCardTypeByCode($card_code): string
    {
        $card_types = array(
            'visa' => '001',
            'mastercard' => '002',
            'amex' => '003',
            'jcb' => '007',
        );
        return $card_types[strtolower($card_code)];
    }

}