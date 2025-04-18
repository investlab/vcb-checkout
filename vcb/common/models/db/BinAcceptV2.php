<?php

namespace common\models\db;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bin_accept_v2".
 *
 * @property int $id
 * @property int $code
 * @property string $card_type
 * @property string $bank_code
 * @property int $status
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class BinAcceptV2 extends \yii\db\ActiveRecord
{

    const TYPE_CREDIT = 0;
    const TYPE_DEBIT = 1;

    const STATUS_ACTIVE = 0;
    const STATUS_IN_ACTIVE = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bin_accept_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'card_type', 'bank_code', 'status'], 'required'],
            [['code', 'status'], 'integer'],
            [['code'], 'unique'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['card_type'], 'string', 'max' => 10],
            [['bank_code'], 'string', 'max' => 12],
            [['import_from_excel'], 'file', 'extensions' => 'xls, xlsx'],


        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã bin',
            'card_type' => 'Loại thẻ',
            'bank_code' => 'Mã ngân hàng',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'deleted_at' => 'Ngày xóa',
        ];
    }

    /**
     * {@inheritdoc}
     * @return BinAcceptV2Query the active query used by this AR class.
     */
    public static function find()
    {
        return new BinAcceptV2Query(get_called_class());
    }

    public function status()
    {
        $data = BinAcceptV2::findOne($this->id);
        switch ($data->status) {
            case 1:
                return '<span class="label label-danger">Đang khóa</span>';
            case 0:
                return '<span class="label label-success">Đang hoạt động</span>';
            default:
                return '<span class="label label-warning">Unknown</span>';
        }
    }

    public function time()
    {
        $data = BinAcceptV2::findOne($this->id);
        $created_at = $data->created_at;
        $updated_at = $data->updated_at;
        return [$created_at, $updated_at];
    }

    public function behaviors()
    {
        parent::behaviors();

        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at']
                ],
                'value' => date('Y-m-d H:i:s'),
            ]
        ];
    }
}
