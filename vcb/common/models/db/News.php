<?php

namespace common\models\db;

use Yii;
use common\components\utils\Strings;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property integer $news_category_id
 * @property Strings $title
 * @property Strings $description
 * @property Strings $content
 * @property Strings $image
 * @property integer $time_publish
 * @property integer $rewrite_rule_id
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class News extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_category_id', 'title', 'status'], 'required'],
            [['news_category_id', 'time_publish', 'rewrite_rule_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['description', 'content'], 'string'],
            [['title', 'image'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_category_id' => 'News Category ID',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'image' => 'Image',
            'time_publish' => 'Time Publish',
            'rewrite_rule_id' => 'Rewrite Rule ID',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function setRow(&$row)
    {
        $row['title'] = Strings::strip($row['title']);
        $row['description'] = Strings::strip($row['description']);
        $row['content'] = Strings::strip($row['content']);
        if (trim($row['image']) != '' && file_exists(IMAGES_NEWS_PATH . $row['image'])) {
            $row['image'] = IMAGES_NEWS_URL . $row['image'];
        } else {
            $row['image'] = '';
        }
        return $row;
    }
}
