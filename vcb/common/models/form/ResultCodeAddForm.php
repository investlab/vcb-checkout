<?php


namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\db\ResultCode;
use common\models\db\ResultCodeLanguage;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use common\components\libs\Tables;

class ResultCodeAddForm extends LanguageBasicForm
{
    public $content;
    public $language_id;
    public $status;

    public function rules()
    {
        return [
            [['language_id', 'content'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['language_id'], 'integer'],
            [['content'], 'checkExits']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã ngân hàng',
            'trade_name' => 'Tên thương mại',
            'name' => 'Tên đầy đủ',
            'description' => 'Ghi chú',
            'status' => 'Trạng thái'
        ];
    }

    public function checkExits($attribute, $param)
    {
        $result_code_language_info = Tables::selectOneDataTable("result_code_language", ["language_id = :language_id AND content = :content ", "language_id" => $this->language_id, "content" => $this->$attribute]);
        if ($result_code_language_info != false) {
            $this->addError($attribute, 'Đã tồn tại mã trả về ' . $result_code_language_info['result_code'] . ' ');
        }
    }
}