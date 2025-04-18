<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserAdminAccountForm extends LanguageBasicForm
{
    public $id;
    public $user_group_id;
    public $user_id;
    public $name;
    public $status;

    public function rules()
    {
        return [
            [['name'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['user_group_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'user_id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên tài khoản',
            'user_group_id' => 'Nhóm quyền',
            'user_id' => 'Mã user',
            'status' => 'Trạng thái',
        ];
    }
}