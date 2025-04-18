<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "news_image_content".
 *
 * @property integer $id
 * @property integer $news_id
 * @property string $image_source
 * @property string $image
 * @property integer $status
 * @property integer $time_created
 */
class NewsImageContent extends \yii\db\ActiveRecord
{
    const STATUS_DOWNLOAD = 2;
    const STATUS_NOT_DOWNLOAD = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_image_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'status'], 'required'],
            [['news_id', 'status', 'time_created'], 'integer'],
            [['image_source', 'image'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_id' => 'News ID',
            'image_source' => 'Image Source',
            'image' => 'Image',
            'status' => 'Status',
            'time_created' => 'Time Created',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_DOWNLOAD => 'Chưa download ảnh',
            self::STATUS_NOT_DOWNLOAD => 'Đã download ảnh',
        );
    }
}
