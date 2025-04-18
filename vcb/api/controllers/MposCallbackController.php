<?php

namespace api\controllers;

use common\components\libs\NotifySystem;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\business\InstallmentBusiness;
use common\models\db\BinAcceptV2;
use common\models\db\InstallmentFeePos;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use api\components\ApiController;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\Merchant;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;

class MposCallbackController extends ApiController
{

    private $log_id;

    const MPOS_ENCRYPT_KEY = 'qVV6iH35sAJ6Z0uj';
    const MPOS_SERVICE_UPDATE_TRANSACTION = 'SERVICE_UPDATE_TRANSACTION';
    const MPOS_TRANS_STATUS_SUCCESS = [100, 103, 104, 105]; // transStatus MPOS: success transaction
    const MPOS_TRANS_STATUS_VOID = [102]; // transStatus MPOS: void transaction
    const MPOS_TRANS_STATUS_REVERT = [101]; // transStatus MPOS: void transaction
    const MPOS_RESPONSE_DATA_SUCCESS = '200'; // response tra MPOS khi xu ly GD thanh cong
    const MPOS_RESPONSE_DATA_FAIL = '201'; // response tra MPOS khi xu ly GD that bai
    const MPOS_PAYMENT_METHOD_SWIPE_CARD = 'MPOS-SWIPE-CARD'; // phuong thuc MPOS thanh toan ngay
    const MPOS_PAYMENT_METHOD_SWIPE_CARD_INSTALLMENT = 'MPOS-SWIPE-CARD-INSTALLMENT'; // phuong thuc MPOS thanh toan tra gop

    const MPOS_ALLOW_CALLBACK_IPS = ['103.109.32.68', '103.109.32.66', '14.177.239.244', '103.109.32.94', '14.177.239.192', '171.244.53.220', '14.177.239.203', '18.142.214.241', '18.143.216.8', '103.77.244.27', '103.52.113.202', '101.99.7.213'];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post'],
//                    'index-raw' => ['post'],
                ],
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $this->log_id = uniqid();
        return true;
    }

    public function actionIndex()
    {

        $response = [
            'resCode' => self::MPOS_RESPONSE_DATA_FAIL
        ];

        $raw_request = trim(file_get_contents("php://input"));
        $this->writeLog('---------------------------------------------------');
        $this->writeLog('[RAW-REQUEST] ' . $raw_request . ' [IP] ' . get_client_ip());
        if (!in_array(get_client_ip(), self::MPOS_ALLOW_CALLBACK_IPS)) {
            exit();
        }

        $request = json_decode($raw_request, true);
        $reqData_raw = $request['reqData'];
        $reqData = $this->decryptAES($reqData_raw);
        $this->writeLog('[REQUEST-AFTER-DECRYPT] ' . $reqData);
        if (!empty($reqData)) {
            $data = json_decode($reqData, true);
            @Logs::writeELK($data, 'pg-mpos-callback', 'INPUT', '', '', 'web', $this->log_id);
            if (!empty($data)) {
                if (!empty($data['serviceName'])) {
                    switch ($data['serviceName']) {
                        case self::MPOS_SERVICE_UPDATE_TRANSACTION:
                            $result = $this->serviceUpdateTransaction($data);
                            $this->writeLog('[UPDATE-TRANSACTION] ' . json_encode($result));
                            if ($result['error'] == '') {
                                $response['resCode'] = self::MPOS_RESPONSE_DATA_SUCCESS;
                            }
                            break;
                    }
                }
            }
        }
        @Logs::writeELK($response, 'pg-mpos-callback', 'OUTPUT', '', '', 'web', $this->log_id);
        echo json_encode($response);
        $this->writeLog('[RESPONSE] ' . json_encode($response));
        exit();
    }

    public function actionIndexRaw()
    {
        $response = [
            'resCode' => self::MPOS_RESPONSE_DATA_FAIL
        ];
        $raw_request = trim(file_get_contents("php://input"));
        $this->writeLog('---------------------------------------------------');
        $request = json_decode($raw_request, true);
        @Logs::writeELK($request, 'pg-mpos-callback', 'INPUT', '', '', 'web', $this->log_id);
        if (true) {
            $data = json_decode($raw_request, true);
            if (!empty($data)) {
                if (!empty($data['serviceName'])) {
                    switch ($data['serviceName']) {
                        case self::MPOS_SERVICE_UPDATE_TRANSACTION:
                            $result = $this->serviceUpdateTransaction($data);
                            $this->writeLog('[UPDATE-TRANSACTION] ' . json_encode($result));
                            if ($result['error'] == '') {
                                $response['resCode'] = self::MPOS_RESPONSE_DATA_SUCCESS;
                            }
                            break;
                    }
                }
            }
        }
        @Logs::writeELK($response, 'pg-mpos-callback', 'OUTPUT', '', '', 'web', $this->log_id);

        echo json_encode($response);
        $this->writeLog('[RESPONSE] ' . json_encode($response));
        exit();
    }

    private function serviceUpdateTransaction($data)
    {
        $error = 'Lỗi không xác định';
        // var_dump($data);die;
        $transStatus = $data['transStatus'];

        $txid = $data['txid'];
        $muid = $data['merchantId'];
        if (in_array($transStatus, self::MPOS_TRANS_STATUS_SUCCESS)) {
            $this->writeLog('[PROCESSING-UPDATE-SUCCESS]');
            $transaction = Transaction::getByBankReferCode($txid);
            if ($transaction == false) {
                $info_by_muid = $this->getInfoByMuid($muid);
                if (!empty($info_by_muid)) {
                    if (!empty($data['bankName']) && !empty($data['period'])) { // thanh toan tra gop
                        $transaction_type = TransactionType::getInstallmentTransactionTypeId();
                        $amount = $data['originalAmount'];
                    } else { // thanh toan ngay
                        $transaction_type = TransactionType::getPaymentTransactionTypeId();
                        $amount = $data['transAmount'];
                    }
                    if (isset($data['qrType']) && $data['qrType'] == 'Ngan Luong') {
                        $error = 'Giao dịch QR';
                    } else {
                        $process_order = $this->processOrder([
                            'merchant_id' => $info_by_muid['merchant_id'],
                            'partner_payment_id' => $info_by_muid['partner_payment_id'],
                            'order_code' => 'MPOS_' . $txid,
                            'order_description' => 'Thanh toán cho đơn hàng MPOS_' . $txid,
                            'bank_refer_code' => $txid,
                            'transaction_type' => $transaction_type,
                            'amount' => $amount,
                            'month' => (empty($data['period'])) ? 0 : $data['period'],
                            'partner_payment_info' => json_encode($data),
                            'cardHolderName' => $data['cardHolderName'] ?? '',
                            'customerMobile' => $data['customerMobile'] ?? '',
                            'customerEmail' => $data['customerEmail'] ?? '',
                            'pan' => $data['pan'] ?? '',
                            'bankName' => $data['bankName'] ?? '',
                            'authCode' => $data['authCode'] ?? '',
                            'transAmount' => $data['transAmount'] ?? '',
                            'first8Digits' => $data['first8Digits'] ?? '',
                        ]);
                        $error = $process_order;
                    }

                } else {
                    $error = 'MPOS muid chưa được cấu hình hoặc bị khóa';
                }
            } else {
                $error = 'Giao dịch đã tồn tại';
            }
        } elseif (in_array($transStatus, self::MPOS_TRANS_STATUS_REVERT)) {
            $this->writeLog('[PROCESSING-UPDATE-REVERT]');
            $transaction = Transaction::getByBankReferCode($txid);
            if ($transaction == false) {
                $info_by_muid = $this->getInfoByMuid($muid);
                $this->writeLog('[PROCESSING-UPDATE-REVERT]1' . json_encode($info_by_muid));


                if (!empty($info_by_muid)) {
                    if (!empty($data['bankName']) && !empty($data['period'])) { // thanh toan tra gop
                        $transaction_type = TransactionType::getInstallmentTransactionTypeId();
                        $amount = $data['originalAmount'];
                    } else { // thanh toan ngay
                        $transaction_type = TransactionType::getPaymentTransactionTypeId();
                        $amount = $data['transAmount'];
                    }
                    $checkout_order = CheckoutOrder::getByOrderMposCode($txid, $info_by_muid['merchant_id']);
                    if (empty($checkout_order)) {
                        $uniquid = uniqid();
                        $process_order = $this->processOrderRevert([
                            'merchant_id' => $info_by_muid['merchant_id'],
                            'partner_payment_id' => $info_by_muid['partner_payment_id'],
                            'order_code' => 'MPOS_' . $txid,
                            'order_description' => 'Thanh toán cho đơn hàng MPOS_' . $txid,
                            'bank_refer_code' => $txid,
                            'transaction_type' => $transaction_type,
                            'amount' => $amount,
                            'month' => (empty($data['period'])) ? 0 : $data['period'],
                            'partner_payment_info' => json_encode($data)
                        ]);
                        $error = $process_order;
                    }
                    $error = 'Đơn hàng đã tồn tại';

                } else {
                    $error = 'MPOS muid chưa được cấu hình hoặc bị khóa';
                }
            } else {
                $info_by_muid = $this->getInfoByMuid($muid);
                $this->writeLog('[PROCESSING-UPDATE-REVERT]2' . json_encode($info_by_muid));


                if (!empty($info_by_muid)) {
                    if (!empty($data['bankName']) && !empty($data['period'])) { // thanh toan tra gop
                        $transaction_type = TransactionType::getInstallmentTransactionTypeId();
                        $amount = $data['originalAmount'];
                    } else { // thanh toan ngay
                        $transaction_type = TransactionType::getPaymentTransactionTypeId();
                        $amount = $data['transAmount'];
                    }
                    $uniquid = uniqid();
                    $process_order = $refund_order = $this->revertOrder([
                        'checkout_order_id' => $transaction['checkout_order_id'],
                        'merchant_id' => $info_by_muid['merchant_id'],
                        'partner_payment_id' => $info_by_muid['partner_payment_id'],
                        'order_code' => 'MPOS_' . $txid,
                        'order_description' => 'Thanh toán cho đơn hàng MPOS_' . $txid,
                        'bank_refer_code' => $txid,
                        'transaction_type' => $transaction_type,
                        'amount' => $amount,
                        'month' => (empty($data['period'])) ? 0 : $data['period'],
                        'partner_payment_info' => json_encode($data),
                        'cardHolderName' => $data['cardHolderName'] ?? '',
                        'customerMobile' => $data['customerMobile'] ?? '',
                        'customerEmail' => $data['customerEmail'] ?? '',
                        'pan' => $data['pan'] ?? '',
                        'bankName' => $data['bankName'] ?? '',
                        'authCode' => $data['authCode'] ?? '',
                        'transAmount' => $data['transAmount'] ?? '',
                        'first8Digits' => $data['first8Digits'] ?? '',
                    ]);
                    $error = $process_order;
                } else {
                    $error = 'MPOS muid chưa được cấu hình hoặc bị khóa';
                }
            }
        } elseif (in_array($transStatus, self::MPOS_TRANS_STATUS_VOID)) {
            $this->writeLog('[PROCESSING-UPDATE-VOID]');
            $transaction = Transaction::getByBankReferCode($txid);
            if ($transaction != false) {
                if ($transaction['status'] == Transaction::STATUS_PAID) {
                    $info_by_muid = $this->getInfoByMuid($muid);
                    if (!empty($info_by_muid)) {
                        $refund_order = $this->refundOrder([
                            'checkout_order_id' => $transaction['checkout_order_id'],
                            'bank_refer_code' => $txid,
                        ]);
                        $error = $refund_order;
                    } else {
                        $error = 'Muid chưa được cấu hình hoặc bị khóa';
                    }
                } else {
                    $error = 'Giao dịch với MPOS txid chưa thành công';
                }
            } else {
                $error = 'Giao dịch không tồn tại';
            }
        } else {
            $error = 'MPOS transStatus không xác định';
        }
        return ['error' => $error];
    }

    private function getInfoByMuid($muid)
    {
        $info = [];
        $partner_payment_account = PartnerPaymentAccount::getByPartnerPaymentAccount($muid);
        if ($partner_payment_account != false) {
            $info['merchant_id'] = $partner_payment_account['merchant_id'];
            $info['partner_payment_id'] = $partner_payment_account['partner_payment_id'];
        }
        return $info;
    }

    private function revertOrder($data)
    {
        $transaction_type = $data['transaction_type'];
        $partner_payment_id = $data['partner_payment_id'];
        $bank_refer_code = $data['bank_refer_code'];
        $month = $data['month'];
        $merchant_id = $data['merchant_id'];
        $error = 'Lỗi không xác định';

        $checkout_order_id = $data['checkout_order_id'];
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $checkout_order_id]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
            $update_paid = TransactionBusiness::failureV2([
                'transaction_id' => $checkout_order_info['transaction_id'],
                'transaction_type_id' => $transaction_type,
                'time_paid' => time(),
                'user_id' => 0,
                'month' => $month,
                'payment_info' => '',
                'reason_id' => '',
                'reason' => 'Giao dịch đảo',
            ]);
            if ($update_paid['error_message'] == '') {
                $inputs = array(
                    'checkout_order_id' => $checkout_order_id,
                    'user_id' => '0',
                );
                $update_checkout_order_failure = CheckoutOrderBusiness::updateCheckoutOrderStatusFailureV2($inputs, false);
                if ($update_checkout_order_failure['error_message'] === '') {
                    $error = '';
                } else {

                    $error = 'Lỗi cập nhật GD(paid): ' . Translate::get($update_checkout_order_failure['error_message']);

                }
                // notify merchant

            } else {
                $error = 'Lỗi cập nhật GD(paid): ' . $update_paid['error_message'];
            }
        } else {
            $error = 'Đơn hàng không tồn tại';
        }
        return $error;
    }

    private function getPaymentMethodIdByType($transaction_type)
    {
        switch ($transaction_type) {
            case TransactionType::getPaymentTransactionTypeId():
                $payment_method_code = self::MPOS_PAYMENT_METHOD_SWIPE_CARD;
                break;
            case TransactionType::getInstallmentTransactionTypeId();
                $payment_method_code = self::MPOS_PAYMENT_METHOD_SWIPE_CARD_INSTALLMENT;
                break;
        }
        $payment_method_id = PaymentMethod::getPaymentMethodIdActiveByCode($payment_method_code);
        if ($payment_method_id != false) {
            return $payment_method_id;
        }
        return $payment_method_id;
    }

    private function processOrder($data)
    {
        $error = 'Lỗi không xác định';
        $transaction_type = $data['transaction_type'];
        $partner_payment_id = $data['partner_payment_id'];
        $bank_refer_code = $data['bank_refer_code'];
        $month = $data['month'];
        $merchant_id = $data['merchant_id'];
        $merchant_info = Merchant::getById($merchant_id);
        if ($merchant_info != false) {
            $payment_method_id = $this->getPaymentMethodIdByType($transaction_type);
            if ($payment_method_id != false) {
                $notify_url = (empty($merchant_info['url_notification'])) ? '' : $merchant_info['url_notification'];
                // tao don hang
                $create_order = CheckoutOrderBusiness::add([
                    'merchant_id' => $merchant_id,
                    'version' => '1.0',
                    'language_id' => '1',
                    'partner_id' => $partner_payment_id,
                    'order_code' => $data['order_code'],
                    'order_description' => $data['order_description'],
                    'amount' => $data['amount'],
                    'currency' => 'VND',
                    'return_url' => '',
                    'cancel_url' => '',
                    'notify_url' => $notify_url,
                    'time_limit' => strtotime(date('c', time() + 7 * 86400)),
                    'buyer_fullname' => $data['cardHolderName'] ?? '',
                    'buyer_email' => $data['customerEmail'] ?? '',
                    'buyer_mobile' => $data['customerMobile'] ?? '',
                    'buyer_address' => '',
                    'user_id' => 0
                ]);
                if ($create_order['error_message'] == '') {
                    $checkout_order_id = $create_order['id'];
                    $checkout_order_token_code = $create_order['token_code'];
                    // update don hang requestPayment

                    $params_request_payment = [
                        'checkout_order_id' => $checkout_order_id,
                        'payment_method_id' => $payment_method_id,
                        'partner_payment_id' => $partner_payment_id,
                        'partner_payment_method_refer_code' => '',
                        'user_id' => 0,
                        'transaction_type_id' => $transaction_type
                    ];


                    if ($transaction_type == TransactionType::getInstallmentTransactionTypeId()) {
                        $first_six_digit = substr(str_replace(' ', '', $data['pan']), 0, 6);


                        if (!empty($first_six_digit)) {
                            $bin_data = false;
                            if (isset($data['first8Digits']) && $data['first8Digits'] != "") {
                                $bin_data = BinAcceptV2::find()->where([
                                    'type' => BinAcceptV2::TYPE_CREDIT,
                                    'code' => $data['first8Digits'],
                                    'status' => BinAcceptV2::STATUS_ACTIVE
                                ])->one();
                            }
                            if (!$bin_data) {
                                $bin_data = BinAcceptV2::find()->where([
                                    'type' => BinAcceptV2::TYPE_CREDIT,
                                    'code' => $first_six_digit,
                                    'status' => BinAcceptV2::STATUS_ACTIVE
                                ])->one();
                            }

                            if ($bin_data) {
                                $fee_config = InstallmentFeePos::find()->where([
                                        'bank_code' => $bin_data['bank_code'],
                                        'method' => $bin_data['card_type'],
                                        'merchant_id' => $merchant_id,
                                        'status' => InstallmentFeePos::STATUS_ACTIVE,
                                        'period' => $month,
                                    ]
                                )->one();
                                @Logs::writeELK(['order_code' => $data['order_code'], 'fee_info' => $fee_config], 'pg-mpos-callback', 'INFO', '', '', 'web', $this->log_id);
                                if ($fee_config) {
                                    switch ($fee_config->fee_bearer) {
                                        case 'MERCHANT':
                                        {
                                            $installment_fee_merchant = $fee_config['merchant_fixed_fee'] + $data['amount'] * $fee_config['merchant_percent_fee'] / 100;
                                            $installment_fee_buyer = 0;
                                            break;
                                        }
                                        case 'CARD_OWNER':
                                        {
                                            $installment_fee_merchant = 0;
                                            $installment_fee_buyer = ceil(($data['amount'] * 100) / (100 - $fee_config['card_owner_percent_fee']) / 1000) * 1000 - $data['amount'];
                                            break;
                                        }
                                        default:
                                        {
                                            $installment_fee_merchant = $fee_config['merchant_fixed_fee'] + $data['amount'] * $fee_config['merchant_percent_fee'] / 100;
                                            $installment_fee_buyer = ceil(($data['amount'] * 100) / (100 - $fee_config['card_owner_percent_fee']) / 1000) * 1000 - $data['amount'];
                                            break;
                                        }
                                    }
                                } else {
                                    $installment_fee_merchant = 0;
                                    $installment_fee_buyer = 0;
                                }
                                $params_request_payment['installment_mpos'] = true;
                                $params_request_payment['installment_fee_merchant'] = ceil($installment_fee_merchant / 1000) * 1000;
                                $params_request_payment['installment_fee_buyer'] = $installment_fee_buyer;
                            }
                        }
                    }
                    $update_request_payment = CheckoutOrderBusiness::requestPayment($params_request_payment);
                    if ($update_request_payment['error_message'] == '') {
                        $transaction_id = $update_request_payment['transaction_id'];
                        // update GD paying
                        $update_paying = TransactionBusiness::paying([
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => $data['partner_payment_info'],
                            'user_id' => 0,
                        ]);
                        if ($update_paying['error_message'] == '') {
                            $cardInfo = array(
                                'card_fullname' => $data['cardHolderName'],
                                'card_number' => Strings::encodeCreditCardNumber($data['pan']),
                                'card_month' => '00',
                                'card_year' => '00',
                                'first_eight_digits' => isset($data['first8Digits']) && $data['first8Digits'] != "" ? $data['first8Digits'] : '',
                            );
                            Transaction::insertCardInfo($transaction_id, $cardInfo);

                            // update GD paid
                            $update_paid = TransactionBusiness::paid([
                                'transaction_id' => $transaction_id,
                                'transaction_type_id' => $transaction_type,
                                'bank_refer_code' => $bank_refer_code,
                                'time_paid' => time(),
                                'user_id' => 0,
                                'month' => $month,
                                'payment_info' => '',
                                'authorizationCode' => $data['authCode'] ?? '',
                            ]);
                            if ($update_paid['error_message'] == '') {
                                if ($transaction_type == TransactionType::getPaymentTransactionTypeId()) {
                                    $error = '';
                                } elseif ($transaction_type == TransactionType::getInstallmentTransactionTypeId()) {

                                    $transaction_info = Transaction::findOne($transaction_id);

                                    if ($data['transAmount'] == $transaction_info->amount + $transaction_info->sender_fee + $transaction_info->installment_fee_buyer) {
                                        // update don hang tra gop paid
                                        $installment_info = [
                                            'method' => $bin_data['card_type'],
                                            'number' => $cardInfo['card_number'],
                                            'card_name' => $data['cardHolderName'],
                                        ];

                                        $update_installment_paid = CheckoutOrderBusiness::updateStatusInstallMentPaidMPOS([
                                            'transaction_id' => $transaction_id,
                                            'checkout_order_id' => $checkout_order_id,
                                            'time_paid' => time(),
                                            'user_id' => 0,
                                            'month' => $month,
                                            'installment_info' => json_encode($installment_info),
                                        ]);
                                        if ($update_installment_paid['error_message'] == '') {
                                            $error = '';
                                        } else {
                                            $error = 'Lỗi cập nhật đơn hàng(updateStatusInstallMentPaidMPOS): ' . $update_installment_paid['error_message'];
                                        }
                                    } else {
                                        $error = '';
                                    }
                                }
                                // notify merchant
                                if ($error == '') {
                                    if ($notify_url != '') {
                                        $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", ["checkout_order_id = :checkout_order_id AND status = :status ", "checkout_order_id" => $checkout_order_id, "status" => CheckoutOrderCallback::STATUS_NEW]);
                                        if ($checkout_order_callback_info != false) {
                                            CheckoutOrderCallback::process($checkout_order_callback_info);
                                        }
                                    }
                                }
                            } else {
                                $error = 'Lỗi cập nhật GD(paid): ' . $update_paid['error_message'];
                            }
                        } else {
                            $error = 'Lỗi cập nhật GD(paying): ' . $update_paying['error_message'];
                        }
                    } else {
                        $error = 'Lỗi cập nhật đơn hàng(requestPayment): ' . $update_request_payment['error_message'];
                    }
                } else {
                    $error = 'Lỗi tạo đơn hàng: ' . $create_order['error_message'];
                }
            } else {
                $error = 'Phương thức thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error = 'Merchant không tồn tại hoặc bị khóa';
        }
        return $error;
    }

    private function processOrderRevert($data)
    {
        $error = 'Lỗi không xác định';
        $transaction_type = $data['transaction_type'];
        $partner_payment_id = $data['partner_payment_id'];
        $bank_refer_code = $data['bank_refer_code'];
        $month = $data['month'];
        $merchant_id = $data['merchant_id'];
        $merchant_info = Merchant::getById($merchant_id);
        if ($merchant_info != false) {
            $payment_method_id = $this->getPaymentMethodIdByType($transaction_type);
            if ($payment_method_id != false) {
                $notify_url = (empty($merchant_info['url_notification'])) ? '' : $merchant_info['url_notification'];
                // tao don hang
                $create_order = CheckoutOrderBusiness::add([
                    'merchant_id' => $merchant_id,
                    'version' => '1.0',
                    'language_id' => '1',
                    'partner_id' => $partner_payment_id,
                    'order_code' => $data['order_code'],
                    'order_description' => $data['order_description'],
                    'amount' => $data['amount'],
                    'currency' => 'VND',
                    'return_url' => '',
                    'cancel_url' => '',
                    'notify_url' => $notify_url,
                    'time_limit' => strtotime(date('c', time() + 7 * 86400)),
                    'buyer_fullname' => '',
                    'buyer_email' => '',
                    'buyer_mobile' => '',
                    'buyer_address' => '',
                    'user_id' => 0
                ]);
                if ($create_order['error_message'] == '') {
                    $checkout_order_id = $create_order['id'];
                    $checkout_order_token_code = $create_order['token_code'];
                    // update don hang requestPayment
                    $update_request_payment = CheckoutOrderBusiness::requestPayment([
                        'checkout_order_id' => $checkout_order_id,
                        'payment_method_id' => $payment_method_id,
                        'partner_payment_id' => $partner_payment_id,
                        'partner_payment_method_refer_code' => '',
                        'user_id' => 0,
                        'transaction_type_id' => $transaction_type
                    ]);
                    if ($update_request_payment['error_message'] == '') {
                        $transaction_id = $update_request_payment['transaction_id'];
                        // update GD paying
                        $update_paying = TransactionBusiness::paying([
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => $data['partner_payment_info'],
                            'user_id' => 0,
                        ]);
                        if ($update_paying['error_message'] == '') {
                            // update GD paid
                            $update_paid = TransactionBusiness::failure([
                                'transaction_id' => $transaction_id,
                                'transaction_type_id' => $transaction_type,
                                'bank_refer_code' => $bank_refer_code,
                                'time_paid' => time(),
                                'user_id' => 0,
                                'month' => $month,
                                'payment_info' => '',
                                'reason_id' => '',
                                'reason' => '',
                            ]);
                            if ($update_paid['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $checkout_order_id,
                                    'user_id' => '0',
                                );
                                $update_checkout_order_failure = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                                if ($update_checkout_order_failure['error_message'] === '') {
                                    $error = '';
                                } else {

                                    $error = 'Lỗi cập nhật GD(paid): ' . Translate::get($update_checkout_order_failure['error_message']);

                                }
                                // notify merchant

                            } else {
                                $error = 'Lỗi cập nhật GD(paid): ' . $update_paid['error_message'];
                            }
                        } else {
                            $error = 'Lỗi cập nhật GD(paying): ' . $update_paying['error_message'];
                        }
                    } else {
                        $error = 'Lỗi cập nhật đơn hàng(requestPayment): ' . $update_request_payment['error_message'];
                    }
                } else {
                    $error = 'Lỗi tạo đơn hàng: ' . $create_order['error_message'];
                }
            } else {
                $error = 'Phương thức thanh toán không tồn tại hoặc bị khóa';
            }
        } else {
            $error = 'Merchant không tồn tại hoặc bị khóa';
        }
        return $error;
    }

    private function refundOrder($data)
    {
        $error = 'Lỗi không xác định';
        $checkout_order_id = $data['checkout_order_id'];
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $checkout_order_id]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
            $request_refund = CheckoutOrderBusiness::processRequestRefund([
                'checkout_order' => $checkout_order,
                'refund_type' => $GLOBALS['REFUND_TYPE']['TOTAL'],
                'refund_amount' => $checkout_order['amount'],
                'refund_reason' => 'Hoàn tiền GD thanh toán qua MPOS',
                'user_id' => 0
            ]);
            if ($request_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['SUCCESS']) {
                $error = '';
            } elseif ($request_refund['refund_status'] == $GLOBALS['REFUND_STATUS']['WAIT']) {
                $update_refund = CheckoutOrderBusiness::updateStatusRefund([
                    'checkout_order_id' => $checkout_order_id,
                    'time_paid' => time(),
                    'bank_refer_code' => $data['bank_refer_code'],
                    'receiver_fee' => 0,
                    'user_id' => 0
                ]);
                if ($update_refund['error_message'] == '') {
                    $payment_transaction = Transaction::findOne($checkout_order_info['transaction_id']);
                    if ($payment_transaction->transaction_type_id == TransactionType::getInstallmentTransactionTypeId()) {
                        $cancel_installment = InstallmentBusiness::cancel([
                            'checkout_order_id' => $checkout_order_info['id']
                        ]);
                    }


                    $error = '';
                } else {
                    $error = 'Lỗi cập nhật hoàn tiền(updateStatusRefund): ' . $update_refund['error_message'];
                }
            } else {
                $error = 'Lỗi tạo yêu cầu hoàn tiền(processRequestRefund): ' . $request_refund['error_message'];
            }
        } else {
            $error = 'Đơn hàng không tồn tại';
        }
        return $error;
    }

    private function encryptAES($plain_text)
    {
        return base64_encode(
            openssl_encrypt($plain_text, 'AES-128-ECB', self::MPOS_ENCRYPT_KEY, OPENSSL_RAW_DATA)
        );
    }

    private function decryptAES($cipher_text)
    {
        return openssl_decrypt(
            base64_decode($cipher_text), 'AES-128-ECB', self::MPOS_ENCRYPT_KEY, OPENSSL_RAW_DATA
        );
    }

    private function writeLog($data)
    {
        $file_name = 'api' . DS . 'mpos_callback' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $this->log_id . ' ' . $data);
    }
}
