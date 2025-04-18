<?php

namespace console\controllers;

use common\components\utils\Logs;
use common\jobs\ExportJob;
use common\jobs\modules\RevenuesJob;
use common\jobs\modules\TransactionJob;
use common\models\db\BinAcceptV2;
use common\models\db\MerchantExternalFee;
use common\models\db\MethodPaymentMethod;
use common\models\db\Transaction;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_CSV;
use PHPExcel_Writer_Excel5;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class ToolController extends Controller
{
    /** run-urls */
    public static function actionRunUrls(){
        $params = [
            'name' => 'test'
        ];
        $urls = [
            'https://evisa.xuatnhapcanh.gov.vn/evisa-ws-portlet/rest/evisatransaction/updateTransStatus','https://hasaki.vn/payment/vcbpay/ipn','https://kh.dai-ichi-life.com.vn:8443/onlinepayment/PostJsonVietcom.aspx/GetVietcombankPostJson','http://123.24.142.4:2301/api/pay/v1/transactions/vietcombank/confirm','https://admin-vietcombank.nganluong.vn/merchant/web/payment-link/notify','https://payment.ueh.edu.vn/NopTien/ipn.aspx','https://portal.fwd.com.vn/eServices/payment/paymentNotify_vcb','http://payment.bvungbuoubg.com/api/paymentgateway/notifiurl?api=2b11k2h3foes9f0809zdn398f0fasdmkj30&code=24279-V151646&status_url=','https://motcua.dongnai.gov.vn/management/management/cloud/nganluongpayverify.cpx','https://alepay-v3-dev.nganluong.vn/merchant/en/transaction/search','https://hub.ihouzz.com/payment-gateway/api/public/vcb/ipn/dxs','https://vietcombank.nganluong.vn/test/merchant_demo.php?option=notify','https://comment.nld.com.vn/api-ipnnganluong.htm','http://hoithao.bvhungvuong.vn:81/Notify-order-from-VCB','https://onehealth.vncare.vn/api/payment/public/vcb/xacnhan-thanhtoan/34014','http://117.2.166.232:1666/api/nganluong/callback/completeQrPayment','https://wiki.nextpay.vn/vi/home','http://118.70.128.66:96/api/ThanhToan/NotifyGetData','https://dichvucong.bocongan.gov.vn/public/vietcombank/confirm_payment','https://www.paydollar.com?order=f70550735368e19a3538da2b1ba48330939c66cc9e1cdfc8','https://www.paydollar.com?order=f70550735368e19ad9606a32fdb84938fd7fbf7b4acb0ffa','https://www.paydollar.com?order=345d83d7e6d9c58bb3a70bc58630df69403ad428fdf1fb56','https://www.paydollar.com?order=f70550735368e19a1550949432188b17864962755c48697b','https://payment.phuhunglife.com/payment-vcb/notify','https://www.paydollar.com?order=f70550735368e19a4bed8b1ebf6ebd1a4e94ae47b47af1b0','https://vietcombank.nganluong.vn/test/merchant_demo_2.php?option=notify','https://onehealth.vncare.vn/api/payment/public/vcb/xacnhan-thanhtoan/01009','https://pay.bcit.edu.vn/api/ApiAscPay/VietcombankExecute','https://webhook.site/30988e2d-fdf5-48d8-a566-6dbd4b544c8e','https://vectorcar.vn/checkouts/f93b4ec0af0947b38ba1f33e3222fc7d/thank_you?type=success','https://api-online.fubonins.com.vn/api/order/get_payment_status','https://apiicnm.medcom.vn/api/vcb/updateorderqr','https://api.benhvienkhanhhoa.org.vn/api/values','https://nextpay.vn/'
        ];
//        $urls = [
//            'https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/test/test-response-revert-0200.php'
//        ];
        foreach ($urls as $url){
            self::_call($params, $url);
        }
//        $url = 'https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/test/test-response-revert-0200.php';
        var_dump('done');die();
    }

    private static function _call($params, $url)
    {

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $url . '?' . http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            self::_writeLog( $url );
//            self::_writeLog('[INPUT]"  ' . json_encode($params) );
            self::_writeLog('[RESULT] ' . $status . '  ' . $error . '|' . $response . '\n');


        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    private static function _writeLog($data)
    {
        $path_process = 'console' . DS . 'tool' . DS . Yii::$app->controller->action->id;
        $path_info = pathinfo($path_process . DS . date('Ymd') . '.txt');
        Logs::createV3($path_info['dirname'], $path_info['basename'],  $data);
    }
}
