<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "result_code_language".
 *
 * @property integer $id
 * @property integer $language_id
 * @property integer $result_code_id
 * @property string $result_code
 * @property string $content
 * @property integer $time_created
 * @property integer $time_updated
 */
class ResultCodeLanguage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'result_code_language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_id', 'result_code_id', 'result_code', 'content'], 'required'],
            [['language_id', 'result_code_id', 'time_created', 'time_updated'], 'integer'],
            [['content', 'result_code'], 'string'],
            [['language_id', 'result_code_id'], 'unique', 'targetAttribute' => ['language_id', 'result_code_id'], 'message' => 'The combination of Language ID and Result Code ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_id' => 'Language ID',
            'result_code_id' => 'Result Code ID',
            'result_code' => 'result_code',
            'content' => 'Content',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }
}
