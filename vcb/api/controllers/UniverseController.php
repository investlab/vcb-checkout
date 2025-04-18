<?php

namespace api\controllers;

use api\components\ApiController;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\util\Helpers;
use DateTime;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class UniverseController extends ApiController
{
    public $request_id;

    public function behaviors(): array
    {
        $debug_enable = @in_array(get_client_ip(), ["::1", "14.177.239.244", "101.99.7.213", "172.26.0.1", "14.177.239.203", "14.177.239.192", "101.99.7.132"]);
        if (!$debug_enable) {
            echo "<pre>";
            var_dump("Bye: " . get_client_ip());
            die();
        }

        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'find-log' => ['post', 'get'],
                ],
            ]
        ];
    }

    public function afterAction($action, $result)
    {
        return $result;
    }


    public function actionFindLog()
    {
//        $file_name = LOG_PATH . 'cbs_vcb_3ds2/output/' . '20230711' . '.txt';
//        $file = fopen($file_name, 'r'); // Mở file để đọc
        Yii::$app->response->format = Response::FORMAT_JSON;

        $log_name = ObjInput::get('log_name', 'str', '');


        $method_name = 'findLog' . ucfirst($log_name);
        if (method_exists($this, $method_name)) {
            $process = $this->$method_name();

            $error_code = '500';

            return [
//                'id' => "COMPLETED",
                'reasonCode' => $process['reasonCode'] ?? $error_code,
                $log_name => $process,
                "reasonMessage" => $this->getErrorMessage($process['reasonCode'] ?? $error_code),
            ];
        } else {
            return [
                'status' => false,
                'error_message' => "Function not support"
            ];
        }


        $result = [];

        if ($file) {
            while (($line = fgets($file)) !== false) {
                // Kiểm tra điều kiện tìm kiếm đúng
                if (strpos($line, '6890458147556512603008') !== false) {
                    $result[] = $line; // In ra nội dung của dòng thỏa điều kiện
                }
            }
            fclose($file); // Đóng file sau khi xử lý xong
        }
        $this->_setHeader(200);
//        echo $result;
//
//        echo "<pre>";
//        var_dump($result );
//        die();
        return $result;
    }

    protected function findLogCyberSource()
    {
        $mode = ObjInput::get('mode', 'str', 'normal');
        $error_code = 500;
        $result = [];


        if ($mode == 'normal') {
            $date = ObjInput::get('date', 'str', '');
            if (Helpers::isDate("Ymd", $date)) {
                $value_search = ObjInput::get('value_search', 'str', '');
                if ($value_search != "") {
                    $path_file_log = LOG_PATH . 'cbs_vcb_3ds2' . DS . 'output' . DS . $date . '.txt';



                    if (is_file($path_file_log)) {
                        $error_code = '000';

                        $search_result = [];
                        $file = fopen($path_file_log, 'r'); // Mở file để đọc


                        while (($line = fgets($file)) !== false) {
                            if (strpos($line, $value_search) !== false) {
                                $search_result[] = $line; // In ra nội dung của dòng thỏa điều kiện
                            }
                        }
                        if (!empty($search_result)) {
                            $pattern = '/(\[[0-9:,\/]+\])(\[[a-zA-z]+\])(\[[a-zA-z0-9]+\])(\{.*\})/';
                            foreach ($search_result as $key=>$string) {
                                if (preg_match($pattern, $string, $matches)) {
                                    $result[$key] = [
                                        'time' => trim($matches[1], "[]"),
                                        'service' => trim($matches[2], "[]"),
                                        'type' => trim($matches[3], "[]"),
                                        'value' => json_decode($matches[4], true)
                                    ];
                                }
                            }
                        } else {
                            $error_code = '104';
                        }
                    } else {
                        $error_code = '103';
                    }

                } else {
                    $error_code = '102';
                }
            } else {
                $error_code = '101';
            }

        } else {

        }
        return [
            'reasonCode' => $error_code,
            'resultSearch' => $result,
        ];
    }

    protected function getErrorMessage($error_code)
    {
        $arrCode = array(
            '000' => Translate::get("COMPLETED"),
            '101' => Translate::get("date invalid"),
            '102' => Translate::get("value_search invalid"),
            '103' => Translate::get("log not exists"),
            '500' => Translate::get("process done"),
        );
        return $arrCode[$error_code] ?? 'Lỗi không xác định (' . $error_code . ')';
    }
}