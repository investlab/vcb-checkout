<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class Language extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'status'], 'required'],
            [['status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getIdByCode($code)
    {
        $language_info = Tables::selectOneDataTable("language", ["code = :code ", "code" => $code]);
        if ($language_info != false) {
            return $language_info['id'];
        }
        return 0;
    }

    public static function getCodeById($id)
    {
        $language_info = Tables::selectOneDataTable("language", ["id = :id ", "id" => $id]);
        if ($language_info != false) {
            return $language_info['code'];
        }
        return 0;
    }
}
