<?php

namespace common\models\form;


use common\models\db\Zone;
use Yii;

class WardsUpdateForm extends Zone
{
    public $id;
    public $name;
    public $position;
    public $remote;
    public $city_id;
    public $parent_id;

    public function rules()
    {
        return [
            [['name', 'position'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['city_id', 'parent_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'],
            [['id', 'position'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên',
            'position' => 'Vị trí',
            'remote' => 'Remote',
            'city_id' => 'Tỉnh - Thành Phố',
            'parent_id' => 'Quận - Huyện',
        ];
    }

    public function beforeSave($insert)
    {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->_updateIndexCategory($this->tableName());
    }
} 