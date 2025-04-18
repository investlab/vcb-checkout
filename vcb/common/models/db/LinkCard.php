<?php


namespace common\models\db;

use Yii;

/**
 * This is the model class for table "bank".
 *
 * @property integer $id
 * @property string $token_cybersource
 * @property string $token_merchant
 * @property integer $merchant_id
 * @property string $card_holder
 * @property string $card_number_mask
 * @property string $card_number_md5
 * @property integer $card_type
 * @property string $customer_email
 * @property string $customer_mobile
 * @property string $bank
 * @property integer $secure_type
 * @property integer $partner_payment_id
 * @property integer $verify_amount
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_verified
 * @property string $user_action
 * @property string $iv
 * @property string $info
 * @property boolean $link_card
 * @property string $token
 * @property mixed|null $customer_field
 */

class LinkCard extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_CANCEL = 3;
    const STATUS_EXPIRE = 4;
    const STATUS_LOCK = 5;
    const STATUS_PROCESS = 6;
    const STATUS_WAIT = 7;
    
    const TYPE_VISA = 1;
    const TYPE_MASTERCARD = 2;
    const TYPE_JCB = 3;
    const TYPE_AMEX = 4;

    const SECURE_TYPE_2D = 1;
    const SECURE_TYPE_3D = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'card_type', 'secure_type', 'partner_payment_id', 'verify_amount', 'status', 'time_created', 'time_updated', 'time_verified'], 'integer'],
            [['customer_mobile'], 'string', 'max' => 20],
            [['card_number_mask'], 'string', 'max' => 30],
            [['token_cybersource', 'token_merchant', 'card_number_md5'], 'string', 'max' => 100],
            [['customer_email', 'iv'], 'string', 'max' => 50],
            [['card_holder', 'bank'], 'string', 'max' => 100],
            [['info'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token_cybersource' => 'Token Cybersource',
            'token_merchant' => 'Token Merchant',
            'merchant_id' => 'Id Merchant',
            'card_holder' => 'Card Holder',
            'card_number_mask' => 'Card Number Mask',
            'card_number_md5' => 'Card Number Md5',
            'card_type' => 'Card Type',
            'customer_email' => 'Customer Email',
            'customer_mobile' => 'Customer Mobile',
            'bank' => 'bank',
            'secure_type' => 'Secure Type',
            'partner_payment_id' => 'Id Partner Payment',
            'verify_amount' => 'Verify Amount',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_verified' => 'Time Verified',
            'user_action' => 'User Action',
        ];
    }

    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'confirm' => array('title' => 'Xác thực', 'confirm' => true),
            'unlink' => array('title' => 'Huỷ liên kết', 'confirm' => true),
//            'lock-link' => array('title' => 'Khoá liên kết', 'confirm' => true),
            'active-link' => array('title' => 'Mở khoá liên kết', 'confirm' => true),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        switch ($row['status']) {
            case self::STATUS_NEW:
                $result['detail'] = $operators['detail'];
                break;
            case self::STATUS_LOCK:
                $result['active-link'] = $operators['active-link'];
                break;
            case self::STATUS_CANCEL:
            case self::STATUS_EXPIRE:
            case self::STATUS_PROCESS:
                $result['detail'] = $operators['detail'];
                break;
            case self::STATUS_ACTIVE:
                $result['detail'] = $operators['detail'];
//                $result['lock-link'] = $operators['lock-link'];
                $result['unlink'] = $operators['unlink'];
                break;
            case self::STATUS_WAIT:
                $result['detail'] = $operators['detail'];
                $result['confirm'] = $operators['confirm'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }
    
    public static function getLinkCardUrl($token)
    {
        return ROOT_URL . 'vi/frontend/link-card/index/' . $token;
    }

    public static function getAuthenticateUrl($token_code)
    {
        return ROOT_URL . 'vi/checkout/card-token/payment/' . $token_code;
    }
    
    public static function getById($card_token_id) {
        $link_card = LinkCard::find()->where(['id' => $card_token_id])->asArray()->one();
        if (!is_null($link_card)) {
            return $link_card;
        }
        return false;
    }

    public static function getByToken($token)
    {
        $link_card = LinkCard::find()->where(['token' => $token])->asArray()->one();
        if (!is_null($link_card)) {
            return $link_card;
        }
        return false;
    }
    
    public static function getByTokenMerchant($token_merchant) {
        $link_card = LinkCard::find()
                ->where(['token_merchant' => $token_merchant, 'status' => self::STATUS_ACTIVE])
//                ->orWhere(['token_merchant' => $token_merchant, 'status' => self::STATUS_PROCESS])
                ->asArray()
                ->one();
        if (!is_null($link_card)) {
            return $link_card;
        }
        return false;
    }

    public static function getByTokenMC($token_merchant) {
        $link_card = LinkCard::find()
            ->where(['token_merchant' => $token_merchant])
//                ->orWhere(['token_merchant' => $token_merchant, 'status' => self::STATUS_PROCESS])
            ->asArray()
            ->one();
        if (!is_null($link_card)) {
            return $link_card;
        }
        return false;
    }
    
    public static function getBankCodeByCardType($card_type) {
        switch ($card_type) {
            case self::TYPE_VISA:
                $bank_code = 'VISA';
                break;
            case self::TYPE_MASTERCARD:
                $bank_code = 'MASTERCARD';
                break;
            case self::TYPE_JCB:
                $bank_code = 'JCB';
                break;
            case self::TYPE_AMEX:
                $bank_code = 'amex';
                break;
            default:
                $bank_code = '';
        }
        return $bank_code;
    }

    public static function convertCardType($card_type)
    {
        switch ($card_type) {
            case 'visa':
                $type = LinkCard::TYPE_VISA;
                break;
            case 'mastercard':
                $type = LinkCard::TYPE_MASTERCARD;
                break;
            case 'jcb':
                $type = LinkCard::TYPE_JCB;
                break;
            case 'amex':
                $type = LinkCard::TYPE_AMEX;
                break;
            default :
                $type = '';
        }
        return $type;
    }

}