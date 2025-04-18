<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "result_code".
 *
 * @property integer $id
 * @property string $code
 * @property string $description
 * @property integer $time_created
 * @property integer $time_updated
 */
class ResultCode extends \yii\db\ActiveRecord
{
    public static $contents = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'result_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['description'], 'string'],
            [['time_created', 'time_updated'], 'integer'],
            [['code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'description' => 'Description',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    private static function _getContents($language_code)
    {
        $contents = array();
        $result_code_language_info = Tables::selectAllDataTable("result_code_language", ["language_id = :language_id", "language_id" => Language::getIdByCode($language_code)]);
        if ($result_code_language_info != false) {
            foreach ($result_code_language_info as $row) {
                $contents[$row['result_code']] = $row['content'];
            }
        }
        return $contents;
    }

    public static function getContentByCode($code, $language_code = null)
    {
        if ($language_code === null) {
            $language_code = Yii::$app->language;
        }
        if (self::$contents === null) {
            self::$contents = self::_getContents($language_code);
        }
        if (isset(self::$contents[$code])) {
            return self::$contents[$code];
        } elseif ($language_code != 'vi-VN') {
            $vi_contents = self::_getContents('vi-VN');
            return isset($vi_contents[$code]) ? $vi_contents[$code] : $code;
        }
        return $code;
    }
}
