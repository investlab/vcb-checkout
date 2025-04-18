<?php
namespace common\components\utils;
/**
 * Class chứa các hàm xử lý, kiểm tra thông tin đầu vào
 *
 * @author ThongNV
 * @copyright 01/2010
 */
class Validation
{

    public static function checkLength($value, $min = 1, $max = false)
    {
        $length = strlen(trim($value));
        if ($length < $min) {
            return false;
        } elseif ($max !== false && $length > $max) {
            return false;
        }

        return true;
    }

    public static function isURL($value)
    {
        $regular = '/^(http|https)(:\/\/)(www.)?(([a-zA-Z0-9\-]+\.){1,2}([a-zA-Z]{1,5}\.)?[a-zA-Z]{1,5})/';
        return self::_process($regular, $value);
    }

    public static function isUserName($value)
    {
        $regular = '/^[a-zA-Z0-9_\-]{4,25}$/';
        return self::_process($regular, $value);
    }

    public static function isDate($value)
    {
        $regular = '/^(?P<date>\d{1,2})[\/-](?P<month>\d{1,2})[\/-](?P<year>\d{4})$/';
        if (self::_process($regular, $value, $groups)) {
            return (checkdate($groups['month'], $groups['date'], $groups['year']) && mktime(0, 0, 0, $groups['month'], $groups['date'], $groups['year']));
        }

        return false;
    }

    public static function checkMinDate($value, $min_time, $hour = 0, $minute = 0, $second = 0)
    {
        $temp = Time::convertDateToTime($value, $hour, $minute, $second);

        return ($min_time <= $temp);
    }

    public static function checkMaxDate($value, $max_time, $hour = 0, $minute = 0, $second = 0)
    {
        $temp = Time::convertDateToTime($value, $hour, $minute, $second);

        return ($max_time >= $temp);
    }

    public static function isValidTimestamp($timestamp) {
        // Kiểm tra nếu chuỗi là một số và lớn hơn 0
        if (ctype_digit($timestamp) && (int)$timestamp > 0) {
            // Chuyển đổi timestamp thành định dạng ngày giờ
            $checkDate = date('Y-m-d H:i:s', (int)$timestamp);
            // Kiểm tra lại timestamp có khớp với chuỗi tạo từ timestamp không
            return (strtotime($checkDate) === (int)$timestamp);
        }
        return false;
    }

    public static function isDateTime($value)
    {
        $dates = explode(" ", $value);
        if (!$dates[0]) {
            return date('H:i:s', $dates[0]);
        }
        $regular = '/^(?P<date>\d{1,2})[\/-](?P<month>\d{1,2})[\/-](?P<year>\d{4})$/';
        if (self::_process($regular, $dates[1], $groups)) {
            return checkdate($groups['month'], $groups['date'], $groups['year']);
        }

        return false;
    }

    public static function isNumber($value)
    {
        $regular = '/[0-9\.]+/uis';

        return self::_process($regular, $value);
    }

    public static function isEmail($value)
    {
        $regular = '/^([a-zA-Z0-9][a-zA-Z0-9_\-]*(([\.][a-zA-Z0-9_\-]*)*)[a-zA-Z0-9]@([a-zA-Z0-9][a-zA-Z0-9_\-]*[a-zA-Z0-9]\.)+([a-zA-Z0-9]{2,15}))$/';

        return self::_process($regular, $value);
    }

    public static function isEmailOrMobile($value)
    {
        $regular = '/^(([a-zA-Z0-9][a-zA-Z0-9_\-]*(([\.][a-zA-Z0-9_\-]*)*)[a-zA-Z0-9]@([a-zA-Z0-9][a-zA-Z0-9_\-]*[a-zA-Z0-9]\.)+([a-zA-Z0-9]{2,4}))|(\d{10,11}))$/';
        return self::_process($regular, $value);
    }

    public static function isMobile($value)
    {
        $regular = '/^\d{10,20}$/';
        return self::_process($regular, $value);
    }

    public static function isPhoneNumberVN($value) {
        $regular = '/^((\(\+84*\))|0|02)+([0-9]{9})*$/';
        return self::_process($regular, $value);
    }

    public static function checkNumberPhone($value ,$max = 20)
    {
        $length = strlen(trim($value));
        if ($length <= $max) {
//            $regular = '/^[0-9+][0-9]*$/';
            $regular = '/^((\(\+[0-9]*\))|[0-9])+([0-9])*$/';
            return self::_process($regular, $value);
        }else{
            return false;
        }
    }

    public static function isPhoneNumber($value)
    {
        $regular = '/^\d{7,15}$/';
        return self::_process($regular, $value);
    }

    public static function isVerifyNumber($value)
    {
        $regular = '/^[a-zA-Z0-9]{6,20}$/';
        return self::_process($regular, $value);
    }

    public static function isPassword($value)
    {
        $regular = '/^[\S]{6,20}$/';
        return self::_process($regular, $value);
    }

    public static function isPasswordOnlyNumber($value)
    {
        $regular = '/^\d{6,20}$/';
        return self::_process($regular, $value);
    }

    public static function isBank($value)
    {
        $regular = '/^\S{5,18}$/';
        return self::_process($regular, $value);
    }

    public static function isFile($file, $key = -1)
    {
        if ($key == -1) {
            if (array_key_exists('tmp_name', $file) && is_file($file['tmp_name'])) {
                return (filesize($file['tmp_name']) != 0);
            }
        } else {
            if (array_key_exists('tmp_name', $file) && is_file($file['tmp_name'][$key])) {
                return (filesize($file['tmp_name'][$key]) != 0);
            }
        }

        return false;
    }

    public static function isImage($file, $key = -1)
    {
        if ($key == -1) {
            if (self::isFile($file)) {
                return (in_array($file['type'], array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png')));
            }
        } else {
            if (self::isFile($file, $key)) {
                return (in_array($file['type'][$key], array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png')));
            }
        }

        return false;
    }

    public static function checkFileSize($file, $max = 100, $key = -1)
    {
        $max = $max * 1024;
        if ($key == -1) {
            return (array_key_exists('tmp_name', $file) && filesize($file['tmp_name']) <= $max);
        } else {
            return (array_key_exists('tmp_name', $file) && filesize($file['tmp_name'][$key]) <= $max);
        }
    }

    public static function checkImage($value)
    {
        if (($value == "image/gif") || ($value == "image/jpg") || ($value == "image/jpeg") || ($value == "image/png"))
            return true;
        else
            return false;
    }

    public static function checkImageSize($value, $max = 512000)
    {
        if ($value < $max)
            return true;
        else
            return false;
    }

    private static function _process($regular, $value, &$groups = false)
    {
        if (preg_match($regular, $value, $groups)) {
            return true;
        } else {
            return false;
        }
    }

    public static function currentPageURL()
    {
        $curpageURL = 'http';
        if (@$_SERVER["HTTPS"] == "on") {
            $curpageURL .= "s";
        }
        $curpageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $curpageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $curpageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $curpageURL;
    }

    public static function gotoUrl()
    {
        return $currentURL = htmlspecialchars_decode(self::currentPageURL());
    }

    /*
     * Kiem tra co phai la so 0 khong
     */

    public static function checkZozo($number)
    {
        if ($number == '0' OR $number === 0) {
            return true;
        }

        return false;
    }

    //Kien tra string co phai la cac chu cai ABC khong
    public static function isChar($string)
    {
        preg_match_all('!\d+!', $string, $matches);
        if (count(@$matches['0']) === 0) {
            return true;
        }
        return false;
    }

    /*
     * kiem tra co phai dia chi IP khong
     * 
     */

    public static function isIp($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }

        return false;
    }

    /*
     * Kiem tra dia chi domain
     */

    public static function isDomain($value)
    {
        $regular = "/(^(?:[A-Z0-9]+(?:\\-*[A-Z0-9])*\\.)+[A-Z]{2,6}$)/mi";

        return self::_process($regular, $value);
    }

    public static function isCardNumber($value)
    {
        $regular = '/^[a-zA-Z0-9_\-]{5,19}$/';
        return self::_process($regular, $value);
    }

    public static function isCardATM($value)
    {
        if (ctype_digit($value)) {
            if (strlen($value) == 15 || strlen($value) == 16 || strlen($value) == 19) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function isCardVisaMaster($value)
    {
        if (ctype_digit($value)) {
            if (strlen($value) == 13 || strlen($value) == 16) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function isAccountHolder($value)
    {
        if (strlen($value) < 3 || strlen($value) > 50) {
            return false;
        } else {
            preg_match_all('!\d+!', $value, $matches);
            if (count($matches['0']) === 0) {
                preg_match('@(\s)@', $value, $matchess);
                if (count($matchess) === 0) {
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }
    }

    public static function isStringVachar($value)
    {
        $regular = '/^[a-zA-Z0-9_,;\- ]+$/';
        return self::_process($regular, $value);
    }

    public static function isStringAddress($value)
    {
        $regular = '/^[a-zA-Z0-9_,;\-\/ ]+$/';
        return self::_process($regular, $value);
    }

    public static function isStringFullname($value)
    {
        $regular = '/^[a-zA-Z_,;\- ]+$/';
        return self::_process($regular, $value);
    }


    // Không có kí tự đặc biệt, có thể là chữ hoặc số, có dấu _ hoặc -, có dấu cách
    public static function checkString($value)
    {
        $regular = '/^[a-zA-Z0-9_\- ]+$/';
        return self::_process($regular, $value);
    }

    // Không có kí tự đặc biệt, có thể là chữ hoặc số, có dấu _ không có dấu cách
    public static function checkContractCode($value)
    {
        //01-Visa/HĐMB/ALEGO-VIMO
        $regular = '/^[a-zA-Z0-9_\/-]*$/';
        return self::_process($regular, $value);
    }

    // Chỉ được nhập chữ hoa , có ít nhất 1 ký tự chữ, dấu _ , không có kí tự đặc biệt
    public static function checkCode($value)
    {
        $regular = '/^[A-Z0-9][A-Z0-9_\/-]*$/';
        return self::_process($regular, $value);
    }

    // Chỉ được nhập chữ hoa không có ký tự đặc biệt
    public static function checkBankFullname($value)
    {
        $regular = '/^[A-Z][A-Z ]*$/';
        return self::_process($regular, $value);
    }

    // Có thể là chữ và số không có ký tự đặc biệt
    public static function checkBankNumber($value)
    {
        $regular = '/^[a-zA-Z0-9]*$/';
        return self::_process($regular, $value);
    }

    // Chỉ là chữ, không có kí tự đặc biệt
    public static function checkStringNotNumber($value)
    {
        $regular = '/^[a-zA-Z][a-zA-Z ]*$/';
        return self::_process($regular, $value);
    }

    // Chỉ là chữ, không có kí tự đặc biệt,có ít nhất một dấu cách
    public static function checkStringSpace($value)
    {
        $regular = '/^[a-zA-Z]+[ ][a-zA-Z ]*$/';
        return self::_process($regular, $value);
    }

    // Là chữ và số , không có kí tự đặc biệt,có ít nhất một dấu cách, có ít nhất 1 kí tự chữ
    public static function checkStringAndNumberSpace($value)
    {
        $regular = '/^[a-zA-Z][a-zA-Z0-9 ]*$/';
        return self::_process($regular, $value);
    }

    // Là chữ và số , không có kí tự đặc biệt
    public static function checkStringAddress($value)
    {
        $regular = '/^[a-zA-Z0-9][a-zA-Z0-9_,;\ ]*$/';
        return self::_process($regular, $value);
    }


    // Là số , không có kí tự đặc biệt có dấu chấm ở sau số đầu tiên
    public static function checkDiscountValue($value)
    {
        $regular = '/^[0-9-][0-9.]*$/';
        return self::_process($regular, $value);
    }

    public static function checkDateFormat($value)
    {
        $regular = '/^(3[0-1]|[1][0-9]|[2][0-9]|0[1-9])-(1[0-2]|0[1-9])-[0-9]{4}$/';
        return self::_process($regular, $value);
    }

    public static function checkContactFullname($value)
    {
        $regular = '/^[A-Z][a-zA-Z ]*$/';
        return self::_process($regular, $value);
    }

    public static function checkIDNumber($value)
    {
        $regular = '/^\d{9,12}$/';
        return self::_process($regular, $value);
    }

    public static function checkHoChieu($value)
    {
        $regular = '/^[A-Z]{1}[0-9]\d{6}$/';
        return self::_process($regular, $value);
    }

    public static function checkDayLimits($value)
    {
        $regular = '/^\d{1,2}(,\d{1,2})*$/';
        return self::_process($regular, $value);
    }

    public static function checkBincode($value)
    {
        $regular = '/^\d{6,8}$/';
        return self::_process($regular, $value);
    }

    public static function isArrayInteger($value)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!is_numeric($item)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    // Là chữ và số , không có kí tự đặc biệt
    public static function checkGiftCode($value)
    {
        $regular = '/^[a-zA-Z0-9_\/-]{6}$/';
        return self::_process($regular, $value);
    }

    public static function isVoucherCustomer($value)
    {
        $regular = '/^[A-Z0-9]{16}$/';
        return self::_process($regular, $value);
    }

    public static function isOTP($value)
    {
        $regular = '/^[A-Z0-9]{' . $GLOBALS['OTP_TRANSACTION_LENGTH'] . '}$/';
        return self::_process($regular, $value);
    }

    public static function checkImeiCode($value)
    {
        $regular = '/^[\S]*$/';
        return self::_process($regular, $value);
    }
}

?>