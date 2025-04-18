<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\models\business\CheckoutOrderBusiness;
use common\components\utils\Logs;


/**
 * This is the model class for table "checkout_order_callback".
 *
 * @property integer $id
 * @property integer $checkout_order_id
 * @property string $notify_url
 * @property integer $time_process
 * @property integer $number_process
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class CheckoutOrderCallback extends \yii\db\ActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_SUCCESS = 4;
    const MAX_NUMBER_PROCESS = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'checkout_order_callback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['checkout_order_id', 'notify_url', 'time_process', 'status'], 'required'],
            [['checkout_order_id', 'time_process', 'number_process', 'status', 'time_created', 'time_updated'], 'integer'],
            [['notify_url'], 'string'],
            [['checkout_order_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'checkout_order_id' => 'Checkout Order ID',
            'notify_url' => 'Notify Url',
            'time_process' => 'Time Process',
            'number_process' => 'Number Process',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Chưa gọi',
            self::STATUS_PROCESSING => 'Đang gọi',
            self::STATUS_ERROR => 'Lỗi',
            self::STATUS_SUCCESS => 'Đã gọi',
        );
    }

    private static function _getTimeProcessTimeout($now)
    {
        return $now - 300;
    }

    private static function _getTimestartCron($now)
    {
        return 1685507400;  // tạm lấy time_start của cron là 31/05/2023 11:30:00
    }

    private static function _addCallbackHistory($checkout_order_callback_info, $time_request)
    {
        $model = new CheckoutOrderCallbackHistory();
        $model->checkout_order_id = $checkout_order_callback_info['checkout_order_id'];
        $model->checkout_order_callback_id = $checkout_order_callback_info['id'];
        $model->notify_url = $checkout_order_callback_info['notify_url'];
        $model->status = CheckoutOrderCallback::STATUS_PROCESSING;
        $model->time_request = $time_request;
        if ($model->validate() && $model->save()) {
            return $model->getDb()->getLastInsertID();
        }
        return false;
    }

    private static function _updateCallbackHistory($checkout_order_callback_history_id, $status, $time_response, $response_data)
    {
        $model = CheckoutOrderCallbackHistory::findOne(["id" => $checkout_order_callback_history_id]);
        if ($model) {
            $model->status = $status;
            $model->time_response = intval($time_response);
            $model->response_data = json_encode($response_data);
            if ($model->validate() && $model->save()) {
                return true;
            }
        }
        return false;
    }

    private static function _updateSuccess($checkout_order_callback_info, $checkout_order_callback_history_id, $response)
    {
        $error_message = 'Lỗi không xác định';
        $transaction = self::getDb()->beginTransaction();
        $commit = false;
        //------------
        $now = time();
        $model = self::findBySql("SELECT * FROM checkout_order_callback WHERE id = " . $checkout_order_callback_info['id'] . " AND status = " . self::STATUS_PROCESSING)->one();
        if ($model) {
            $model->status = self::STATUS_SUCCESS;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'checkout_order_id' => $checkout_order_callback_info['checkout_order_id'],
                    'user_id' => 0,
                );
                $result = CheckoutOrderBusiness::updateCallbackStatusSuccess($inputs, false);
                if ($result['error_message'] == '') {
                    if (self::_updateCallbackHistory($checkout_order_callback_history_id, CheckoutOrderCallbackHistory::STATUS_SUCCESS, $now, $response)) {
                        $error_message = '';
                        $commit = true;
                    }
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Data không hợp lệ';
        }
        if ($commit) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        return array('error_message' => $error_message);
    }

    private static function _updateProcessing($checkout_order_callback_info)
    {
        $error_message = 'Lỗi không xác định';
        $now = time();
        $history_id = null;
        $model = self::findBySql("SELECT * FROM checkout_order_callback WHERE id = " . $checkout_order_callback_info['id'] . "  ")->one();
        if ($model) {
            $sql = "UPDATE checkout_order_callback "
                . "SET status = " . self::STATUS_PROCESSING . ", "
                . "number_process = number_process + 1, "
                . "time_process = " . $now . ", "
                . "time_updated = $now "
                . "WHERE id = " . $checkout_order_callback_info['id'] . " "
                . "AND ((time_process <= $now AND status IN (" . self::STATUS_NEW . "," . self::STATUS_ERROR . ")) OR (status = " . self::STATUS_PROCESSING . " AND time_process <= " . self::_getTimeProcessTimeout($now) . ")) "
                . "AND number_process < " . self::MAX_NUMBER_PROCESS . " ";
            $connection = $model->getDb();
            $command = $connection->createCommand($sql);
            $update = $command->execute();
            if ($update) {
                $history_id = self::_addCallbackHistory($checkout_order_callback_info, $now);
                if ($history_id != false) {
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi thêm lịch sử gọi lại';
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Data không hợp lệ';
        }
        return array('error_message' => $error_message, 'history_id' => $history_id);
    }

    private static function _updateError($checkout_order_callback_info, $checkout_order_callback_history_id, $response)
    {
        $error_message = 'Lỗi không xác định';
        $now = time();
        $model = self::findBySql("SELECT * FROM checkout_order_callback WHERE id = " . $checkout_order_callback_info['id'] . " AND status = " . self::STATUS_PROCESSING)->one();
        if ($model) {
            $model->status = self::STATUS_ERROR;
            $model->time_updated = $now;
            if ($model->validate() && $model->save()) {
                if ($model->number_process >= self::MAX_NUMBER_PROCESS) {
                    $inputs = array(
                        'checkout_order_id' => $checkout_order_callback_info['checkout_order_id'],
                        'user_id' => 0,
                    );
                    $result = CheckoutOrderBusiness::updateCallbackStatusError($inputs, false);
                    if ($result['error_message'] == '') {
                        self::_updateCallbackHistory($checkout_order_callback_history_id, CheckoutOrderCallbackHistory::STATUS_ERROR, $now, $response);
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    self::_updateCallbackHistory($checkout_order_callback_history_id, CheckoutOrderCallbackHistory::STATUS_ERROR, $now, $response);
                    $error_message = '';
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Data không hợp lệ';
        }
        return array('error_message' => $error_message);
    }

    public static function getCurrentProcessInfo()
    {
        $now = time();
        $checkout_order_callback_info = Tables::selectOneDataTable("checkout_order_callback", "((time_process <= $now AND status IN (" . self::STATUS_ERROR . "," . self::STATUS_NEW . ")) OR (status = " . self::STATUS_PROCESSING . " AND time_process < " . self::_getTimeProcessTimeout($now) . ")) AND number_process < " . self::MAX_NUMBER_PROCESS . " AND time_process >  " . self::_getTimestartCron($now), "id ASC, time_process ASC ");
        return $checkout_order_callback_info;
    }

    public static function process($checkout_order_callback_info)
    {
        $error_message = 'Lỗi không xác định';
        $response = null;
        $url = $checkout_order_callback_info['notify_url'];
        $update = self::_updateProcessing($checkout_order_callback_info);
        if ($update['error_message'] == '') {
            $history_id = $update['history_id'];
            $response = self::_call($url, $checkout_order_callback_info);
            if (self::_isResponse($response) == true) {
                if (self::_isResponseSuccess($response) == true) {
                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = @$response['result_message'];
                    $result = self::_updateError($checkout_order_callback_info, $history_id, $response);

                }
            } else {
                $error_message = 'Timeout hoặc kết quả xử lý không hợp lệ';
                $result = self::_updateError($checkout_order_callback_info, $history_id, $response);

            }
        } else {
            $error_message = $update['error_message'];
        }
        return array('error_message' => $error_message, 'response' => $response);
    }

    public static function processCheckQr($checkout_order_callback_info)
    {
        $error_message = 'Lỗi không xác định';
        $response = null;
        $url = $checkout_order_callback_info['notify_url'];
        $update = self::_updateProcessing($checkout_order_callback_info);
        if ($update['error_message'] == '') {
            $history_id = $update['history_id'];
            $response = self::_call($url, $checkout_order_callback_info);
            if (self::_isResponse($response) == true) {
                if (self::_isResponseRevert($response) == true) {
                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                    if ($result['error_message'] == '') {
                        $error_message = 'REVERT';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } elseif (self::_isResponseSuccess($response) == true) {
                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                    if ($result['error_message'] == '') {
                        $error_message = 'SUCCESS';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    if (isset($response['result_message'])) {
                        $error_message = $response['result_message'];
                    }
                    if (isset($response['message'])) {
                        $error_message = $response['message'];
                    }

                    $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
                }
            } else {
                $error_message = 'Timeout hoặc kết quả xử lý không hợp lệ';
                $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
            }
        } else {
            $error_message = $update['error_message'];
        }
        return array('error_message' => $error_message, 'response' => $response);
    }

    public static function processVCBVA($checkout_order_callback_info)
    {
        $error_message = 'Lỗi không xác định';
        $response = null;
        $url = $checkout_order_callback_info['notify_url'];
        $update = self::_updateProcessing($checkout_order_callback_info);
        if ($update['error_message'] == '') {
            $history_id = $update['history_id'];
            $response = self::_call($url, $checkout_order_callback_info);
            if ($response == null) {
                $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                if ($result['error_message'] == '') {
                    $error_message = 'TIMEOUT';
                } else {
                    $error_message = $result['error_message'];
                }
                return array('error_message' => $error_message, 'response' => $response);

            }
            if (self::_isResponse($response) == true) {
                if (self::_isResponseTimeoutVCBVA($response) == true) {
                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
//                    var_dump($result['error_message']);die();

                    if ($result['error_message'] == '') {
                        $error_message = 'TIMEOUT';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } elseif (self::_isResponseSuccess($response) == true) {
//                    var_dump(456);die();

                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                    if ($result['error_message'] == '') {
                        $error_message = 'SUCCESS';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
//                    var_dump(789);die();

                    if (isset($response['result_message'])) {
                        $error_message = $response['result_message'];
                    }
                    if (isset($response['message'])) {
                        $error_message = $response['message'];
                    }

                    $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
                }
            } else {
                $error_message = 'TIMEOUT';
                $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
            }
        } else {
            $error_message = $update['error_message'];
        }
        return array('error_message' => $error_message, 'response' => $response);
    }

    public static function processBCA($checkout_order_callback_info)
    {
        $error_message = 'Lỗi không xác định';
        $response = null;
        $url = $checkout_order_callback_info['notify_url'];
        $update = self::_updateProcessing($checkout_order_callback_info);
        if ($update['error_message'] == '') {
            $history_id = $update['history_id'];
            $response = self::_call($url, $checkout_order_callback_info);
            if (self::_isResponse($response) == true) {
                if (self::_isResponseSuccess($response) == true) {
                    $result = self::_updateSuccess($checkout_order_callback_info, $history_id, $response);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = $response['result_message'] ?? '';
                    $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
                }
            } else {
                $error_message = 'Timeout hoặc kết quả xử lý không hợp lệ';
                $result = self::_updateError($checkout_order_callback_info, $history_id, $response);
            }
        } else {
            $error_message = $update['error_message'];
        }
        self::_writeLog('[CHECKOUT_ORDER_CALLBACK_INFO_FUNCTION_PROCESS]: ' . json_encode($checkout_order_callback_info));
        self::_writeLog('[ERROR_MESSAGE]: ' . $error_message);
        return array('error_message' => $error_message, 'response' => $response);
    }

    private static function _isResponse($response)
    {
        if ($response != false && is_array($response)) {
            if (array_key_exists('result_code', $response) && array_key_exists('result_message', $response)) {
                return true;
            }
            if (array_key_exists('code', $response) && array_key_exists('message', $response)) {
                return true;
            }
            if (array_key_exists('MaLoi', $response) && array_key_exists('MoTaLoi', $response)) {
                return true;
            }
            if (array_key_exists('responseCode', $response) && array_key_exists('msg', $response)) {
                return true;
            }
        }
        return false;
    }

    private static function _isResponseSuccess($response)
    {
        if (self::_isResponse($response)) {
            if (isset($response['result_code'])) {
                return ($response['result_code'] == '0000');
            }
            if (isset($response['code'])) {
                return ($response['code'] == '0000');
            }
            if (isset($response['MaLoi'])) {
                return ($response['MaLoi'] == '00');
            }
            if (isset($response['responseCode'])) {
                return ($response['responseCode'] == 202);
            }

        }
        return false;
    }

    private static function _isResponseRevert($response)
    {
        if (self::_isResponse($response)) {
            if (isset($response['result_code'])) {
                if (in_array($response['result_code'], self::_getAllErrorCodeForRevert()))
                    return true;
            }
            if (isset($response['code'])) {
                if (in_array($response['code'], self::_getAllErrorCodeForRevert()))
                    return true;
            }
        }
        return false;
    }

    private static function _isResponseTimeoutVCBVA($response)
    {
        if (self::_isResponse($response)) {
            if (isset($response['result_code'])) {
                if (in_array($response['result_code'], self::_getAllErrorCodeForTimeoutVCBVA()))
                    return true;
            }
            if (isset($response['code'])) {
                if (in_array($response['code'], self::_getAllErrorCodeForTimeoutVCBVA()))
                    return true;
            }
        }
        return false;
    }

    private static function _getAllErrorCodeForRevert()
    {
        return array(
            '0200', '0201', '0203'
        );
    }

    private static function _getAllErrorCodeForTimeoutVCBVA()
    {
        return array(
            '999'
        );
    }

    private static function _getParams($checkout_order_callback_info, &$is_bca = false, &$is_fubon = false, &$is_buudien = false, &$is_mpos = false)
    {
        $is_bca = false;
        $is_mpos = false;
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id", "id" => $checkout_order_callback_info['checkout_order_id']]);
        if ($checkout_order_info != false) {
            if (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_BCA'])) {
                $is_bca = true;

                $params = CheckoutOrder::getParamsForNotifyUrlBCA($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrlBCA($checkout_order_info['merchant_id'], $params);
                self::_writeLog('[PARAMS_FINAL]: ' . json_encode($params));
                return $params;
            } elseif (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_QNI'])) {
                $params = CheckoutOrder::getParamsForNotifyUrlQni($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
                return $params;
            } elseif (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_VHC'])) {
                $is_mpos = true;
                $params = CheckoutOrder::getParamsForNotifyUrlVhc($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrlVhc($checkout_order_info['merchant_id'], $params);
                return $params;
            } elseif (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_DONGNAI'])) {
                $params = CheckoutOrder::getParamsForNotifyUrlDni($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
                return $params;
            } elseif (in_array(intval($checkout_order_info['merchant_id']), $GLOBALS['MERCHANT_FUBON'])) {

                $is_fubon = true;
                $params = CheckoutOrder::getParamsForNotifyUrlFubon($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);

                return $params;
            } elseif (in_array(intval($checkout_order_info['merchant_id']), $GLOBALS['MERCHANT_BUUDIEN'])) {

                $is_buudien = true;
                $params = CheckoutOrder::getParamsForNotifyUrl($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);

                return $params;
            } elseif (in_array(intval($checkout_order_info['merchant_id']), $GLOBALS['MERCHANT_XANHPON'])) {
                $params = CheckoutOrder::getParamsForNotifyUrlXanhPon($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
                return $params;
            }else {
                //TODO CHeck mpos
                $notify_url = $checkout_order_callback_info['notify_url'];
                $mpos_check_url = 'https://pushpayment.nextpay.vn/webhook';
                if(strpos($notify_url, $mpos_check_url) !== false){
                    $is_mpos = true;
                }
                $params = CheckoutOrder::getParamsForNotifyUrl($checkout_order_info);
                if (isset($params['result_data'])) {
                    return $params;
                }
                $params['checksum'] = self::_getChecksumNotifyUrl($checkout_order_info['merchant_id'], $params);
                return $params;
            }

        }
        return false;
    }

    private static function _getChecksumNotifyUrl($merchant_id, $params)
    {
        $api_key = Merchant::getApiKey($merchant_id, $merchant_info);
        $str_checksum = $params['token_code'];
        $str_checksum .= '|' . $params['version'];
        $str_checksum .= '|' . $params['order_code'];
        $str_checksum .= '|' . $params['order_description'];
        $str_checksum .= '|' . $params['amount'];
        $str_checksum .= '|' . $params['sender_fee'];
        $str_checksum .= '|' . $params['receiver_fee'];
        $str_checksum .= '|' . $params['currency'];
        $str_checksum .= '|' . $params['return_url'];
        $str_checksum .= '|' . $params['cancel_url'];
        $str_checksum .= '|' . $params['notify_url'];
        $str_checksum .= '|' . $params['status'];
        $str_checksum .= '|' . $params['payment_method_code'];
        $str_checksum .= '|' . $params['payment_method_name'];
        $str_checksum .= '|' . $api_key;
        return md5($str_checksum);
    }

    /** Cho MC Viet ha Chi */
    private static function _getChecksumNotifyUrlVhc($merchant_id, $params)
    {
        $api_key = Merchant::getApiKey($merchant_id, $merchant_info);
        $str_checksum = $params['token_code'];
        $str_checksum .= '|' . $params['version'];
        $str_checksum .= '|' . $params['order_code'];
        $str_checksum .= '|' . $params['order_description'];
        $str_checksum .= '|' . $params['amount'];
        $str_checksum .= '|' . $params['sender_fee'];
        $str_checksum .= '|' . $params['receiver_fee'];
        $str_checksum .= '|' . $params['currency'];
        $str_checksum .= '|' . $params['return_url'];
        $str_checksum .= '|' . $params['cancel_url'];
        $str_checksum .= '|' . $params['notify_url'];
        $str_checksum .= '|' . $params['status'];
        $str_checksum .= '|' . $params['payment_method_code'];
        $str_checksum .= '|' . $params['payment_method_name'];
        $str_checksum .= '|' . $api_key;
        return md5($str_checksum);
    }

    private static function _getChecksumNotifyUrlBCA($merchant_id, $params)
    {
        $api_key = Merchant::getApiKey($merchant_id, $merchant_info);
        $str_checksum = $params['token_code'];
        $str_checksum .= '|' . $params['version'];
        $str_checksum .= '|' . $params['order_code'];
        $str_checksum .= '|' . $params['order_description'];
        $str_checksum .= '|' . $params['amount'];
        $str_checksum .= '|' . $params['currency'];
        $str_checksum .= '|' . $params['status'];
        $str_checksum .= '|' . $params['so_the'];
        $str_checksum .= '|' . $params['bank_code'];
        $str_checksum .= '|' . $params['thoi_gian_gd'];
        $str_checksum .= '|' . $api_key;
        return md5($str_checksum);
    }

    private static function _call($url, $checkout_order_callback_info)
    {

        try {
            $params = self::_getParams($checkout_order_callback_info, $is_bca, $is_fubon, $is_buudien, $is_mpos);
//            var_dump($is_mpos);die();
            if ($is_bca == true) {
                $headers = array(
                    'Content-Type: application/json',
                );
                try {
                    self::_writeLog('[INPUT] ' . $url . '?' . json_encode($params));

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    if (substr($url, 0, 5) == 'https') {
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    }
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/10.0');
                    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
                    $result = curl_exec($ch);

                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $result);

                    curl_close($ch);
                    if ($result != '' && $status == 200) {
                        $result = str_replace('&', '&amp;', trim($result));
                        $data = json_decode($result, true);
                        return $data;
                    }
                } catch (\Exception $ex) {
                    self::_writeLog('[RESULT][' . $url . ']:' . $ex);

                    return false;
                }

            } elseif ($is_fubon == true) {

                $get_token_string = http_build_query([
                    'user' => 'fubon',
                    'password' => 'FuB@n01@o23',
                ]);

                $url_get_token = 'https://api-online.fubonins.com.vn/api/authentication/get_access_token';

                $ch = curl_init();
                $headers = array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'User-Agent: ' . Yii::$app->request->headers->get('user-agent')

                );
                curl_setopt($ch, CURLOPT_URL, $url_get_token . '?' . ($get_token_string));
                @self::_writeLog('[INPUT-GETOKEN] ' . "[" . @$params['token_code'] . "]?" . $url_get_token . '?' . ($get_token_string));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $body = '{}';
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                // Timeout in seconds
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $result_auth_token = curl_exec($ch);

                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                self::_writeLog('[RESULT-GETTOKEN] ' . $status . '  ' . $error . '|' . $result_auth_token);

                if ($result_auth_token != '' && $status == 200) {
                    $token_info = json_decode($result_auth_token, true);
                    $token = $token_info['token'];
                    $query_string = http_build_query($params);
                    $url_notify_fubon = 'https://api-online.fubonins.com.vn/api/order/get_payment_status';
                    $ch = curl_init();
                    $headers[] = "Authorization: Bearer " . $token;


                    curl_setopt($ch, CURLOPT_URL, $url_notify_fubon . '?' . ($query_string));
                    self::_writeLog('[INPUT] ' . $url_notify_fubon . '?' . ($query_string));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    $body = '{}';

                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                    // Timeout in seconds
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    $result_notify = curl_exec($ch);
                    $result = json_decode($result_notify, true);
                    self::_writeLog('[RESULT] ' . $status . '  |' . $error . '|' . $result_notify);
                    return $result;


                }


            } elseif ($is_buudien == true) {
                $query_string = http_build_query($params);

                self::_writeLog('[INPUT] ' . $url . '?' . $query_string);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url . '?' . $query_string,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);
                self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $response);


                curl_close($curl);
                $result = json_decode($response, true);

                return $result;

            } elseif ($is_mpos == true) {
                //======== Call Phuong thuc GET neu la url MPOS
                $query_string = http_build_query($params);

                self::_writeLog('[METHOD: GET][INPUT] ' . $url . '?' . $query_string);
//                $params_elk = $params;
//                $params_elk['call_method'] = 'GET';
                $data_elk = [
                    'url_callback' => $url,
                    'data' => $query_string,
                    'call_method' => 'GET'
                ];
                @Logs::writeELKLogCheckoutCallback($data_elk, "INPUT");


                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url . '?' . $query_string,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);

                $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
                // Kiểm tra xem phản hồi có phải là HTML hay không
                if (strpos($content_type, 'text/html') !== false) {
                    $response_is_html = true;
                } else {
                    $response_is_html = false;
                }
                self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $response);


                curl_close($curl);
                $result = json_decode($response, true);
                $data_elk = [
                    'status_code' => $status,
                    'error_message' => $error,
                    'data' => $result,
                    'is_html' => $response_is_html
                ];
                @Logs::writeELKLogCheckoutCallback($data_elk, "OUTPUT");

                return $result;

            } else {
                if ($params != false) {
                    $query_string = http_build_query($params);

                    self::_writeLog('[INPUT] ' . $url . '?' . $query_string);
                    $data_elk = [
                        'url_callback' => $url,
                        'data' => $query_string
                    ];
                    @Logs::writeELKLogCheckoutCallback($data_elk, "INPUT");
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    if (substr($url, 0, 5) == 'https') {
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    }
                    curl_setopt($ch, CURLOPT_ENCODING, "");
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                    $result = curl_exec($ch);

                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    curl_close($ch);
                    // Kiểm tra xem phản hồi có phải là HTML hay không
                    if (strpos($content_type, 'text/html') !== false) {
                        $response_is_html = true;
                    } else {
                        $response_is_html = false;
                    }
                    self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $result, $response_is_html);
                    $data_elk = [
                        'status_code' => $status,
                        'error_message' => $error,
                        'data' => $result,
                        'is_html' => $response_is_html
                    ];
                    @Logs::writeELKLogCheckoutCallback($data_elk, "OUTPUT");
                    if ($result != '' && $status == 200) {
                        $result = json_decode($result, true);
                        return $result;
                    } else {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $url . '?' . $query_string,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => 2,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);
                        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        $error = curl_error($curl);
                        self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $response);
                        $data_elk = [
                            'status_code' => $status,
                            'error_message' => $error,
                            'data' => $response,
                        ];
                        @Logs::writeELKLogCheckoutCallback($data_elk, "OUTPUT");
                        curl_close($curl);
                        $result = json_decode($response, true);

                        return $result;
                    }
                }
            }


        } catch (\Exception $ex) {
            return false;
        }
        return false;
    }


    private static function _writeLog($data, $is_html = false)
    {
        if ($is_html) {
            $data = substr($data, 0, 300);
        }
        $file_name = 'checkout_order_callback' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
