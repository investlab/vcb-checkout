<?php


namespace common\models\business;
use common\components\utils\Logs;
use Yii;

class ReceiptBussiness
{
    public static $PDF_URL = ROOT_URL . DS . 'data' .  DS . 'pdf' . DS;
    public static $PDF_PATH = ROOT_PATH . DS . 'data' . DS . 'pdf' . DS;
    private static $USER_NAME_CKS = 'nganluong.dev@peacesoft.net';
    private static $PASSWORD_CKS = '5Ti8s76wVqbhs7V@';
    private static $CLIENT_ID = '4b0c-637260218657289133.apps.signserviceapi.com';
    private static $CLIENT_SECRET = 'YzQwYWZkZTc-MWNlYi00YjBj';
    private static $ACCESS_TOKEN_URL = 'https://gateway.vnpt-ca.vn/signservice/v4/oauth/token';
    private static $API_CKS_URL = 'https://gateway.vnpt-ca.vn/signservice/v4/api_gateway';


    public static $access_token_cks = '';
    public static $refresh_token_cks = '';
    public static $RequestID = '';

    const NOT_SIGN = 1;
    const SIGN = 2;

    public static function processMakeBillUrl($params, $file_name)
    {

        require_once ROOT_PATH . DS . 'common'. DS . 'components'.DS.'libs'.DS. 'TCPDF'. DS . 'tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Ngan Luong');
        $pdf->SetTitle('');
        $pdf->SetSubject('');


        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 6));
        $pdf->SetMargins(10, 18, 15);

        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetFont('dejavusans', '', 8);

        $pdf->AddPage();
        $html_content = self::makeHTMLBill($params);

        $pdf->writeHTML($html_content, true, false, true, false, '');

        $pdf->LastPage();
        $file_path = self::$PDF_PATH . $file_name . '.pdf';
        $file_pdf_url = self::$PDF_URL . $file_name . '.pdf';
        $params = array(
            'token' => base64_encode($file_name),

        );
        $file_url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/view-bill',  'token' => base64_encode($file_name)], HTTP_CODE);
//         $file_url = ROOT_URL . 'service' . DS . 'vpcp' . DS . 'viewbill?' . http_build_query($params);
        // http://ip-ss-donvi:8080/XrdAdapter/RestService/forward/service/vpcp/viewbill?token=MjAwNTI3MzAwMDE0&dstcode=VN:COM:0106001236:NganLuongPaymentSvc&providerurl=http://10.0.0.41:8083/


        $result = $pdf->Output($file_path, "F");


        if (!empty($result)) {
            //TODO Viết action khác, return luôn file_url tại đây! (phục vụ file chưa có kí số)
            $makeAccessToken = self::makeAccessToken();
            if ($makeAccessToken) {
                self::$access_token_cks = $makeAccessToken['access_token'];
                self::$refresh_token_cks = $makeAccessToken['refresh_token'];
                self::$RequestID = self::getGUID();
            }
            $result_cks = self::processMakeChuKiSo($file_name . '.pdf', $file_path, 3);
            self::_writeLogCKS('Process_make_cks'.json_encode($result_cks));
            if ($result_cks != false) {
            return array(
                'error_message' => 'Success',
                'url' => $file_url
            );
                self::_writeLogCKS('[' . $data['MaThamChieu'] . ']Update  is_signatured' . $bill['data'][0]['maGD'] . ']' . json_encode($update));
            }

        } else {
            return array(
                'error_message' => 'Có lỗi trong quá trình xuất file',
                'url' => ''
            );
        }
    }

    public static function processMakeBillNotSignUrl($params, $file_name)
    {

        require_once ROOT_PATH . DS . 'common'. DS . 'components'.DS.'libs'.DS. 'TCPDF'. DS . 'tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Ngan Luong');
        $pdf->SetTitle('');
        $pdf->SetSubject('');


        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 6));
        $pdf->SetMargins(10, 18, 15);

        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetFont('dejavusans', '', 8);

        $pdf->AddPage();
        $html_content = self::makeHTMLBill($params);

        $pdf->writeHTML($html_content, true, false, true, false, '');

        $pdf->LastPage();
        $file_path = self::$PDF_PATH . $file_name . '.pdf';
        $file_pdf_url = self::$PDF_URL . $file_name . '.pdf';
        $params = array(
            'token' => base64_encode($file_name),

        );
        $file_url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/view-bill',  'token' => base64_encode($file_name)], HTTP_CODE);
//         $file_url = ROOT_URL . 'service' . DS . 'vpcp' . DS . 'viewbill?' . http_build_query($params);
        // http://ip-ss-donvi:8080/XrdAdapter/RestService/forward/service/vpcp/viewbill?token=MjAwNTI3MzAwMDE0&dstcode=VN:COM:0106001236:NganLuongPaymentSvc&providerurl=http://10.0.0.41:8083/


        $result = $pdf->Output($file_path, "F");

        if (!empty($result)) {
            return array(
                'error_message' => 'Success',
                'url' => $file_url
            );

        } else {
            return array(
                'error_message' => 'Có lỗi trong quá trình xuất file',
                'url' => ''
            );
        }
    }

    public function makeAccessToken()
    {
        $params = array(
            'client_id' => self::$CLIENT_ID,
            'client_secret' => self::$CLIENT_SECRET,
            'username' => self::$USER_NAME_CKS,
            'password' => self::$PASSWORD_CKS,
            'grant_type' => 'password'
        );

        self::_writeLogCKS('[URL]'.self::$ACCESS_TOKEN_URL);
        self::_writeLogCKS('[INPUT]'.json_encode($params));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$ACCESS_TOKEN_URL,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($params)
        ]);
        $response = curl_exec($curl);
        self::_writeLogCKS('[OUTPUT]'.json_encode($response));

        curl_close($curl);
        $response = json_decode($response);

        if (isset($response->error)) {
            return false;
        } else {

            return array(
                'access_token' => $response->access_token,
                'refresh_token' => $response->refresh_token,
            );
        }
    }
    public function makeHTMLBill($params)
    {
        $total_word = self::convertNumberToWords(intval($params['cashin_amount']));


        $html_content = '<table width="100%">
  <tr>
       <th align="center">
       <p><img src ="https://upload.nganluong.vn/public/css/nganluong/images/logoNL.png" width="126" height="30" ></p>
    </th>
    <th align="center">
    	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	Nội dung theo mẫu số 03c</p>
    	<p>	Ký hiệu: C1-10/NS</p>
    </th>
    
  </tr>
  
</table>
<h3 align="center">BIÊN LAI THU THUẾ, PHÍ, LỆ PHÍ VÀ THU PHẠT VI PHẠM HÀNH CHÍNH	</h3>
<p align="center">(Áp dụng đối với trường hợp in từ chương trình ứng dụng thu ngân sách nhà nước)</p>
<p align="center">(Liên số:  ……………. Lưu tại: ……………………………..)</p>
<table width="100%">
  <tr height ="100px">
    <th align="center" width="70%"></th>
    <th align="left" width="30%">
    	<p>	Số Sêri: ...........</p>
    	<p>	Số biên lai: ...........</p>
    </th>
    
  </tr>
  
  
</table>
<table  width="100%"> 
<tr>
      <th align="center"  width="15%">  
      </th>
      <th align="center"  width="75%">  
        <p  align="left">Thu phạt: &nbsp; ' . html_entity_decode('&#9744;', ENT_XHTML, "ISO-8859-1") . '&nbsp; </p>
        <p  align="left">Thu phí,lệ phí: &nbsp; ' . html_entity_decode('&#9745;', ENT_XHTML, "ISO-8859-1") . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tên phí lệ phí: Phí xuất nhập cảnh '.'  </p>
        <p  align="left">Thu thuế: &nbsp; ' . html_entity_decode('&#9744;', ENT_XHTML, "ISO-8859-1") . '</p>
        
        <p align="left">Người nộp: ' . $params['buyer_fullname'] . ' &nbsp;&nbsp; MST/Số CMND/HC : ' . $params['order_code'] . '</p>
        <p align="left">Địa chỉ: ' . $params['buyer_address'] . '</p>
        <p align="left">Theo Quyết định/Thông báo số: ' .$params['order_code'] . ' &nbsp;&nbsp;&nbsp;&nbsp; Ngày:  ' . date('d-m-Y',$params['time_created']) . '</p>
        <p align="left">Của: ' . $GLOBALS['BCA_ALL_CITIES'][$params['merchant_id']]['area'] . '</p>
        <p align="left">Đơn vị nhận tiền: ' . $GLOBALS['BCA_ALL_CITIES'][$params['merchant_id']]['area'] . ' </p>
      </th>
  </tr>
</table>
<br/>
<br/>
<table > 
<tr>
      <th align="left"  width="15%">  
      </th>
      <th align="left"  width="75%">  
        <table width="100%"  cellpadding="3" style=" border: 0.1px solid black;">
       <thead >
            <tr>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;font-weight: bold;width: 10%">STT</th>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;font-weight: bold;width:65%">Nội dung các khoản nộp NS/Mã định danh hồ sơ (ID)</th>
                <th style="height:15px;border-right:0.1px solid black;vertical-align:middle;padding: 15px;text-align: center;width: 25%;font-weight: bold;">Số tiền</th>
   
            </tr>
        </thead>
        <tbody>
            <tr>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 10%;border: 0.1px solid black;padding: 15px;text-align: center">' . 1 . '</td>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 65%;border: 0.1px solid black;padding: 15px;">&nbsp;&nbsp;' . $params['order_description'] . '</td>
                  <td style="height:15px;border-right: 0.1px solid black;border-top: 0.1px solid black;border-bottom: 0.1px solid black;padding:5px;vertical-align:middle;width: 25%;border: 0.1px solid black;padding: 15px;text-align: center">' . number_format($params['cashin_amount']) . ' VND</td>
            </tr>
            <tr>
                <td  style="height:15px;vertical-align:middle;padding: 15px;text-align: center;" colspan="2"><b>Tổng cộng</b></td>
                <td style="height:15px;vertical-align:middle;padding: 15px;text-align: center;">' . number_format($params['cashin_amount']) . ' VND </td>
            </tr>
        </tbody>
    </table>

      </th>
  </tr>
</table>
<div></div>
<table width="100%">
<tr>
<td width="15%"></td>
<td width="75%"><p align="left">Tổng số tiền ghi bằng chữ:    ' . $total_word . ' đồng</p></td>
</tr>

<tr>
<td width="15%"></td>
<td width="75%"><p align="left">Hình thức thanh toán: Thanh toán trực tuyến</p></td>
</tr>

</table>
<table width="100%">
  <tr>
    <th align="center" width="50%" style="margin-top: 15px">
        <p  style="margin: 0"></p>
         <b>Người nộp tiền</b>
        <p style="margin: 0"><i>(Ký, ghi họ tên)</i></p>
    </th>
     <th align="center" width="50%">
    	<p style="margin: 0"><i>Ngày ' . date('d') . ' tháng	' . date('m') . ' năm ' . date('Y') . '</i></p>
         <b>Người nhận tiền</b>
        <p style="margin: 0">(Ký, ghi họ tên)</p>
    </th>
    
  </tr>
</table>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<p align="center">Ghi chú: Chứng từ này sử dụng trong trường hợp thu phạt VPHC; thu phí, lệ phí vào tài khoản phí, lệ phí chờ nộp NS của tổ chức thu phí, lệ phí</p>

';

        return $html_content;
    }

    public function processMakeChuKiSo($file_name, $file_path, $maDV)
    {
        $file_name_log = 'data/logs/vpcp/uat/vnpt_cks/' . date("Ymd", time()) . ".txt";


        $fp = @fopen($file_path, "r");

        if (!$fp) {
            return false;
        } else {
            $html_content = fread($fp, filesize($file_path));
        }
        fclose($fp);
        if (intval($maDV) == 4) {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMzYsMjgzLDU1NiwzNjMiLA0KInBhZ2UiOiAxDQp9DQpd';
        } else if (intval($maDV) == 3) {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMjAsMTAwLDU0MCwxODAiLA0KInBhZ2UiOiAxDQp9DQpd';
        } else {
            $Signatures = 'Ww0Kew0KInJlY3RhbmdsZSI6ICIzMTUsMjIxLDUzNSwzMDEiLA0KInBhZ2UiOiAxDQp9DQpd';
        }

        $serviceGroupID = self::getServiceGroupID();
        $CertID = self::getCertID();
        $data_sign = [
            'RequestID' => self::$RequestID,
            'ServiceID' => 'SignServer',
            'FunctionName' => 'SignPdfAdvance',
            'Parameter' => [
                'CertID' => $CertID,
                'ContentType' => 'application/pdf',
                'Type' => 'pdf',
                'ServiceGroupID' => $serviceGroupID,
                'FileName' => $file_name,
                'DataBase64' => base64_encode(($html_content)),
                'VisibleType' => 5,
                'FontSize' => 11,
                'Signatures' => $Signatures,
                'FontName' => 'Time',
                'Comment' => '',
                'FontStyle' => 1,
                'FontColor' => '#FF0303',
                'SignatureText' => 'Công ty CP cổng trung gian thanh toán Ngân Lượng Đã ký',
                'TextAlign' => 1,
                'Image' => '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',
            ]
        ];

        self::_writeLogCKS( '[URL]' . self::$API_CKS_URL . 'FUNCTION: SignServer/SignPdfAdvance');
        $result = self::call_signature_curl($data_sign);
        unset($data_sign['Parameter']['Image']);
        unset($data_sign['Parameter']['DataBase64']);
        self::_writeLogCKS('[INPUT]' . json_encode($data_sign));
        if ($result['ResponseCode'] == 1) {
            $data = base64_decode($result['Content']['SignedData'], true);
            file_put_contents($file_path, $data);
            unset($result['Content']['SignedData']);
            self::_writeLogCKS( '[OUTPUT]' . json_encode($result));

            return true;
        } else {
            self::_writeLogCKS('[OUTPUT]' . json_encode($result));

            return;
        }
    }
    public function getServiceGroupID()
    {
        $file_name = 'data/logs/vpcp/uat/vnpt_cks/' . date("Ymd", time()) . ".txt";

        $data_getProfiles = [
            'RequestID' => self::$RequestID,
            'ServiceID' => 'UserAccount',
            'FunctionName' => 'GetProfile',
        ];
        self::_writeLogCKS( '[URL]' . self::$API_CKS_URL);
        self::_writeLogCKS( '[FUNCTION: UserAccount/GetProfile');
        self::_writeLogCKS( '[INPUT]' . json_encode($data_getProfiles));

        $result = self::call_signature_curl($data_getProfiles);
        self::_writeLogCKS( '[OUTPUT]' . json_encode($result));
        if ($result['ResponseCode'] == 1) {
            return $result['Content']['GroupID'];
        } else {
            return '';
        };
    }
    public function getCertID()
    {

        $data_getCert = [
            'RequestID' => self::getGUID(),
            'ServiceID' => 'Certificate',
            'FunctionName' => 'GetAccountCertificateByEmail',
            'Parameter' => [
                'PageIndex' => 0,
                'PageSize' => 10
            ]
        ];

        self::_writeLogCKS( '[URL]' . self::$API_CKS_URL);
        self::_writeLogCKS( '[FUNCTION: Certificate/GetAccountCertificateByEmail');

        self::_writeLogCKS( '[INPUT]' . json_encode($data_getCert));

        $result = self::call_signature_curl($data_getCert);
        self::_writeLogCKS( '[OUTPUT]' . json_encode($result));
        if ($result['ResponseCode'] == 1) {
            return $result['Content']['Items'][0]['ID'];
        } else {
            return '';
        }
    }
    public function getGUID()
    {
        mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }
    public function call_signature_curl($data)
    {
        $access_token = self::$access_token_cks;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::$API_CKS_URL,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $access_token,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $msg = json_decode($response, true);
        curl_close($curl);

        return $msg;
    }

    public function convertNumberToWords($number)
    {
        $hyphen = ' ';
        $linh = ' linh ';
        $conjunction = '  ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín',
            10 => 'Mười',
            11 => 'Mười một',
            12 => 'Mười hai',
            13 => 'Mười ba',
            14 => 'Mười bốn',
            15 => 'Mười năm',
            16 => 'Mười sáu',
            17 => 'Mười bảy',
            18 => 'Mười tám',
            19 => 'Mười chín',
            20 => 'Hai mươi',
            30 => 'Ba mươi',
            40 => 'Bốn mươi',
            50 => 'Năm mươi',
            60 => 'Sáu mươi',
            70 => 'Bảy mươi',
            80 => 'Tám mươi',
            90 => 'Chín mươi',
            100 => 'trăm',
            1000 => 'nghìn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'nghìn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            // overflow
//            trigger_error('convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING);
            return false;
        }

        if ($number < 0) {
            return $negative . self::convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;

                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    if ($remainder > 0 && $remainder < 10) {
                        $string .= $conjunction . $linh . self::convertNumberToWords($remainder);
                    } else {
                        $string .= $conjunction . self::convertNumberToWords($remainder);

                    }
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
    private static function _writeLogCKS($data)
    {


        $file_name = 'cks' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }



}