<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\models\business\CheckoutOrderBusiness;
use common\components\utils\Logs;


/**
 * This is the model class for table "checkout_order_callback".
 *
 * @property integer $id
 * @property integer $checkout_order_id
 * @property string $notify_url
 * @property integer $time_process
 * @property integer $number_process
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property mixed|null $email_send
 */
class CheckoutOrderEmail extends \yii\db\ActiveRecord {

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_SUCCESS = 4;
    const MAX_NUMBER_PROCESS = 3;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'checkout_order_email';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['checkout_order_id', 'time_process', 'status'], 'required'],
            [['checkout_order_id', 'time_process', 'status', 'time_created', 'time_updated'], 'integer'],
            [['email_send'], 'string'],
            [['checkout_order_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'checkout_order_id' => 'Checkout Order ID',
            'email_send' => 'Email gửi',
            'time_process' => 'Time Process',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus() {
        return array(
            self::STATUS_NEW => 'Chưa gọi',
            self::STATUS_PROCESSING => 'Đang gọi',
            self::STATUS_ERROR => 'Lỗi',
            self::STATUS_SUCCESS => 'Đã gọi',
        );
    }





    private static function _writeLog($data) {


        $file_name = 'checkout_order_email' .DS. date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
