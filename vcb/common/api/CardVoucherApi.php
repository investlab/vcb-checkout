<?php

namespace common\api;

use common\components\libs\NotifySystem;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\AccountBusiness;
use common\models\business\CardVoucherBusiness;
use common\models\db\Account;
use common\models\db\CardVoucher;
use common\models\db\CardVoucherRequirement;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\db\User;
use common\util\Helpers;
use Yii;
use yii\db\Exception;

class CardVoucherApi extends CardVoucherBasicApi
{
    private $card_voucher_info;
    public $merchant_info = null;

    public function getVersion()
    {
        // TODO: Implement getVersion() method.
        return ObjInput::get('version', 'str', '1.0');
    }

    protected function _isFunction($function): bool
    {
        // TODO: Implement _isFunction() method.
        return in_array($function, ['GetCardInfo', 'Payment', 'cancel', 'CheckOrder', 'CheckToken']);
    }

    public function getData($function): array
    {
        // TODO: Implement getData() method.
        switch ($function) {
            case 'GetCardInfo':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', '1.0'),
                    'merchant_site_code' => ObjInput::get('merchant_site_code', 'int', 0),
                    'card_number' => ObjInput::get('card_number', 'str', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                ];
                break;
            case 'Payment':
                $request = [
                    'function' => $function,
                    'version' => ObjInput::get('version', 'str', '1.0'),
                    'merchant_site_code' => ObjInput::get('merchant_site_code', 'int', 0),
                    'card_number' => ObjInput::get('card_number', 'str', ''),
                    'amount' => ObjInput::get('amount', 'int', ''),
                    'checksum' => ObjInput::get('checksum', 'str', ''),
                    'order_code' => ObjInput::get('order_code', 'str', ''),
                    'serial' => ObjInput::get('serial', 'str', ''),
                ];
                break;
            default:
                $request = [];
        }
        return $request;
    }

    protected function processRequest($request): array
    {
        $error_code = '0001';
        $data = [];
        $method_name = 'process' . ucfirst($request['function']);
        if (method_exists($this, $method_name)) {
            $process = $this->$method_name($request);
            $error_code = $process['error_code'];
            $data = $process['data'];
        }
        return ['error_code' => $error_code, 'data' => $data];
    }

    public function getResultMessage($result_code): string
    {
        $message = array(
            '0000' => 'Success',
            '0001' => 'Undefined error',
            '0002' => 'Invalid merchant site code',
            '0003' => 'Invalid card number',
            '0004' => 'Merchant not active',
            '0005' => 'Invalid amount',
            '0006' => 'Insufficient balance',
            '0007' => 'Invalid order code',
            '0008' => 'Invalid card number status',
            '0017' => 'Invalid checksum',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }


    protected function _validateDataGetCardInfo(&$data): array
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if (!$api_key) {
            $error_code = '0002';
        } else {
            if (!$this->_validateChecksumGetCardInfo($data, $api_key)) {
                $error_code = '0017';
            } elseif (!$this->_validateCardNumber($data['card_number'], $data['merchant_site_code'])) {
                $error_code = '0003';
            } else {
                $error_code = '0000';
            }
        }

        return array('error_code' => $error_code);
    }

    protected function _validateChecksumGetCardInfo($data, $api_key): bool
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _validateChecksumPayment($data, $api_key): bool
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['card_number'];
        $str_checksum .= '|' . $data['amount'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                echo $str_checksum . ' ======== ' . hash('sha256', $str_checksum);
                die();
            }
        }
        return false;
    }

    protected function _validateAmount($amount): array
    {
        if (!(intval(ObjInput::formatCurrencyNumber($amount)) > 0)) {
            $error_code = '0005';
        } elseif (ObjInput::formatCurrencyNumber($amount) > $this->card_voucher_info->balance) {
            $error_code = '0006';
        } else {
            $error_code = '0000';
        }
        return array('error_code' => $error_code);
    }

    protected function _getCardInfo($data): array
    {
        $error_code = '0000';
        $result_data = [
            'card_number' => (string)$this->card_voucher_info->card_number,
            'merchant_site_code' => (string)$this->card_voucher_info->merchant_id,
            'balance' => ObjInput::formatCurrencyNumber($this->card_voucher_info->balance),
            'status_code' => $this->getStatusCode($this->card_voucher_info->status),
            'time_active' => (string)$this->card_voucher_info->time_active,
            'time_expired' => (string)$this->card_voucher_info->time_expired,
        ];

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function getStatusCode($status): string
    {
        $list = [
            CardVoucher::STATUS_NEW => '000',
            CardVoucher::STATUS_ACTIVE => '001',
            CardVoucher::STATUS_LOCK => '002',
            CardVoucher::STATUS_EXPIRED => '003',
        ];
        return $list[$status];
    }

    protected function _validateDataPayment(&$data): array
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if (!$api_key) {
            $error_code = '0002';
        } else {
            $this->merchant_info = Merchant::findOne($this->merchant_info['id']);
            if ($this->merchant_info->status != Merchant::STATUS_ACTIVE) {
                $error_code = '0004';
            } elseif (!$this->_validateChecksumPayment($data, $api_key)) {
                $error_code = '0017';
            } elseif(!$this->_validateCardNumber($data['card_number'], $data['merchant_site_code']))  {
                $error_code = '0003';
            }elseif (!$this->_validateCardNumberForPayment($data['card_number'], $data['merchant_site_code'])) {
                $error_code = '0008';
            } else {
                $result_validate_amount = $this->_validateAmount($data['amount']);
                if (!($result_validate_amount['error_code'] == '0000')) {
                    $error_code = $result_validate_amount['error_code'];
                } elseif (!$this->_validateOrderCode($data['order_code'])) {
                    $error_code = '0007';
                } else {
                    $error_code = '0000';
                }
            }
        }
        return array('error_code' => $error_code);
    }

    /**
     * @throws Exception
     */
    protected function _payment($data, $rollback = true): array
    {
        $error_code = '0001';
        $commit = false;
        $result_data = null;
        if ($rollback) {
            $card_voucher_begin = CardVoucher::getDb()->beginTransaction();
        }
        $balance_follow_begin = AccountBusiness::getBalanceCardVoucher("139");
        $amount = ObjInput::formatCurrencyNumber($data['amount']);
        $require = new CardVoucherRequirement();
        $require->require_id = Helpers::randomNumber(6);
        $require->card_voucher_id = $this->card_voucher_info->id;
        $require->type = CardVoucherRequirement::TYPE_WITH_DRAW;
        $require->amount = $amount;
        $require->status = CardVoucherRequirement::STATUS_NEW;
        $require->order_code = $data['order_code'];
        if (str_contains($data['order_code'],'_')){
            $arr_mb = explode('_',$data['order_code']);
            if (isset($arr_mb[0])){
                $require->mobile_user = $arr_mb[0];
            }
        }
        $require->user_created = User::SYSTEM_USER_ID;
        if (!empty($data['serial'])){
            $require->serial = $data['serial'];
        }

        $check_exists = true;
        while ($check_exists) {
            $check_exists_require_id = CardVoucherRequirement::find()->where(['require_id' => $require->require_id])->exists();
            if (!$check_exists_require_id) {
                $check_exists = false;
            } else {
                $require->require_id = Helpers::randomNumber(6);
            }
        }

        if ($require->validate()) {
            if ($require->save()) {
                $inputs = array(
                    'card_voucher_id' => $this->card_voucher_info->id,
                    'amount' => $amount,
                    'user_id' => User::SYSTEM_USER_ID,
                );
                $checkpoint = [
                    'balance_old' => $balance_follow_begin,
                    'old' => $require->getCardVoucher()->one()->getAttributes(),
                ];
                $result = CardVoucherBusiness::decreaseBalance($inputs, false);
                if ($result['error_message'] != '') {
                    NotifySystem::send("ERR_CARD_VOUCHER_API |" . $result['error_message']);
                } else {
                    $result = CardVoucherBusiness::requirementAccept($require->id);
                    if ($result['error_message'] == '') {
                        $commit = true;
                        $result_data = [
                            'require_id' => $require->require_id,
                            'order_code' => $require->order_code,
                        ];
                        $error_code = "0000";
                    } else {
                        NotifySystem::send("ERR_CARD_VOUCHER_API |" . $result['error_message']);
                    }
                }
            } else {
                NotifySystem::send("ERR_CARD_VOUCHER_API |" . json_encode($require->getErrors()));
            }
        } else {
            NotifySystem::send("ERR_CARD_VOUCHER_API |" . json_encode($require->getErrors()));
            $error_code = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit) {
                $card_voucher_begin->commit();
            } else {
                $card_voucher_begin->rollBack();
            }
        }

        $balance_follow_after = AccountBusiness::getBalanceCardVoucher("139");
        $checkpoint['new'] = $require->getCardVoucher()->one()->getAttributes();
        $checkpoint['balance_new'] = $balance_follow_after;
        $require->checkpoint = json_encode($checkpoint);
        $require->save();
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateCardNumber($card_number, $merchant_id): bool
    {
        $card_voucher = CardVoucher::find()
            ->where(['card_number' => $card_number])
            ->andWhere(['merchant_id' => $merchant_id])
            ->one();
        if (!$card_voucher) {
            return false;
        } else {
            $this->card_voucher_info = $card_voucher;
            return true;
        }
    }

    protected function _validateMerchantSiteCode($merchant_site_site_code): bool
    {
        if (!(intval($merchant_site_site_code) > 0)) {
            return false;
        } else {
            return Merchant::find()->where(['id' => $merchant_site_site_code])->exists();
        }
    }

    protected function _validateOrderCode($order_code): bool
    {
        if (trim($order_code) == "" || $order_code == null) {
            return false;
        } elseif (strlen(trim($order_code)) > 50) {
            return false;
        } else {
            $check_exists = Transaction::find()
                ->where(['bank_refer_code' => $order_code])
                ->andWhere(['transaction_type_id' => TransactionType::WITHDRAW_CARD_VOUCHER])
                ->exists();
            return !$check_exists;
        }
    }

    protected function _validateCardNumberForPayment($card_number, $merchant_id): bool
    {
        $card_voucher = CardVoucher::find()
            ->where(['card_number' => $card_number])
            ->andWhere(['merchant_id' => $merchant_id])
            ->andWhere(['status' => CardVoucher::STATUS_ACTIVE])
            ->one();
        if (!$card_voucher) {
            return false;
        } else {
            $this->card_voucher_info = $card_voucher;
            return true;
        }
    }


}