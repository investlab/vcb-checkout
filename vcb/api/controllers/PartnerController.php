<?php

namespace api\controllers;

use api\components\ApiController;
use checkout\controllers\CallBackController;
use common\components\libs\NotifySystem;
use common\components\utils\Logs;
use common\components\utils\Strings;
use common\models\business\TransactionBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\CheckoutOrderCallback;
use common\models\db\Transaction;
use common\partner_payments\PartnerPaymentBidvVa;
use common\partner_payments\PartnerPaymentZaloPay;
use common\payments\VCBVA;
use common\payments\ZALOPAY;
use common\util\Helpers;
use phpDocumentor\Reflection\Element;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class PartnerController extends ApiController
{
    const PATH_LOG_PREFIX = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'api' . DS . 'partner_callback';
    protected $rq_id;

    public function behaviors(): array
    {
        $this->rq_id = uniqid();

        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'bidv-va-get-bill' => ['post'],
                    'bidv-va-pay-bill' => ['post'],
                    'bidv-va-compare' => ['post'],
                    'vccb-va-notify' => ['post'],
                    'vcb-va-get-bill' => ['post'],
                    'vcb-va-pay-bill' => ['post'],
                    'zalo-pay-notify' => ['post'],
                ],
            ]
        ];
    }

    public function afterAction($action, $result)
    {
        return $result;
    }

    public function actionBidvVaGetBill()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $log_path = self::PATH_LOG_PREFIX . DS . Yii::$app->controller->action->id . DS;
        $this->_writeLog($log_path, '[INPUT]' . json_encode($inputs));
        Logs::writeELKLogPartnerPayment($inputs, 'INPUT', 'BIDV_VA_GET_BILL', '', 'bidv_va');

        $merchant_name = 'NGANLUONG';
        if ($this->validateBidvVa($inputs, $transaction_id, "GET_BILL")) {
            $transaction_info = Transaction::findOne(['id' => $transaction_id]);
            if ($transaction_info) {

                /** config name */
                if($transaction_info->merchant_id == 2374){
                    $merchant_name = 'DH NGAN HANG HCM';
                }elseif (in_array($transaction_info->merchant_id, [193, 194, 1434])) {
                    $merchant_name = 'EMART';
                }elseif (in_array($transaction_info->merchant_id, [4094])) {
                    $merchant_name = 'DH TAI CHINH MKT';
                } elseif (in_array($transaction_info->merchant_id, [119])) {
                    $merchant_name = 'BV QUANG NINH';
                }elseif (in_array($transaction_info->merchant_id, [1960, 3157])) {
                    $merchant_name = 'BV PHUONG NAM';
                } elseif (in_array($transaction_info->merchant_id, [1129])) {
                    $merchant_name = 'BV QUANG NINH TAM UNG';
                } elseif (in_array($transaction_info->merchant_id, [1130])) {
                    $merchant_name = 'BV QUANG NINH NHA THUOC';
                } elseif (in_array($transaction_info->merchant_id, [1387])) {
                    $merchant_name = 'FUBON';
                } elseif (in_array($transaction_info->merchant_id, [2273])) {
                    $merchant_name = 'GREENTECH';
                } elseif (in_array($transaction_info->merchant_id, [176, 154, 178, 179, 180])) {
                    $merchant_name = 'BV XANH PON';
                } elseif (in_array($transaction_info->merchant_id, [185])) {
                    $merchant_name = 'BV VINH PHUC';
                } elseif (in_array($transaction_info->merchant_id, [204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233,949,1263,1431,1432,2345,2346,2353,3315,3316,3317,3461])) {
                    $merchant_name = 'BV BUU DIEN';
                } else {
                    $merchant_name = 'NGANLUONG';
                }


                if ($transaction_info->status == Transaction::STATUS_PAYING) {
                    $checkout_order_info = CheckoutOrder::findOne(['id' => $transaction_info->checkout_order_id]);
                    $result_code = '000';
                    $result_desc = 'SUCCESS';
                } else if ($transaction_info->status == Transaction::STATUS_PAID) {
                    $result_code = '023';
                    $result_desc = 'INVALID';
                } else {
                    $result_code = '031';
                    $result_desc = 'SYSTEM ERROR';
                }
            } else {
                $result_code = '011';
                $result_desc = 'CUSTOMER NOT EXISTS';
            }
        } else {
            $result_code = '001';
            $result_desc = 'INVALID PARAMETER';
        }

        if ($result_code == '000') {
            $response = [
                'result_code' => $result_code,
                'result_desc' => $result_desc,
                'customer_id' => $transaction_id,
                'customer_name' => $merchant_name,
                'customer_addr' => $checkout_order_info->buyer_address ?? '',
                'data' => [
                    'type' => 1,
                    'amount' => Transaction::getPartnerPaymentAmount($transaction_info ?? 0),
                    'bill_id' => 'VCBPG' . $transaction_info->id ?? 0
                ]
            ];


        } else {
            $response = [
                'result_code' => $result_code,
                'result_desc' => $result_desc
            ];
        }
        Logs::writeELKLogPartnerPayment($response, 'OUTPUT', 'BIDV_VA_GET_BILL', '', 'bidv_va');
        return $response;
    }

    public function actionBidvVaPayBill()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        Logs::writeELKLogPartnerPayment($inputs, 'INPUT', 'BIDV_VA_PAY_BILL', '', 'bidv_va');
        $log_path = self::PATH_LOG_PREFIX . DS . Yii::$app->controller->action->id . DS;

        if ($this->validateBidvVa($inputs, $transaction_id, "PAY_BILL")) {
            $transaction_info = Transaction::findOne(['id' => $transaction_id]);
            if ($transaction_info && $transaction_info->partner_payment_id == PartnerPaymentBidvVa::PARTNER_PAYMENT_ID) {
                if ($transaction_info->status == Transaction::STATUS_PAYING) {
                    $amount = $inputs['amount'];
                    if ($amount == Transaction::getPartnerPaymentAmount($transaction_info)) {
                        $params = array(
                            'transaction_id' => $transaction_id,
                            'time_paid' => time(),
                            'bank_refer_code' => $inputs['trans_id'],
                            'user_id' => 0
                        );
                        $result = TransactionBusiness::paid($params);
                        if ($result['error_message'] == '') {
                            $checkout_order_id = $transaction_info->checkout_order_id;
                            $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $checkout_order_id]);
                            $this->_writeLog($log_path, '[CALLBACK-0] MC_ID: ' . $transaction_info->merchant_id);

                            if (!empty($checkout_order_callback)) {
                                if (!empty($checkout_order_callback)) {
                                    $merchant_id = $transaction_info->merchant_id;
                                    $this->_writeLog($log_path, '[DEBUG-1] ');
                                    if (!in_array($merchant_id,$GLOBALS['MERCHANT_XANHPON']) && !in_array($merchant_id,$GLOBALS['MERCHANT_BUUDIEN']) ){
                                        $this->_writeLog($log_path, '[CALLBACK-1] MC_ID: ' . $merchant_id);
                                        CheckoutOrderCallback::process($checkout_order_callback);
                                    }
                                }
                            }
                            $result_code = '000';
                            $result_desc = 'SUCCESS';
                        } else {
                            $result_code = '031';
                            $result_desc = 'SYSTEM ERROR';
                        }
                    } else {
                        $result_code = '022';
                        $result_desc = 'INVALID AMOUNT';
                    }
                } else if ($transaction_info->status == Transaction::STATUS_PAID) {
                    $result_code = '023';
                    $result_desc = 'INVALID';
                } else {
                    $result_code = '031';
                    $result_desc = 'SYSTEM ERROR';
                }
            } else {
                $result_code = '011';
                $result_desc = 'CUSTOMER NOT EXISTS';
            }

        } else {
            $result_code = '001';
            $result_desc = 'INVALID PARAMETER';
        }
        $response = [
            'result_code' => $result_code,
            'result_desc' => $result_desc,
        ];
        Logs::writeELKLogPartnerPayment($response, 'OUTPUT', 'BIDV_VA_PAY_BILL', '', 'bidv_va');
        return $response;
    }

    public function actionBidvVaCompare()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($this->validateBidvVa($inputs, $transaction_id, "COMPARE")) {
            $result_code = '000';
            $result_desc = 'SUCCESS';
            $result_data = [];

            $transactions = Transaction::find()
                ->where(['partner_payment_id' => PartnerPaymentBidvVa::PARTNER_PAYMENT_ID])
                ->andWhere(['>=', 'time_paid', $inputs['start_date']])
                ->andWhere(['<=', 'time_paid', $inputs['end_date']])
                ->andWhere(['status' => Transaction::STATUS_PAID])
                ->all();

            if ($transactions) {
                foreach ($transactions as $transaction) {
                    $result_data[] = [
                        'orderCode' => PartnerPaymentBidvVa::PREFIX_ORDER . $transaction->id,
                        'customerId' => $transaction->id,
                        'amount' => Transaction::getPartnerPaymentAmount($transaction ?? 0),
                        'paymentTime' => date('hms', $transaction->time_paid),
                        'paymentDate' => date('dYmd', $transaction->time_paid),
                        'tranIdBIDV' => $transaction->bank_refer_code,
                    ];
                }
            }
        } else {
            $result_code = '001';
            $result_desc = 'INVALID PARAMETER';
        }

        if (isset($inputs['fake']) && $inputs['fake']) {
            return [
                'result_code' => '000',
                'result_desc' => 'SUCCESS',
                'result_data' => [
                    [
                        'orderCode' => 'NL57107580',
                        'customerId' => '9629310001',
                        'amount' => '55555',
                        'paymentTime' => '155534',
                        'paymentDate' => '20230725',
                        'tranIdBIDV' => '2613851',
                    ],
                    [
                        'orderCode' => 'NL19542734',
                        'customerId' => '9629310001',
                        'amount' => '44444',
                        'paymentTime' => '155509',
                        'paymentDate' => '20230725',
                        'tranIdBIDV' => '2613850',
                    ]
                ]
            ];
        }

        return [
            'result_code' => $result_code,
            'result_desc' => $result_desc,
            'result_data' => $result_data
        ];
    }

    public function actionVccbVaNotify()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $log_path = self::PATH_LOG_PREFIX . DS . Yii::$app->controller->action->id . DS;
        $this->_writeLog($log_path, '[INPUT]' . json_encode($inputs));

        $res_status = false;
        $res_mess = 'UNKNOWN';

        if (isset($inputs['data']) && $inputs['data'] != "") {
            $data_input = json_decode($inputs['data'], true);

            $transaction_id = $data_input['cashin_id'];
            $bank_transaction_id = $data_input['bank_transaction_id'];

            $transaction_info = Transaction::find()
                ->where(['id' => $transaction_id])
                ->andWhere(['status' => Transaction::STATUS_PAYING])
                ->one();

            if ($transaction_info && $transaction_info['partner_payment_id'] == VCCB_VA_PARTNER_ID) {
                $amount = $data_input['transaction_amount'];
                if ($amount == Transaction::getPartnerPaymentAmount($transaction_info)) {
                    $params = array(
                        'transaction_id' => $transaction_id,
                        'time_paid' => time(),
                        'bank_refer_code' => $bank_transaction_id,
                        'user_id' => 0
                    );
                    $result = TransactionBusiness::paid($params);
                    if ($result['error_message'] == '') {
                        $checkout_order_id = $transaction_info['checkout_order_id'];
                        $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $checkout_order_id]);
                        if (!empty($checkout_order_callback)) {
                            CheckoutOrderCallback::process($checkout_order_callback);
                            $res_status = true;
                            $res_mess = "SUCCESS";
                        } else {
                            $res_mess = "ADD CALLBACK ERROR";
                            NotifySystem::send();
                        }
                    } else {
                        $res_mess = "PAID ERROR";
                    }
                } else {
                    $res_mess = "INVALID AMOUNT";
                }
            } else {
                $res_mess = 'TRANSACTION NOT EXISTS';
            }
        } else {
            $res_mess = 'INVALID FORMAT OR EMPTY';
        }

        $result_gw = [
            'status' => $res_status,
            'message' => $res_mess,
        ];

        $this->_writeLog($log_path, "[OUTPUT]" . json_encode($result_gw));

        return $result_gw;
    }

    protected function validateBidvVa($inputs, &$transaction_id, $api)
    {
        switch ($api) {
            case 'GET_BILL':
            {
                $keys_to_check = ['customer_id', 'service_id', 'checksum'];
                break;
            }
            case 'PAY_BILL':
            {
                $keys_to_check = [
                    'trans_id',
                    'trans_date',
                    'customer_id',
                    'service_id',
                    'bill_id',
                    'amount',
                ];
                break;
            }
            case 'COMPARE':
            {
                $keys_to_check = [
                    'start_date',
                    'end_date',
                ];
                break;
            }
        }

        if (Helpers::checkKeysExist($keys_to_check, $inputs) && !empty($inputs['customer_id']) && !empty($inputs['service_id']) && !empty($inputs['checksum'])) {
            $transaction_id = str_replace('VCBPG', '', $inputs['customer_id']);
            return true;
        } else if ($api == 'COMPARE' && Helpers::checkKeysExist($keys_to_check, $inputs)) {
            return true;
        }
        return false;
    }

    /** vcb-va-get-bill */
    public function actionVcbVaGetBill()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'partner_payment' . DS . 'vcb_va' . DS;
        $this->_writeLog($log_path, '[VCB_VA_GET_BILL][START]');
//        $inputs = Yii::$app->request->post('data');
//        $data_decode = json_decode($inputs, true);
        $data_decode = $inputs;
        $this->_writeLog($log_path, '[INPUT_API]: ' . json_encode($inputs));
        Logs::writeELKLogPartnerPayment($inputs, 'INPUT', 'VCB_VA_GET_BILL', '', 'vcb_va');


        $context = $data_decode['context'];
        $payload = $data_decode['payload'];
        $channelId = $context['channelId'];
        $channelRefNumber = $context['channelRefNumber'];
        $requestDateTime = $context['requestDateTime'];
        $responseMsgId = self::getGUID();
        $customerCode = $payload['customerCode'];
        $paymentSequence = "1";
        $billId = $customerCode;
        $bills_amount = '0';
        $providerid = $payload['providerId'];
        $signature = $channelId . "|" . $channelRefNumber . "|" . $responseMsgId;
        if ($customerCode == "VCBNL20000003493426") { // giả lập case GW truy vấn tk timeout
            sleep(100);
        }


        if ($this->validateVcbVa($data_decode, $transaction_id, "GET_BILL")) {
//        if (false) {
            $this->_writeLog($log_path, 'TRANSACTIUONID: ' . $transaction_id);
            $transaction_info = Transaction::findOne($transaction_id);
            if ($transaction_info) {
                if (str_contains($customerCode, 'VCBNL2')) {
                    $bills_amount = '' . Transaction::getPartnerPaymentAmount($transaction_info ?? 0);
                }
                if ($transaction_info->status == Transaction::STATUS_PAYING) {
                    $checkout_order_info = CheckoutOrder::findOne($transaction_info->checkout_order_id);
                    $result_code = '0';
                    $result_desc = self::getErrorMessageVCBVA($result_code);
                } else if ($transaction_info->status == Transaction::STATUS_PAID) {
                    $result_code = '1';
                    $result_desc = self::getErrorMessageVCBVA($result_code);
                } else {
                    $this->_writeLog($log_path, 'ERROR TRANSACTION STATUS');
                    $result_code = '99';
                    $result_desc = self::getErrorMessageVCBVA($result_code);
//                    $result_desc = '';
                }
            } else {
                $this->_writeLog($log_path, 'ERROR TRANSACTION NOT FOUND');
                $result_code = '99';
                $result_desc = self::getErrorMessageVCBVA($result_code);
            }
        } else {
            $this->_writeLog($log_path, 'ERROR VALIDATE TRANSACTION');
            $result_code = '99';
            $result_desc = self::getErrorMessageVCBVA($result_code);
//            $result_desc = '';
        }

        $merchant_name = 'NGANLUONG';
//        var_dump($transaction_id);die();
        if(!empty($transaction_id)){
            $transaction_info = Transaction::findOne($transaction_id);
            if($transaction_info != null){
                if($transaction_info->merchant_id == 2374){
                    $merchant_name = 'DH NGAN HANG HCM';
                }elseif (in_array($transaction_info->merchant_id, [193, 194, 1434])) {
                    $merchant_name = 'EMART';
                }elseif (in_array($transaction_info->merchant_id, [4094])) {
                    $merchant_name = 'DH TAI CHINH MKT';
                } elseif (in_array($transaction_info->merchant_id, [119])) {
                    $merchant_name = 'BV QUANG NINH';
                }elseif (in_array($transaction_info->merchant_id, [1960, 3157])) {
                    $merchant_name = 'BV PHUONG NAM';
                } elseif (in_array($transaction_info->merchant_id, [1129])) {
                    $merchant_name = 'BV QUANG NINH TAM UNG';
                } elseif (in_array($transaction_info->merchant_id, [1130])) {
                    $merchant_name = 'BV QUANG NINH NHA THUOC';
                } elseif (in_array($transaction_info->merchant_id, [1387])) {
                    $merchant_name = 'FUBON';
                } elseif (in_array($transaction_info->merchant_id, [2273])) {
                    $merchant_name = 'GREENTECH';
                } elseif (in_array($transaction_info->merchant_id, [176, 154, 178, 179, 180])) {
                    $merchant_name = 'BV XANH PON';
                } elseif (in_array($transaction_info->merchant_id, [185])) {
                    $merchant_name = 'BV VINH PHUC';
                } elseif (in_array($transaction_info->merchant_id, [204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233,949,1263,1431,1432,2345,2346,2353,3315,3316,3317,3461])) {
                    $merchant_name = 'BV BUU DIEN';
                } else {
                    $merchant_name = 'NGANLUONG';
                }
            }
        }
        $addnlFields = [
            [
                'fieldId' => 'CustName',
                'fieldValue' => $merchant_name,
            ],
            [
                'fieldId' => 'CustAddress',
                'fieldValue' => $checkout_order_info->buyer_address ?? '',
            ],
        ];

        if ($result_code == '0') {
            $response_gw = [
                'context' => [
                    'channelId' => $channelId,
                    'channelRefNumber' => $channelRefNumber,
                    'requestDateTime' => $requestDateTime,
                    'responseMsgId' => $responseMsgId,
                    'status' => 'SUCCESS',
                    'errorCode' => "0",
                    'errorMessage' => "SUCCESSFUL",
                ],
                'payload' => [
                    'customerCode' => $customerCode,
                    'paymentSequence' => $paymentSequence,
                    'bills' => [
                        'billId' => $billId,
                        'amount' => $bills_amount,
                        'addnlFields' => $addnlFields,
                    ],
                ],
                'signature' => $signature
            ];

        } else {
            $response_gw = [
                "context" => [
                    "channelId" => $channelId,
                    "channelRefNumber" => $channelRefNumber,
                    "requestDateTime" => $requestDateTime,
                    "responseMsgId" => $responseMsgId,
                    "status" => "FAILURE",
                    "errorCode" => $result_code,
                    "errorMessage" => $result_desc
                ],
                "payload" => [
                    "customerCode" => $customerCode
                ],
                "signature" => $signature
            ];
        }
        $this->_writeLog($log_path, '[RESPONSE_API]: ' . json_encode($response_gw));
        Logs::writeELKLogPartnerPayment($response_gw, 'OUTPUT', 'VCB_VA_GET_BILL', '', 'vcb_va');

        return $response_gw;
    }

    /** vcb-va-pay-bill */
    public function actionVcbVaPayBill()
    {
//        var_dump(123);die();
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'partner_payment' . DS . 'vcb_va' . DS;
        $this->_writeLog($log_path, '[VCB_VA_PAY_BILL][START]');
        $this->_writeLog($log_path, '[INPUT_API]: ' . json_encode($inputs));
        Logs::writeELKLogPartnerPayment($inputs, 'INPUT', 'VCB_VA_PAY_BILL', '', 'vcb_va');

        #sample
        /**
         * {
         * "context": {
         * "channelId": "VAH",
         * "channelRefNumber": "202309125874514",
         * "requestDateTime": "12-09-2023 09:01:00"
         * },
         * "payload": {
         * "providerId": "VCBNL2",
         * "serviceId": "BC1",
         * "customerCode": "VCBNL20000003493150",
         * "totalPaymentAmount": "10000",
         * "paymentMode": "C",
         * "internalTransactionRefNo": "5000-00001-20230912-090204-06800",
         * "bills": [
         * {
         * "billId": "VCBNL20000003493150",
         * "amount": "10000",
         * "billDate": "",
         * "billDueDate": "",
         * "addnlFields": [
         * {
         * "fieldId": "CustName",
         * "fieldValue": "CHU VAN KIEN"
         * }
         * ]
         * }
         * ]
         * },
         * "signature": "fc8fa9f8af2664be7ec11bc324aeaf74"
         * }
         */

        #region decode
        $context = $inputs['context'];
        $payload = $inputs['payload'];
        $signature = $inputs['signature'];
        $responseMsgId = self::getGUID();
        if (is_array($context)) {
            $channelId = $context['channelId'] ?? '';
            $channelRefNumber = $context['channelRefNumber'] ?? '';
            $requestDateTime = $context['requestDateTime'] ?? '';
        }

        if (is_array($payload)) {
            $providerId = $payload['providerId'] ?? '';
            $serviceId = $payload['serviceId'] ?? '';
            $bills = $payload['bills'] ?? '';
            $totalPaymentAmount = $payload['totalPaymentAmount'] ?? '';
            $customerCode = $payload['customerCode'] ?? '';
            if (is_array($bills)) {
                if (isset($bills[0])) {
                    $billId = $bills[0]['billId'] ?? '';
                    $amount = $bills[0]['amount'] ?? '';
                    $addnlFields = $bills[0]['addnlFields'] ?? '';
                    if ($addnlFields[0]) {
                        if (isset($addnlFields[0]['fieldId']) && $addnlFields[0]['fieldId'] == 'CustName') {
                            $fieldValue_CustName = $addnlFields[0]['fieldValue'];
                        }
                    }
//                    var_dump($fieldValue);die();
                }

            }
        }
        #endregion
        $inputs_validate = [
            'amount_check' => $amount,
            'fieldValue_CustName' => $fieldValue_CustName,
            'billId' => $billId,
            'customerCode' => $customerCode,
            'totalPaymentAmount' => $totalPaymentAmount,
        ];

        //tắt cờ lỗi không tường minh
        $result_code = '1000';
        $result_desc = '';
        // END tắt cờ lỗi không tường minh

        //bật cờ lỗi không tường minh
        if ($customerCode == "VCBNL20000003493481" || FLAG_EXPLICIT_ERROR) { // giả lập case GW truy vấn tk timeout
            $result_code = '400'; // thay bàng ma trong bootstrap
            $result_desc = 'EXPLICIT_ERROR';
        }
        // END bật cờ lỗi không tường minh


        if (!FLAG_EXPLICIT_ERROR) {
            $result_validation = $this->validateVcbVa($inputs_validate, $transaction_id, "PAY_BILL");
            if ($result_validation['status'] && $result_validation['error_message'] == '') {
                $transaction_info = Transaction::findOne($transaction_id);
                if ($transaction_info && $transaction_info->partner_payment_id == 32) { // SANDBOX: VCBVA 29. LIVE CHƯA CONFIG
//                if ($transaction_info && $transaction_info->partner_payment_id == 29) { // SANDBOX: VCBVA 29. LIVE CHƯA CONFIG
                    if ($transaction_info->status == Transaction::STATUS_PAYING) {
                        if ($amount == Transaction::getPartnerPaymentAmount($transaction_info)) {
                            $params = array(
                                'transaction_id' => $transaction_id,
                                'time_paid' => time(),
                                'bank_refer_code' => $billId,
                                'user_id' => 0
                            );
                            $result = TransactionBusiness::paid($params);
                            if ($result['error_message'] == '') {
                                $checkout_order_id = $transaction_info->checkout_order_id;
                                $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $checkout_order_id]);
                                $merchant_id = $transaction_info->merchant_id;
                                if (in_array($merchant_id, [193, 194, 120, 1434])) {
                                    $result_code = '0';
                                    $result_desc = self::getErrorMessageVCBVA($result_code);
                                }elseif (in_array($merchant_id, $GLOBALS['MERCHANT_BUUDIEN'])) {
                                    $result_code = '0';
                                    $result_desc = self::getErrorMessageVCBVA($result_code);
                                }elseif (in_array($merchant_id, $GLOBALS['MERCHANT_XANHPON'])) {
                                    $result_code = '0';
                                    $result_desc = self::getErrorMessageVCBVA($result_code);
                                } else {
                                    if (!empty($checkout_order_callback)) {
                                        $result = CheckoutOrderCallback::processVCBVA($checkout_order_callback);
                                        if ($result['error_message'] == 'TIMEOUT') {
                                            $result_code = '999';
                                            $result_desc = self::getErrorMessageVCBVA($result_code);
                                        } else {
                                            $result_code = '0';
                                            $result_desc = self::getErrorMessageVCBVA($result_code);
                                        }
                                    }
                                }


                            } else {
                                $result_code = '031';
                                $result_desc = self::getErrorMessageVCBVA($result_code);
                            }
                        } else {
                            $result_code = '3';
                            $result_desc = self::getErrorMessageVCBVA($result_code);
                        }
                    } else if ($transaction_info->status == Transaction::STATUS_PAID) {
                        $result_code = '1';
                        $result_desc = self::getErrorMessageVCBVA($result_code);
                    } else {
                        $result_code = '031';
                        $result_desc = self::getErrorMessageVCBVA($result_code);
                    }
                } else {
                    $result_code = '24';
                    $result_desc = self::getErrorMessageVCBVA($result_code);
                }

            } else {
                $result_code = $result_validation['error_code'];
                $result_desc = $result_validation['error_message'];
            }
        }


        $signature = $channelId . "|" . $channelRefNumber . "|" . $responseMsgId;

        #sample response
        if ($result_code == '0') {
            $context = [
                "channelId" => $channelId,
                "channelRefNumber" => $channelRefNumber,
                "requestDateTime" => $requestDateTime,
                "responseMsgId" => $responseMsgId,
                "status" => "SUCCESS",
                "errorCode" => "0",
                "errorMessage" => "SUCCESSFUL"
            ];
            $payload = [
                "providerId" => $providerId,
                "serviceId" => $serviceId,
                "bills" => [
                    "billId" => $billId,
                    "amount" => $amount,
                    "billErrorCode" => "0",
                    "billErrorDesc" => "SUCCESS"
                ]
            ];
        } else {
            $context = [
                "channelId" => $channelId,
                "channelRefNumber" => $channelRefNumber,
                "requestDateTime" => $requestDateTime,
                "responseMsgId" => $responseMsgId,
                "status" => "FAILURE",
                "errorCode" => $result_code,
                "errorMessage" => $result_desc
            ];
            $payload = [
                'providerId' => $providerId,
                'serviceId' => $serviceId
            ];
        }
        $response_gw = [
            'context' => $context,
            'payload' => $payload,
            'signature' => $signature
        ];
        $this->_writeLog($log_path, '[RESPONSE_API]: ' . json_encode($response_gw));
        Logs::writeELKLogPartnerPayment($response_gw, 'OUTPUT', 'VCB_VA_PAY_BILL', '', 'vcb_va');


        return $response_gw;

    }

    protected function validateVcbVa($inputs, &$transaction_id, $api)
    {
        switch ($api) {
            case 'GET_BILL':
            {
                $keys_to_check = ['customer_id', 'service_id', 'checksum'];
                break;
            }
            case 'PAY_BILL':
            {
                $keys_to_check = [
//                    'trans_id',
//                    'trans_date',
//                    'customer_id',
//                    'service_id',
                    'customerCode',
                    'fieldValue_CustName',
                    'billId',
                    'amount_check',
                ];
                break;
            }
            case 'COMPARE':
            {
                $keys_to_check = [
                    'start_date',
                    'end_date',
                ];
                break;
            }
        }
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'partner_payment' . DS . 'vcb_va' . DS;
        $str_convert = '';
        if ($api == 'GET_BILL') {
            if (isset($inputs['payload']) && isset($inputs['payload']['customerCode'])) {
                $customerCode = $inputs['payload']['customerCode'];
                if (str_contains($customerCode, 'VCBNL2')) {
                    $str_convert = str_replace('VCBNL2', '', $customerCode);
                } elseif (str_contains($customerCode, 'VCBNL1')) {
                    $str_convert = str_replace('VCBNL1', '', $customerCode);
                }
                if (is_numeric(intval(ltrim($str_convert, '0')))) {
                    $transaction_id = ltrim($str_convert, '0');
                    $transaction_info = Transaction::findOne($transaction_id);
                    if ($transaction_info != null) {
                        if (isset($inputs['payload']) && isset($inputs['payload']['providerId']) && $inputs['payload']['providerId'] == VCBVA::PREFIX_2) { // PREFIX2 thi moi check amount
//                            if(intval(Transaction::getPartnerPaymentAmount($transaction_info)) == intval($inputs['amount_check'])  && is_numeric(intval($inputs['amount_check']))){
                            return ['status' => true, 'error_code' => '0', 'error_message' => ''];
//                            }else{
//                                $error_code = '3'; //Số tiền gạch nợ không hợp lệ
//                                return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
//                            }
                        }
                        return ['status' => true, 'error_code' => '0', 'error_message' => ''];
                    }
                } else {
                    $error_code = '17'; // Mã khách hàng/hóa đơn không hợp lệ
                    return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
                }
            }
        } elseif ($api == 'PAY_BILL' && Helpers::checkKeysExist($keys_to_check, $inputs)) {
            if ($inputs['billId'] != $inputs['customerCode']) {
                self::_writeLog($log_path, '[ERROR]: BillId khác customerCode');
                $error_code = '17'; // Mã khách hàng/hóa đơn không hợp lệ
                return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
            }
            if ($inputs['totalPaymentAmount'] != $inputs['amount_check']) {
                self::_writeLog($log_path, '[ERROR]: totalPaymentAmount khác bills.amount');
                $error_code = '3'; // Mã khách hàng/hóa đơn không hợp lệ
                return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
            }
            if (str_contains($inputs['billId'], 'VCBNL2')) {
                $str_convert = str_replace('VCBNL2', '', $inputs['billId']);
            } elseif (str_contains($inputs['billId'], 'VCBNL1')) {
                $str_convert = str_replace('VCBNL1', '', $inputs['billId']);
            }

            $this->_writeLog($log_path, '[STRING_CONVERT]: ' . $str_convert);
            if (is_numeric(intval(ltrim($str_convert, '0')))) {

                $transaction_id = ltrim($str_convert, '0');
                $this->_writeLog($log_path, '[TRANSACTION_ID]: ' . $transaction_id);

                $transaction_info = Transaction::findOne($transaction_id);
                $checkout_order_info = CheckoutOrder::findOne($transaction_info->checkout_order_id);
                if ($transaction_info != null) {
//                    var_dump(intval(Transaction::getPartnerPaymentAmount($transaction_info)));
//                    var_dump(intval($inputs['amount_check']));die();
                    if (isset($inputs['payload']) && isset($inputs['payload']['providerId']) && $inputs['payload']['providerId'] == VCBVA::PREFIX_2) { // PREFIX2 thi moi check amount
                        if (intval(Transaction::getPartnerPaymentAmount($transaction_info)) == intval($inputs['amount_check']) && is_numeric(intval($inputs['amount_check']))) {
                            return ['status' => true, 'error_code' => '0', 'error_message' => ''];
                        } else {
                            $error_code = '3'; //Số tiền gạch nợ không hợp lệ
                            return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
                        }
                    }
                    $this->_writeLog($log_path, '[BUYER_FULLNAME_OF_CHECKOUT_ORDER]: ' . @$checkout_order_info->buyer_fullname);
                    $this->_writeLog($log_path, '[fieldValue_CustName]: ' . @$inputs['fieldValue_CustName']);

                    if ($checkout_order_info->buyer_fullname == $inputs['fieldValue_CustName'] || true) { // PUSH THÌ BỎ TRUE
                        return ['status' => true, 'error_code' => '0', 'error_message' => ''];
                    } else {
                        $error_code = '17'; // ServiceId/ProviderId không đúng/không tồn tại
                        $this->_writeLog($log_path, '[ERROR][BUYER_FULLNAME_NOT_EQUAL_FULLNAME_OF_INPUT] ');
                        return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
                    }

                } else {
                    $error_code = '17'; // Mã khách hàng/hóa đơn không hợp lệ
                    return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
                }
            } else {
                $error_code = '24';  //Mã khách hàng/hóa đơn không hợp lệ
                return ['status' => false, 'error_code' => $error_code, 'error_message' => self::getErrorMessageVCBVA($error_code)];
            }
        } else if ($api == 'COMPARE' && Helpers::checkKeysExist($keys_to_check, $inputs)) {
            return ['status' => true, 'error_message' => ''];
        }


        return ['status' => false, 'error_code' => '24', 'error_message' => 'UNDEFINED'];
    }

    protected function getErrorMessageVCBVA($error_code)
    {
        $messages = [
            '0' => 'SUCCESSFUL',
            '1' => 'PAID',
            '3' => 'INVALID_AMOUNT',
            '10' => 'MISSING_FIELD',
            '17' => 'INVALID_USER',
            '18' => 'INVALID_SIGNATURE',
            '24' => 'INVALID_SERVICE_PROVIDER',
            '031' => 'UNDEFINED',
            '999' => 'TIMEOUT',
        ];
        return array_key_exists($error_code, $messages) ? $messages[$error_code] : $messages['0001'];
    }

    public function actionZaloPayNotify()
    {
        $inputs = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $log_path = self::PATH_LOG_PREFIX . DS . Yii::$app->controller->action->id . DS;
        $this->_writeLog($log_path, '[INPUT]' . json_encode($inputs));
        Logs::writeELKLogPartnerPayment($inputs, 'INPUT', 'ZALOPAY_NOTIFY', '', 'zalo_pay');


        if (isset($inputs['app_trans_id']) && $inputs['app_trans_id'] != "") {
            $pattern = '/_VCBPG(.+)/';

            if (preg_match($pattern, $inputs['app_trans_id'], $matches)) {
                // $matches[1] sẽ chứa ký tự sau dấu "_"
                $transaction_id = $matches[1];

                $transaction_info = Transaction::findOne(['id' => $transaction_id]);


                if ($transaction_info && $transaction_info->partner_payment_id == PartnerPaymentZaloPay::PARTNER_PAYMENT_ID) {
                    if ($transaction_info->status == Transaction::STATUS_PAYING) {
                        $amount = $inputs['amount'];
                        if ($amount == Transaction::getPartnerPaymentAmount($transaction_info)) {

                            $input_check_status = [
                                'merchant_id' => $transaction_info->merchant_id,
                                'partner_payment_id' => $transaction_info->partner_payment_id,
                                'app_trans_id' => date('ymd', $transaction_info->time_created) . "_" . ZALOPAY::PREFIX . Helpers::addZeroPrefix($transaction_id, 8),
                            ];

                            $check_status = ZALOPAY::getOrderStatus($input_check_status);

                            if ($check_status['status'] && $check_status['error_code'] == 1) {
                                if ($check_status['data']->return_code == 1) {
                                    $params = array(
                                        'transaction_id' => $transaction_id,
                                        'time_paid' => time(),
                                        'bank_refer_code' => $check_status['data']->zp_trans_id,
                                        'user_id' => 0
                                    );
                                    $result = TransactionBusiness::paid($params);

                                    if ($result['error_message'] == '') {
                                        $checkout_order_id = $transaction_info->checkout_order_id;
                                        $checkout_order_callback = CheckoutOrderCallback::findOne(['checkout_order_id' => $checkout_order_id]);
                                        if (!empty($checkout_order_callback)) {
                                            CheckoutOrderCallback::process($checkout_order_callback);
                                        }
                                        $result_code = '000';
                                        $result_desc = 'SUCCESS';
                                    } else {
                                        $result_code = '031';
                                        $result_desc = 'SYSTEM ERROR - PAID';
                                    }
                                } else {
                                    $result_code = '031';
                                    $result_desc = 'ORDER NOT UPDATE';
                                }
                            } else {
                                $result_code = '031';
                                $result_desc = 'SYSTEM ERROR';
                            }
                        } else {
                            $result_code = '022';
                            $result_desc = 'INVALID AMOUNT';
                        }
                    } else if ($transaction_info->status == Transaction::STATUS_PAID) {
                        $result_code = '023';
                        $result_desc = 'INVALID';
                    } else {
                        $result_code = '031';
                        $result_desc = 'SYSTEM ERROR';
                    }
                } else {
                    $result_code = '011';
                    $result_desc = 'CUSTOMER NOT EXISTS';
                }
            } else {
                $result_code = '001';
                $result_desc = 'INVALID PARAMETER';
            }
        } else {
            $result_code = '001';
            $result_desc = 'INVALID PARAMETER - app_trans_id';
        }
        $response =  [
            'result_code' => $result_code,
            'result_desc' => $result_desc
        ];
        Logs::writeELKLogPartnerPayment($response, 'OUTPUT', 'ZALOPAY_NOTIFY', '', 'zalo_pay');
        return $response;
    }

    private function _writeLog($file_name, $data)
    {
        if (is_dir($file_name) || mkdir($file_name, 0777, true)) {
            $log_file = date('Ymd') . '.txt';
            $file = fopen($file_name . $log_file, 'a+');
            if ($file) {
                fwrite($file, '[' . date('H:i:s, d/m/Y') . '][' . $this->rq_id . ']' . $data . "\n");
                fclose($file);
                return true;
            }
        }
        return false;
    }

    /** clone từ Version_1_0Controller */
    protected static function getGUID()
    {
        mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }
}