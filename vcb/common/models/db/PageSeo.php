<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "page_seo".
 *
 * @property integer $id
 * @property integer $page_id
 * @property string $title
 * @property string $keyword
 * @property string $description
 * @property string $tags
 * @property string $metas
 * @property string $links
 * @property integer $time_updated
 */
class PageSeo extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page_seo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'time_updated'], 'integer'],
            [['keyword', 'description', 'tags', 'metas', 'links'], 'string'],
            [['title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'page_id' => 'Page ID',
            'title' => 'Title',
            'keyword' => 'Keyword',
            'description' => 'Description',
            'tags' => 'Tags',
            'metas' => 'Metas',
            'links' => 'Links',
            'time_updated' => 'Time Updated',
        ];
    }
}
