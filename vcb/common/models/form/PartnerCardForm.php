<?php


namespace common\models\form;


use common\components\libs\Tables;
use common\components\utils\Strings;
use common\components\utils\Validation;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class PartnerCardForm extends LanguageBasicForm
{
    public $id;
    public $code;
    public $name;
    public $config;
    public $bill_type;

    public function rules()
    {
        return [
            [['code', 'name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'bill_type'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['config'], 'string'],
            ['code', 'checkExits'],
            [['code'], 'checkString']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã đối tác',
            'name' => 'Tên đối tác',
            'config' => 'Cấu hình',
            'bill_type' => 'Loại'
        ];
    }

    public function checkExits($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if ($this->id != null) {
                    $partner_card = Tables::selectOneDataTable("partner_card", ["id = :id", "id" => $this->id]);
                    if ($partner_card != null) {
                        if ($partner_card['code'] != $this->code) {
                            $partner_card_code = Tables::selectOneDataTable("partner_card", ["code = :code", "code" => $this->code]);
                            if ($partner_card_code != null) {
                                $this->addError($attribute, 'Mã đối tác đã tồn tại.');
                            }
                        }
                    }
                } else {
                    $partner_card_code = Tables::selectOneDataTable("partner_card", ["code = :code", "code" => $this->code]);
                    if ($partner_card_code != null) {
                        $this->addError($attribute, 'Mã đối tác đã tồn tại.');
                    }
                }
                break;
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if (!Validation::checkContractCode($this->code)) {
                    $this->addError($attribute, 'Mã đối tác không hợp lệ.');
                }
                break;

        }
    }

} 