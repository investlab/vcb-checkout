<?php

namespace common\models\db;

use common\models\db\Merchant;
use common\models\db\MyActiveRecord;
use common\models\db\UserLogin;
use Yii;
use common\components\libs\Tables;
use yii\base\BaseObject;
use yii\db\Query;
/**
 * @property string|null $import_from_excel
 * @property string|null $code
 * @property string|null $currency
 * @property integer|null $amount
 * @property string|null $language
 * @property string|null $object_name
 * @property string|null $object_code
 * @property string|null $order_description
 * @property string|null $time_limited
 * @property integer|null $time_limit
 * @property string|null $merchant_site_code
 * @property string|null $merchant_key
 * @property integer|null $status
 * @property string|null $buyer_fullname
 * @property string|null $buyer_email
 * @property string|null $buyer_mobile
 * @property string|null $buyer_address
 */


class PaymentLink extends MyActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_PAYING = 2;
    const STATUS_PAID = 3;
    const STATUS_CANCEL = 4;
    const STATUS_AUTHORIZE = 5;
    const STATUS_FAILURE = 12;

    public $import_from_excel;

    public static function tableName()
    {
        return 'payment-link';
    }

    public function rules()
    {
        return [
            [['amount'], 'number'],
            [['import_from_excel'], 'safe'],
            [['import_from_excel'], 'file', 'extensions' => 'xlsx,xls',
                'mimeTypes' => [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'wrongMimeType' => \Yii::t('app', 'Chỉ chấp nhận file .xlsx .xls'),
                'checkExtensionByMimeType' => false,
                'skipOnEmpty' => true]
        ];
    }

    public function attributeLabels()
    {
        return [
            'object_name' => 'Tên định danh',
            'object_code' => 'Mã định danh',
            'language' => 'Ngôn ngữ',
            'time_limited' => 'Hạn thanh toán',
            'amount' => 'Số tiền',
            'order_description' => 'Ghi chú',
            'verifyCode' => 'Mã bảo mật',
            'import_from_excel' => 'Nhập file danh sách thông tin link thanh toán',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Chưa thanh toán',
            self::STATUS_PAYING => 'Đang thanh toán',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_CANCEL => 'Đã hủy',
            self::STATUS_FAILURE => 'Thất bại',
        );
    }

    public static function add($request){
        $model = new PaymentLink();
        $model->amount = $request["amount"];
        $model->language = $request["language"];
        $model->object_name = $request["object_name"];
        $model->object_code = $request["object_code"];
        $model->order_description = $request["order_description"];
        $model->time_limited = $request["time_limit"];
        $model->currency = $request["currency"];
        $model->code = $request["order_code"];
        $model->status = self::STATUS_NEW;
        $model->time_limit = $request["time_limited"] + time();
        $model->merchant_site_code = $request["merchant_site_code"];
        $model->merchant_key = $request["merchant_key"];
        $model->buyer_address = $request["buyer_address"];
        $model->buyer_fullname = isset($request["buyer_fullname"]) ? $request["buyer_fullname"] : '';
        $model->buyer_email = isset($request["buyer_email"]) ? $request["buyer_email"] : '';
        $model->buyer_mobile = isset($request["buyer_mobile"]) ? $request["buyer_mobile"] : '';
        $model->time_created = time();
        $model->time_updated = time();
        return $model->save();
    }

    public static function addV2($request){
        $model = new PaymentLink();
        $model->amount = $request["amount"];
        $model->language = $request["language"];
        $model->object_name = $request["object_name"];
        $model->object_code = $request["object_code"];
        $model->order_description = $request["order_description"];
        $model->time_limited = $request["time_limit"];
        $model->currency = $request["currency"];
        $model->code = $request["order_code"];
        $model->status = self::STATUS_NEW;
        $model->time_limit = $request["time_limited"] + time();
        $model->merchant_site_code = $request["merchant_site_code"];
        $model->merchant_key = $request["merchant_key"];
        $model->buyer_address = $request["buyer_address"];
        $model->buyer_fullname = isset($request["buyer_fullname"]) ? $request["buyer_fullname"] : '';

        $id = (\Yii::$app->user->id);
        $mc_id = UserLogin::findOne(['id'=>$id])['merchant_id'];
        $mc = Merchant::findOne(['id'=>$mc_id]);
        $mc_email_notification = !empty($mc["email_notification"]) ? $mc["email_notification"] : 'quangnt@nganluong.vn';
        $model->buyer_email = isset($request["buyer_email"]) ? $request["buyer_email"] : $mc_email_notification;
        $model->buyer_mobile = isset($request["buyer_mobile"]) ? $request["buyer_mobile"] : '';
        $model->time_created = time();
        $model->time_updated = time();
        return $model->save();
    }

    public static function updateLink($link,$code){
        $model = PaymentLink::findOne(['code'=>$code]);
        $model["link"] = $link;
        $model->save();
    }

}