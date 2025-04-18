<?php


namespace common\models\form;


use common\components\utils\ObjInput;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CardLogUpdateSuccessForm extends LanguageBasicForm
{
    public $id;
    public $card_price;
    public $partner_card_refer_code;


    public function rules()
    {
        return [
            [['card_price'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['id'], 'integer'],
            [['partner_card_refer_code'], 'string'],
            [['card_price'], 'checkValidate']
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_card_refer_code' => 'Mã tham chiếu với đối tác',
            'card_price' => 'Mệnh giá thẻ'
        ];
    }

    public function checkValidate()
    {
        if (ObjInput::formatCurrencyNumber($this->card_price) < 0) {
            $this->addError('card_price', 'Mệnh giá thẻ phải >= 0');
        }
    }

} 