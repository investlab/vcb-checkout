<?php

/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 10/24/2019
 * Time: 2:30 PM
 */

namespace common\partner_payments;

use common\partner_payments\PartnerPaymentBasic;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\Alepay;
use common\payments\NganLuong;
use common\models\db\Transaction;
use common\components\libs\Weblib;
use common\components\libs\Tables;
use Yii;
use common\components\utils\Translate;

class PartnerPaymentAlepay extends PartnerPaymentBasic {


    public $alepay_bank_code = array(
        'STB' => 'Sacombank',
        'TCB' => 'Techcombank',
        'BIDV' => 'BIDV',
        'HSBC' => 'HSBC',
        'SC' => 'SC',
        'FE' => 'FE',
        'CTB' => 'CTB',
    );

    public function initRequest(PaymentMethodBasicForm &$form) {


    }

    /**
     *
     * @param \common\payment_methods\nganluong_seamless\PaymentMethodAtmCardNganluongSeamlessForm $form
     * @param type $params : transaction_id, transaction_amount, transaction_info, card_fullname, card_number, card_month, card_year
     * @return type
     */
    public function processRequest(PaymentMethodBasicForm &$form, $params) {
        $error_message = 'Lỗi không xác định';
        $payment_url = null;
        //------------
        $card_request = Yii::$app->request->post('card_info');

        if (!empty($card_request)){
            $card_info =  explode('-',Yii::$app->request->post('card_info'));
            $month = $card_info[1];
            $paymentMethod = $card_info[0];
        }else{
            $month = '';
            $paymentMethod = '';
        }
        $payment_info = Alepay::getPaymentMethodAndBankCode($form->payment_method_code);
        $inputs = array(
            'orderCode' => $GLOBALS['PREFIX'].$params['transaction_id'],
            'amount' => number_format($params['transaction_amount'], 1, '.', ''),
            'currency' => 'VND',
            'orderDescription' => 'Thanh toán giao dịch ' . $params['transaction_id'] . ' cho đơn hàng ' . $form->checkout_order['order_code'],
            'totalItem' => 1,
            'checkoutType' => 2,
            'installment' => true,
            'month' => $month,
            'bankCode' => $payment_info['bank_code'],
            'paymentMethod' => $paymentMethod,
            'returnUrl' => $form->_getUrlConfirmVerify($params['transaction_id']),
            'cancelUrl' => $form->_getUrlCancel(),
            'buyerName' => $form->checkout_order['buyer_fullname'],
            'buyerEmail' => $form->checkout_order['buyer_email'],
            'buyerPhone' => $form->checkout_order['buyer_mobile'],
            'buyerAddress' => $form->checkout_order['buyer_address'],
            'buyerCity' => 'Ha Noi',
            'buyerCountry' => 'Viet Nam',
            'paymentHours' => 48,
            'promotionCode' => '',
            'allowDomestic' => false,
            'language' => (Yii::$app->language != 'vi-VN' ? 'en' : 'vi'),
            'partner_payment_code' => $form->partner_payment_code
        );

        $response = Alepay::checkout($inputs,$form['checkout_order']['merchant_id'],$form ["info"]["partner_payment_id"]);




        if ($response['errorCode'] == '000') {
            $error_message = '';
            $payment_url = $this->_getPaymentUrlLanguage($response['data']['checkoutUrl']);
        } else {
            $error_message = $this->convertMessage($response['errorCode']);
        }
        return array('error_message' => $error_message, 'response' => $response, 'payment_url' => $payment_url);


    }

    private function _getPaymentUrlLanguage($payment_url)
    {
        return $payment_url;
    }


    function initVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            return true;
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }

    public function processVerify(PaymentMethodBasicForm &$form, $params) {
        $error_message = 'Lỗi không xác định';
        $bank_refer_code = null;
        //------------
        NganLuongSeamless::$receiver_email = $this->getPartnerPaymentAccount($form->payment_transaction);
        $payment_info = NganLuongSeamless::getPaymentMethodAndBankCode($form->payment_method_code);
        $inputs = array(
            'receiver_email' => NganLuongSeamless::$receiver_email,
            'token' => $form->payment_transaction['partner_payment_method_refer_code'],
            'otp' => $form->otp,
            'auth_url' => @$form->payment_transaction['partner_payment_info']['auth_url'],
        );
        $response = NganLuongSeamless::authenTransaction($inputs);
        if ($response['error_code'] == '00') {
            $response = NganLuongSeamless::getTransactionDetail($form->payment_transaction['partner_payment_method_refer_code']);
            if (NganLuongSeamless::verifyResponse($response, $form->payment_transaction, $error_message)) {
                $error_message = '';
                $bank_refer_code = @$response['transaction_id'];
            }
        } else {
            if ($response['error_code'] == '98' && trim(@$response['description']) != '') {
                $error_message = @$response['description'];
            } else {
                $error_message = NganLuongSeamless::getErrorMessage($response['error_code']);
            }
        }
        return array('error_message' => $error_message, 'bank_refer_code' => $bank_refer_code);
    }

    public  function getCardAccept(PaymentMethodBasicForm &$form,$params){
        $arr_card_config = array();
        $arr_card_alepay = array();
        $arr_alepay_config = array();
        $arr_card_type_cycle =array();
        $arr_card_type_config =array();
        $installment = Tables::selectAllDataTable('installment_config','merchant_id=' . $params['merchant_id'] . ' AND status=' . ACTIVE_STATUS);

        if (!empty($installment)) {
            $installment_card = json_decode($installment[0]['card_accept'],true);
            foreach ($installment_card as $key_card => $card) {
                array_push($arr_card_type_config,$card);

            }

            $installment_cycle = json_decode($installment[0]['cycle_accept'],true);
            foreach ($installment_cycle as $key_cycle => $cycle) {
                array_push($arr_card_type_cycle,$cycle);

            }
            foreach ($arr_card_type_config[0] as $item){
                if (!empty($arr_card_type_cycle[0])) {
                    foreach ($arr_card_type_cycle[0] as $item1){
                        $item_config = $item.'-'.key($item1);
                        array_push($arr_card_config,$item_config);
                    }
                }
            }

        }

        $alepay_config = Alepay::getInstallmentInfo($params,$form['checkout_order']['merchant_id'],$form ["info"]["partner_payment_id"]);

        if ( $alepay_config){
            foreach ($alepay_config as $item_alepay){

                if (!empty($item_alepay['bankCode']) && $item_alepay['bankCode'] == strtoupper($this->convertBankCode($params['bank_code']))){
                    array_push($arr_alepay_config,$item_alepay);
                }

            }
        }
        if (!empty($arr_alepay_config) && $arr_alepay_config[0]['paymentMethods']){
            foreach ($arr_alepay_config[0]['paymentMethods'] as $item){
                if ($item['periods']){
                    foreach ($item['periods'] as $item1){
                        array_push($arr_card_alepay,$item['paymentMethod'].'-'.$item1['month']);
                    }
                }
            }
        }

        $cardInfo = array_unique(array_intersect($arr_card_config,$arr_card_alepay));
        ksort($cardInfo,SORT_STRING  );
        return $cardInfo;

    }

    function initConfirmVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        $transactionCode = ObjInput::get('transactionCode', 'str', '');


        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            $inputs = [
                'transactionCode' => $transactionCode,
                'partner_payment_code' => $form->partner_payment_code,
            ];
            $result = Alepay::getTransactionDetail($inputs,$form['checkout_order']['merchant_id'],$form ["info"]["partner_payment_id"]);
            $trans_status = $result['data']['status'];
            if (Alepay::verifyResponse($result, $form->payment_transaction, $error_message) ) {

                if (intval($form->payment_transaction['status']) == Transaction::STATUS_PAYING ||intval($form->payment_transaction['status']) == Transaction::STATUS_NEW) {
                    $inputs = array(
                        'transaction_id' => $form->payment_transaction['id'],
                        'transaction_type_id' => 5,
                        'bank_refer_code' => @$result['transaction_id'],
                        'time_paid' => time(),
                        'user_id' => 0,
                        'month' => $result['data']["month"],
                        'payment_info' => json_encode($result['data']),
                    );
                    if ($trans_status == 150) {
                        $result = TransactionBusiness::review($inputs);
                    } else {
                        $result = TransactionBusiness::paid($inputs);
                    }


                    if ($result['error_message'] === '') {
                        header('Location:' . $form->_getUrlSuccess($form->payment_transaction['id']));
                        die();
                    } else {
                        $form->error_message = $result['error_message'];
                    }
                } elseif ($form->payment_transaction['status'] == Transaction::STATUS_PAID) {
                    header('Location:' . $form->_getUrlSuccess($form->payment_transaction['id']));
                    die();
                } else {
                    $form->error_message = 'Đơn hàng không hợp lệ';
                }
            } else {
                $form->error_message = $error_message;
            }
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }
    public function convertBankCode($bank_code){
        foreach ($this->alepay_bank_code as $key => $value){
            if ($key == $bank_code){
                return $value;
            }
        }


    }

    public function convertMessage($error_code)
    {
        $arr_error_message = [
            '101' => "Checksum không hợp lệ",
            '102' => "Mã hóa không hợp lệ",
            '103' => "IP không được phép truy cập",
            '104' => "Dữ liệu không hợp lệ",
            '105' => "Token key không hợp lệ",
            '106' => "Token thanh toán Alepay không tồn tại hoặc đã bị hủy",
            '107' => "Giao dịch đang được xử lý",
            '108' => "Dữ liệu không tìm thấy",
            '109' => "Mã đơn hàng không tìm thấy",
            '110' => "Phải có email hoặc số điện thoại người mua",
            '111' => "Giao dịch thất bại",
            '120' => "Giá trị đơn hàng phải lớn hơn 0",
            '121' => "Loại tiền tệ không hợp lệ",
            '122' => "Mô tả đơn hàng không tìm thấy",
            '123' => "Tổng số sản phẩm phải lớn hơn không",
            '124' => "Định dạng URL không chính xác (http://, https://)",
            '125' => "Tên người mua không đúng định dạng",
            '126' => "Email người mua không đúng định dạng",
            '127' => "SĐT người mua không đúng định dạng",
            '128' => "Địa chỉ người mua không hợp lệ",
            '129' => "City người mua không hợp lệ",
            '130' => "quốc gia người mua không hợp lệ",
            '131' => "hạn thanh toán phải lớn hơn 0",
            '132' => "Email không hợp lệ",
            '133' => "Thông tin thẻ không hợp lệ",
            '134' => "Thẻ hết hạn mức thanh toán",
            '135' => "Giao dịch bị từ chối bởi ngân hàng phát hành thẻ",
            '136' => "Mã giao dịch không tồn tại",
            '137' => "Giao dịch không hợp lệ",
            '138' => "Tài khoản Merchant không tồn tại",
            '139' => "Tài khoản Merchant không hoạt động",
            '140' => "Tài khoản Merchant không hợp lệ",
            '142' => "Ngân hàng không hỗ trợ trả góp",
            '143' => "Thẻ không được phát hành bởi ngân hàng đã chọn",
            '144' => "Kỳ thanh toán không hợp lệ",
            '145' => "Số tiền giao dịch trả góp không hợp lệ",
            '146' => "Thẻ của bạn không thuộc ngân hang hỗ trợ trả góp",
            '147' => "Số điện thoại không hợp lệ",
            '148' => "Thông tin trả góp không hợp lệ",
            '149' => "Loại thẻ không hợp lệ",
            '150' => "Thẻ bị review",
            '150' => "Thẻ bị review",
            '151' =>  "Ngân hàng không hỗ trợ thanh toán",
            '152' =>  "Số thẻ không phù hợp với loại thẻ đã chọn",
            '153' =>  "Giao dịch không tồn tại",
            '154' =>  "Số tiền vượt quá hạn mức cho phép",
            '155' =>  "Đợi người mua xác nhận trả góp",
            '156' =>  "Số tiền thanh toán không hợp lệ",
            '157' =>  "email không khớp với profile đã tồn tại",
            '158' =>  "số điện thoại không khớp với profile đã tồn tại",
            '159' =>  "Id không được để trống",
            '160' =>  "First name không được để trống",
            '161' =>  "Last name không được để trống",
            '162' =>  "Email không được để trống",
            '163' =>  "city không được để trống",
            '164' =>  "country không được để trống",
            '165' =>  "SĐT Không được để trống",
            '166' =>  "state không được để trống",
            '167' =>  "street không được để trống",
            '168' =>  "postalcode không được để trống",
            '169' =>  "url callback không đươc để trống",
            '170' =>  "otp nhập sai quá 3 lần",
            '171' =>  "Thẻ của khách hàng đã được liên kết trên Merchant",
            '172' =>  "thẻ tạm thời bị cấm liên kết do vượt quá số lần xác thực số tiền",
            '173' =>  "trạng thái liên kết thẻ không đúng",
            '174'  =>  "không tìm thấy phiên liên kết thẻ",
            '175' =>  "số tiền thanh toán của thẻ 2D chưa xác thực vượt quá hạn mức",
            '176' =>  "thẻ 2D đang chờ xác thực",
            '177' =>  "khách hàng ấn nút hủy giao dịch",
            '178' =>  "thanh toán subscription thành công",
            '179' =>  "thanh toán subscription thất bại",
            '180' =>  "đăng ký subscription thành công",
            '181' =>  "đăng ký subscription thất bại",
            '182' =>  "Mã Alepay token không hợp lệ",
            '183' =>  "Mã plan không được trống",
            '184' =>  "URL callback không được trống",
            '185' =>  "Subscription Plan không tồn tại",
            '186' =>  "Subscription plan không kích hoạt",
            '187' =>  "Subscription plan hết hạn",
            '188' =>  "Subscription Record đã tồn tại",
            '189' =>  "Subscription Record không tồn tại",
            '190' =>  "Trạng thái Subscription Record không hợp lệ",
            '191' =>  "Xác thực OTP quá số lần cho phép",
            '192' =>  "Sai OTP xác thực",
            '193' =>  "Đăng ký subscription cho khách hàng thành công",
            '194' =>  "Khách hàng cần confirm subscription",
            '195' =>  "Trạng thái Alepay token không hợp lệ",
            '196' =>  "Gửi OTP không thành công",
            '197' =>  "Ngày kết thúc hoặc số lần thanh toán tối đa không hợp lệ",
            '198' =>  "Alepay token không được để trống",
            '199' =>  "Alepay token chưa được active",
            '200' =>  "Subscription Plan không hợp lệ",
            '201' =>  "thời gian bắt đầu không hợp lệ",
            '202' =>  "IP request của merchant chưa được cấu hình hoặc không được cho phép",
            '203' =>  "không tìm thấy file subscription",
            '204' =>  "Alepay token chưa được xác thực",
            '205' =>  " tên chủ thẻ không hợp lệ",
            '206' =>  "Merchant không được phép sử dụng dịch vụ này",
            '207' =>  "Ngân hàng nội địa không hợp lệ",
            '208' =>  "Mã token xác thực không hợp lệ",
            '209' =>  "Số tiền xác thực không hợp lệ",
            '210' =>  "Quá số lần xác thực số tiền",
            '211' =>  "Tên người mua phải bao gồm cả họ và tên",
            '212' =>  "Merchant không được phép liên kết thẻ",
            '213' =>  "Khách hàng không lựa chọn liên kết thẻ",
            '214' =>  " Giao dịch chưa được thực hiện",
            '215' =>  "Không duyệt thẻ bị review",
            '216' =>  "Thẻ không được hỗ trợ thanh toán",
            '217' =>  "Profile khách hàng không tồn tại trên hệ thống",
            '220' =>  "Giao dịch đã được hoàn",
            '221' =>  "Giao dịch đã tạo yêu cầu hoàn",
            '222' =>  "Giao dịch hoàn đang được xử lý",
            '223' =>  "Giao dịch trả góp không được hoàn",
            '224' =>  "Yêu cầu hoàn tiền đã bị hủy",
            '226' =>  " Mã chương trình khuyến mãi không hợp lệ",
            '227' =>  "Chờ merchant confirm (Chỉ dành riêng cho robin)",
            '228' =>  "Ngân hàng không hỗ trợ trả góp trong ngày sao kê",
            '229' =>  " Thẻ đã hết hạn sử dụng, vui lòng liên hệ ngân hàng phát hành thẻ để biết
thêm chi tiết",
            '230' =>  "Thẻ không được phép liên kết",
            '231' =>  "Trạng thái giao dịch không đúng",
            '232' =>  "Lỗi kết nối tới ngân hàng",
            '999' =>  "Lỗi không xác định. Vui lòng liên hệ với Quản trị viên Alepay",
        ];

        if (in_array($error_code, array_keys($arr_error_message))) {
            $error_message = $arr_error_message[$error_code];
        } else {
            $error_message = 'Lỗi không xác định';
        }
        return $error_message;
    }

}
