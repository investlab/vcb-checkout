<?php

namespace common\api;

use Yii;
use common\models\db\Merchant;
use common\components\utils\Validation;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;
use common\models\business\CashoutBusiness;
use common\payments\NganLuongTransferOld;
use common\components\utils\Translate;
use common\models\db\Cashout;
use common\components\libs\Tables;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
class PayoutVersion1_0StaticApi extends PayoutBasicApi {

    public $_allow_payout = array('24', '1');

    public function allowIP($ip = false) {
        $array = $GLOBALS['ALLOW_API_TRANFER'];
        if (empty($ip)) {
            if (empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $ip = Yii::$app->getRequest()->getUserIP();
            } else
                $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        if (!in_array($ip, $array))
            $this->writeLog('===== Invalid IP ' . $ip);
        return in_array($ip, $array);
    }

    public function getVersion() {
        return '1.0';
    }

    protected function _isFunction($function) {
        return ($function == 'Query' || $function == 'Tranfer' || $function == 'CashoutRequest' || $function == 'GetZone' || $function == 'CheckEmailNL');
    }

    public function getData($function) {
        if ($function == 'Tranfer') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['reference_code'] = ObjInput::get('reference_code', 'str', '');
            $data['reason'] = ObjInput::get('reason', 'str', '');
            $data['amount'] = ObjInput::get('amount', 'str', '');
            $data['receive_email'] = ObjInput::get('receive_email', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == 'Query') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['cashout_id'] = ObjInput::get('cashout_id', 'int', 0);
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == 'CashoutRequest') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['amount'] = ObjInput::get('amount', 'str', '');
            $data['reference_code'] = ObjInput::get('reference_code', 'str', '');
            $data['bank_code'] = ObjInput::get('bank_code', 'str', '');
            $data['account_fullname'] = ObjInput::get('account_fullname', 'str', '');
            $data['account_number'] = ObjInput::get('account_number', 'str', '');
            $data['branch_name'] = ObjInput::get('branch_name', 'str', '');
            $data['notify_url'] = ObjInput::get('notify_url', 'str', '');
            $data['zone_id'] = ObjInput::get('zone_id', 'int', 0);
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == 'GetZone') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            return $data;
        } elseif ($function == 'CheckEmailNL') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['email'] = ObjInput::get('email', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code) {
        $message = array(
            '0000' => 'Success', '0001' => 'Undefined Error', '0002' => 'Invalid Function name', '0003' => 'Invalid merchant_site_code ', '0004' => 'Invalid version', '0005' => 'Invalid reference_code', '0006' => 'Invalid reason', '0007' => 'Invalid amount format ', '0010' => 'Invalid reciver_email', '0017' => 'Invalid checksum', '0101' => 'Request params are ok, but could not create request withdraw', '0012' => 'Invalid zone_id', '0106' => 'Invalid bank_code', '0102' => 'Invalid Email'
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    /**
     *
     * @param type $params : merchant_site_code, reference_code, reason, amount, receive_email,checksum
     * @return type
     */
    protected function _tranfer($params) {
        $id = 0;
        $error_code = '0001';
        $result_data = null;
        //-------------
        $inputs = array(
            'version' => $this->getVersion(),
            'language_id' => $this->_getLanguageId('en'),
            'merchant_id' => $params['merchant_site_code'],
            'reference_code' => $params['reference_code'],
            'reason' => $params['reason'],
            'amount' => $params['amount'],
            'receive_email' => $params['receive_email'],
            'user_id' => 0,
        );

        //Procces withdraw

        $payment_method_id = 80; // RÚT TIỀN QUA VÍ NGANLUONG
        $this->writeLog('===== [_tranfer ' . @$params["reference_code"] . '] ' . json_encode($params));
        $addForCheckoutOrder = array(
            'payment_method_id' => $payment_method_id,
            'merchant_id' => $params['merchant_site_code'],
            'amount' => $params['amount'],
            'currency' => $GLOBALS['CURRENCY']['VND'],
            'bank_account_code' => $params['receive_email'],
            'bank_account_name' => NULL,
            'bank_account_branch' => NULL,
            'bank_card_month' => NULL,
            'bank_card_year' => NULL,
            'partner_payment_data' => '',
            'user_id' => 0,
            'reference_code_merchant' => $params["reference_code"],
        );
        $partner_payment_id = 2; // Rút qua kênh Ngân Lượng
        $inputs = array(
            'merchant_id' => $params['merchant_site_code'],
            'partner_payment_id' => $partner_payment_id, // Rút qua kênh Ngân Lượng
            'currency' => 'VND',
            'user_id' => Yii::$app->user->getId(),
        );
        $result_updateBalance = \common\models\business\PartnerPaymentAccountBusiness::updatePartnerPaymentBalanceByMerchant($inputs, false);

        if ($result_updateBalance ['error_message'] == '') {
            $result = CashoutBusiness::addForCheckoutOrder($addForCheckoutOrder, false);
            $this->writeLog('===== [addForCheckoutOrder ' . @$params["reference_code"] . '] ' . json_encode($result));
            $error_code = '0101';
            if ($result['error_message'] == '') {
                $cashout_id = $result['id'];
                $params_updateStatusWaitAccept = array(
                    'cashout_id' => $cashout_id,
                    'partner_payment_id' => $partner_payment_id,
                    'user_id' => 0
                );

                $result_update = CashoutBusiness::updateStatusWaitAccept($params_updateStatusWaitAccept, false);
                $this->writeLog('===== [updateStatusWaitAccept ' . @$params["reference_code"] . '] ' . json_encode($result_update));
                if ($result_update['error_message'] == '') {

                    $result_call = $this->_callTranferNganLuong($cashout_id, $params);
                    $this->writeLog('===== [_callTranferNganLuong ' . @$params["reference_code"] . '] ' . json_encode($result_call));
                    $error_code = '0101';
                    if ($result_call['error_message'] == '') {
                        $id = $result_call['transaction_id'];
                        $error_code = '0000';
                        $result_data = array(
                            'reference_code' => $params['reference_code'],
                            'transaction_id' => $id
                        );
                    }
                }
            }
        } else {
            $error_code = '0101';
            $this->writeLog('===== [updatePartnerPaymentBalanceByMerchant ' . @$params["reference_code"] . '] ' . json_encode($result_updateBalance));
        }




        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    function _callTranferNganLuong($cashout_id, $params) {
        $error_message = '';
        $total_amount = $params['amount'];
        $nganluong_account = '';
        $partner_payment_refer_code = 0;
        //get email chi
        $partner_payment_account_info = Tables::selectOneDataTable("partner_payment_account", ["merchant_id = :merchant_id and balance > :amounts and partner_payment_id = 2", "merchant_id" => $params['merchant_site_code'], "amounts" => $total_amount], "balance DESC", "*");

        $nganluong_account = @$partner_payment_account_info['partner_payment_account'];
        if (!empty($nganluong_account)) {
            $inputs = array(
                'receive_email' => $params['receive_email'],
                'amount' => $total_amount,
                'reference_code' => $GLOBALS['PREFIX'] . $cashout_id . '-' . $params['reference_code'],
                'sender_email' => $nganluong_account
            );


            $this->writeLog('===== [NganLuongTransferOld::tranfer INPUT' . @$params["reference_code"] . '] ' . json_encode($inputs));

            $result = NganLuongTransferOld::tranfer($inputs);
            $this->writeLog('===== [NganLuongTransferOld::tranfer ' . @$params["reference_code"] . '] ' . json_encode($result));
            if ($result['response_code'] === 'E00') {
                $partner_payment_refer_code = $result['response']['transaction_id'];
                $time_paid = $result['response']['time_created'];
                //------
//            $inputs = array(
//                'time_created_from' => $time_paid - 3600,
//                'time_created_to' => $time_paid + 3600,
//                'type_filter' => 1,
//                'value_filter' => $partner_payment_refer_code,
//                'type' => 1,
//                'status' => 4,
//                'sender_email' => $nganluong_account
//            );
                // $result = NganLuongTransferOld::getTransaction($inputs);
                // $this->writeLog('===== [NganLuongTransferOld::getTransaction ' . @$params["reference_code"] . '] ' . json_encode($result));
                // if ($result['response_code'] === 'E00' && $partner_payment_refer_code == @$result['response'][0]['id']) {
                //$receiver_fee = @$result['response'][0]['receiver_fee'];
                //--------
                $inputs = array(
                    'cashout_id' => $cashout_id,
                    'time_paid' => $time_paid,
                    'bank_refer_code' => strval($partner_payment_refer_code),
                    'receiver_fee' => 0,
                    'user_id' => 0,
                );
                $result = CashoutBusiness::updateStatusAcceptAndPaid($inputs);
                if ($result['error_message'] == '') {
                    $error_message = '';
                } else {
                    $error_message = Translate::get($result['error_message']);
                }
                //} else {
                // $error_message = NganLuongTransferOld::getErrorMessage($result['response_code']);
                //}
            } else {
                $error_message = NganLuongTransferOld::getErrorMessage($result['response_code']);
            }
        } else {
            $error_message = 'Balance is not enough';
        }

        return array('error_message' => $error_message, 'transaction_id' => $partner_payment_refer_code);
    }

    protected function _query($params) {
        $error_code = '0001';
        $result_data = null;
        //-------------
        $result_data = Tables::selectOneDataTable("cashout", ["id = :id AND merchant_id = :merchant_id ", "id" => $params['cashout_id'], "merchant_id" => $params['merchant_site_code']]);
        if (empty($result_data)) {
            $error_code = '0404';
        } else {
            $arr_status = array(
                '3' => 'New',
                '8' => 'Cancel',
                '7' => 'Complete',
                '6' => 'Approved',
                '4' => 'Waiting To Approve'
            );
            $result_data = array(
                'reference_code' => $result_data['reference_code_merchant'],
                'amount' => $result_data['amount'],
                'status' => @$arr_status[$result_data['status']],
                'currency' => $result_data['currency'],
                'merchant_site_code' => $result_data['merchant_id'],
                'cashout_id' => $result_data['id'],
                'account_number' => $result_data['bank_account_code'],
                'account_fullname' => $result_data['bank_account_name'],
                'branch_name' => $result_data['bank_account_branch'],
                'time_created' => date('h:i:s d/m/Y', $result_data['time_created']) . ' GMT+7',
                'time_updated' => date('h:i:s d/m/Y', $result_data['time_updated']) . ' GMT+7',
            );
            $error_code = '0000';
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataQuery(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumQuery($data, $api_key)) {
                $error_code = '0000';
                if (empty($data['cashout_id'])) {
                    $error_code = '0018';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumQuery($data, $api_key) {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['cashout_id'];
        $str_checksum .= '|' . $api_key;
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, reference_code, reason, amount, buyer_email,checksum
     */
    protected function _validateDataTranfer(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false && in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ALLOW_API_TRANFER'])) {
            if ($this->_validateChecksumTranfer($data, $api_key)) {
                if ($this->_validateOrderCode($data['reference_code'])) {
                    if ($this->_validateOrderDescription($data['reason'])) {
                        if ($this->_validateAmount($data['amount'])) {
                            if ($this->_validateBuyerEmail($data['receive_email'])) {
                                $error_code = '0000';
                            } else {
                                $error_code = '0010';
                            }
                        } else {
                            $error_code = '0007';
                        }
                    } else {
                        $error_code = '0006';
                    }
                } else {
                    $error_code = '0005';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumTranfer($data, $api_key) {

        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['reference_code'];
        $str_checksum .= '|' . $data['reason'];
        $str_checksum .= '|' . $data['amount'];
        $str_checksum .= '|' . $data['receive_email'];
        $str_checksum .= '|' . $api_key;
        //echo($str_checksum).'<br>';
        //echo md5($str_checksum);
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    protected function _getLanguageId($language) {
        if ($language == 'en') {
            return 2;
        } else {
            return 1;
        }
    }

    /*
     * Create Cashout Requset*
     */

    protected function _cashoutRequest($params) {
        $id = 0;
        $error_code = '0001';
        $result_data = null;
        //Procces withdraw

        $this->writeLog('===== [_cashoutRequest ' . @$params["reference_code"] . '] ' . json_encode($params));
        $partner_payment_id = 2; // Rút qua kênh Ngân Lượng
        $inputs = array(
            'merchant_id' => $params['merchant_site_code'],
            'partner_payment_id' => $partner_payment_id, // Rút qua kênh Ngân Lượng
            'currency' => 'VND',
            'user_id' => Yii::$app->user->getId(),
        );
        $result_updateBalance = \common\models\business\PartnerPaymentAccountBusiness::updatePartnerPaymentBalanceByMerchant($inputs, false);

        if ($result_updateBalance ['error_message'] == '') {
            $partner_payment_data = json_encode(['zone_id' => $params['zone_id']]);
            $payment_method_code = $params['bank_code'] . '-WITHDRAW-IB-OFFLINE';


            $payment_method_info = Tables::selectOneDataTable("payment_method", ["code = :payment_method_code", "payment_method_code" => $payment_method_code]);
            if ($payment_method_info != false) {

                $addForCheckoutOrder = array(
                    'payment_method_id' => $payment_method_info['id'],
                    'merchant_id' => $params['merchant_site_code'],
                    'amount' => ObjInput::formatCurrencyNumber($params['amount']),
                    'currency' => $GLOBALS['CURRENCY']['VND'],
                    'bank_account_code' => $params['account_number'],
                    'bank_account_name' => $params['account_fullname'],
                    'bank_account_branch' => $params['branch_name'],
                    'bank_card_month' => NULL,
                    'bank_card_year' => NULL,
                    'partner_payment_data' => '',
                    'user_id' => 0,
                    'reference_code_merchant' => $params["reference_code"],
                );
                $this->writeLog('Input[_cashoutRequest+addForCheckoutOrder ' . @$params["reference_code"] . '] ' . json_encode($addForCheckoutOrder));
                $result = CashoutBusiness::addForCheckoutOrder($addForCheckoutOrder, false);
                $this->writeLog('===== [_cashoutRequest+addForCheckoutOrder ' . @$params["reference_code"] . '] ' . json_encode($result));
                $error_code = '0101';
                if ($result['error_message'] == '') {
                    $error_code = '0000';
                    $result_data = array(
                        'reference_code' => $params['reference_code'],
                        'cashout_id' => $result['id'],
                        'message' => ''
                    );
                } else {
                    $result_data = array(
                        'reference_code' => $params['reference_code'],
                        'cashout_id' => $result['id'],
                        'message' => $result['error_message']
                    );
                }
            }
        } else {
            $error_code = '0101';
            $this->writeLog('===== [_cashoutRequest+updatePartnerPaymentBalanceByMerchant ' . @$params["reference_code"] . '] ' . json_encode($result_updateBalance));
        }




        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataCashoutRequest(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false && in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ALLOW_API_TRANFER'])) {
            if ($this->_validateChecksumCashoutRequest($data, $api_key)) {
                if ($this->_validateOrderCode($data['reference_code'])) {
                    if ($this->_validateAmount($data['amount'])) {
                        if ($this->_validateBuyerFullname($data['account_fullname'])) {
                            if ($this->_validateBuyerFullname($data['account_number'])) {
                                if ($this->_validateBuyerFullname($data['branch_name'])) {
                                    if ($this->_validateZoneId($data['zone_id'])) {
                                        if ($this->_validateBankCode($data['bank_code'])) {
                                            $error_code = '0000';
                                        } else {
                                            $error_code = '0106';
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
                    $error_code = '0005';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

//checksum = md5(
//merchant_site_code + '|' + reference_code + '|' + amount + '|' + bank_ code + '|' + account_fullname + '|' + account_number + '|' + branch_name + '|' + zone_id + '|' + merchant_passcode))
//With merchant_passcode) : merchant password connection of merchnat_site_code

    protected function _validateChecksumCashoutRequest($data, $api_key) {

        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['reference_code'];
        $str_checksum .= '|' . $data['amount'];
        $str_checksum .= '|' . $data['bank_code'];
        $str_checksum .= '|' . $data['account_fullname'];
        $str_checksum .= '|' . $data['account_number'];
        $str_checksum .= '|' . $data['branch_name'];
        $str_checksum .= '|' . $data['zone_id'];

        $str_checksum .= '|' . $api_key;
        //echo($str_checksum).'<br>';
        //echo md5($str_checksum);
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    function _validateZoneId($zone_id) {
        if (empty($zone_id))
            return false;
        $models = new \common\models\form\CashoutForm();
        $arr_zone = $models->getZones(false);
        foreach ($arr_zone as $k => $v) {
            $arr_zone_id[] = $k;
        }
        //$this->writeLog('Zone ' . json_encode($arr_zone_id));
        return in_array($zone_id, $arr_zone_id);
    }

    function _validateBankCode($bank_code) {
        if (empty($bank_code))
            return false;
        $array = array('AGB', 'BAB', 'BIDV', 'EXB', 'MSB', 'STB', 'SGB', 'NCB', 'PGB', 'GPB', 'ICB', 'TCB', 'TPB', 'VAB', 'VIB', 'VCB', 'DAB', 'MB', 'ACB', 'HDB', 'VPB', 'OJB', 'SHB', 'NAB', 'BVB', 'CTB', 'KLB', 'GDB', 'PVCOMBANK', 'KB', 'GTB', 'SCB', 'SHNB', 'HSB', 'SC', 'ANZ', 'ABB', 'GAB', 'OCB', 'SEA', 'NVB');
        return in_array($bank_code, $array);
    }

    function _getZone($params) {
        $models = new \common\models\form\CashoutForm();
        $arr_zone = $models->getZones(false);
        if (empty($arr_zone)) {
            $error_code = '0101';
            $result_data = [];
        } else {
            $error_code = '0000';
            $result_data = $arr_zone;
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataGetZone(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false && in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ALLOW_API_TRANFER'])) {
            $error_code = '0000';
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumDataCheckEmailNL($data, $api_key) {

        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['email'];
        $str_checksum .= '|' . $api_key;
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    protected function _validateDataCheckEmailNL(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false && in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ALLOW_API_TRANFER'])) {
            if ($this->_validateChecksumDataCheckEmailNL($data, $api_key)) {
                if ($this->_validateBuyerEmail($data['email'])) {
                    $error_code = '0000';
                } else {
                    $error_code = '0102';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _checkEmailNL($params) {
        $id = 0;
        $error_code = '0001';
        $result_data = null;
        //-------------
        $inputs = array(
            'email' => $params['email'],
        );
        $this->writeLog('Input [NganLuongTransferOld::getInfo ' . @$params["email"] . '] ' . json_encode($inputs));
        $result = NganLuongTransferOld::getInfo($inputs);
        $this->writeLog('Output [NganLuongTransferOld::getInfo ' . @$params["email"] . '] ' . json_encode($result));
        if ($result['response_code'] === 'E00') {
            $error_code = '0000';
            $result_data = $result['response'];
        } else {
            $error_code = '02' . str_replace('E', '', $result['response_code']);
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

}
