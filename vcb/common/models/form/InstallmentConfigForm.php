<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class InstallmentConfigForm extends LanguageBasicForm
{
    public $id;
    public $merchant_id;
    public $cycle_accept;
    public $card_accept;
    public $status;

    public function rules()
    {
        return [
            [['merchant_id', 'card_accept', 'cycle_accept'], 'required', 'message' => 'Bạn phải chọn {attribute}.'],
            ['merchant_id', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Mã merchant',
            'card_accept' => 'Loại thẻ hỗ trợ',
            'cycle_accept' => 'Kỳ hạn trả góp',
            'status' => 'Trạng thái cấu hình trả góp'
        ];
    }

} 