<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserLoginUpdateForm extends LanguageBasicForm
{
    public $id;
    public $fullname;
    public $gender;
    public $birthday;

    public function rules()
    {
        return [
            [['fullname'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['id', 'gender'], 'integer'],
            [['birthday'], 'safe'],
            [['birthday'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Họ và tên',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính'
        ];
    }

} 