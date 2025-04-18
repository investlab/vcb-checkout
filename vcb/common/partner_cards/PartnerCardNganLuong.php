<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_cards;

use Yii;
use common\components\libs\Tables;
use common\models\db\PartnerCard;
use common\models\db\CardLog;
use common\models\db\PartnerCardLog;

class PartnerCardNganLuong extends PartnerCardBasic
{

    const PARTNER_ID = 'nganluong';
    const USERNAME = 'nganluong';
    const PASSWORD = 'nganluong123';
    const COMPANY_CODE = '[NGANLUONG]-[HOABINH]';
    const SERVICE_CODE = '001';

    public $api_url = 'http://10.0.0.70:8080/MobiCardNewProxy/';
    public $merchant_info = null;
    public $config = null;

    public function getBillType()
    {
        return PartnerCard::BILL_TYPE_NOT_VAT;
    }

    protected function _getStatusCardSuccess()
    {
        return array('1');
    }

    protected function _getStatusCardFail()
    {
        return array(
            '-1', //'Thẻ đã sử dụng',
            '-2', //'Thẻ đã khoá',
            '-3', //'Thẻ đã hết hạn sử dụng',
            '-4', //'Thẻ chưa được kích hoạt',
            '-10', //'Mã thẻ không đúng định dạng',
            '-12', //'Thẻ không tồn tại',
            '-22', //'Thẻ không tồn tại',
        );
    }

    protected function _convertErrorCode($error_code)
    {
        $convert_errors = array(
            '-1' => '07', //'Thẻ đã sử dụng',
            '-2' => '08', //'Thẻ đã khoá',
            '-3' => '09', //'Thẻ đã hết hạn sử dụng',
            '-4' => '10', //'Thẻ chưa được kích hoạt',
            '-10' => '11', //'Mã thẻ không đúng định dạng',
            '-12' => '14', //'Thẻ không tồn tại',
            '-13' => '18', //'Gạch thẻ thành công, có lỗi khi nạp tiền trên INGW.',
            '-14' => '19', //'Thuê bao không tồn tại.',
            '-15' => '19', //'Trạng thái thuê bao không hợp lệ',
            '-16' => '19', //'Thuê bao bị khóa',
            '-17' => '19', //'Mã công ty/mã dịch vụ không được truyền',
            '-18' => '19', //'Mã công ty/mã dịch vụ không đúng.',
            '-19' => '19', //'Giao dịch không tồn tại',
            '0' => '18', //'Lỗi khác',
            '-99' => '19', //'Lỗi hệ thống',
            '-22' => '14', //'Thẻ không tồn tại',
        );
        return array_key_exists($error_code, $convert_errors) ? $convert_errors[$error_code] : '99';
    }

    protected function _getNewSessionId($card_log_id)
    {
        $inputs = array(
            'user_name' => self::USERNAME,
            'password' => self::PASSWORD,
            'partner_id' => self::PARTNER_ID
        );
        $result = $this->_process($card_log_id, 'login', $inputs, PartnerCardLog::TYPE_GET_SESSION);
        if ($result['error_code'] == '00') {
            return $result['new_session_id'];
        }
        return false;
    }

    protected function _getSessionTimeLimit($now)
    {
        return $now + 600;
    }

    /**
     *
     * @param type $params : version, merchant_id, pin_card, card_serial, type_card, ref_code, client_fullname, client_email, client_mobile, checksum
     */
    public function cardCharge($card_log_id, $params)
    {
        $error_code = '99';
        $card_price = 0;
        $partner_card_refer_code = null;
        //------------
        $session_id = $this->_getSessionID($card_log_id);
        if ($session_id != false) {
            $merchant_service = $this->_getMerchantServiceCode($params);
            $inputs = array(
                'partner_id' => self::PARTNER_ID,
                'session_id' => $session_id,
                'card_id' => $params['pin_card'],
                'card_code' => '',
                'card_serial' => $params['card_serial'],
                'company_code' => $merchant_service['company_code'],
                'service_code' => $merchant_service['service_code'],
                'transaction_id' => $this->_getTransactionId($card_log_id),
            );
            $result = $this->_process($card_log_id, 'userCard', $inputs, PartnerCardLog::TYPE_CARD_CHARGE, $params['pin_card'], $params['card_serial'], $session_id);
            if ($result['error_code'] == '00') {
                $error_code = '00';
                $card_price = $result['card_price'];
                $partner_card_refer_code = $result['refer_code'];
            } else {
                $error_code = $result['error_code'];
            }
        } else {
            $error_code = '17';
        }
        return array('error_code' => $error_code, 'card_price' => $card_price, 'partner_card_refer_code' => $partner_card_refer_code);
    }

    protected function _getTransactionId($card_log_id)
    {
        return $card_log_id;
    }

    protected function _getMerchantServiceCode($params)
    {
        return array('service_code' => self::SERVICE_CODE, 'company_code' => self::COMPANY_CODE);
    }

    protected function _getPartnerOutput($response)
    {
        $error_code = '99';
        $refer_code = '';
        $card_price = 0;
        //----------
        if ($this->_isSuccess($response)) {
            $error_code = '00';
            $card_price = $this->_getCardPrice($response);
        } else {
            $error_code = $this->_convertErrorCode($response['response_status']);
        }
        return array('error_code' => $error_code, 'card_price' => $card_price, 'refer_code' => $refer_code);
    }

    protected function _getCardPrice($response)
    {
        if (preg_match('/^CARD VALUE:(\d+)$/', @$response['description'], $temp)) {
            return intval($temp[1]);
        }
        return 0;
    }

    protected function _isSuccess($response)
    {
        return ($response['response_status'] === '1');
    }

    protected function _getResult($function, $output, $params)
    {
        $error_code = '99';
        $refer_code = null;
        $card_price = 0;
        $card_status = CardLog::CARD_STATUS_TIMEOUT;
        $new_session_id = null;
        //--------
        $response = json_decode($output, true);
        if ($function == 'userCard') {
            if (isset($response['result']['usercard_response']) && !empty($response['result']['usercard_response'])) {
                if (@$response['result']['usercard_response']['status'] == true) {
                    $partner_output = $this->_getPartnerOutput($response['result']['usercard_response']);
                    if ($partner_output['error_code'] == '00') {
                        $card_price = intval(@$partner_output['card_price']);
                        $refer_code = $params['card_id'] . '-' . $params['card_serial'];
                        $card_status = CardLog::CARD_STATUS_SUCCESS;
                        if ($this->_checkCardPrice($this->config['card_type_id'], $card_price)) {
                            $error_code = '00';
                        } else {
                            $error_code = '18';
                        }
                    } else {
                        $error_code = $partner_output['error_code'];
                        $card_status = $this->_getCardStatusByErrorCode($partner_output['error_code']);
                    }
                } else {
                    $error_code = '18';
                }
            } else {
                $error_code = '18';
            }
        } elseif ($function == 'login') {
            if (isset($response['result']['login_response']) && !empty($response['result']['login_response'])) {
                if (@$response['result']['login_response']['status'] == true) {
                    $error_code = '00';
                    $new_session_id = @$response['result']['login_response']['session_id'];
                } else {
                    $error_code = '18';
                }
            } else {
                $error_code = '18';
            }
        }
        return array(
            'error_code' => $error_code,
            'output' => $output,
            'refer_code' => $refer_code,
            'card_price' => $card_price,
            'card_status' => $card_status,
            'new_session_id' => $new_session_id,
        );
    }


    final public function _getCardStatusByErrorCode($error_code)
    {
        $errors = array(
            '00' => 4, //'Giao dịch thành công',
            '99' => 2, //'Lỗi, tuy nhiên lỗi chưa được định nghĩa hoặc chưa xác định được nguyên nhân',
            '01' => 3, //'Lỗi, địa chỉ IP truy cập API của NgânLượng.vn bị từ chối',
            '02' => 3, //'Lỗi, tham số gửi từ merchant tới NgânLượng.vn chưa chính xác (thường sai tên tham số hoặc thiếu tham số)',
            '03' => 3, //'Lỗi, Mã merchant không tồn tại hoặc merchant đang bị khóa kết nối tới NgânLượng.vn',
            '04' => 3, //'Lỗi, Mã checksum không chính xác (lỗi này thường xảy ra khi mật khẩu giao tiếp giữa merchant và NgânLượng.vn không chính xác, hoặc cách sắp xếp các tham số trong biến params không đúng)',
            '05' => 3, //'Tài khoản nhận tiền nạp của merchant không tồn tại',
            '06' => 3, //'Tài khoản nhận tiền nạp của merchant đang bị khóa hoặc bị phong tỏa, không thể thực hiện được giao dịch nạp tiền',
            '07' => 1, //'Thẻ đã được sử dụng',
            '08' => 1, //'Thẻ bị khóa',
            '09' => 1, //'Thẻ hết hạn sử dụng',
            '10' => 1, //'Thẻ chưa được kích hoạt hoặc không tồn tại',
            '11' => 1, //'Mã thẻ sai định dạng',
            '12' => 1, //'Sai số serial của thẻ',
            '13' => 1, //'Mã thẻ và số serial không khớp',
            '14' => 1, //'Thẻ không tồn tại',
            '15' => 1, //'Thẻ không sử dụng được',
            '16' => 1, //'Số lần thử (nhập sai liên tiếp) của thẻ vượt quá giới hạn cho phép',
            '17' => 3, //'Hệ thống Telco bị lỗi hoặc quá tải, thẻ chưa bị trừ',
            '18' => 2, //'Hệ thống Telco bị lỗi hoặc quá tải, thẻ có thể bị trừ, cần phối hợp với NgânLượng.vn để tra soát',
            '19' => 3, //'Kết nối từ NgânLượng.vn tới hệ thống Telco bị lỗi, thẻ chưa bị trừ (thường do lỗi kết nối giữa NgânLượng.vn với Telco, ví dụ sai tham số kết nối, mà không liên quan đến merchant)',
            '20' => 4, //'Kết nối tới telco thành công, thẻ bị trừ nhưng chưa cộng tiền trên NgânLượng.vn',
            '21' => 1, //'Khách hàng đang nạp thẻ bị khóa (do nhập sai mã thẻ liên tiếp)',
            '22' => 1, //'Khách hàng đang nạp thẻ bị khóa (do nhập sai mã thẻ liên tiếp)',
        );
        return array_key_exists($error_code, $errors) ? $errors[$error_code] : 2;
    }

    protected function _call($card_log_id, $partner_card_log_id, $function, $params)
    {
        try {
            /*$params['fnc'] = $function;
            $query_string = http_build_query($params);
            $this->_writeLog('[' . $card_log_id . '][input]' . $query_string);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            curl_setopt($ch, CURLOPT_USERPWD, "NganluonG@321:sadn9324Fd*(SFS$30dlk%");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $result = curl_exec($ch);
            $this->_writeLog('[' . $card_log_id . '][output]' . $result);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);*/

            $result = $this->_getResultDemo($function, $params, $status);
            //var_dump($result);die();
            if ($result != false && $result != '' && $status == 200) {
                return $this->_getResult($function, $result, $params);
            }
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    private function _getResultDemo($function, $params, &$status = '')
    {
        $result = false;
        if ($function == 'login') {
            $status = 200;
            $result = array(
                'result' => ['login_response' => ['status' => true, 'session_id' => 'demo_' . uniqid()]]
            );
            $result = json_encode($result);
        } elseif ($function == 'userCard') {
            $status = 200;
            /*
            '-1', //'Thẻ đã sử dụng',
            '-2', //'Thẻ đã khoá',
            '-3', //'Thẻ đã hết hạn sử dụng',
            '-4', //'Thẻ chưa được kích hoạt',
            '-12', //'Thẻ không tồn tại',
            */
            if ($params['card_id'] == '100000000000') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '1', 'description' => 'CARD VALUE:10000']]
                );
            } elseif ($params['card_id'] == '200000000000') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '1', 'description' => 'CARD VALUE:20000']]
                );
            } elseif ($params['card_id'] == '500000000000') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '1', 'description' => 'CARD VALUE:50000']]
                );
            } elseif ($params['card_id'] == '111111111111') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '-1', 'description' => '']]
                );
            } elseif ($params['card_id'] == '222222222222') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '-2', 'description' => '']]
                );
            } elseif ($params['card_id'] == '333333333333') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '-3', 'description' => '']]
                );
            } elseif ($params['card_id'] == '444444444444') {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '-4', 'description' => '']]
                );
            } else {
                $result = array(
                    'result' => ['usercard_response' => ['status' => true, 'response_status' => '-12', 'description' => '']]
                );
            }
            $result = json_encode($result);
        }
        return $result;
    }
}
