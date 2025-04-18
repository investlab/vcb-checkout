<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\api;

use Yii;
use common\components\libs\Tables;
use common\models\db\Merchant;
use common\models\db\PartnerCard;
use common\models\db\MerchantCardFee;
use common\models\db\CardType;
use common\models\db\PartnerCardSession;

class CardVersion1_0Api extends CardBasicApi
{

    public function getVersion()
    {
        return '1.0';
    }

    /**
     *
     * @param type $params : version, merchant_id, pin_card, card_serial, type_card, ref_code, client_fullname, client_email, client_mobile, checksum
     */
    public function cardCharge($params)
    {
        $error_code = '99';
        $response_data = null;
        //------------
        $now = time();
        $validate = $this->_validateCardCharge($params, $merchant_info);
        if ($validate['error_code'] == '00') {
            $check_config = $this->_getMerchantConfig($merchant_info, $params['type_card'], $now);
            if ($check_config['error_code'] == '00') {
                $config = $check_config['config'];
                $class_name = 'common\partner_cards\\' . $this->_getClassName($config['partner_card_code']);
                if (class_exists($class_name)) {
                    $obj = new $class_name($merchant_info, $config);
                    if ($obj->getBillType() == $config['bill_type']) {
                        $inputs = array(
                            'version' => $params['version'],
                            'merchant_refer_code' => $params['ref_code'],
                            'card_code' => $params['pin_card'],
                            'card_serial' => $params['card_serial'],
                            'merchant_input' => json_encode($params),
                            'time_created' => $now,
                        );
                        $insert = $obj->insertCardLog($inputs);
                        if ($insert['error_code'] == '00') {
                            $card_log_id = $insert['card_log_id'];
                            $result = $obj->cardCharge($card_log_id, $params);
                            if ($result['error_code'] == '00') {
                                $error_code = '00';
                            } else {
                                $error_code = $result['error_code'];
                            }
                            //-------------
                            $card_status = $this->getCardStatusByErrorCode($error_code);
                            $card_amount = 0;
                            if (intval($result['card_price']) > 0) {
                                $card_amount = $result['card_price'] - MerchantCardFee::calculateFee($result['card_price'], $config['percent_fee']);
                            }
                            $response_data = $this->_getMerchantOutput($params, $card_log_id, $result['card_price'], $card_amount);
                            $inputs = array(
                                'result_code' => $error_code,
                                'merchant_output' => json_encode($response_data),
                                'card_price' => $result['card_price'],
                                'card_amount' => $card_amount,
                                'card_status' => $card_status,
                                'partner_card_refer_code' => $result['partner_card_refer_code'],
                            );
                            $update = $obj->updateCardLog($card_log_id, $inputs);
                            if ($update['error_code'] != '00') {
                                if ($error_code == '00') {
                                    $error_code = '20';
                                } else {
                                    $error_code = '18';
                                }
                            }
                        } else {
                            $error_code = $insert['error_code'];
                        }
                    } else {
                        $error_code = '19';
                    }
                } else {
                    $error_code = '19';
                }
            } else {
                $error_code = $check_config['error_code'];
            }
        } else {
            $error_code = $validate['error_code'];
        }

        return array('error_code' => $error_code, 'error_message' => $this->getErrorMessage($error_code), 'response_data' => $response_data);
    }

    /**
     *
     * @param type $params : version, merchant_id, pin_card, card_serial, type_card, ref_code, client_fullname, client_email, client_mobile, checksum
     */
    protected function _validateCardCharge($params, &$merchant_info = false)
    {
        $error_code = '99';
        //----------
        if ($this->_checkCardCode($params['type_card'], $params['pin_card'])) {
            if ($this->_checkCardSerial($params['type_card'], $params['pin_card'])) {
                if ($this->_checkClientFullname($params['client_fullname']) && $this->_checkClientEmail($params['client_email']) && $this->_checkClientMobile($params['client_mobile'])) {
                    $check = $this->_checkMerchant($params['merchant_id']);
                    if ($check['error_code'] == '00') {
                        $merchant_info = $check['merchant_info'];
                        if ($this->_checkChecksum($merchant_info, $params)) {
                            $card_merchant_refer_code_info = Tables::selectOneDataTable("card_merchant_refer_code", ["merchant_id = :merchant_id AND merchant_refer_code = :merchant_refer_code ", "merchant_id" => $params['merchant_id'], "merchant_refer_code" => $params['ref_code']]);
                            if ($this->_checkReferCode($params['ref_code'], $params['merchant_id'])) {
                                $error_code = '00';
                            } else {
                                $error_code = '22';
                            }
                        } else {
                            $error_code = '04';
                        }
                    } else {
                        $error_code = $check['error_code'];
                    }
                } else {
                    $error_code = '02';
                }
            } else {
                $error_code = '12';
            }
        } else {
            $error_code = '11';
        }
        return array('error_code' => $error_code);
    }

    /**
     *
     * @param type $params : version, merchant_id, ref_code, checksum
     */
    public function getTransactionDetail($params)
    {
        $error_code = '99';
        $response_data = array(
            'merchant_id' => $params['merchant_id'],
            'pin_card' => '',
            'card_serial' => '',
            'type_card' => '',
            'ref_code' => $params['ref_code'],
            'client_fullname' => '',
            'client_email' => '',
            'client_mobile' => '',
            'card_amount' => 0,
            'transaction_amount' => 0,
            'transaction_id' => '',
        );
        //------------
        $validate = $this->_validateGetTransactionDetail($params, $merchant_info);
        if ($validate['error_code'] == '00') {
            $card_log_info = \common\models\db\CardLog::getInfoByMerchantReferCode($merchant_info['id'], $params['ref_code']);
            if ($card_log_info != false) {
                $merchant_input = json_decode($card_log_info['merchant_input'], true);
                $response_data['pin_card'] = $card_log_info['card_code'];
                $response_data['card_serial'] = $card_log_info['card_serial'];
                $response_data['type_card'] = CardType::getCodeById($card_log_info['card_type_id']);
                $response_data['client_fullname'] = @$merchant_input['client_fullname'];
                $response_data['client_email'] = @$merchant_input['client_email'];
                $response_data['client_mobile'] = @$merchant_input['client_mobile'];
                $response_data['card_amount'] = intval($card_log_info['card_price']);
                if ($card_log_info['result_code'] != '') {
                    $error_code = $card_log_info['result_code'];
                    if (intval($card_log_info['card_price']) > 0) {
                        $response_data['transaction_amount'] = $card_log_info['card_amount'];
                        $response_data['transaction_id'] = $card_log_info['id'];
                    }
                } else {
                    $error_code = '18';
                }
            } else {
                $error_code = '22';
            }
        } else {
            $error_code = $validate['error_code'];
        }
        return array('error_code' => $error_code, 'error_message' => $this->getErrorMessage($error_code), 'response_data' => $response_data);
    }

    /**
     *
     * @param type $params : version, merchant_id, ref_code, checksum
     */
    protected function _validateGetTransactionDetail($params, &$merchant_info = false)
    {
        $error_code = '99';
        $check = $this->_checkMerchant($params['merchant_id']);
        if ($check['error_code'] == '00') {
            $merchant_info = $check['merchant_info'];
            if ($this->_checkChecksum($merchant_info, $params)) {
                $error_code = '00';
            } else {
                $error_code = '04';
            }
        } else {
            $error_code = $check['error_code'];
        }
        return array('error_code' => $error_code);
    }

    /**
     *
     * @param type $params : version, merchant_id, pin_card, card_serial, type_card, ref_code, client_fullname, client_email, client_mobile, checksum
     * @param type $card_log_id
     * @param type $card_price
     * @param type $card_amount
     * @return type
     */
    protected function _getMerchantOutput($params, $card_log_id, $card_price, $card_amount)
    {
        $transaction_id = '';
        if (intval($card_amount) > 0) {
            $transaction_id = $card_log_id;
        }
        return array(
            'merchant_id' => $params['merchant_id'],
            'pin_card' => $params['pin_card'],
            'card_serial' => $params['card_serial'],
            'type_card' => $params['type_card'],
            'ref_code' => $params['ref_code'],
            'client_fullname' => $params['client_fullname'],
            'client_email' => $params['client_email'],
            'client_mobile' => $params['client_mobile'],
            'card_amount' => $card_price,
            'transaction_amount' => $card_amount,
            'transaction_id' => $transaction_id,
        );
    }

    public function getCardStatusByErrorCode($error_code)
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

    public function getErrorMessage($error_code)
    {
        $message = array(
            '00' => 'Giao dịch thành công',
            '99' => 'Lỗi, tuy nhiên lỗi chưa được định nghĩa hoặc chưa xác định được nguyên nhân',
            '01' => 'Lỗi, địa chỉ IP truy cập API của NgânLượng.vn bị từ chối',
            '02' => 'Lỗi, tham số gửi từ merchant tới NgânLượng.vn chưa chính xác (thường sai tên tham số hoặc thiếu tham số)',
            '03' => 'Lỗi, Mã merchant không tồn tại hoặc merchant đang bị khóa kết nối tới NgânLượng.vn',
            '04' => 'Lỗi, Mã checksum không chính xác (lỗi này thường xảy ra khi mật khẩu giao tiếp giữa merchant và NgânLượng.vn không chính xác, hoặc cách sắp xếp các tham số trong biến params không đúng)',
            '05' => 'Loại thẻ không được hỗ trợ',
            '06' => 'Merchant không được cấu hình gạch loại thẻ đang yêu cầu',
            '07' => 'Thẻ đã được sử dụng',
            '08' => 'Thẻ bị khóa',
            '09' => 'Thẻ hết hạn sử dụng',
            '10' => 'Thẻ chưa được kích hoạt hoặc không tồn tại',
            '11' => 'Mã thẻ sai định dạng',
            '12' => 'Sai số serial của thẻ',
            '13' => 'Mã thẻ và số serial không khớp',
            '14' => 'Thẻ không tồn tại',
            '15' => 'Thẻ không sử dụng được',
            '16' => 'Số lần thử (nhập sai liên tiếp) của thẻ vượt quá giới hạn cho phép',
            '17' => 'Hệ thống Telco bị lỗi hoặc quá tải, thẻ chưa bị trừ',
            '18' => 'Hệ thống Telco bị lỗi hoặc quá tải, thẻ có thể bị trừ, cần phối hợp với NgânLượng.vn để tra soát',
            '19' => 'Kết nối từ NgânLượng.vn tới hệ thống Telco bị lỗi, thẻ chưa bị trừ (thường do lỗi kết nối giữa NgânLượng.vn với Telco, ví dụ sai tham số kết nối, mà không liên quan đến merchant)',
            '20' => 'Kết nối tới telco thành công, thẻ bị trừ nhưng chưa cộng tiền trên NgânLượng.vn',
            '21' => 'Khách hàng đang nạp thẻ bị khóa (do nhập sai mã thẻ liên tiếp)',
            '22' => 'ref_code không hợp lệ',
        );
        $error_code = array_key_exists($error_code, $message) ? $error_code : '99';
        return $message[$error_code];
    }
}
