<?php

namespace frontend\models\form;

use frontend\models\form\LanguageBasicForm;
use common\components\utils\Translate;
use common\models\db\LinkCard;

class LinkCardForm extends LanguageBasicForm
{

    const SCENARIO_INDEX = 'index';
    const SCENARIO_VERIFY_2D = 'verify_2d';
    const PREFIX_CODE = 'PG-TOKEN_';
    const DEFAULT_CURRENCY_CODE = '704' /*704 is VNĐ*/;

    public $card_number;
    public $card_month;
    public $card_year;
    public $card_type;
    public $verifyCode;
    public $verify_amount;
    public $error_message = '';
    public $cvv_code;

    public function rules()
    {
        return [
            [[
                'card_number',
                'card_month',
                'card_year',
                'cvv_code'
            ],
                'required',
                'message' => 'Quý khách vui lòng nhập {attribute}.',
                'on' => self::SCENARIO_INDEX
            ],
            [['cvv_code'], 'string', 'message' => '{attribute} không hợp lệ.'],
//            [['verifyCode'], 'captcha', 'captchaAction' => 'link-card/captcha', 'message' => Translate::get('{attribute} không đúng')]
        ];
    }

    public function attributeLabels()
    {
        return [
            'card_type' => 'Loại thẻ',
            'card_number' => 'Số thẻ',
            'card_month' => 'Tháng hết hạn',
            'card_year' => 'Năm hết hạn',
            'verifyCode' => 'Mã xác nhận',
            'verify_amount' => 'Số tiền xác nhận',
            'cvv_code' => 'Mã CVV',
        ];
    }

    public function getCardTypes()
    {
        return [
            '' => 'Chọn loại thẻ',
            'visa' => 'VISA',
            'mastercard' => 'MASTERCARD',
            'jcb' => 'JCB'
        ];
    }

    public function convertCardType($card_type)
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

    public function getExpiredCardMonths()
    {
        $card_months = array(
            '' => Translate::get("Tháng"),
            '01' => Translate::get('Tháng 1'),
            '02' => Translate::get('Tháng 2'),
            '03' => Translate::get('Tháng 3'),
            '04' => Translate::get('Tháng 4'),
            '05' => Translate::get('Tháng 5'),
            '06' => Translate::get('Tháng 6'),
            '07' => Translate::get('Tháng 7'),
            '08' => Translate::get('Tháng 8'),
            '09' => Translate::get('Tháng 9'),
            '10' => Translate::get('Tháng 10'),
            '11' => Translate::get('Tháng 11'),
            '12' => Translate::get('Tháng 12')
        );
        return $card_months;
    }

    public function getExpiredCardYears()
    {
        $card_years = array('' => Translate::get("Năm"));
        $year = date('Y');
        for ($i = $year; $i < ($year + 10); $i++) {
            $card_years[$i] = $i;
        }
        return $card_years;
    }

}
