<?php

namespace common\components\utils;

use Yii;
use yii\base\Exception;

class Logs
{

    private static $mode = 0777;

    /*
     * Hàm ghi Log [ Logs::create('data', 'test.txt', 'ví dụ'); ]
     * @param string $path Đường dẫn thư mục tương đối
     * @param string $file_name Tên file
     * @param string $content Nội dung
     */

    public static function create($path, $file_name = '', $content)
    {
        if (empty($file_name)) {
            $file_name = date('Ymd') . '.txt';
        }


        if (self::createDirPath(LOG_PATH . $path)) {
            $fp = fopen(LOG_PATH . $path . DS . $file_name, 'a');
            if ($fp) {
                $line = date("[H:i:s,d/m/Y]", time()) . $content . " \n";
                fwrite($fp, $line);
                fclose($fp);
            }
        }
    }

    public static function createV3($path, $file_name = '', $content)
    {
        if (empty($file_name)) {
            $file_name = date('Ymd') . '.txt';
        }


        if (self::createDirPath(LOG_PATH . $path)) {
            $fp = fopen(LOG_PATH . $path . DS . $file_name, 'a');
            if ($fp) {
                $line = $content;
                fwrite($fp, $line);
                fclose($fp);
            }
        }
    }

    /**
     * haipv debug.
     *
     * @param $file
     * @param $func
     * @param $line
     * @param $content
     * @return null
     */
    public static function createv5($file, $func, $line, $content)
    {
        $body = "[{$file}][$func][$line]" . $content;
        return self::create('checkout', 'haipv.log', $body);
    }


    public static function createV2($path, $file_name = '', $content, $write = 1)
    {
        if ($write == 1) {
            try {
                date_default_timezone_set('Asia/Bangkok');
                if (empty($file_name)) {
                    $file_name = date('Ymd') . '.txt';
                }
                if (self::createDirPath(LOG_PATH . $path)) {
                    $fp = fopen(LOG_PATH . $path . DS . $file_name, 'a');
                    if ($fp) {
                        $content['time_request'] = date("H:i:s d/m/Y", time());
                        $line = json_encode($content) . " \r\n";
                        fwrite($fp, $line);
                        fclose($fp);
                    }
                }
            } catch (Exception $exc) {

            }
        }
    }


    public static function createDirPath($path)
    {

        try {
            if (is_dir($path)) {
                return true;
            } else {
                if (self::mkdir_r($path, self::$mode)) {
                    return true;
                }
            }
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    static function mkdir_r($dirName, $rights = 0777)
    {
        $dirs = explode('/', $dirName);

        $dir = '';
        foreach ($dirs as $part) {
            $dir .= $part . '/';

            if (!is_dir($dir) && strlen($dir) > 0)
                mkdir($dir, $rights);
        }
    }

    public static function writeELKLog($data, $log_type = '', $mode_type = 'INPUT', $fnc = '', $version = 'all', $filepath = '', $env = 'web')
    {
        $params = (array)$data;
        if ($filepath == 'checkout/vcb') {
            $params = json_decode($data['data'], true);
        }
        if (isset($params['fnc'])) {
            $fnc = $params['fnc'];
            unset($params['fnc']);
        }

        if (isset($params['func'])) {
            $fnc = $params['func'];
            unset($params['func']);
        }

        $arr_fnc_no_write = ['GetZone'];
        if (!in_array($fnc, $arr_fnc_no_write)) {
            if (isset($params['merchant_password'])) {
                if (!empty($params['merchant_password'])) {
                    $params['merchant_password'] = substr($params['merchant_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['merchant_password'], -1);
                } else {
                    $params['merchant_password'] = '';
                }
            }

            if (isset($params['password'])) {
                if (!empty($params['password'])) {
                    $params['password'] = substr($params['password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['password'], -1);
                } else {
                    $params['password'] = '';
                }
            }

            if (isset($params['payment_password'])) {
                if (!empty($params['payment_password'])) {
                    $params['payment_password'] = substr($params['payment_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['payment_password'], -1);
                } else {
                    $params['payment_password'] = '';
                }
            }
            if (isset($params['card_number'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['card_number']);
                unset($params['card_number']);
            } else if (isset($params['accountNumber'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['accountNumber']);
                unset($params['accountNumber']);
            }

            if (isset($params['card'])) {
                unset($params['card']);
            }
//            array_walk_recursive($params, function(&$item){$item=strval($item);});
            $data_log = $params;

            $data_write = array(
                'TYPE' => $log_type,
                'MODE' => $mode_type,
                'ENV' => $env,
                'FNC' => $fnc,
                'version' => $version,
                'REQ_ID' => REQ_ID,
                'IP' => get_client_ip(),
                'DATA' => json_encode($data_log, JSON_UNESCAPED_SLASHES)
            );
            self::createELK($filepath, date('Ymd') . '.json', $data_write);
        }
    }

    public static function writeELK($data, $log_type = 'pg-elk-log', $mode_type = 'INPUT', $fnc = '', $version = 'all', $env = 'web')
    {
        $params = (array)$data;
        $filepath = 'elk_log/' . $log_type;
        if (isset($params['fnc'])) {
            $fnc = $params['fnc'];
            unset($params['fnc']);
        }

        if (isset($params['func'])) {
            $fnc = $params['func'];
            unset($params['func']);
        }

        $arr_fnc_no_write = ['GetZone'];
        if (!in_array($fnc, $arr_fnc_no_write)) {
            if (isset($params['merchant_password'])) {
                if (!empty($params['merchant_password'])) {
                    $params['merchant_password'] = substr($params['merchant_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['merchant_password'], -1);
                } else {
                    $params['merchant_password'] = '';
                }
            }

            if (isset($params['password'])) {
                if (!empty($params['password'])) {
                    $params['password'] = substr($params['password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['password'], -1);
                } else {
                    $params['password'] = '';
                }
            }

            if (isset($params['payment_password'])) {
                if (!empty($params['payment_password'])) {
                    $params['payment_password'] = substr($params['payment_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['payment_password'], -1);
                } else {
                    $params['payment_password'] = '';
                }
            }
            if (isset($params['card_number'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['card_number']);
                unset($params['card_number']);
            } else if (isset($params['accountNumber'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['accountNumber']);
                unset($params['accountNumber']);
            }
            if (isset($params['card'])) {
                unset($params['card']);
            }
            $data_log = $params;

            $data_write = array(
                'TYPE' => $log_type,
                'MODE' => $mode_type,
                'ENV' => $env,
                'FNC' => $fnc,
                'version' => $version,
                'REQ_ID' => REQ_ID,
                'IP' => get_client_ip(),
                'DATA' => json_encode($data_log, JSON_UNESCAPED_SLASHES)
            );
            $params_checkout_order = self::getCheckoutOrderParams();
            self::createELK($filepath, date('Ymd') . '.json', $data_write);
        }
    }


    public static function writeELKLogPartnerPayment($data, $mode_type = 'INPUT', $fnc = '', $version = 'all', $filepath = '', $env = 'web')
    {
        $log_type = 'pg-vcb-checkout-partner-payment';
        $params = (array)$data;
        $filepath = 'elk_log/partner_payment' . DS . $filepath;
        if (isset($params['fnc'])) {
            $fnc = $params['fnc'];
            unset($params['fnc']);
        }

        if (isset($params['func'])) {
            $fnc = $params['func'];
            unset($params['func']);
        }

        $arr_fnc_no_write = ['GetZone'];
        if (!in_array($fnc, $arr_fnc_no_write)) {
            if (isset($params['merchant_password'])) {
                if (!empty($params['merchant_password'])) {
                    $params['merchant_password'] = substr($params['merchant_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['merchant_password'], -1);
                } else {
                    $params['merchant_password'] = '';
                }
            }

            if (isset($params['password'])) {
                if (!empty($params['password'])) {
                    $params['password'] = substr($params['password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['password'], -1);
                } else {
                    $params['password'] = '';
                }
            }

            if (isset($params['payment_password'])) {
                if (!empty($params['payment_password'])) {
                    $params['payment_password'] = substr($params['payment_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['payment_password'], -1);
                } else {
                    $params['payment_password'] = '';
                }
            }
            if (isset($params['card_number'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['card_number']);
                unset($params['card_number']);
            } else if (isset($params['accountNumber'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['accountNumber']);
                unset($params['accountNumber']);
            }
            if (isset($params['card'])) {
                unset($params['card']);
            }
            $data_log = $params;

            $data_write = array(
                'TYPE' => $log_type,
                'MODE' => $mode_type,
                'ENV' => $env,
                'FNC' => $fnc,
                'version' => $version,
                'REQ_ID' => REQ_ID,
                'IP' => get_client_ip(),
                'DATA' => json_encode($data_log, JSON_UNESCAPED_SLASHES)
            );
            $params_checkout_order = self::getCheckoutOrderParams();

            if ($params_checkout_order && is_array($params_checkout_order)) {
                $data_write = array_merge($data_write, $params_checkout_order);
//                $data_write['checkout_order'] = $params_checkout_order;
            }

            if (in_array($fnc, ['BIDV_VA_GET_BILL', 'BIDV_VA_PAY_BILL'])) {
                $params_transaction = self::getTrasactionParams($data);
                if ($params_transaction && is_array($params_transaction)) {
                    $data_write = array_merge($data_write, $params_transaction);
                }
            }
            self::createELK($filepath, date('Ymd') . '.json', $data_write);
        }
    }

    public static function writeELKLogCheckoutCallback($data, $mode_type = 'INPUT', $fnc = 'DEFAULT_CALLBACK', $env = 'checkout-dc')
    {
        $log_type = 'pg-vcb-checkout-order-callback';
        $params = (array)$data;
        $filepath = 'elk_log/checkout_order_callback';
        if (isset($params['fnc'])) {
            $fnc = $params['fnc'];
            unset($params['fnc']);
        }
        if (isset($params['func'])) {
            $fnc = $params['func'];
            unset($params['func']);
        }

        $call_method = '';
        if (isset($params['call_method'])) {
            $call_method = $params['call_method'];
            unset($params['call_method']);
        }

        $arr_fnc_no_write = ['GetZone'];
        if (!in_array($fnc, $arr_fnc_no_write)) {
            if (isset($params['card_number'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['card_number']);
                unset($params['card_number']);
            } else if (isset($params['accountNumber'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['accountNumber']);
                unset($params['accountNumber']);
            }
            if (isset($params['card'])) {
                unset($params['card']);
            }
            $data_log = $params['data'];
            if (isset($data['is_html']) && $data['is_html']) {
                $data_log = substr($data_log, 0, 300);
            }
            $data_write = array(
                'TYPE' => $log_type,
                'MODE' => $mode_type,
                'URL_CALLBACK' => $data['url_callback'] ?? '',
                'STATUS_CODE' => $data['status_code'] ?? '',
                'ERROR_MESSAGE' => $data['error_message'] ?? '',
                'ENV' => $env,
                'FNC' => $fnc,
                'REQ_ID' => REQ_ID,
                'IP' => get_client_ip(),
                'DATA' => json_encode($data_log, JSON_UNESCAPED_SLASHES),
                'CALL_METHOD' => $call_method,
            );
            $params_checkout_order = self::getCheckoutOrderParams();

            if ($params_checkout_order && is_array($params_checkout_order)) {
                $data_write = array_merge($data_write, $params_checkout_order);
            }
            self::createELK($filepath, date('Ymd') . '.json', $data_write);
        }
    }

    private static function getCheckoutOrderParams()
    {
        try {
            $checkout_order = Yii::$app->controller->checkout_order;
            $params = [
                'order_code' => $checkout_order['order_code'],
                'token_code' => $checkout_order['token_code'],
            ];
            return $params;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private static function getTrasactionParams($input)
    {
        try {
            $res = false;
            if (isset($input['trans_id'])) {
                $res['trans_id'] = $input['trans_id'];
            }
            if (isset($input['bill_id'])) {
                $res['bill_id'] = $input['bill_id'];
            }
            if (isset($input['customer_id'])) {
                $res['customer_id'] = $input['customer_id'];
            }
            return $res;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public static function writeELKLogNew($data, $log_type = '', $mode_type = 'INPUT', $fnc = '', $version = 'all', $filepath = '', $env = 'web', $req_id)
    {
        $params = (array)$data;
        if ($filepath == 'checkout/vcb') {
            $params = json_decode($data['data'], true);
        }
        if (isset($params['fnc'])) {
            $fnc = $params['fnc'];
            unset($params['fnc']);
        }

        if (isset($params['func'])) {
            $fnc = $params['func'];
            unset($params['func']);
        }

        $arr_fnc_no_write = ['GetZone'];
        if (!in_array($fnc, $arr_fnc_no_write)) {
            if (isset($params['merchant_password'])) {
                if (!empty($params['merchant_password'])) {
                    $params['merchant_password'] = substr($params['merchant_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['merchant_password'], -1);
                } else {
                    $params['merchant_password'] = '';
                }
            }

            if (isset($params['password'])) {
                if (!empty($params['password'])) {
                    $params['password'] = substr($params['password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['password'], -1);
                } else {
                    $params['password'] = '';
                }
            }

            if (isset($params['payment_password'])) {
                if (!empty($params['payment_password'])) {
                    $params['payment_password'] = substr($params['payment_password'], 0, 2) . '.' . 'xxx' . '.' . substr($params['payment_password'], -1);
                } else {
                    $params['payment_password'] = '';
                }
            }
            if (isset($params['card_number'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['card_number']);
                unset($params['card_number']);
            } else if (isset($params['accountNumber'])) {
                $params['card_number_mask'] = self::_hideAccountNumber($params['accountNumber']);
                unset($params['accountNumber']);
            }

            if (isset($params['card'])) {
                unset($params['card']);
            }
//            array_walk_recursive($params, function(&$item){$item=strval($item);});
            $data_log = $params;

            $data_write = array(
                'TYPE' => $log_type,
                'MODE' => $mode_type,
                'ENV' => $env,
                'FNC' => $fnc,
                'version' => $version,
                'REQ_ID' => REQ_ID,
                'IP' => get_client_ip(),
                'DATA' => json_encode($data_log, JSON_UNESCAPED_SLASHES)
            );
            self::createELK($filepath, date('Ymd') . '.json', $data_write);
        }
    }

    public static function createELK($path, $file_name = '', $content, $write = 1)
    {
        if ($write == 1 && !empty($content)) {
            try {
                date_default_timezone_set('Asia/Bangkok');
                if (empty($file_name)) {
                    $file_name = date('Ymd') . '.json';
                }
                if (self::createDirPath(LOG_PATH . $path)) {
                    $fp = fopen(LOG_PATH . $path . DS . $file_name, 'a');
                    if ($fp) {
                        $content['time_request'] = date("H:i:s d/m/Y", time());
                        $content['tempid'] = uniqid();
                        array_walk_recursive($content, function (&$item) {
                            $item = strval($item);
                        });
                        $line = json_encode($content, JSON_UNESCAPED_SLASHES) . " \r\n";
                        $line = str_replace("\\\\\\", "\\", $line);
                        fwrite($fp, $line);
                        fclose($fp);
                    }
                }
            } catch (Exception $exc) {

            }
        }
    }

    public static function _hideAccountNumber($account_number)
    {
        return substr($account_number, 0, 6) . '.' . 'xxxx.xxxx.' . substr($account_number, -4);
    }

}

function Logs($dirname = '', $basename = '', $data = [])
{
    //Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    Logs::create($dirname, $basename, $data);
}
