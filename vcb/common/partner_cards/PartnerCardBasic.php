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
use common\models\db\Merchant;
use common\models\db\PartnerCard;
use common\models\db\MerchantCardFee;
use common\models\db\CardType;
use common\models\db\PartnerCardSession;
use common\models\business\CardLogBusiness;
use common\models\business\PartnerCardLogBusiness;
use common\models\db\CardLog;
use common\models\business\PartnerCardSessionBusiness;
use common\models\db\CardPrice;
use common\models\db\PartnerCardLog;

abstract class PartnerCardBasic {

    public $merchant_info = null;
    public $config = null;
    
    abstract public function getBillType();

    abstract protected function _getStatusCardSuccess();

    abstract protected function _getStatusCardFail();

    abstract protected function _convertErrorCode($error_code);
    
    abstract public function cardCharge($card_log_id, $params);
    
    abstract protected function _getNewSessionId($card_log_id);
    
    abstract protected function _getSessionTimeLimit($now);
    
    abstract protected function _call($card_log_id, $partner_card_log_id, $function, $params);
    
    function __construct($merchant_info, $config) {
        $this->merchant_info = $merchant_info;
        $this->config = $config;
    }
    
    /**
     * 
     * @param type $params : version, merchant_refer_code, card_code, card_serial, merchant_input, time_created
     * @return type
     */
    final public function insertCardLog($params) {
        $error_code = '99';
        $card_log_id = null;
        //------
        $inputs = array(
            'version' => $params['version'],
            'merchant_id' => $this->merchant_info['id'], 
            'merchant_refer_code' => $params['merchant_refer_code'], 
            'bill_type' => $this->config['bill_type'], 
            'cycle_day' => $this->config['cycle_day'], 
            'card_type_id' => $this->config['card_type_id'], 
            'card_code' => $params['card_code'], 
            'card_serial' => $params['card_serial'], 
            'partner_card_id' => $this->config['partner_card_id'], 
            'percent_fee' => $this->config['percent_fee'], 
            'currency' => $this->config['currency'],
            'merchant_input' => $params['merchant_input'], 
            'time_created' => $params['time_created'], 
            'user_id' => 0,
        );
        $result = CardLogBusiness::add($inputs);
        if ($result['error_message'] == '') {
            $error_code = '00';
            $card_log_id = $result['id'];
        } else {
            $error_code = '17';
        }
        return array('error_code' => $error_code, 'card_log_id' => $card_log_id);
    }
    
    /**
     * 
     * @param type $card_log_id
     * @param type $params : result_code, merchant_output, card_price, card_amount, card_status, partner_card_refer_code
     * @return type
     */
    final public function updateCardLog($card_log_id, $params) {
        $error_code = '99';
        //-------
        $inputs = array(
            'card_log_id' => $card_log_id, 
            'result_code' => $params['result_code'], 
            'merchant_output' => $params['merchant_output'], 
            'card_price' => $params['card_price'], 
            'card_amount' => $params['card_amount'], 
            'card_status' => $params['card_status'], 
            'partner_card_refer_code' => $params['partner_card_refer_code'], 
            'user_id' => 0,
        );
        $result = CardLogBusiness::update($inputs);
        if ($result['error_message'] == '') {
            $error_code = '00';
        } else {
            $error_code = '18';
        }
        return array('error_code' => $error_code);
    }
    
    /**
     * 
     * @param type $params: type, function, input, session_id, card_log_id, card_code, card_serial
     * @return type
     */
    final public function insertPartnerCardLog($params) {
        $error_code = '99';
        $partner_card_log_id = null;
        //-----------
        $inputs = array(
            'partner_card_id' => $this->config['partner_card_id'], 
            'type' => $params['type'], 
            'function' => $params['function'], 
            'input' => $params['input'], 
            'session_id' => $params['session_id'], 
            'card_log_id' => $params['card_log_id'], 
            'card_type_id' => $this->config['card_type_id'], 
            'card_code' => $params['card_code'], 
            'card_serial' => $params['card_serial'], 
            'user_id' => 0,
        );
        $result = PartnerCardLogBusiness::add($inputs);
        if ($result['error_message'] == '') {
            $error_code = '00';
            $partner_card_log_id = $result['id'];
        } else {
            $error_code = '17';
        }
        return array('error_code' => $error_code, 'partner_card_log_id' => $partner_card_log_id);
    }
    
    /**
     * 
     * @param type $partner_card_log_id
     * @param type $params : output, new_session_id, refer_code, card_price, card_status
     * @return type
     */
    final public function updatePartnerCardLog($partner_card_log_id, $params) {
        $error_code = '99';
        //---------
        $inputs = array(
            'partner_card_log_id' => $partner_card_log_id, 
            'output' => $params['output'], 
            'result' => $params['new_session_id'], 
            'refer_code' => $params['refer_code'], 
            'card_price' => $params['card_price'], 
            'card_status' => $params['card_status'], 
            'user_id' => 0,
        );
        $result = PartnerCardLogBusiness::update($inputs);
        if ($result['error_message'] == '') {
            $error_code = '00';
        } else {
            $error_code = '18';
        }
        return array('error_code' => $error_code);
    }
    
    final protected function _checkCardPrice($card_type_id, $card_price) {
        $card_price_info = Tables::selectOneDataTable("card_price", ["card_type_id = :card_type_id AND price = :price AND status = :status ", "card_type_id" => $card_type_id, "price" => $card_price, 'status' => CardPrice::STATUS_ACTIVE]);
        if ($card_price_info != false) {
            return true;
        }
        return false;
    }
    
    final protected function _getSessionID($card_log_id, $jump = 0) {        
        $partner_card_session_info = Tables::selectOneDataTable("partner_card_session", ["partner_card_id = :partner_card_id", "partner_card_id" => $this->config['partner_card_id']]);
        if ($partner_card_session_info != false) {
            if ($partner_card_session_info['status'] == PartnerCardSession::STATUS_ACTIVE || $partner_card_session_info['time_updated'] < (time())) {
                if ($partner_card_session_info['status'] == PartnerCardSession::STATUS_ACTIVE && $partner_card_session_info['session_time_limit'] > time()) {
                    return $partner_card_session_info['session_id'];
                } else {
                    // update status wait
                    $inputs = array(
                        'partner_card_id' => $this->config['partner_card_id'], 
                    );
                    $result = PartnerCardSessionBusiness::updateStatusWait($inputs);
                    if ($result['error_message'] == '') {
                        $new_session_id = $this->_getNewSessionId($card_log_id);
                        if ($new_session_id != false) {
                            // update new_session_id
                            $inputs = array(
                                'partner_card_id' => $this->config['partner_card_id'], 
                                'session_id' => $new_session_id, 
                                'session_time_limit' => $this->_getSessionTimeLimit(time()), 
                            );
                            $result = PartnerCardSessionBusiness::updateNewSessionId($inputs);
                            if ($result['error_message'] == '') {
                                return $new_session_id;
                            }
                        }
                    } else {
                        if ($jump == 0) {
                            sleep(15);
                            return $this->_getSessionID($card_log_id, $jump + 1);
                        }
                    }
                }
            } elseif ($partner_card_session_info['status'] == PartnerCardSession::STATUS_WAIT) {
                if ($jump == 0) {
                    sleep(15);
                    return $this->_getSessionID($card_log_id, $jump + 1);
                }
            }
        } else {            
            // add new_session_id
            $inputs = array(
                'partner_card_id' => $this->config['partner_card_id'], 
                'session_id' => '', 
                'session_time_limit' => 0, 
            );
            $result = PartnerCardSessionBusiness::add($inputs);
            if ($result['error_message'] == '') {
                $new_session_id = $this->_getNewSessionId($card_log_id);
                if ($new_session_id != false) {
                    // update new_session_id
                    $inputs = array(
                        'partner_card_id' => $this->config['partner_card_id'], 
                        'session_id' => $new_session_id, 
                        'session_time_limit' => $this->_getSessionTimeLimit(time()), 
                    );
                    $result = PartnerCardSessionBusiness::updateNewSessionId($inputs);
                    if ($result['error_message'] == '') {
                        return $new_session_id;
                    }
                }
            } else {
                if ($jump == 0) {
                    sleep(15);
                    return $this->_getSessionID($card_log_id, $jump + 1);
                }
            }
        }
        return false;
    }

    final protected function _process($card_log_id, $function, $params, $type, $card_code = '', $card_serial = '', $session_id = '') {
        $error_code = '99';
        $output = null;
        $refer_code = null;
        $card_price = 0;
        $card_status = CardLog::CARD_STATUS_TIMEOUT;
        $new_session_id = null;
        //--------
        $inputs = array(
            'type' => $type, 
            'function' => $function, 
            'input' => json_encode($params), 
            'session_id' => $session_id, 
            'card_log_id' => $card_log_id, 
            'card_code' => $card_code, 
            'card_serial' => $card_serial
        );
        $insert = $this->insertPartnerCardLog($inputs);
        if ($insert['error_code'] == '00') {
            $partner_card_log_id = $insert['partner_card_log_id'];
            $result = $this->_call($card_log_id, $partner_card_log_id, $function, $params);
            if ($result != false && !empty($result)) {
                if ($result['error_code'] == '00') {
                    $error_code = '00';
                    $output = $result['output'];
                    $refer_code = $result['refer_code'];
                    $card_price = $result['card_price'];
                    $card_status = $result['card_status'];
                    $new_session_id = $result['new_session_id'];
                } else {
                    $error_code = $result['error_code'];
                }
            } else {
                $error_code = '18';
            }
            //--------------
            $inputs = array(
                'output' => $output, 
                'new_session_id' => $new_session_id,
                'refer_code' => $type == PartnerCardLog::TYPE_GET_SESSION ? '' : $refer_code, 
                'card_price' => $card_price, 
                'card_status' => $card_status,
            );
            $update = $this->updatePartnerCardLog($partner_card_log_id, $inputs);
            if ($update['error_code'] != '00') {
                $error_code = $update['error_code'];
            }
        } else {
            $error_code = $insert['error_code'];
        }
        return array('error_code' => $error_code, 'output' => $output, 'refer_code' => $refer_code, 'card_price' => $card_price, 'card_status' => $card_status ,'new_session_id' => $new_session_id);
    }

    final protected function _writeLog($data) {
        $now = time();
        $file_name = LOG_PATH . 'partner_card' . DS . strtolower($this->config['partner_card_code']). DS . date('Ymd', $now).'.txt';
        $fp = fopen($file_name, 'a');
        if ($fp) {
            $line = date("H:i:s, d/m/Y:  ", $now) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }
}
