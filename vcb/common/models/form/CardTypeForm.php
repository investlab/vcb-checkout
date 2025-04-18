<?php


namespace common\models\form;


use common\components\libs\Tables;
use common\components\utils\Validation;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CardTypeForm extends LanguageBasicForm
{
    public $id;
    public $code;
    public $name;

    public function rules()
    {
        return [
            [['code', 'name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id'], 'integer'],
            [['code'], 'string', 'max' => 50],
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
            'code' => 'Mã loại thẻ',
            'name' => 'Tên loại thẻ',
        ];
    }

    public function checkExits($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if ($this->id != null) {
                    $card_type = Tables::selectOneDataTable("card_type", ["id = :id", "id" => $this->id]);
                    if ($card_type != null) {
                        if ($card_type['code'] != $this->code) {
                            $card_type_code = Tables::selectOneDataTable("card_type", ["code = :code", "code" => $this->code]);
                            if ($card_type_code != null) {
                                $this->addError($attribute, 'Mã loại thẻ đã tồn tại.');
                            }
                        }
                    }
                } else {
                    $card_type_code = Tables::selectOneDataTable("card_type", ["code = :code", "code" => $this->code]);
                    if ($card_type_code != null) {
                        $this->addError($attribute, 'Mã loại thẻ đã tồn tại.');
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
                    $this->addError($attribute, 'Mã loại thẻ không hợp lệ.');
                }
                break;

        }
    }

} 