<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class BankForm extends LanguageBasicForm
{
    public $id;
    public $name;
    public $trade_name;
    public $code;
    public $description;
    public $status;
    public $time_created;
    public $time_updated;
    public $user_created;
    public $user_updated;

    public function rules()
    {
        return [
            [['description'], 'string'],
            [['id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'trade_name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên ngân hàng',
            'trade_name' => 'Tên viết tắt',
            'code' => 'Mã ngân hàng',
            'description' => 'Ghi chú',
            'status' => 'Trạng thái',
            'time_created' => 'Ngày tạo',
            'time_updated' => 'Ngày cập nhật',
            'user_created' => 'Người tạo',
            'user_updated' => 'Người cập nhật',
        ];
    }
} 