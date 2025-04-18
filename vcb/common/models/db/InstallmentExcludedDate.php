<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "installment_excluded_date".
 *
 * @property int $id
 * @property string $method
 * @property string $bank_code
 * @property string $bin
 * @property string $excluded_date
 * @property string $status
 * @property string $message
 * @property string $created_at
 * @property string $apply_from
 * @property string $expired_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class InstallmentExcludedDate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'installment_excluded_date';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['method', 'bank_code', 'excluded_date', 'status', 'message', 'apply_from', 'expired_at'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            //[['method'], 'string', 'max' => 16],
            [['bank_code'], 'string', 'max' => 10],
            [['excluded_date', 'message'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'method' => 'Phương thức thanh toán',
            'bank_code' => 'Mã ngân hàng',
            'bin' => 'BIN',
            'excluded_date' => 'Ngày từ chối trả góp',
            'status' => 'Trạng thái',
            'message' => 'Thông báo',
            'created_at' => 'Ngày tạo',
            'apply_from' => 'Ngày áp dụng',
            'expired_at' => 'Ngày hết hạn',
            'updated_at' => 'Ngày cập nhật',
            'deleted_at' => 'Ngày xóa',
        ];
    }

    /**
     * {@inheritdoc}
     * @return InstallmentExcludedDateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InstallmentExcludedDateQuery(get_called_class());
    }

    public function methodDisplay()
    {
        $data = InstallmentExcludedDate::findOne($this->id);
        if ($data->method != null) {
            $method = json_decode($data->method, true);
            $html = '';
            if (!empty($method)) {
                foreach ($method as $key => $item) {
                    $html .= '<a class="label label-success">' . $item . '</a>  ';
                }
            } else {
                $html = '';
            }

            return $html;
        }
        return null;

    }

    public function binDisplay()
    {
        $data = InstallmentExcludedDate::findOne($this->id);
        if ($data->bin != null) {
            $bin = json_decode($data->bin, true);
            $html = '';
            if (is_array($bin)) {
                foreach ($bin as $key => $item) {
                    $html .= '<a class="label label-success">' . $item . '</a>  ';
                }
                return $html;
            }

        }
        return null;

    }

    public function excludedDateDisplay()
    {
        $data = InstallmentExcludedDate::findOne($this->id);
        if ($data->excluded_date != null) {
            $excluded_date = $data->excluded_date;
            $excluded_date = explode(',', $excluded_date);
            $html = '';
            foreach ($excluded_date as $key => $item) {
                $html .= '<a class="label label-success">' . $item . '</a>  ';
            }
            return $html;
        }
        return null;
    }

    public function statusDisplay()
    {
        $data = InstallmentExcludedDate::findOne($this->id);
        $status = $data->status;
        switch ($status) {
            case 0:
                return '<a class="label label-success">Đang hoạt động</a>';
            case 1:
                return '<a class="label label-danger">Đang khóa</a>';
            case 2:
                return '<a class="label label-warning">Chưa áp dụng</a>';
            case 3:
                return '<a class="label label-dark">Đã hết hạn</a>';


        }

    }
}
