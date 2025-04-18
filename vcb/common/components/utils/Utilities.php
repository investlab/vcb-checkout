<?php

namespace common\components\utils;

use common\components\api\AccountApi;
use common\components\api\SupplierApi;
use common\components\api\ServiceApi;
use Yii;

class Utilities {
    /*
     * lay cac chu so trong 1 string
     * return number
     */

    public static function getNumberToString($string) {
        $number = preg_replace("/[^0-9]/", "", $string);

        return $number;
    }

    /*
     * Tao ma unique
     * Neu co gia tri mac dinh truy vao thi lay gia tri mac dinh + strtoupper(uniqid())
     * Neu khong co gia tri mac dinh thi lay date('ym') + strtoupper(uniqid())
     */

    public static function makeCodeUnique($default = '') {
        $defaultCode = date('ym');
        if ($default != '') {
            $defaultCode = $default;
        }

        $code = $defaultCode . strtoupper(uniqid());

        return $code;
    }

    public static function makeIdAgent() {
        return date('YmdHis');
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateRandomUppercaseString($length = 10) {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateRandomUppercaseCaptchar($length = 6) {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '123456789ABCDEFGHKLMNPQRTUVXY';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /*
     * Rundom number
     */

    public static function createNumber($length = 6) {
        $min = 1;
        $max = 9;
        if ($length > 1) {
            for ($i = 1; $i <= $length; $i++) {
                $min .= '0';
                $max .= '9';
            }
        }
        return rand($min, $max);
    }

    /*
     * Tao tai khoan dang nhap
     */

    public static function setUsername($string) {
        return strtolower($string);
    }

    /**
     * Tao 1 link excel
     */
    public static function buidLinkExcel($action) {
        $url = Yii::$app->request->url;
        $params = explode('?', $url);
        $params = isset($params[1]) && !empty($params) ? '?' . $params[1] : '';
        return '/' . $action . '/' . $params;
    }

    private static function _getChecksum($params) {
        ksort($params);
        return md5(md5(implode('|', $params) . '|' . CHECKSUM_KEY));
    }

    public static function validateChecksum($params, $checksum) {
        if (self::_getChecksum($params) == $checksum) {
            return true;
        }
        return false;
    }

    public static function buildUrlChecksum($action, $params) {
        $inputs = array();
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $inputs[$key] = $value;
            }
            $inputs['checksum'] = self::_getChecksum($params);
        }
        return ROOT_URL . ROOT_FOLDER . $action . '?' . http_build_query($inputs);
    }

    public static $mode = 0777;

    public static function logs($path, $file_name, $content) {
        if (empty($file_name)) {
            $file_name = 'noname_' . uniqid() . '.txt';
        }

        $path = str_replace(ROOT_PATH, '', $path);
        if (self::createDirPath(PATH_LOG . $path)) {
            $fp = fopen(PATH_LOG . $path . DIRECTORY_SEPARATOR . $file_name, 'a');
            if ($fp) {
                $line = date("H:i:s, d/m/Y:  ", time()) . $content . " \n";
                fwrite($fp, $line);
                fclose($fp);
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    public static function createDirPath($path) {

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

    static function mkdir_r($dirName, $rights = 0777) {
        $dirs = explode('/', $dirName);
        $dir = '';
        foreach ($dirs as $part) {
            $dir .= $part . '/';
            if (!is_dir($dir) && strlen($dir) > 0)
                mkdir($dir, $rights);
        }
    }

}
