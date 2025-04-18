<?php

namespace common\payment_methods\cyber_source_vcb_3ds2;

use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\TransactionBusiness;
use common\models\db\BinAcceptV2;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\MerchantInstallmentFeeOnlineV2;
use common\models\db\PartnerPaymentAccount;
use common\models\db\Transaction;
use common\partner_payments\PartnerPaymentBasic;
use common\payment_methods\PaymentMethodTraGopForm;
use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class PaymentMethodTraGopCyberSourceVcb3Ds2Form extends PaymentMethodTraGopForm
{
    public $cycle_installment = null;
    public $merchant_id = null;
    public $card_type = null;
    public $card_info = null;
    public $fields = array();
    public $verifyCode = null;
    public $payment_method_id = null;
    public $partner_payment_id = null;
    public $otp = null;
    public $card_number = null;
    public $card_fullname = null;
    public $ProcessorTransactionId = null;
    public $jwt_back = null;
    public $order = null;
    public $jwt = null;
    public $partner_payment_account_info = null;
    public $card_month = null;
    public $card_year = null;
    public $card_cvv = null;
    public $city = null;
    public $zip_or_portal_code = null;
    public $billing_address = null;
    public $country = null;
    public $state = null;
    public $partner_payment_fee = null;

    public function rules()
    {
        if ($this->option == 'request') {
            $rules = array(
                array(array('merchant_id', 'card_info', 'cycle_installment', 'payment_method_id', 'partner_payment_id', 'card_month', 'card_year'), 'required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                array(array('card_cvv', 'card_number', 'card_fullname'), 'required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                array(array('card_number'), 'isCardNumber'),
                array(array('card_fullname'), 'isCardHolderName'),
                array(array('card_month'), 'isExpiredCardMonth'),
                array(array('card_year'), 'isExpiredCardYear'),
                array(array('card_cvv'), 'isCardcvv'),
                array(array('ProcessorTransactionId', 'jwt_back'), 'string'),
//                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không đúng.')),
            );
            return $rules;
        } elseif ($this->option == 'verify') {
            return array(
                array(array('otp'), 'required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                array(array('otp'), 'isOTP'),
//                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không đúng.')),
            );
        }
        return array();
    }

    public function attributeLabels()
    {
        return [
            'payment_method_id' => Translate::get('Ngân hàng để thanh toán'),
            'partner_payment_id' => Translate::get('Kênh thanh toán'),
            'verifyCode' => Translate::get('Captcha'),
            'otp' => Translate::get('Mã OTP'),
            'card_cvv' => Translate::get('Mã CVV'),
            'card_number' => Translate::get('Số thẻ'),
            'card_fullname' => Translate::get('Tên chủ thẻ'),
            'card_month' => Translate::get('Tháng'),
            'card_year' => Translate::get('Năm'),
            'card_type' => Translate::get('Loại thẻ'),
            'card_info' => Translate::get('Thông tin thẻ'),
            'cycle_installment' => Translate::get('Kỳ hạn trả góp'),
        ];
    }

    function initRequest(PartnerPaymentBasic &$partner_payment)
    {
        try {
            $Order = array(
                "OrderDetails" => array(
                    "OrderNumber" => $this->checkout_order['order_code'],
                    "Amount" => $this->checkout_order['amount'],
                    "CurrencyCode" => '704'
                )
            );

            $this->order = $Order;
            $TransactionId = uniqid();
            $merchant_id = $this->checkout_order["merchant_info"]["id"];
            $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($merchant_id, $this->partner_payment_id);
            $this->checkout_order['partner_payment_account_info'] = $partner_payment_account_info;
            $cardinal_config = '';
            if ($partner_payment_account_info != false && $partner_payment_account_info['token_key'] != null && $partner_payment_account_info['partner_merchant_password'] != null && $partner_payment_account_info['checksum_key'] != null) {
                $cardinal_config = [
                    'OrgUnitId' => $partner_payment_account_info['partner_merchant_password'],
                    'ApiIdentifier' => $partner_payment_account_info['token_key'],
                    'ApiKey' => $partner_payment_account_info['checksum_key'],
                ];
            } else {
                $this->redirectErrorPage('Chưa cấu hình phí cho phương thức thanh toán này');
            }
            $this->partner_payment_account_info = $cardinal_config;
            $currentTime = time();
            $expireTime = 3600; // expiration in seconds - this equals 1hr
            $token = (new Builder())->setIssuer($cardinal_config['ApiIdentifier']) // API Key Identifier (iss claim)
            ->setId($TransactionId, true) // The Transaction Id (jti claim)
            ->setIssuedAt($currentTime) // Configures the time that the token was issued (iat claim)
            ->setExpiration($currentTime + $expireTime) // Configures the expiration time of the token (exp claim)
            ->set('OrgUnitId', $cardinal_config['OrgUnitId']) // Configures a new claim, called "OrgUnitId"
            ->set('Payload', $Order) // Configures a new claim, called "Payload", containing the OrderDetails
            ->set('ObjectifyPayload', true)
                ->sign(new Sha256(), $cardinal_config['ApiKey']) // Sign with API Key
                ->getToken(); // Retrieves the generated token
            $this->jwt = $token;
            return true;
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            return array('error_message' => $error_message);
        }

    }

    /**
     * Hàm sử dụng công thức tính phí trả góp mới.
     *
     * @param $params
     * @return array|void
     */
    public function processRequest($params = array())
    {

        $error_message = Translate::get('Lỗi không xác định');
        $payment_url = null;
        //--------
        $data_form = $this->partner_payment->getCardInfo();
        //        $bank_code = $this->payment_method_code;
        $bin = substr(str_replace(' ', '', $this->card_number), 0, 6);
        $bank_code = str_replace('-TRA-GOP', '', $this->payment_method_code);
        $binAccept = BinAcceptV2::find()->where(['code' => $bin, 'status' => BinAcceptV2::STATUS_ACTIVE, 'bank_code' => $bank_code])->asArray()->one();

        $installment_config = $this->getInstallmentFee($this->payment_method_code, $this->cycle_installment, $binAccept['card_type']);

        if (!$installment_config) {
            return [
                'error_message' => 'Không tìm thấy cấu hình phí trả góp',
                'payment_url' => $this->_getUrlFailure(),
            ];
        }

        $inputs = [
            'checkout_order_id' => $this->checkout_order['id'] ?? 0,
            'payment_method_id' => $this->payment_method_id ?? 0,
            'partner_payment_id' => $this->partner_payment_id ?? '',
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
            'transaction_type_id' => 5,
            'installment_conversion' => Transaction::InstallmentConversion_NEW,
            'installment_fee' => $data_form['card_fee'] ?? '',
            'installment_fee_bearer' => $data_form['card_fee_bearer'] ?? '',
            'version' => $this->version,
        ];

        if ($this->version == 3) {
            $inputs['installment_fee'] = $installment_config ?? [];
            $inputs['payment_fee'] = $this->checkout_order['merchant_fee_info'] ?? [];
            $inputs['payment_amount'] = $this->payment_amount ?? 0;
            $inputs['installment_fee_bearer'] = $installment_config['fee_bearer'];
        }

        //TODO cần tính phí tại đây.
        $result = CheckoutOrderBusiness::requestPayment($inputs);

        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = $this->partner_payment->getTransaction($transaction_id);
            if ($transaction_info != false) {
                $cardInfo = [
                    'card_fullname' => $this->card_fullname,
                    'card_number' => Strings::encodeCreditCardNumber($this->card_number),
                    'card_month' => $this->card_month,
                    'card_year' => $this->card_year,
                    'first_eight_digits' => substr($this->card_number, 0, 8),
                ];
                Transaction::insertCardInfo($transaction_id, $cardInfo);

                $params = [
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                ];
                $key = $this->partner_payment_account_info['ApiKey'];
                if ($this["ProcessorTransactionId"] != null) {
                    $params['ProcessorTransactionId'] = $this["ProcessorTransactionId"];
                }
                $result = $this->partner_payment->processRequest($this, $params);
                if ($result['error_message'] == '') {
                    $bank_trans_id = $result['bank_trans_id'];
                    $xid = $result['xid'];
                    $authorizationCode = @$result['authorizationCode'];
                    $result_code = $result['result_code'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => '',
                        'partner_payment_info' => json_encode($result),
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paying($inputs);
                    if ($result['error_message'] == '') {
                        if ($result_code == 'ACCEPT') {
                            $data = $this->partner_payment->getCardInfo();
                            $installment_info = array('method' => $data['card_type'], 'number' => $data['info_card'], 'card_name' => $data['name_card']);
                            $inputs = array(
                                'transaction_id' => $transaction_id,
                                'time_paid' => time(),
                                'bank_refer_code' => $bank_trans_id,
                                'user_id' => 0,
                                'month' => $data['card_cycle'],
                                'payment_info' => json_encode($installment_info),
                                'authorizationCode' => $authorizationCode,
                            );
                            $result = TransactionBusiness::paid($inputs);
                            if ($result['error_message'] === '') {
                                $error_message = '';
                                $payment_url = $this->_getUrlSuccess($transaction_id);
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        } elseif ($result_code == 'REVIEW') {
                            $data = $this->partner_payment->getCardInfo();
                            $installment_info = array('method' => $data['card_type'], 'number' => $data['info_card'], 'card_name' => $data['name_card']);
                            $inputs = array(
                                'transaction_id' => $transaction_id,
                                'time_paid' => time(),
                                'bank_refer_code' => $bank_trans_id,
                                'user_id' => 0,
                                'month' => $data['card_cycle'],
                                'payment_info' => json_encode($installment_info)
                            );
                            $result = TransactionBusiness::updateReview($inputs);
                            if ($result['error_message'] === '') {
                                $error_message = '';
                                $payment_url = $this->_getUrlReview($transaction_id);
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        } else {
                            $error_message = Translate::get('Lỗi không xác định');
                        }
                    } else {
                        $error_message = Translate::get('Lỗi không xác định');
                    }
                } else {
                    if ($result['result_code'] == 'REJECT') {
//                        $inputs = array(
//                            'transaction_id' => $transaction_id,
//                            'partner_payment_method_refer_code' => '',
//                            'partner_payment_info' => json_encode($result),
//                            'user_id' => 0,
//                        );
//                        TransactionBusiness::updatePartnerPaymentInfo($inputs);
//                        $inputs = array(
//                            'transaction_id' => $transaction_id,
//                            'reason_id' => 0,
//                            'reason' => $result['error_message'],
//                            'user_id' => 0,
//                        );
//                        $cancel = TransactionBusiness::cancel($inputs);
//                        if ($cancel['error_message'] === '') {
//                            $error_message = Translate::get($result['error_message']);
//                        } else {
//                            $error_message = Translate::get($result['error_message']);
//                        }
                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'reason_id' => @$result['reasonCode'],
                            'reason' => $result['error_message'],
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
                                if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
                                    $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                                }
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $payment_url = $this->_getUrlFailure();
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        } else {
                            $error_message = Translate::get($result['error_message']);
                        }
                    } else {
                        $error_message = Translate::get($result['error_message']);
                    }
                }
            }
        } else {
            $error_message = Translate::get($result['error_message']);
        }
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
    }

//    public function processRequestBackup($params = array())
//    {
//
//        // Lấy thông tin ngăn xếp cuộc gọi
//        $backtrace = debug_backtrace();
//
//        // In ra nơi gọi hàm này
//        echo "Hàm processRequestV2 được gọi từ:\n";
//        if (isset($backtrace[1])) {
//            echo "File: " . $backtrace[1]['file'] . "\n";
//            echo "Dòng: " . $backtrace[1]['line'] . "\n";
//            echo "Hàm gọi: " . $backtrace[1]['function'] . "\n";
//        } else {
//            echo "Không xác định được nơi gọi hàm.\n";
//        }
//        $error_message = Translate::get('Lỗi không xác định');
//        $payment_url = null;
//        //--------
//        $data_form = $this->partner_payment->getCardInfo();
//        $inputs = array(
//            'checkout_order_id' => $this->checkout_order['id'],
//            'payment_method_id' => $this->payment_method_id,
//            'partner_payment_id' => $this->partner_payment_id,
//            'partner_payment_method_refer_code' => '',
//            'user_id' => 0,
//            'transaction_type_id' => 5,
//            'installment_conversion' => Transaction::InstallmentConversion_NEW,
//            'installment_fee' => isset($data_form['card_fee']) ? $data_form['card_fee'] : '',
//            'installment_fee_bearer' => isset($data_form['card_fee_bearer']) ? $data_form['card_fee_bearer'] : '',
//        );
//        $result = CheckoutOrderBusiness::requestPayment($inputs);
//        if ($result['error_message'] == '') {
//            $transaction_id = $result['transaction_id'];
//            $transaction_info = $this->partner_payment->getTransaction($transaction_id);
//            if ($transaction_info != false) {
//                $cardInfo = [
//                    'card_fullname' => $this->card_fullname,
//                    'card_number' => Strings::encodeCreditCardNumber($this->card_number),
//                    'card_month' => $this->card_month,
//                    'card_year' => $this->card_year,
//                ];
//                Transaction::insertCardInfo($transaction_id, $cardInfo);
//
//                $params = [
//                    'transaction_id' => $transaction_id,
//                    'transaction_amount' => \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info),
//                    'transaction_info' => $transaction_info,
//                ];
//                $key = $this->partner_payment_account_info['ApiKey'];
//                if ($this["ProcessorTransactionId"] != null) {
//                    $params['ProcessorTransactionId'] = $this["ProcessorTransactionId"];
//                }
//                $result = $this->partner_payment->processRequest($this, $params);
//                if ($result['error_message'] == '') {
//                    $bank_trans_id = $result['bank_trans_id'];
//                    $xid = $result['xid'];
//                    $authorizationCode = @$result['authorizationCode'];
//                    $result_code = $result['result_code'];
//                    $inputs = array(
//                        'transaction_id' => $transaction_id,
//                        'partner_payment_method_refer_code' => '',
//                        'partner_payment_info' => json_encode($result),
//                        'user_id' => 0,
//                    );
//                    $result = TransactionBusiness::paying($inputs);
//                    if ($result['error_message'] == '') {
//                        if ($result_code == 'ACCEPT') {
//                            $data = $this->partner_payment->getCardInfo();
//                            $installment_info = array('method' => $data['card_type'],'number' => $data['info_card'],'card_name' => $data['name_card']);
//                            $inputs = array(
//                                'transaction_id' => $transaction_id,
//                                'time_paid' => time(),
//                                'bank_refer_code' => $bank_trans_id,
//                                'user_id' => 0,
//                                'month' => $data['card_cycle'],
//                                'payment_info' => json_encode($installment_info),
//                                'authorizationCode' => $authorizationCode,
//                            );
//                            $result = TransactionBusiness::paid($inputs);
//                            if ($result['error_message'] === '') {
//                                $error_message = '';
//                                $payment_url = $this->_getUrlSuccess($transaction_id);
//                            } else {
//                                $error_message = Translate::get($result['error_message']);
//                            }
//                        } elseif ($result_code == 'REVIEW') {
//                            $data = $this->partner_payment->getCardInfo();
//                            $installment_info = array('method' => $data['card_type'],'number' => $data['info_card'],'card_name' => $data['name_card']);
//                            $inputs = array(
//                                'transaction_id' => $transaction_id,
//                                'time_paid' => time(),
//                                'bank_refer_code' => $bank_trans_id,
//                                'user_id' => 0,
//                                'month' => $data['card_cycle'],
//                                'payment_info' => json_encode($installment_info)
//                            );
//                            $result = TransactionBusiness::updateReview($inputs);
//                            if ($result['error_message'] === '') {
//                                $error_message = '';
//                                $payment_url = $this->_getUrlReview($transaction_id);
//                            } else {
//                                $error_message = Translate::get($result['error_message']);
//                            }
//                        } else {
//                            $error_message = Translate::get('Lỗi không xác định');
//                        }
//                    } else {
//                        $error_message = Translate::get('Lỗi không xác định');
//                    }
//                } else {
//                    if ($result['result_code'] == 'REJECT') {
////                        $inputs = array(
////                            'transaction_id' => $transaction_id,
////                            'partner_payment_method_refer_code' => '',
////                            'partner_payment_info' => json_encode($result),
////                            'user_id' => 0,
////                        );
////                        TransactionBusiness::updatePartnerPaymentInfo($inputs);
////                        $inputs = array(
////                            'transaction_id' => $transaction_id,
////                            'reason_id' => 0,
////                            'reason' => $result['error_message'],
////                            'user_id' => 0,
////                        );
////                        $cancel = TransactionBusiness::cancel($inputs);
////                        if ($cancel['error_message'] === '') {
////                            $error_message = Translate::get($result['error_message']);
////                        } else {
////                            $error_message = Translate::get($result['error_message']);
////                        }
//                        $inputs = array(
//                            'transaction_id' => $transaction_id,
//                            'reason_id' => @$result['reasonCode'],
//                            'reason' => $result['error_message'],
//                            'user_id' => 0,
//                        );
//                        $cancel = TransactionBusiness::failure($inputs);
//                        if ($cancel['error_message'] === '') {
//                            $inputs = array(
//                                'checkout_order_id' => $this->checkout_order['id'],
//                                'user_id' => '0',
//                            );
//                            $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
//                            if ($result['error_message'] === '') {
//                                $inputs_callback = [
//                                    'checkout_order_id' => $this->checkout_order['id'],
//                                    'notify_url' => $this->checkout_order['notify_url'],
//                                    'time_process' => time(),
//                                ];
//                                if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
//                                    $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
//                                }
//                                if ($result['error_message'] == '') {
//                                    $error_message = '';
//                                    $payment_url = $this->_getUrlFailure();
//                                } else {
//                                    $error_message = $result['error_message'];
//                                }
//                            } else {
//                                $error_message = Translate::get($result['error_message']);
//                            }
//                        } else {
//                            $error_message = Translate::get($result['error_message']);
//                        }
//                    } else {
//                        $error_message = Translate::get($result['error_message']);
//                    }
//                }
//            }
//        } else {
//            $error_message = Translate::get($result['error_message']);
//        }
//        return array('error_message' => $error_message, 'payment_url' => $payment_url);
//    }

    private function setCardType()
    {
        switch ($this->payment_method_code) {
            case 'VISA-CREDIT-CARD':
                $this->card_type = 'visa';
                break;
            case 'MASTERCARD-CREDIT-CARD':
                $this->card_type = 'mastercard';
                break;
            case 'JCB-CREDIT-CARD':
                $this->card_type = 'jcb';
                break;
            case 'AMEX-CREDIT-CARD':
                $this->card_type = 'americanexpress';
                break;
        }
    }

    function initVerify(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initVerify($this);
        return true;
    }

    function processVerify()
    {
        return false;
    }

    public function isExpiredCardMonth($attribute, $params)
    {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getCardMonths())) {
            $this->addError($attribute, Translate::get('Tháng hết hạn thẻ không hợp lệ'));
        } else {
            if ($this->card_year == date('Y') && $this->card_month < intval(date('m'))) {
                $this->addError($attribute, Translate::get('Tháng hết hạn thẻ không hợp lệ'));
            }
        }
    }

    public function isExpiredCardYear($attribute, $params)
    {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getExpiredCardYears())) {
            $this->addError($attribute, Translate::get('Năm hết hạn thẻ không hợp lệ'));
        }
    }

    public function isCardNumber($attribute, $params)
    {
        $this->$attribute = str_replace(' ', '', $this->$attribute);
        if (!preg_match('/^\d{15,23}$/', $this->$attribute)) {
            $this->addError($attribute, Translate::get('Số thẻ không hợp lệ'));
        }
    }

    public function isCardHolderName($attribute, $params)
    {
        $name = strtoupper(Strings::_convertToSMS(trim($this->$attribute)));
        if (!preg_match('/^[\w\s]+$/', $name)) {
            $this->addError($attribute, Translate::get('Tên chủ thẻ không hợp lệ'));
        }
    }

    public function isCardcvv($attribute, $params)
    {
        $length = strlen(Strings::_convertToSMS(trim($this->$attribute)));
        if ($length < 3 || $length > 4) {
            $this->addError($attribute, Translate::get('Mã cvv không hợp lệ'));
        }
    }

    public function getCardMonths()
    {
        return array(
            '' => '----',
            '1' => Translate::get('Tháng 1'),
            '2' => Translate::get('Tháng 2'),
            '3' => Translate::get('Tháng 3'),
            '4' => Translate::get('Tháng 4'),
            '5' => Translate::get('Tháng 5'),
            '6' => Translate::get('Tháng 6'),
            '7' => Translate::get('Tháng 7'),
            '8' => Translate::get('Tháng 8'),
            '9' => Translate::get('Tháng 9'),
            '10' => Translate::get('Tháng 10'),
            '11' => Translate::get('Tháng 11'),
            '12' => Translate::get('Tháng 12')
        );
    }

    public function getExpiredCardYears()
    {
        $card_years = array('' => '----');
        $year = date('Y');
        for ($i = $year; $i < ($year + 10); $i++) {
            $card_years[$i] = $i;
        }
        return $card_years;
    }

    public function getCardInfo2($method_code, $card_type)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this, $inputs);
        $card_info = array();
        foreach ($card_accept as $item => $value) {
            $arrCard = explode('-', $value);
            if (in_array($card_type, $arrCard)) {
                $card_info[$card_type][$arrCard[1]] = $value;
            }
        }
        return $card_info;
    }

    public function getBankCard($method_code)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this, $inputs, $bank_code);
        if ($card_accept == false) {
            return false;
        } else {
            return $card_accept[0]['card'];
        }
    }

    public function getBankCycle($method_code)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this, $inputs, $bank_code);
        if ($card_accept == false) {
            return false;
        } else {
            return $card_accept[0]['cycle'];
        }
    }

    /**
     * Hàm lấy phí trả góp theo version
     * Version = 1 => lấy phí theo version cũ.
     * Version = 3 => lấy phí theo version mới, công thức tính phí mới.
     * @param $method_code
     * @param $cycle
     * @param $method
     * @return array|ActiveRecord|null
     */
    public function getInstallmentFee($method_code, $cycle, $method)
    {
        if ($this->version === 3) {
            $bank_code = str_replace('-TRA-GOP', '', $method_code);
            return MerchantInstallmentFeeOnlineV2::find()->where([
                'merchant_id' => $this->checkout_order['merchant_id'],
                'bank_code' => $bank_code,
                'status' => MerchantInstallmentFeeOnlineV2::STATUS_ACTIVE,
                'method' => $method,
                'period' => $cycle,
            ])->orderBy(['period' => SORT_ASC])->asArray()->one();
        } else {
            return Tables::selectOneDataTable('installment_config', 'merchant_id = ' . $this->checkout_order['merchant_id'] . ' AND status = 1', '', '');
        }
    }

    /**
     * @param $method_code
     * @return array
     */
    public function getBankCycleVer2($method_code)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);

        $merchantInstallmentFeeOnlines = MerchantInstallmentFeeOnlineV2::find()->where([
            'merchant_id' => $this->checkout_order['merchant_id'],
            'bank_code' => $bank_code,
            'status' => MerchantInstallmentFeeOnlineV2::STATUS_ACTIVE,
        ])
            ->orderBy(['period' => SORT_ASC])
            ->asArray()->all();

        $cycle = [];
        foreach ($merchantInstallmentFeeOnlines as $merchantInstallmentFeeOnline) {
            $row['card_type'] = $merchantInstallmentFeeOnline['method'];
            $row['fee'] = $merchantInstallmentFeeOnline['card_owner_percent_fee'];
            $row['cycle'] = $merchantInstallmentFeeOnline['period'];
            $row['card_owner_fixed_fee'] = $merchantInstallmentFeeOnline['card_owner_fixed_fee'];
            $row['info'] = $merchantInstallmentFeeOnline;
            $cycle[] = $row;
        }

        return $cycle;
    }

    /**
     * @param $method_code
     * @return array
     */
    public function getBankCycleVer3($method_code): array
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);

        $merchantInstallmentFeeOnlines = MerchantInstallmentFeeOnlineV2::find()->where([
            'merchant_id' => $this->checkout_order['merchant_id'],
            'bank_code' => $bank_code,
            'status' => MerchantInstallmentFeeOnlineV2::STATUS_ACTIVE,
        ])
            ->orderBy(['period' => SORT_ASC])
            ->asArray()->all();

        $cycle = [];
        foreach ($merchantInstallmentFeeOnlines as $merchantInstallmentFeeOnline) {
            $cycle[] = $merchantInstallmentFeeOnline;
        }

        return $cycle;
    }

    public function getFeeBearer($method_code)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this, $inputs, $bank_code);
        if ($card_accept == false) {
            return false;
        } else {
            return $card_accept[0]['fee_bearer'];
        }
    }

    public function getFeeByCardTypeAndCycle($method_code, $card_type, $cycle)
    {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this, $inputs, $bank_code);
        foreach ($card_accept[0]['cycle'] as $value) {
            if ($card_type == $value['card_type'] && $value['cycle'] == $cycle) {
                return $value['fee'];
            }
        }
        return false;
    }


}
