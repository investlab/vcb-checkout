<?php

namespace common\payment_methods\cyber_source_vcb_3ds2;

use common\components\utils\Encryption;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\LinkCardBusiness;
use common\models\db\LinkCard;
use common\models\db\Merchant;
use common\models\db\PartnerPaymentAccount;
use common\models\db\Transaction;
use common\payment_methods\PaymentMethodCreditCardForm;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\TransactionBusiness;
use common\components\utils\Translate;
use common\components\utils\Strings;
use common\payments\CyberSourceVcb3ds2;
use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Yii;
use common\models\business\CheckoutOrderBusiness;
use yii\db\Exception;

class PaymentMethodCreditCardCyberSourceVcb3ds2 extends PaymentMethodCreditCardForm
{
    public $card_type = null;
    public $verifyCode = null;
    public $payment_method_id = null;
    public $partner_payment_id = null;
    public $otp = null;
    public $card_number = null;
    public $card_fullname = null;
    public $card_first_name = null;
    public $card_last_name = null;
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
    public $sessionId = null;

    public function rules()
    {
//      Custom Field Cybersource
        if ($this->option == 'request') {
            $field = array('payment_method_id', 'partner_payment_id', 'card_number', 'card_first_name', 'card_last_name', 'card_month', 'card_year', 'card_cvv');

//          QuangNT
            if (in_array($this->checkout_order['merchant_id'], [
                '91',
                '112',
                '2732',
                '3771',
                '192'
            ])) {
                $field[] = 'city';
                $field[] = 'zip_or_portal_code';
                $field[] = 'billing_address';
                $field[] = 'country';
                $field[] = 'state';
            }
//            Loại bỏ trường nhập tên với một số MC yêu cầu
            if (in_array($this->checkout_order['merchant_id'], ['19'])) {
                $field = array_diff($field, ['card_first_name', 'card_last_name']);
            }
            $rules = array(
                array($field, 'required', 'message' => Translate::get('Quý khách vui lòng nhập {attribute}.')),
                array(array('card_number'), 'isCardNumber'),
                array(array('card_month'), 'isExpiredCardMonth'),
                array(array('card_year'), 'isExpiredCardYear'),
                array(array('card_cvv'), 'isCardcvv'),
                array(array('ProcessorTransactionId', 'jwt_back'), 'string'),
            );
            return $rules;
        } elseif ($this->option == 'verify') {
            return array(
                array(array('otp'), 'required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                array(array('otp'), 'isOTP'),
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
            'card_number' => Translate::get('Mã số thẻ'),
            'card_fullname' => Translate::get('Tên chủ thẻ'),
            'card_first_name' => Translate::get('Họ của chủ thẻ'),
            'card_last_name' => Translate::get('Tên của chủ thẻ'),
            'card_month' => Translate::get('Tháng'),
            'card_year' => Translate::get('Năm'),
            'city' => Translate::get('Thành phố'),
            'country' => Translate::get('Quốc gia'),
            'billing_address' => Translate::get('Địa chỉ thanh toán'),
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
     * @throws Exception
     */
    public function processRequest($params = array())
    {

        $error_message = Translate::get('Lỗi không xác định');
        $payment_url = null;
        //--------
        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );

        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = $this->partner_payment->getTransaction($transaction_id);
            if ($transaction_info) {
                if (isset($this->card_first_name) && isset($this->card_last_name)) {
                    $card_fullname = $this->card_first_name . ' ' . $this->card_last_name;
                    CyberSourceVcb3ds2::_processCardFullname($card_fullname, $first_name, $last_name);
                    $this->card_fullname = $card_fullname;
                } else {
                    if ($this->card_fullname == null || $this->card_fullname == "") {
                        $this->card_fullname = $this->checkout_order['buyer_fullname'];
                    }
                    CyberSourceVcb3ds2::_processCardFullname($this->card_fullname, $first_name, $last_name);
                }
                $cardInfo = [
                    'card_fullname' => $this->card_fullname,
                    'card_number' => Strings::encodeCreditCardNumber($this->card_number),
                    'card_month' => $this->card_month,
                    'card_year' => $this->card_year,
                    'first_eight_digits' => substr($this->card_number, 0, 8),
                ];
                Transaction::insertCardInfo($transaction_id, $cardInfo);

                $this->setCardType();
                $params = [
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => Transaction::getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                ];
                if ($this["ProcessorTransactionId"] != null) {
                    $params['ProcessorTransactionId'] = $this["ProcessorTransactionId"];
                }
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'partner_payment_method_refer_code' => '',
                    'user_id' => 0,
                    'partner_payment_info' => '',
                );
                $paying = TransactionBusiness::paying($inputs);

                if ($this->country == null || !in_array($this->country, ['US', "CA"])) {
                    $postal_code = "";
                    $state = "";
                } else {
                    $postal_code = $this->zip_or_portal_code != null ? $this->zip_or_portal_code : '91356';
                    $state = $this->state != null ? $this->state : '';
                }

                if ($paying['error_message'] == '') {


                    $result = $this->partner_payment->processRequest($this, $params);
                    if ($result['error_message'] == '') {
                        $bank_trans_id = $result['bank_trans_id'];
                        $authorizationCode = @$result['authorizationCode'];
                        $xid = $result['xid'];
                        $result_code = $result['result_code'];
                        $token_info = $result['token_info'];

                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => json_encode($result),
                            'user_id' => 0,
                        );
                        $result = TransactionBusiness::paying($inputs);
                        if ($result['error_message'] == '') {
                            if ($result_code == 'ACCEPT') {
                                if ($this->checkout_order['link_card']) {
                                    $params_add_link_card = [
                                        'merchant_id' => $this->checkout_order['merchant_id'],
                                        'card_holder' => $cardInfo['card_fullname'],
                                        'customer_email' => $this->checkout_order['buyer_email'],
                                        'customer_mobile' => $this->checkout_order['buyer_mobile'],
                                        'link_card' => true,
                                        'info' => json_encode([
                                            'version' => "1.0",
                                            'street' => $this->billing_address != null ? $this->billing_address : $this->checkout_order['buyer_address'],
                                            'city' => $this->billing_address != null ? $this->billing_address : $this->checkout_order['buyer_address'],
                                            'state' => $state,
                                            'postal_code' => $postal_code,
                                            'notify_url' => $this->checkout_order['notify_url'],
                                            'first_name' => $first_name,
                                            'last_name' => $last_name,
                                            'card_info' => $cardInfo,
                                            'email' => $this->checkout_order['buyer_email'],
                                            'phone' => $this->checkout_order['buyer_mobile'],
                                            'customer_id' => $this->checkout_order['token_code'],
                                            'customer_field' => $this->checkout_order['customer_field'],
                                        ]),
                                    ];
                                    $result_add_link_card = LinkCardBusiness::add($params_add_link_card, true, $this->checkout_order['token_code']);

                                    if ($result_add_link_card['error_message'] == '') {
                                        $token_cybersource = $token_info['token_cybersource'];
                                        $token_cybersource_encrypt = Encryption::encryptAES($token_cybersource, $GLOBALS['AES_KEY']);
                                        $token_merchant = Encryption::hashHmacSHA256($this->checkout_order['merchant_id'] . $token_cybersource_encrypt['cipher_text'], $GLOBALS['SHA256_KEY']);
                                        $update_card_token_process = LinkCardBusiness::updateProcess([
                                            'id' => $result_add_link_card['id'],
                                            'token_cybersource' => $token_cybersource_encrypt['cipher_text'],
                                            'token_merchant' => $token_merchant,
                                            'card_number_mask' => Strings::encodeCreditCardNumber($this->card_number),
                                            'card_number_md5' => Encryption::hashHmacSHA256($this->card_number, $GLOBALS['SHA256_KEY']),
                                            'card_type' => LinkCard::convertCardType($this->card_type),
                                            'bank' => '',
                                            'secure_type' => LinkCard::SECURE_TYPE_3D,
                                            'partner_payment_id' => $this->partner_payment_id,
                                            'verify_amount' => 0,
                                            'iv' => $token_cybersource_encrypt['iv'],
                                        ]);
                                    }


                                }


                                $inputs = array(
                                    'transaction_id' => $transaction_id,
                                    'time_paid' => time(),
                                    'bank_refer_code' => $bank_trans_id,
                                    'authorizationCode' => $authorizationCode,
                                    'user_id' => 0,
                                );
                                $result = TransactionBusiness::paid($inputs);
                                if ($result['error_message'] === '') {
                                    $error_message = '';
                                    $payment_url = $this->_getUrlSuccess($transaction_id);
                                } else {
                                    $error_message = Translate::get($result['error_message']);
                                }
                            } elseif ($result_code == 'REVIEW') {
                                $inputs = array(
                                    'transaction_id' => $transaction_id,
                                    'time_paid' => time(),
                                    'bank_refer_code' => $bank_trans_id,
                                    'user_id' => 0,
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
                            $partner_payment_info = [
                                'result_code' => $result['result_code'],
//                                'reasonCode' => $result['reasonCode'],
                                'bank_trans_id' => $result['bank_trans_id'],
//                                'settle' => $result['settle'],
//                                'reversal' => $result['reversal'],
                            ];


                            $inputs = array(
                                'transaction_id' => $transaction_id,
                                'reason_id' => @$result['reasonCode'],
                                'reason' => $result['error_message'],
                                'partner_payment_info' => json_encode($partner_payment_info),
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
                } else {
                    $error_message = Translate::get($result['error_message']);
                }
            }
        } else {
            $error_message = Translate::get($result['error_message']);
        }
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
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
        $card_months = array(
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
        return $card_months;
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

    public function getCountry()
    {
        $arr = [
            Translate::get("Chọn quốc gia") => "",
            "Afghanistan" => "AF",
            "Åland Islands" => "AX",
            "Albania" => "AL",
            "Algeria" => "DZ",
            "American Samoa" => "AS",
            "Andorra" => "AD",
            "Angola" => "AO",
            "Anguilla" => "AI",
            "Antarctica" => "AQ",
            "Antigua and Barbuda" => "AG",
            "Argentina" => "AR",
            "Armenia" => "AM",
            "Aruba" => "AW",
            "Australia" => "AU",
            "Austria" => "AT",
            "Azerbaijan" => "AZ",
            "Bahamas" => "BS",
            "Bahrain" => "BH",
            "Bangladesh" => "BD",
            "Barbados" => "BB",
            "Belarus" => "BY",
            "Belgium" => "BE",
            "Belize" => "BZ",
            "Benin" => "BJ",
            "Bermuda" => "BM",
            "Bhutan" => "BT",
            "Bolivia:  Plurinational State of" => "BO",
            "Bonaire:  Sint Eustatius and Saba" => "BQ",
            "Bosnia and Herzegovina" => "BA",
            "Botswana" => "BW",
            "Bouvet Island" => "BV",
            "Brazil" => "BR",
            "British Indian Ocean Territory" => "IO",
            "Brunei Darussalam" => "BN",
            "Bulgaria" => "BG",
            "Burkina Faso" => "BF",
            "Burundi" => "BI",
            "Cambodia" => "KH",
            "Cameroon" => "CM",
            "Canada" => "CA",
            "Cape Verde" => "CV",
            "Cayman Islands" => "KY",
            "Central African Republic" => "CF",
            "Chad" => "TD",
            "Chile" => "CL",
            "China" => "CN",
            "Christmas Island" => "CX",
            "Cocos (Keeling) Islands" => "CC",
            "Colombia" => "CO",
            "Comoros" => "KM",
            "Congo" => "CG",
            "Congo:  the Democratic Republic of the" => "CD",
            "Cook Islands" => "CK",
            "Costa Rica" => "CR",
            "Côte d'Ivoire" => "CI",
            "Croatia" => "HR",
            "Cuba" => "CU",
            "Curaçao" => "CW",
            "Cyprus" => "CY",
            "Czech Republic" => "CZ",
            "Denmark" => "DK",
            "Djibouti" => "DJ",
            "Dominica" => "DM",
            "Dominican Republic" => "DO",
            "Ecuador" => "EC",
            "Egypt" => "EG",
            "El Salvador" => "SV",
            "Equatorial Guinea" => "GQ",
            "Eritrea" => "ER",
            "Estonia" => "EE",
            "Eswatini" => "SZ",
            "Ethiopia" => "ET",
            "Falkland Islands (Malvinas)" => "FK",
            "Faroe Islands" => "FO",
            "Fiji" => "FJ",
            "Finland" => "FI",
            "France" => "FR",
            "French Guiana" => "GF",
            "French Polynesia" => "PF",
            "French Southern Territories" => "TF",
            "Gabon" => "GA",
            "Gambia" => "GM",
            "Georgia" => "GE",
            "Germany" => "DE",
            "Ghana" => "GH",
            "Gibraltar" => "GI",
            "Greece" => "GR",
            "Greenland" => "GL",
            "Grenada" => "GD",
            "Guadeloupe" => "GP",
            "Guam" => "GU",
            "Guatemala" => "GT",
            "Guernsey" => "GG",
            "Guinea" => "GN",
            "Guinea-Bissau" => "GW",
            "Guyana" => "GY",
            "Haiti" => "HT",
            "Heard Island and McDonald Islands" => "HM",
            "Holy See (Vatican City State)" => "VA",
            "Honduras" => "HN",
            "Hong Kong" => "HK",
            "Hungary" => "HU",
            "Iceland" => "IS",
            "India" => "IN",
            "Indonesia" => "ID",
            "Iran:  Islamic Republic of" => "IR",
            "Iraq" => "IQ",
            "Ireland" => "IE",
            "Isle of Man" => "IM",
            "Israel" => "IL",
            "Italy" => "IT",
            "Jamaica" => "JM",
            "Japan" => "JP",
            "Jersey" => "JE",
            "Jordan" => "JO",
            "Kazakhstan" => "KZ",
            "Kenya" => "KE",
            "Kiribati" => "KI",
            "Korea:  Democratic People's Republic of" => "KP",
            "Korea:  Republic of" => "KR",
            "Kuwait" => "KW",
            "Kyrgyzstan" => "KG",
            "Lao People's Democratic Republic" => "LA",
            "Latvia" => "LV",
            "Lebanon" => "LB",
            "Lesotho" => "LS",
            "Liberia" => "LR",
            "Libya" => "LY",
            "Liechtenstein" => "LI",
            "Lithuania" => "LT",
            "Luxembourg" => "LU",
            "Macao" => "MO",
            "Macedonia:  the Former Yugoslav Republic of" => "MK",
            "Madagascar" => "MG",
            "Malawi" => "MW",
            "Malaysia" => "MY",
            "Maldives" => "MV",
            "Mali" => "ML",
            "Malta" => "MT",
            "Marshall Islands" => "MH",
            "Martinique" => "MQ",
            "Mauritania" => "MR",
            "Mauritius" => "MU",
            "Mayotte" => "YT",
            "Mexico" => "MX",
            "Micronesia:  Federated States of" => "FM",
            "Moldova:  Republic of" => "MD",
            "Monaco" => "MC",
            "Mongolia" => "MN",
            "Montenegro" => "ME",
            "Montserrat" => "MS",
            "Morocco" => "MA",
            "Mozambique" => "MZ",
            "Myanmar" => "MM",
            "Namibia" => "NA",
            "Nauru" => "NR",
            "Nepal" => "NP",
            "Netherlands" => "NL",
            "New Caledonia" => "NC",
            "New Zealand" => "NZ",
            "Nicaragua" => "NI",
            "Niger" => "NE",
            "Nigeria" => "NG",
            "Niue" => "NU",
            "Norfolk Island" => "NF",
            "Northern Mariana Islands" => "MP",
            "Norway" => "NO",
            "Oman" => "OM",
            "Pakistan" => "PK",
            "Palau" => "PW",
            "Palestine:  State of" => "PS",
            "Panama" => "PA",
            "Papua New Guinea" => "PG",
            "Paraguay" => "PY",
            "Peru" => "PE",
            "Philippines" => "PH",
            "Pitcairn" => "PN",
            "Poland" => "PL",
            "Portugal" => "PT",
            "Puerto Rico" => "PR",
            "Qatar" => "QA",
            "Réunion" => "RE",
            "Romania" => "RO",
            "Russian Federation" => "RU",
            "Rwanda" => "RW",
            "Saint Barthélemy" => "BL",
            "Saint Helena:  Ascension and Tristan da Cunha" => "SH",
            "Saint Kitts and Nevis" => "KN",
            "Saint Lucia" => "LC",
            "Saint Martin (French part)" => "MF",
            "Saint Pierre and Miquelon" => "PM",
            "Saint Vincent and the Grenadines" => "VC",
            "Samoa" => "WS",
            "San Marino" => "SM",
            "Sao Tome and Principe" => "ST",
            "Saudi Arabia" => "SA",
            "Senegal" => "SN",
            "Serbia" => "RS",
            "Seychelles" => "SC",
            "Sierra Leone" => "SL",
            "Singapore" => "SG",
            "Sint Maarten (Dutch part)" => "SX",
            "Slovakia" => "SK",
            "Slovenia" => "SI",
            "Solomon Islands" => "SB",
            "Somalia" => "SO",
            "South Africa" => "ZA",
            "South Georgia and the South Sandwich Islands" => "GS",
            "South Sudan" => "SS",
            "Spain" => "ES",
            "Sri Lanka" => "LK",
            "Sudan" => "SD",
            "Suriname" => "SR",
            "Svalbard and Jan Mayen" => "SJ",
            "Sweden" => "SE",
            "Switzerland" => "CH",
            "Syrian Arab Republic" => "SY",
            "Taiwan:  Province of China" => "TW",
            "Tajikistan" => "TJ",
            "Tanzania:  United Republic of" => "TZ",
            "Thailand" => "TH",
            "Timor-Leste" => "TL",
            "Togo" => "TG",
            "Tokelau" => "TK",
            "Tonga" => "TO",
            "Trinidad and Tobago" => "TT",
            "Tunisia" => "TN",
            "Turkey" => "TR",
            "Turkmenistan" => "TM",
            "Turks and Caicos Islands" => "TC",
            "Tuvalu" => "TV",
            "Uganda" => "UG",
            "Ukraine" => "UA",
            "United Arab Emirates" => "AE",
            "United Kingdom" => "GB",
            "United States" => "US",
            "United States Minor Outlying Islands" => "UM",
            "Uruguay" => "UY",
            "Uzbekistan" => "UZ",
            "Vanuatu" => "VU",
            "Venezuela:  Bolivarian Republic of" => "VE",
            "Viet Nam" => "VN",
            "Virgin Islands:  British" => "VG",
            "Virgin Islands:  U.S." => "VI",
            "Wallis and Futuna" => "WF",
            "Western Sahara" => "EH",
            "Yemen" => "YE",
            "Zambia" => "ZM",
            "Zimbabwe" => "ZW"
        ];
        return array_flip($arr);

    }

}
