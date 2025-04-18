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

abstract class CardBasicApi
{

    abstract public function getVersion();

    abstract public function getErrorMessage($error_code);

    abstract public function getCardStatusByErrorCode($error_code);

    protected function _checkChecksum($merchant_info, $params)
    {
        return (md5($merchant_info['id'] . '|' . $merchant_info['password']) == @$params['checksum']);
    }

    final protected function _checkCardCode($card_type, $card_code)
    {
        if (preg_match('/^\d{10,14}$/', $card_code)) {
            return true;
        }
        return false;
    }

    final protected function _checkCardSerial($card_type, $card_serial)
    {
        if (preg_match('/^[A-Za-z0-9]{10,20}$/', $card_serial)) {
            return true;
        }
        return false;
    }

    final protected function _checkReferCode($refer_code, $merchant_id)
    {
        if (strlen($refer_code) <= 50) {
            $card_merchant_refer_code_info = Tables::selectOneDataTable("card_merchant_refer_code", ["merchant_id = :merchant_id AND merchant_refer_code = :merchant_refer_code ", "merchant_id" => $merchant_id, "merchant_refer_code" => $refer_code]);
            if ($card_merchant_refer_code_info == false) {
                return true;
            }
        }
        return false;
    }

    final protected function _checkClientFullname($client_fullname)
    {
        if (strlen($client_fullname) <= 255) {
            return true;
        }
        return false;
    }

    final protected function _checkClientEmail($client_email)
    {
        if ($client_email == '' || \common\components\utils\Validation::isEmail($client_email)) {
            return true;
        }
        return false;
    }

    final protected function _checkClientMobile($client_mobile)
    {
        if ($client_mobile == '' || \common\components\utils\Validation::isMobile($client_mobile)) {
            return true;
        }
        return false;
    }

    final protected function _checkMerchant($merchant_id)
    {
        $error_code = '99';
        $merchant_info = null;
        //------
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $merchant_id]);
        if ($merchant_info != false) {
            if ($merchant_info['status'] == Merchant::STATUS_ACTIVE) {
                $error_code = '00';
            } else {
                $error_code = '03';
            }
        } else {
            $error_code = '03';
        }
        return array('error_code' => $error_code, 'merchant_info' => $merchant_info);
    }

    final protected function _getMerchantConfig($merchant_info, $card_type_code, $time_card_charge)
    {
        $error_code = '99';
        $config = '';
        //------        
        $card_type_id = CardType::getIdByCode($card_type_code);
        if ($card_type_id != false) {
            $merchant_card_fee_info = MerchantCardFee::getFee($merchant_info, $card_type_id, $time_card_charge);
            if ($merchant_card_fee_info != false) {
                $partner_card_code = PartnerCard::getPartnerCardCodeActive($card_type_id, $merchant_card_fee_info['bill_type'], $merchant_card_fee_info['cycle_day'], $partner_card_id);
                if ($partner_card_code != false) {
                    $config = array(
                        'partner_card_code' => $partner_card_code,
                        'partner_card_id' => $partner_card_id,
                        'card_type_id' => $card_type_id,
                        'bill_type' => $merchant_card_fee_info['bill_type'],
                        'cycle_day' => $merchant_card_fee_info['cycle_day'],
                        'percent_fee' => $merchant_card_fee_info['percent_fee'],
                        'currency' => $merchant_card_fee_info['currency'],
                    );
                    $error_code = '00';
                } else {
                    $error_code = '19';
                }
            } else {
                $error_code = '06';
            }
        } else {
            $error_code = '05';
        }
        return array('error_code' => $error_code, 'config' => $config);
    }

    final protected function _getClassName($partner_card_code)
    {
        $result = 'PartnerCard';
        $code = trim(strtolower($partner_card_code));
        $temp = explode('-', $code);
        foreach ($temp as $item) {
            $result .= ucfirst($item);
        }
        return $result;
    }

    final public function writeLog($data)
    {
        $now = time();
        $file_name = LOG_PATH . 'api' . DS . 'card' . DS . 'version' . $this->getVersion() . DS . date('Ymd', $now) . '.txt';
        $fp = fopen($file_name, 'a');
        if ($fp) {
            $line = date("[H:i:s, d/m/Y]: ", $now) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }

}
