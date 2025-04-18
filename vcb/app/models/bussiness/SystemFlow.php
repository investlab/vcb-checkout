<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 12/6/2019
 * Time: 4:09 PM
 */

namespace app\models\bussiness;
use common\components\utils\Encryption;
use common\models\db\CheckoutOrder;

define('LINK_MUASAMONLINE','https://naipot.com/');
define('LINK_NAPTIENDIENTHOAI','https://thanhtoanonline.vn/nap-the-dien-thoai.html');
define('LINK_LAPQUYTIETKIEM','https://account.nganluong.vn/');
define('LINK_THANHTOANHOADON','https://thanhtoanonline.vn/nap-the-dien-thoai.html');
define('LINK_MUABAOHIEM','https://account.nganluong.vn/');
define('LINK_MUATHECAO','https://account.nganluong.vn/');
define('LINK_BANNER_TOP','');
define('LINK_BANNER_BOTTOM','');
define('LINK_SLIDE','');

class SystemFlow
{

    public function getConfig(){

        $data = array(
            'key_fix' => $GLOBALS['LINK_FIX'],
            'key_pre' => $GLOBALS['ENCRYPT_KEY']
        );
        $link_figuration = self::encryptData($data);
        $data = array(
            'status' => [
                [
                    'code' => '',
                    'name' => 'Tất cả',
                ],[
                    'code' => CheckoutOrder::STATUS_NEW,
                    'name' => 'Chưa thanh toán',
                ],[
                    'code' => CheckoutOrder::STATUS_PAYING,
                    'name' => 'Đang thanh toán',
                ],[
                    'code' => CheckoutOrder::STATUS_PAID,
                    'name' => 'Đã thanh toán',
                ],
                [
                    'code' => CheckoutOrder::STATUS_CANCEL,
                    'name' => 'Đã hủy',
                ],

            ],
            'refund_type' =>  [
                [
                    'code' => 1,
                    'name' => 'Hoàn toàn bộ'
                ],
                [
                    'code' => 2,
                    'name' => 'Hoàn 1 phần'
                ],

            ],
            'link_fix'=> $GLOBALS['LINK_FIX'],
            'link_figuration'=> $link_figuration,
            'link_verify'=> 'VCBNLMC_'.$GLOBALS['ENCRYPT_KEY'],




        );
        return array(
            'error_code' => 0,
            'error_message' => '',
            'response' => $data
        );

    }


    public function encryptData($data) {
        $key = hash('sha256', $GLOBALS['ENCRYPT_KEY']);
        $data = Encryption::EncryptTrippleDes(json_encode($data), $key);
        return $data;
    }
}