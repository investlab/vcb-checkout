<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property integer $portal_id
 * @property string $name
 * @property integer $status
 * @property integer $default
 */
class Page extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['portal_id', 'status', 'default'], 'integer'],
            [['name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'portal_id' => 'Portal ID',
            'name' => 'Name',
            'status' => 'Status',
            'default' => 'Default',
        ];
    }
}
