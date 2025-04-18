<?php

namespace common\models\business;

use backend\controllers\LinkCardController;
use common\api\CardTokenBasicApi;
use common\api\CardTokenVersion1_0Api;
use common\components\utils\Encryption;
use common\models\db\LinkCard;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PaymentLink;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;
use common\payments\CyberSource;
use common\payments\CyberSourceVcb;
use common\payments\CyberSourceVcb3ds2;
use Firebase\JWT\JWT;
use yii\db\Exception;

class LinkCardBusiness
{

    const MIN_MOUNT = 2000;
    const MAX_AMOUNT = 10000;

    /**
     * @throws Exception
     */
    public static function add($params, $rollback = true, $custom_token = false)
    {
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = LinkCard::getDb()->beginTransaction();
        }
        $model = new LinkCard();
        $model->merchant_id = $params['merchant_id'];
        $model->card_holder = $params['card_holder'];
        $model->customer_email = $params['customer_email'];
        $model->customer_mobile = $params['customer_mobile'];
        $model->link_card = isset($params['link_card']) && $params['link_card'];
        $model->status = LinkCard::STATUS_NEW;
        $model->time_created = time();
        $model->info = $params['info'];
        $model->token = $custom_token ?: self::getTokenCode($model->time_created);
        $model->customer_field = $params['customer_field'];
        if ($model->validate()) {
            if ($model->save()) {
                $commit = true;
                $id = $model->id;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm thẻ liên kết';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'token' => $model->token, 'id' => $id);
    }

    public static function updateProcess($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        if ($model->status == LinkCard::STATUS_NEW) {
            $model->token_cybersource = $params['token_cybersource'];
            $model->token_merchant = $params['token_merchant'];
            $model->card_number_mask = $params['card_number_mask'];
            $model->card_number_md5 = $params['card_number_md5'];
            $model->card_type = $params['card_type'];
            $model->bank = $params['bank'];
            $model->secure_type = $params['secure_type'];
            $model->partner_payment_id = $params['partner_payment_id'];
            $model->verify_amount = $params['verify_amount'];
            $model->iv = $params['iv'];
            $model->status = LinkCard::STATUS_ACTIVE;
            $model->time_updated = time();
            if (isset($params['card_month']) && isset($params['card_year'])) {
                $card_token_info = json_decode($model->info, true);
                $card_token_info['card_info'] = [
                    'card_month' => $params['card_month'],
                    'card_year' => $params['card_year'],
                ];
                $model->info = json_encode($card_token_info);
            }

            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                    self::callBackMerchant($model->attributes);
                } else {
                    $error_message = 'Có lỗi khi thêm thẻ liên kết';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Trạng thái thẻ liên kết không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function updateReview($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        if ($model->status == LinkCard::STATUS_PROCESS) {
            $model->status = LinkCard::STATUS_WAIT;
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi thêm thẻ liên kết';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Trạng thái thẻ liên kết không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function updateCancel($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = LinkCard::STATUS_CANCEL;
            $model->time_updated = time();
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Huỷ liên kết thẻ';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function updateSuccess($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        if ($model != null) {
            if (in_array($model->status, [LinkCard::STATUS_PROCESS], LinkCard::STATUS_WAIT)) {
                $model->status = LinkCard::STATUS_ACTIVE;
                $model->time_updated = time();
                $model->time_verified = time();
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                    self::callBackMerchant($model->attributes);
                } else {
                    $error_message = 'Có lỗi khi cập nhật liên kết thẻ';
                }
            } else {
                $error_message = 'Trạng thái thẻ liên kết không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function checkTokenMerchant($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = LinkCard::getDb()->beginTransaction();
        }
        $token = LinkCard::getByTokenMC($params['token_merchant']);
        if ($token == false) {
            $error_message = 'Thẻ chưa được liên kết';
            $result = [];
        } else {
            $customer = json_decode($token['info'])->customer_id;
            if ($customer != $params['customer_id']) {
                $error_message = 'customer_id error';
                $result = [];
            } else {
                $result = [
                    'customer_id' => $params['customer_id'],
                    'token_merchant' => $params['token_merchant'],
                    'token_status' => $token['status'],
                    'checksum' => $params['checksum']
                ];
                $error_message = '';
            }
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'result' => $result);
    }

    public static function lockLink($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        $user_action = json_decode($model->user_action);
        $user_action[] = [$params['user_id'] => 'lock-link'];

        if ($model != null) {
            $model->status = LinkCard::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_action = json_encode($user_action);
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa liên kết';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function activeLink($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        $user_action = json_decode($model->user_action, true);
        $user_action[] = [$params['user_id'] => 'active-link'];

        if ($model != null) {
            $model->status = LinkCard::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_action = json_encode($user_action);
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Mở khóa liên kết';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function unlink($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        $user_action = json_decode($model->user_action, true);
        $user_action[] = [$params['user_id'] => 'unlink'];

        if ($model != null) {
            $model->status = LinkCard::STATUS_CANCEL;
            $model->time_updated = time();
            $model->user_action = json_encode($user_action);
            if ($model->save()) {
                $result = self::removeTokenCybersource($model);
                if ($result['error_message'] === 'Success') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi Huỷ liên kết';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    public static function confirm($params, $rollback = false)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $link_card = LinkCard::getDb()->beginTransaction();
        }
        $model = LinkCard::findOne(['id' => $params['id']]);
        $user_action = json_decode($model->user_action, true);
        $user_action[] = [$params['user_id'] => 'confirm'];

        if ($model != null) {
            $model->status = LinkCard::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_action = json_encode($user_action);
            $model->time_verified = time();
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Xác thực liên kết';
            }
        } else {
            $error_message = 'Không tìm thấy Thẻ liên kết này';
        }
        if ($rollback) {
            if ($commit == true) {
                $link_card->commit();
            } else {
                $link_card->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    /**
     * @throws \SoapFault
     */
    public static function processCardToken($params)
    {
        $error_message = 'Lỗi không xác định';
        $result_code = null;
        $first_name = null;
        $last_name = null;
        $xid = null;
        $bank_trans_id = null;
        $token_cybersource = null;
        $partner_payment_id = null;
        $verify_amount = null;
        $bank = null;
        $type_card = null;

        $card_token = $params['card_token'];
        $merchant_id = $card_token['merchant_id'];
        $card_token_info = json_decode($card_token['info'], true);
        $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber($params['card_number'], false);

        $get_partner_payment = self::getPartnerPaymentByBankCode($type_card, $merchant_id);
        if ($get_partner_payment['error_message'] == '') {
            $partner_payment = $get_partner_payment['partner_payment'];
            $partner_payment_id = $partner_payment['id'];
            $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($merchant_id, $partner_payment_id);
            if ($partner_payment_account_info != false) {
                $verify_amount = "0";
                $card_fullname = self::_convertName($card_token['card_holder']);
                self::_processCardFullName($card_fullname, $first_name, $last_name);
                $partner_payment_account_info_cp['partner_payment_account_info'] = $partner_payment_account_info;
                if ($last_name !== "" && $first_name !== "") {
                    $inputs = array(
                        'reference_code' => "PG-Token-Create-" . $card_token['id'],
                        'city' => $card_token_info['city'],
                        'country' => 'VN',
                        'email' => $card_token['customer_email'],
                        'phone' => $card_token['customer_mobile'],
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'postal_code' => $card_token_info['postal_code'],
                        'state' => $card_token_info['state'],
                        'address' => $card_token_info['street'],
                        'customer_id' => 0,
                        'account_number' => $params['card_number'],
                        'card_type' => $type_card,
                        'expiration_month' => $params['card_month'],
                        'expiration_year' => $params['card_year'],
                        'currency' => 'VND',
                        'amount' => $params['transaction_amount'] = "10000",
                        'cvv_code' => $params['cvv_code'],
                        'product_code' => "",
                        'client_ip' => get_client_ip(),
                        'order_code' => $card_token['id'],
                        'ProcessorTransactionId' => isset($params['ProcessorTransactionId']) ? $params['ProcessorTransactionId'] : '',
                        'referenceID' => isset($params['referenceID']) ? $params['referenceID'] : '',

                    );

                    $cbs_3ds2 = new CyberSourceVcb3ds2($partner_payment_account_info_cp);
//                            $result = $cbs_3ds2->authorizeCard($inputs);
                    $create_token = $cbs_3ds2->createToken($inputs);

                    if ($create_token['error'] == '') {
                        $token_cybersource = $create_token['result']->paySubscriptionCreateReply->subscriptionID;
                        $error_message = '';
                        $bank_trans_id = $create_token['result']->requestID;
                        $result_code = 'ACCEPT';
                    }
                }
            } else {
                $error_message = 'Tài khoản kênh thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error_message = $get_partner_payment['error_message'];
        }

        return [
            'error_message' => $error_message,
            'result_code' => $result_code,
            'xid' => $xid,
            'bank_trans_id' => $bank_trans_id,
            'token_cybersource' => $token_cybersource,
            'partner_payment_id' => $partner_payment_id,
            'verify_amount' => $verify_amount,
            'bank' => $bank,
            'card_type' => $type_card,
            'cardIs3d' => isset($cardIs3d) ? $cardIs3d : false,
        ];
    }

    public static function processCardToken3d($params)
    {
        $error_message = 'Lỗi không xác định';
        $result_code = '';
        $bank_trans_id = '';
        //------------
        $card_token = $params['card_token'];
        $merchant_id = $card_token['merchant_id'];
        $session_info = CyberSource::decryptSessionInfo($params['session_info']);
        $get_partner_payment = self::getPartnerPaymentByBankCode($session_info['card_info']['card_type'], $merchant_id);
        if ($get_partner_payment['error_message'] == '') {
            $partner_payment = $get_partner_payment['partner_payment'];
            $partner_payment_id = $partner_payment['id'];
            $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($merchant_id, $partner_payment_id);
            if ($partner_payment_account_info != false) {
                if ($partner_payment['code'] == 'CYBER-SOURCE') {
                    $token = Encryption::decryptAES($card_token['token_cybersource'], $GLOBALS['AES_KEY'], $card_token['iv']);
                    $cashin_id = $session_info['process_info']['cashin_id'];
                    $inputs_authorize3D = array(
                        'cashin_id' => $cashin_id,
                        'cashin_amount' => $session_info['process_info']['cashin_amount'],
                        'token' => $token,
                        'signedPARes' => $params['paRes'],
                        'card_type' => $session_info['card_info']['card_type'],
                        'last_name' => $session_info['card_info']['last_name'],
                        'first_name' => $session_info['card_info']['first_name'],
                        'account_number' => $session_info['card_info']['card_number'],
                        'expiration_month' => $session_info['card_info']['card_month'],
                        'expiration_year' => $session_info['card_info']['card_year'],
                    );
                    $cybersource = new CyberSource($merchant_id, $partner_payment_id);
                    $result = $cybersource->authorizeSubcription3D($inputs_authorize3D);
                    if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100') {
                        $eciRaw = @$result['result']->payerAuthValidateReply->eciRaw;
                        $error_message = '';
                        $bank_trans_id = $result['result']->requestID;
                        if (!empty($eciRaw) && in_array($eciRaw, array('02', '05', '01', '06'))) { // success
                            $result_code = 'ACCEPT';
                        } else {
                            $result_code = 'REJECT';
                            $cbs_stb->cancelAuthorizeCard(array('token' => $token));
                            $error_message = 'Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp';
                        }
                    } else {
                        if (CyberSource::checkVisaReview($result['result'])) {
                            $error_message = '';
                            $bank_trans_id = $result['result']->requestID;
                            $result_code = 'REVIEW';
                        } elseif (CyberSource::checkVisaReject($result['result'])) {
                            $result_code = 'REJECT';
                            $error_message = CyberSource::getErrorMessage($result['result']->reasonCode);
                        } else {
                            $error_message = CyberSource::getErrorMessage($result['result']->reasonCode);
                        }
                    }
                } elseif ($partner_payment['code'] == 'CYBER-SOURCE-VCB') {
                    $token = Encryption::decryptAES($card_token['token_cybersource'], $GLOBALS['AES_KEY'], $card_token['iv']);
                    $inputs_authorize3D = array(
                        'cashin_id' => $session_info['process_info']['reference_code'],
                        'cashin_amount' => $session_info['process_info']['amount'],
                        'token' => $token,
                        'signedPARes' => $params['paRes'],
                        'card_type' => $session_info['card_info']['card_type'],
                        'last_name' => $session_info['card_info']['last_name'],
                        'first_name' => $session_info['card_info']['first_name'],
                        'account_number' => $session_info['card_info']['account_number'],
                        'expiration_month' => $session_info['card_info']['expiration_month'],
                        'expiration_year' => $session_info['card_info']['expiration_year'],
                    );
                    $cybersource = new CyberSourceVcb($merchant_id, $partner_payment_id);
                    $result = $cybersource->authorizeSubcription3D($inputs_authorize3D);
                    if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100') {
                        $eciRaw = @$result['result']->payerAuthValidateReply->eciRaw;
                        $error_message = '';
                        $bank_trans_id = $result['result']->requestID;
                        if (!empty($eciRaw) && in_array($eciRaw, array('02', '05', '01', '06'))) { // success
                            $result_code = 'ACCEPT';
                        } else {
                            $result_code = 'REJECT';
                            $cbs_stb->cancelAuthorizeCard(array('token' => $token));
                            $error_message = 'Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp';
                        }
                    } else {
                        if (CyberSourceVcb::checkVisaReview($result['result'])) {
                            $error_message = '';
                            $bank_trans_id = $result['result']->requestID;
                            $result_code = 'REVIEW';
                        } elseif (CyberSourceVcb::checkVisaReject($result['result'])) {
                            $result_code = 'REJECT';
                            $error_message = CyberSourceVcb::getErrorMessage($result['result']->reasonCode);
                        } else {
                            $error_message = CyberSourceVcb::getErrorMessage($result['result']->reasonCode);
                        }
                    }
                }
            } else {
                $error_message = 'Tài khoản kênh thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error_message = $get_partner_payment['error_message'];
        }
        CyberSource::_clearSessionVerifyCard($session_info['response_info']['xid']);
        return array('error_message' => $error_message, 'result_code' => $result_code, 'bank_trans_id' => $bank_trans_id);
    }

    public static function getPartnerPaymentByBankCode($bank_code, $merchant_id)
    {
        $error_message = 'Lỗi không xác định';
        $partner = null;
        $payment_method_code = $bank_code . '-TOKENIZATION';
        $payment_method_id = PaymentMethod::getPaymentMethodIdActiveByCode($payment_method_code);
        if ($payment_method_id != false) {
            $partner_payment_method = PartnerPaymentMethod::getByPaymentMethodId($payment_method_id);
            if ($partner_payment_method != false) {
                $partner_payment = PartnerPayment::getById($partner_payment_method['partner_payment_id']);
                if ($partner_payment != false) {
                    $error_message = '';
                    $partner = $partner_payment;
                } else {
                    $error_message = 'Kênh không tồn tại hoặc bị khóa';
                }
            } else {
                $error_message = 'Kênh thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error_message = 'Phương thức thanh toán không tồn tại hoặc bị khóa';
        }
        return ['error_message' => $error_message, 'partner_payment' => $partner];
    }

    public static function checkCardNumberExist($card_number)
    {
        $card_token = LinkCard::find()
            ->where(['card_number_md5' => Encryption::hashHmacSHA256($card_number, $GLOBALS['SHA256_KEY'])])
            ->andWhere(['in', 'status', [LinkCard::STATUS_ACTIVE, LinkCard::STATUS_LOCK]])
            ->all();
        if (!empty($card_token)) {
            return true;
        }
        return false;
    }

    public static function getRandomAmount($min = self::MIN_MOUNT, $max = self::MAX_AMOUNT)
    {
        return rand($min, $max);
    }

    public static function callBackMerchant($card_token, $is_post = true)
    {


        $card_token_info = json_decode($card_token['info'], true);
        $notify_url = $card_token_info['notify_url'];
        $mask_1 = substr_replace($card_token['card_number_mask'], str_repeat("X", 6), 0, 6);
        $data = [
            'id' => $card_token['id'],
            'customer_id' => $card_token_info['customer_id'],
            'token_merchant' => $card_token['token_merchant'],
            'token_status' => strval($card_token['status']),
            'card_type' => $card_token['card_type'],
            'card_info' => $mask_1,
        ];

        if (isset($card_token_info['customer_field']) && $card_token_info['customer_field'] != "") {
            $data['customer_field'] = json_decode($card_token_info['customer_field'], true);
            $data['card_month'] = $card_token_info['card_info']['card_month'];
            $data['card_year'] = $card_token_info['card_info']['card_year'];
        } elseif (isset($card_token_info['card_info']['card_month']) && isset($card_token_info['card_info']['card_year'])) {
            $data['card_month'] = $card_token_info['card_info']['card_month'];
            $data['card_year'] = $card_token_info['card_info']['card_year'];
        }


        $data['checksum'] = self::_getChecksumNotifyUrl($card_token);
        $payment_link = PaymentLink::find()->where('code = :code AND card_token = 1', [':code' => $card_token_info['customer_id']])->one();
        if ($payment_link) {
            $payment_link->card_token = $card_token['id'];
            $payment_link->status = PaymentLink::STATUS_AUTHORIZE;
            $payment_link->save();
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $notify_url);
            if ($is_post) {
                $request = json_encode($data);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if (substr($notify_url, 0, 5) == 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36');
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_errno($ch);
            CardTokenBasicApi::writeLogCallback('[CARD-TOKEN-ID] ' . $card_token['id'] .
                ' [CALLBACK-DATA] ' . json_encode($data) .
                ' [HTTP-CODE] ' . $http_code .
                ' [CURL-ERROR] ' . $curl_error .
                ' [MERCHANT-RESPONSE]' . json_encode($result));
        }


    }

    protected static function removeTokenCybersource($params)
    {
        if (!empty($params->token_cybersource)) {
            $token_cybersource = Encryption::decryptAES($params->token_cybersource, $GLOBALS['AES_KEY'], $params->iv);
            $partner_payment = PartnerPayment::getById($params->partner_payment_id);
            if (!empty($partner_payment)) {
                $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($params->merchant_id, $params->partner_payment_id);
                if (!empty($partner_payment_account_info)) {
                    if ($partner_payment['code'] == 'CYBER-SOURCE') {
                        $cybersource = new CyberSource($params->merchant_id, $params->partner_payment_id);
                        $cancel_card_token = $cybersource->cancelAuthorizeCard([
                            'token' => $token_cybersource
                        ]);
                    } elseif ($partner_payment['code'] == 'CYBER-SOURCE-VCB') {
                        $cybersource = new CyberSourceVcb($params->merchant_id, $params->partner_payment_id);
                        $cancel_card_token = $cybersource->cancelAuthorizeCard([
                            'token' => $token_cybersource
                        ]);
                    } else {
                        $cancel_card_token['error'] = '';
                    }
                    if ($cancel_card_token['error'] == '') {
                        $error_message = 'Success';
                    } else {
                        $error_message = 'Huỷ token Cybersource thất bại';
                    }
                } else {
                    $error_message = 'Tài khoản kênh thanh toán không tồn tại hoặc bị khóa';
                }
            } else {
                $error_message = 'Kênh thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error_message = 'Chưa có token Cybersource, không thể huỷ liên kết';
        }

        return ['error_message' => $error_message];
    }

    private static function _processCardFullName($fullname, &$first_name = '', &$last_name = '')
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

    private static function _convertName($content)
    {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    private static function _getChecksumNotifyUrl($params)
    {

        $first_name = null;
        $last_name = null;
        $infos = json_decode($params['info']);
        foreach ($infos as $key => $info) {
            $params[$key] = $info;
        }

        if ($params['link_card']) {
            $list_data = [
                'version',
                'merchant_id',
                'token_merchant',
                'status',
                'notify_url',
            ];

        } else {
            $list_data = [
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
                'notify_url',
            ];

        }


        $string_data = '';
        $is_first_key = false;
        foreach ($list_data as $key) {
            if ($is_first_key) {
                $string_data .= '&' . $key . '=' . $params[$key];
            } else {
                $string_data .= $key . '=' . $params[$key];
                $is_first_key = true;
            }
        }
        $merchant_pass = self::getMerchantPass($params['merchant_id']);

        CardTokenBasicApi::writeLogCallback('[String] ' . $string_data . '-Hash-key' . $merchant_pass . '-Checksum' . Encryption::hashHmacSHA256($string_data, $merchant_pass));


        return Encryption::hashHmacSHA256($string_data, $merchant_pass);
    }

    private static function getMerchantPass($merchant_id)
    {
        $merchant_password = '';
        $merchant_info = Merchant::findOne(['id' => $merchant_id]);
        if ($merchant_info->password) {
            $merchant_password = @$merchant_info->password;
        }
        return $merchant_password;

    }

    private static function _getCode($checkout_order_id)
    {
        return strtoupper('TPL' . substr(md5($checkout_order_id . 'checkout_order' . rand()), 9, 10));
    }

    public static function getTokenCode($checkout_order_id)
    {
        return $checkout_order_id . '-' . self::_getCode($checkout_order_id);
    }
}






