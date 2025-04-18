<?php

namespace common\util;

use common\components\utils\Logs;
use common\components\utils\Translate;
use common\components\utils\Utilities;
use DateTime;
use Yii;

class Helpers
{
    public static function isInt($value)
    {
        return preg_match('/^\d+$/', $value);
    }

    public static function writeLog($data)
    {
        $file_name = 'qnt' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    public static function privateDebug(): bool
    {
        return @in_array(get_client_ip(), ["::1", "14.177.239.244", "101.99.7.213"]);
    }

    public static function initFolder($path): string
    {
        $path = ROOT_PATH . DS . 'data' . DS . $path;
        if (!file_exists($path)) {
            self::createFolder($path);
        }
        return $path;
    }

    public static function createFolder($path): bool
    {
        return mkdir($path, 0777, true);
    }

    public static function showNotify($notify, $type, $redirect = false)
    {
        if (is_array($notify)) {
            foreach ($notify as $content) {
                Yii::$app->session->setFlash($type, Translate::get($content));
            }
        } else {
            Yii::$app->session->setFlash($type, Translate::get($notify));
        }

        if ($redirect) {
            Yii::$app->response->redirect($redirect);
        }
    }

    public static function randomString($length = 10): string
    {
        return Utilities::generateRandomString($length);
    }

    public static function randomStringAlphabet($length = 10): string
    {

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;

    }

    public static function randomNumber($length):string
    {
        $key = random_int(0, 999999);
        $key = str_pad($key, $length, 0, STR_PAD_LEFT);
        return $key;
    }

    public static function arrayUnsetByKey($arr, $list_accept)
    {
        if (is_array($arr)) {
            foreach ($arr as $key=>$item) {
                if (!in_array($key, $list_accept)) {
                    unset($arr[$key]);
                }
            }
            return $arr;
        } else {
            return false;
        }
    }

    public static function checkLinkIsActive($check_link){
//        $ch = curl_init($check_link);
//        curl_setopt($ch, CURLOPT_NOBODY, true);
//        curl_exec($ch);
//        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        if($info == 200){
//            return true;
//        }else{
//            return false;
//        }

        // create a new curl instance
        $ch = curl_init();

// set the URL to request
        curl_setopt($ch, CURLOPT_URL, $check_link);

// measure the time it takes to receive a response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Connection: Keep-Alive'
        ));
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$headers){
            $headers[] = $header;
            return strlen($header);
        });
        $start = microtime(true);
        $response = curl_exec($ch);
        $end = microtime(true);
        $elapsed = round(($end - $start) * 1000);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // close the curl instance
        curl_close($ch);
        return[
            'http_code' => $http_code, 'time_delay' =>   $elapsed,
        ];



    }

    public static function convertToStringArrayTelegram($arr) {
        $string = '(';
        if (!empty($arr)) {
            foreach ($arr as $key => $item) {
                if ($key == 0) {
                    $string .= $item;
                } else {
                    $string .= "\n".  $item;
                }
            }
        }
        $string .= ')';

        return $string;
    }

    public static function convertToStringArray($arr) {
        $string = '(';
        if (!empty($arr)) {
            foreach ($arr as $key => $item) {
                if ($key == 0) {
                    $string .= $item;
                } else {
                    $string .= ','. $item;
                }
            }
        }
        $string .= ')';

        return $string;
    }

    public static function is_json($string,$return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

    public static function addZeroPrefix($value, $length): string
    {
        return str_repeat('0', $length - strlen($value)) . $value;
    }

    public static function isDate($format = "Ymd", $date)
    {
        $date = DateTime::createFromFormat($format, $date);
        $errors = DateTime::getLastErrors();

        if ($errors['warning_count'] + $errors['error_count'] > 0) {
            return false;
        } else {
            return $date;
        }
    }

    public static function checkKeysExist($keys, $array) {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }

    public static function isCreditCard(&$card_number): bool
    {
        $card_number = self::removeSpaceString($card_number);

        $pattern = '/\b(?:\d[ -]*?){13,16}\b/';

        if (preg_match($pattern, $card_number)) {
            return true;
        } else {
            return false;
        }
    }

    public static function removeSpaceString($string)
    {
        return preg_replace('/\s+/', '', $string);
    }

    public static function removeSpecialChar($value){
        return preg_replace('/[^a-zA-Z0-9_ -]/s','',$value);
    }
    public static function hashCardNumber($card_number): string
    {
        return hash('sha256', $card_number);
    }


}
