<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 13/11/2017
 * Time: 10:52 SA
 */
namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\Bill;

class BillUpdateSaleChannelForm extends Bill {

    public $sale_channel_id = null;

    public function rules() {
        return [
            [['sale_channel_id'], 'integer', 'message' => '{attribute} không hợp lệ']
        ];
    }

    public function attributeLabels() {
        return array(
            'sale_channel_id' => 'Kênh bán hàng',
        );
    }
}