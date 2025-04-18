<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "checkout_order_callback_history".
 *
 * @property integer $id
 * @property integer $checkout_order_id
 * @property integer $checkout_order_callback_id
 * @property string $notify_url
 * @property integer $status
 * @property integer $time_request
 * @property integer $time_response
 * @property string $response_data
 */
class CheckoutOrderCallbackHistory extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_SUCCESS = 4;
    const MAX_NUMBER_PROCESS = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'checkout_order_callback_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['checkout_order_id', 'checkout_order_callback_id', 'notify_url', 'status', 'time_request'], 'required'],
            [['checkout_order_id', 'checkout_order_callback_id', 'status', 'time_request', 'time_response'], 'integer'],
            [['notify_url', 'response_data'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'checkout_order_id' => 'Checkout Order ID',
            'checkout_order_callback_id' => 'Checkout Order Callback ID',
            'notify_url' => 'Notify Url',
            'status' => 'Status',
            'time_request' => 'Time Request',
            'time_response' => 'Time Response',
            'response_data' => 'Response Data',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Chưa gọi',
            self::STATUS_PROCESSING => 'Đang gọi',
            self::STATUS_ERROR => 'Lỗi',
            self::STATUS_SUCCESS => 'Đã gọi',
        );
    }

    public static function setRow(&$row)
    {
        $checkout_order_id = $row['checkout_order_id'];
        if (intval($checkout_order_id) > 0) {
            $checkout_order = Tables::selectOneDataTable('checkout_order', ['id = :id', 'id' => $checkout_order_id]);
            $row['checkout_order_info'] = CheckoutOrder::setRow($checkout_order);
        }
        return $row;
    }

    public static function setRows(&$rows)
    {
        $checkout_order_ids = array();
        $checkout_orders = array();

        foreach ($rows as $row) {
            if (intval($row['checkout_order_id']) > 0) {
                $checkout_order_ids[$row['checkout_order_id']] = $row['checkout_order_id'];
            }
        }
        if (!empty($checkout_order_ids)) {
            $checkout_orders_info = Tables::selectAllDataTable("checkout_order", "id IN (" . implode(',', $checkout_order_ids) . ") ", "", "id");
            $checkout_orders_callback_info = Tables::selectAllDataTable("checkout_order_callback", "checkout_order_id IN (" . implode(',', $checkout_order_ids) . ") ", "", "checkout_order_id");
            $checkout_orders = CheckoutOrder::setRows($checkout_orders_info);
        }
        foreach ($rows as $key => $row) {
            $rows[$key]['checkout_order_info'] = @$checkout_orders[$row['checkout_order_id']];
            $rows[$key]['checkout_orders_callback_info'] = @$checkout_orders_callback_info[$row['checkout_order_id']];
        }
        return $rows;
    }
}
