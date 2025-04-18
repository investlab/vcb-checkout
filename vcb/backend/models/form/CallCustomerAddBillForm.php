<?php

namespace backend\models\form;

use common\models\db\Bill;
use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\models\db\Zone;
use common\models\db\Product;
use common\models\db\CallHistory;
use common\models\business\BillBusiness;
use common\models\business\CallHistoryBusiness;
use common\components\libs\Tables;
use common\components\utils\Validation;

class CallCustomerAddBillForm extends CallCustomerBasicForm {

    public $title = 'Đơn hàng';
    public $buyer_fullname = null;
    public $buyer_mobile = null;
    public $buyer_email = null;
    public $buyer_address = null;
    public $buyer_city_id = null;
    public $buyer_district_id = null;
    public $buyer_zone_id = null;
    public $buyer_note = null;
    public $product_id = null;
    public $product_quantity = 1;
    public $gift_code;

    public function rules() {
        return [
            [['buyer_fullname', 'buyer_mobile', 'buyer_address', 'buyer_zone_id'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['buyer_city_id', 'buyer_zone_id', 'buyer_district_id'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['buyer_fullname', 'buyer_address', 'buyer_note'], 'string', 'message' => '{attribute} không hợp lệ.'],
            [['buyer_zone_id'], 'isZoneId'],
            [['buyer_mobile'], 'isMobile'],
            [['buyer_email'], 'isEmail'],
            [['gift_code', 'product_id', 'product_quantity'], 'safe'],
        ];
    }

    public function attributeLabels() {
        return [
            'buyer_fullname' => 'Họ tên người nhận',
            'buyer_mobile' => 'Số điện thoại',
            'buyer_email' => 'Email',
            'buyer_address' => 'Địa chỉ người nhận',
            'buyer_city_id' => 'Tỉnh/Thành phố',
            'buyer_district_id' => 'Quận/Huyện',
            'buyer_zone_id' => 'Phường/Xã',
            'buyer_note' => 'Ghi chú',
            'product_id' => 'Sản phẩm',
            'product_quantity' => 'Số lượng',
            'gift_code' => 'Mã giảm giá',
        ];
    }

    protected function _afterLoadRequest() {
        $this->buyer_fullname = $this->customer_info['name'];
        $this->buyer_email = $this->customer_info['email'];
        $this->buyer_mobile = $this->customer_info['mobile'];
        if (intval($this->customer_info['zone_id']) != 0) {
            $this->buyer_district_id = Zone::getDistrictId($this->customer_info['zone_id']);
        }
        if (intval($this->buyer_district_id) != 0) {
            $this->buyer_city_id = Zone::getCityId($this->buyer_district_id);
        }
        $this->buyer_zone_id = $this->customer_info['zone_id'];
        $this->buyer_address = $this->customer_info['address'];
    }

    public function getProduct() {
        if (isset($this->product_id) && !empty($this->product_id)) {
            $ids = is_array($this->product_id) ? $this->product_id : array($this->product_id);
            $result = Tables::selectAllDataTable("product", ["id IN(:ids)", 'ids' => $ids]);
            if ($result != false) {
                return $product_info = Product::setRows($result);
            }
        }
        return false;
    }

    public function getCities() {
        return Zone::getCities($this->buyer_city_id);
    }

    public function getDistricts() {
        return Zone::getDistricts($this->buyer_city_id, $this->buyer_district_id);
    }

    public function getZones() {
        return Zone::getZones($this->buyer_district_id, $this->buyer_zone_id);
    }

    public function isZoneId($attribute, $params) {
        if (intval($this->$attribute) == 0) {
            $this->addError($attribute, 'Quận huyện chưa chọn');
        } else {
            $zone_info = Tables::selectOneDataTable("zone", "id = " . intval($this->$attribute));
            if ($zone_info == false) {
                $this->addError($attribute, 'Quận huyện không hợp lệ');
            }
        }
    }

    public function isMobile($attribute, $params) {
        if (!Validation::isMobile($this->$attribute)) {
            $this->addError($attribute, 'Số điện thoại không hợp lệ');
        }
    }

    public function isEmail($attribute, $params) {
        if (!Validation::isEmail($this->$attribute)) {
            $this->addError($attribute, 'Email không hợp lệ');
        }
    }

    public function submit() {

        if ($this->call_history_info != false && $this->call_history_info['status'] == CallHistory::STATUS_CALLED) {
            $inputs = array(
                'id' => $this->call_history_info['id'],
                'buyer_fullname' => $this->buyer_fullname,
                'buyer_mobile' => $this->buyer_mobile,
                'buyer_email' => $this->buyer_email,
                'buyer_address' => $this->buyer_address,
                'buyer_zone_id' => $this->buyer_zone_id,
                'buyer_note' => $this->buyer_note,
                'item_id' => $this->product_id,
                'item_quantity' => $this->product_quantity,
                'enviroment' => 'WEB',
                'user_id' => Yii::$app->user->getId(),
                'gift_codes' => $this->gift_code,
            );
            $result = CallHistoryBusiness::addSaleOrder($inputs);
            if ($result['error_message'] == '') {
                $this->addMessage('Thêm đơn hàng thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index', 'option' => $this->key, 'customer_id' => $this->customer_info['id'], 'call_history_id' => @$this->call_history_info['id']]);
                header('Location:' . $url);
                die();
            } else {
                $this->error = $result['error_message'];
            }
        } else {
            $items = [];
            if (isset($this->product_id) && $this->product_id != null) {
                foreach ($this->product_id as $key => $data) {
                    $items[] = array(
                        'item_id' => $data,
                        'item_quantity' => $this->product_quantity[$key]
                    );
                }
                $inputs = array(
                    'buyer_fullname' => @$this->customer_info['name'],
                    'buyer_mobile' => @$this->customer_info['mobile'],
                    'buyer_email' => @$this->customer_info['email'],
                    'buyer_address' => $this->buyer_address,
                    'buyer_zone_id' => $this->buyer_zone_id,
                    'buyer_note' => $this->buyer_note,
                    'receiver_fullname' => $this->buyer_fullname,
                    'receiver_mobile' => $this->buyer_mobile,
                    'receiver_email' => @$this->customer_info['email'],
                    'receiver_address' => $this->buyer_address,
                    'receiver_zone_id' => $this->buyer_zone_id,
                    'items' => $items,
                    'user_id' => Yii::$app->user->getId(),
                    'customer_id' => @$this->customer_info['id']
                );
                if ($this->gift_code != '') {
                    $inputs['gift_codes'] = [
                        0 => $this->gift_code
                    ];
                    $result = BillBusiness::addAndApplyGiftCode($inputs);
                    if ($result['error_message'] == '') {
                        $this->addMessage('Thêm đơn hàng thành công');
                        $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index', 'option' => $this->key, 'customer_id' => $this->customer_info['id'], 'call_history_id' => @$this->call_history_info['id']]);
                        header('Location:' . $url);
                        die();
                    } else {
                        $this->error = $result['error_message'];
                    }
                } else {
                    $result = BillBusiness::add($inputs);
//                    var_dump($result);die;
                    if ($result['error_message'] == '') {
                        $this->addMessage('Thêm đơn hàng thành công');
                        $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index', 'option' => $this->key, 'customer_id' => $this->customer_info['id'], 'call_history_id' => @$this->call_history_info['id']]);
                        header('Location:' . $url);
                        die();
                    } else {
                        $this->error = $result['error_message'];
                    }
                }
            } else {
                $this->error = "Chưa chọn sản phẩm";
            }
//            $result = BillBusiness::add($inputs);
        }
//        if ($result['error_message'] == '') {
//            $this->addMessage('Thêm đơn hàng thành công');
//            $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index', 'option' => $this->key, 'customer_id' => $this->customer_info['id'], 'call_history_id' => @$this->call_history_info['id']]);
//            header('Location:' . $url);
//            die();
//        } else {
//            $this->error = $result['error_message'];
//        }
    }

}
