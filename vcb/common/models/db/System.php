<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "system".
 *
 * @property integer $id
 * @property string $title
 * @property string $keyword
 * @property string $description
 * @property string $footer
 */
class System extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'keyword', 'description', 'footer'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'keyword' => 'Keyword',
            'description' => 'Description',
            'footer' => 'Footer',
        ];
    }
}
