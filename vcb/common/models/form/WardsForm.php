<?php

namespace common\models\form;


use common\components\libs\Tables;
use common\models\db\Zone;
use yii\base\Model;
use Yii;

class WardsForm extends Zone
{

    public $id;
    public $name;
    public $position;
    public $remote;
    public $city_id;
    public $district_id;

    public function rules()
    {
        return [
            [['name', 'position'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['city_id', 'district_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'],
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
            'district_id' => 'Quận - Huyện',
        ];
    }

    public function beforeSave($insert)
    {
        $this->left = 1;
        $this->right = 1;
        $this->zone_id = 0;
        $this->level = $this->_getLevel($this->parent_id);
        $this->time_created = time();
        $this->user_created = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->_updateIndexCategory($this->tableName());
    }

    protected function _getLevel($parent_id)
    {
        if ($parent_id != 0) {
            $result = Tables::selectOneDataTable("zone", "id = $parent_id ");
            if ($result != false) {
                return $result['level'] + 1;
            }
        } else {
            return 1;
        }
    }


}