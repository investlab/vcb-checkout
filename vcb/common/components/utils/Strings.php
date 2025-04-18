<?php

namespace common\components\utils;

class Strings
{

    public static function _convertToSMS($content)
    {
        $utf82abc = array(
            'à' => 'a',
            'á' => 'a',
            'ả' => 'a',
            'ã' => 'a',
            'ạ' => 'a',
            'ă' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'ặ' => 'a',
            'â' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e',
            'é' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ẹ' => 'e',
            'ê' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ệ' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'ỉ' => 'i',
            'ĩ' => 'i',
            'ị' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ỏ' => 'o',
            'õ' => 'o',
            'ọ' => 'o',
            'ô' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ộ' => 'o',
            'ơ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ợ' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'ủ' => 'u',
            'ũ' => 'u',
            'ụ' => 'u',
            'ư' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ự' => 'u',
            'ỳ' => 'y',
            'ý' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'ỵ' => 'y',
            'À' => 'A',
            'Á' => 'A',
            'Ả' => 'A',
            'Ã' => 'A',
            'Ạ' => 'A',
            'Ă' => 'A',
            'Ằ' => 'A',
            'Ắ' => 'A',
            'Ẳ' => 'A',
            'Ẵ' => 'A',
            'Ặ' => 'A',
            'Â' => 'A',
            'Ầ' => 'A',
            'Ấ' => 'A',
            'Ẩ' => 'A',
            'Ẫ' => 'A',
            'Ậ' => 'A',
            'Đ' => 'D',
            'È' => 'E',
            'É' => 'E',
            'Ẻ' => 'E',
            'Ẽ' => 'E',
            'Ẹ' => 'E',
            'Ê' => 'E',
            'Ề' => 'E',
            'Ế' => 'E',
            'Ể' => 'E',
            'Ễ' => 'E',
            'Ệ' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Ỉ' => 'I',
            'Ĩ' => 'I',
            'Ị' => 'I',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ỏ' => 'O',
            'Õ' => 'O',
            'Ọ' => 'O',
            'Ô' => 'O',
            'Ồ' => 'O',
            'Ố' => 'O',
            'Ổ' => 'O',
            'Ỗ' => 'O',
            'Ộ' => 'O',
            'Ơ' => 'O',
            'Ờ' => 'O',
            'Ớ' => 'O',
            'Ở' => 'O',
            'Ỡ' => 'O',
            'Ợ' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Ủ' => 'U',
            'Ũ' => 'U',
            'Ụ' => 'U',
            'Ư' => 'U',
            'Ừ' => 'U',
            'Ứ' => 'U',
            'Ử' => 'U',
            'Ữ' => 'U',
            'Ự' => 'U',
            'Ỳ' => 'Y',
            'Ý' => 'Y',
            'Ỷ' => 'Y',
            'Ỹ' => 'Y',
            'Ỵ' => 'Y',
            '̀' => '',
            '́' => '',
            '̉' => '',
            '̃' => '',
            '̣' => ''
        );

        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }
    public static function _convertToVCBSMS($content)
    {
        $utf82abc = array(
            'à' => 'a',
            'á' => 'a',
            'ả' => 'a',
            'ã' => 'a',
            'ạ' => 'a',
            'ă' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'ặ' => 'a',
            'â' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e',
            'é' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ẹ' => 'e',
            'ê' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ệ' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'ỉ' => 'i',
            'ĩ' => 'i',
            'ị' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ỏ' => 'o',
            'õ' => 'o',
            'ọ' => 'o',
            'ô' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ộ' => 'o',
            'ơ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ợ' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'ủ' => 'u',
            'ũ' => 'u',
            'ụ' => 'u',
            'ư' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ự' => 'u',
            'ỳ' => 'y',
            'ý' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'ỵ' => 'y',
            'À' => 'A',
            'Á' => 'A',
            'Ả' => 'A',
            'Ã' => 'A',
            'Ạ' => 'A',
            'Ă' => 'A',
            'Ằ' => 'A',
            'Ắ' => 'A',
            'Ẳ' => 'A',
            'Ẵ' => 'A',
            'Ặ' => 'A',
            'Â' => 'A',
            'Ầ' => 'A',
            'Ấ' => 'A',
            'Ẩ' => 'A',
            'Ẫ' => 'A',
            'Ậ' => 'A',
            'Đ' => 'D',
            'È' => 'E',
            'É' => 'E',
            'Ẻ' => 'E',
            'Ẽ' => 'E',
            'Ẹ' => 'E',
            'Ê' => 'E',
            'Ề' => 'E',
            'Ế' => 'E',
            'Ể' => 'E',
            'Ễ' => 'E',
            'Ệ' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Ỉ' => 'I',
            'Ĩ' => 'I',
            'Ị' => 'I',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ỏ' => 'O',
            'Õ' => 'O',
            'Ọ' => 'O',
            'Ô' => 'O',
            'Ồ' => 'O',
            'Ố' => 'O',
            'Ổ' => 'O',
            'Ỗ' => 'O',
            'Ộ' => 'O',
            'Ơ' => 'O',
            'Ờ' => 'O',
            'Ớ' => 'O',
            'Ở' => 'O',
            'Ỡ' => 'O',
            'Ợ' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Ủ' => 'U',
            'Ũ' => 'U',
            'Ụ' => 'U',
            'Ư' => 'U',
            'Ừ' => 'U',
            'Ứ' => 'U',
            'Ử' => 'U',
            'Ữ' => 'U',
            'Ự' => 'U',
            'Ỳ' => 'Y',
            'Ý' => 'Y',
            'Ỷ' => 'Y',
            'Ỹ' => 'Y',
            'Ỵ' => 'Y',
            '-' => '',
            '_' => '',
            '̀' => '',
            '́' => '',
            '̉' => '',
            '̃' => '',
            '̣' => ''
        );

        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }
    public static function _convertToVCBSMSV1($content)
    {
        $utf82abc = array(
            'à' => 'a',
            'á' => 'a',
            'ả' => 'a',
            'ã' => 'a',
            'ạ' => 'a',
            'ă' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'ặ' => 'a',
            'â' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e',
            'é' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ẹ' => 'e',
            'ê' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ệ' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'ỉ' => 'i',
            'ĩ' => 'i',
            'ị' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ỏ' => 'o',
            'õ' => 'o',
            'ọ' => 'o',
            'ô' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ộ' => 'o',
            'ơ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ợ' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'ủ' => 'u',
            'ũ' => 'u',
            'ụ' => 'u',
            'ư' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ự' => 'u',
            'ỳ' => 'y',
            'ý' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'ỵ' => 'y',
            'À' => 'A',
            'Á' => 'A',
            'Ả' => 'A',
            'Ã' => 'A',
            'Ạ' => 'A',
            'Ă' => 'A',
            'Ằ' => 'A',
            'Ắ' => 'A',
            'Ẳ' => 'A',
            'Ẵ' => 'A',
            'Ặ' => 'A',
            'Â' => 'A',
            'Ầ' => 'A',
            'Ấ' => 'A',
            'Ẩ' => 'A',
            'Ẫ' => 'A',
            'Ậ' => 'A',
            'Đ' => 'D',
            'È' => 'E',
            'É' => 'E',
            'Ẻ' => 'E',
            'Ẽ' => 'E',
            'Ẹ' => 'E',
            'Ê' => 'E',
            'Ề' => 'E',
            'Ế' => 'E',
            'Ể' => 'E',
            'Ễ' => 'E',
            'Ệ' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Ỉ' => 'I',
            'Ĩ' => 'I',
            'Ị' => 'I',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ỏ' => 'O',
            'Õ' => 'O',
            'Ọ' => 'O',
            'Ô' => 'O',
            'Ồ' => 'O',
            'Ố' => 'O',
            'Ổ' => 'O',
            'Ỗ' => 'O',
            'Ộ' => 'O',
            'Ơ' => 'O',
            'Ờ' => 'O',
            'Ớ' => 'O',
            'Ở' => 'O',
            'Ỡ' => 'O',
            'Ợ' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Ủ' => 'U',
            'Ũ' => 'U',
            'Ụ' => 'U',
            'Ư' => 'U',
            'Ừ' => 'U',
            'Ứ' => 'U',
            'Ử' => 'U',
            'Ữ' => 'U',
            'Ự' => 'U',
            'Ỳ' => 'Y',
            'Ý' => 'Y',
            'Ỷ' => 'Y',
            'Ỹ' => 'Y',
            'Ỵ' => 'Y',
//            '-' => '',
            '_' => '',
            '̀' => '',
            '́' => '',
            '̉' => '',
            '̃' => '',
            '̣' => ''
        );

        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    public static function _convertToCardHolder($content)
    {
        return strtoupper(self::_convertToSMS($content));
    }

    static function strip($text)
    {
        return stripslashes(stripslashes(stripslashes($text)));
    }

    /*
     * Chuyen so tien thanh chu
     */

    public static function convertStringToMoneyNumber($number)
    {
        $obj = new Converts;

        return $obj->readNumber($number);
    }

    public static function convertNameToRoute($name)
    {
        $result = '';
        $index = 0;
        while ($index < strlen($name)) {
            $char = substr($name, $index, 1);
            if (strtoupper($char) == $char) {
                $result .= '-' . strtolower($char);
            } else {
                $result .= $char;
            }
            $index++;
        }
        if (substr($result, 0, 1) == '-') {
            $result = substr($result, 1);
        }
        return $result;
    }

    public static function convertNameForUrl($name)
    {
        $name = self::_convertToSMS(trim($name));
        $name = preg_replace('/[^\w]/', ' ', $name);
        $name = str_replace(array('(', ')', '/'), ' ', $name);
        $name = trim($name);
        $name = str_replace(' ', '-', $name);
        $name = self::_replaceAll('--', '-', $name);
        return strtolower($name);
    }

    private static function _replaceAll($search, $replace, $value)
    {
        while (strpos($value, $search) !== false) {
            $value = str_replace($search, $replace, $value);
        }
        return $value;
    }

    public static function antiSQL($value)
    {
        if (is_string($value)) {
            $value = preg_replace(self::mb_sql_regcase('/(from|select|insert|update|delete|where|drop table|show tables|union)/'), '', $value);
            return trim(addslashes($value));
        }
        return $value;
    }

    private static function mb_sql_regcase($string, $encoding = 'auto')
    {
        $max = mb_strlen($string, $encoding);
        $ret = '';
        for ($i = 0; $i < $max; $i++) {
            $char = mb_substr($string, $i, 1, $encoding);
            $up = mb_strtoupper($char, $encoding);
            $low = mb_strtolower($char, $encoding);
            $ret .= ($up != $low) ? '[' . $up . $low . ']' : $char;
        }
        return $ret;
    }

    public static function getBinCode($card_number)
    {
        return substr($card_number, 0, 8);
    }

    public static function removeTagHTML($tagSource)
    {
        $searchTags = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@([\r\n])[\s]+@', // Strip out white space
            '@&(quot|#34);@i', // Replace HTML entities
            '@&(amp|#38);@i',
            '@&(lt|#60);@i',
            '@&(gt|#62);@i',
            '@&(nbsp|#160);@i',
            '@&(iexcl|#161);@i',
            '@&(cent|#162);@i',
            '@&(pound|#163);@i',
            '@&(copy|#169);@i',
            '@&#(\d+);@e'
        ); // evaluate as php

        $replaceTags = array(
            '',
            '',
            '\1',
            '"',
            '&',
            '<',
            '>',
            ' ',
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            'chr(\1)'
        );
        return preg_replace($searchTags, $replaceTags, $tagSource);
    }

    public static function convertNumberToString($number)
    {
        $result = array();
        $position = 0;
        $length = strlen($number);
        do {
            $unit = self::_getUnit($position);
            $position += 3;
            $value = substr($number, (-1) * $position, $position < $length ? 3 : $length - $position + 3);
            $temp = self::_getStringForNumber($value);
            if ($temp != '') {
                $result[] = self::_getStringForNumber($value) . ' ' . $unit;
            }
        } while ($position < $length);
        krsort($result);
        return trim(implode(' ', $result));
    }

    private static function _getStringForNumber($number)
    {
        if (intval($number) != 0) {
            if (strlen($number) == 3) {
                $cents = substr($number, 0, 1);
                $tens = substr($number, 1, 1);
                $units = substr($number, 2, 1);
                return trim(self::_getStringForNumberCents($cents, $tens, $units) . ' ' . self::_getStringForNumberTens($cents, $tens, $units) . ' ' . self::_getStringForNumberUnits($cents, $tens, $units));
            } elseif (strlen($number) == 2) {
                $cents = 0;
                $tens = substr($number, 0, 1);
                $units = substr($number, 1, 1);
                return trim(self::_getStringForNumberTens($cents, $tens, $units) . ' ' . self::_getStringForNumberUnits($cents, $tens, $units));
            } else {
                $cents = 0;
                $tens = 0;
                $units = $number;
                return trim(self::_getStringForNumberUnits($cents, $tens, $units));
            }
        }
        return '';
    }

    private static function _getStringForNumberCents($cents, $tens, $units)
    {
        return self::_getNameForNumber($cents) . ' trăm';
    }

    private static function _getStringForNumberTens($cents, $tens, $units)
    {
        if ($tens == 1) {
            return 'mười';
        } elseif ($tens == 0 && $units != 0) {
            return 'linh';
        } elseif ($tens == 0 && $units == 0) {
            return '';
        } else {
            return self::_getNameForNumber($tens) . ' mươi';
        }
    }

    private static function _getStringForNumberUnits($cents, $tens, $units)
    {
        if ($units == 0) {
            return '';
        } elseif ($units == 1 && $tens > 1) {
            return 'mốt';
        } elseif ($units == 4 && $tens > 1) {
            return 'tư';
        } elseif ($units == 5 && $tens > 0) {
            return 'lăm';
        } else {
            return self::_getNameForNumber($units);
        }
    }

    private static function _getUnit($position)
    {
        $units = array(
            3 => 'nghìn',
            6 => 'triệu',
            9 => 'tỷ',
        );
        return @$units[$position];
    }

    private static function _getNameForNumber($number)
    {
        $names = array(
            '0' => 'không',
            '1' => 'một',
            '2' => 'hai',
            '3' => 'ba',
            '4' => 'bốn',
            '5' => 'năm',
            '6' => 'sáu',
            '7' => 'bảy',
            '8' => 'tám',
            '9' => 'chín',
        );
        return @$names[$number];
    }

    public static function upperCase($str, $encoding = 'UTF-8')
    {
        if (self::_isEncodingExist($encoding)) {
            return mb_convert_case($str, MB_CASE_UPPER, $encoding);
        }
        return $str;
    }

    public static function lowerCase($str, $encoding = 'UTF-8')
    {
        if (self::_isEncodingExist($encoding)) {
            return mb_convert_case($str, MB_CASE_LOWER, $encoding);
        }
        return $str;
    }

    private static function _isEncodingExist($encoding)
    {
        $encodings = mb_list_encodings();
        return in_array($encoding, $encodings);
    }

    public static function encodeCardNumber($card_number)
    {
        //return substr($card_number, 0, 8).substr(md5($card_number), 0, 16).substr($card_number, -4);
        return substr($card_number, 0, 4) . '.' . substr($card_number, 4, 4) . '.XXXX.' . substr($card_number, -4);
    }
    
    public static function encodeCreditCardNumber($card_number)
    {
        //return substr($card_number, 0, 8).substr(md5($card_number), 0, 16).substr($card_number, -4);
        return substr($card_number, 0, 6) . '.XXXX.XXXX.' . substr($card_number, -4);
    }

    public static function decodeCardNumber($card_number_encode)
    {
        //return substr($card_number_encode, 0, 4).'.'.substr($card_number_encode, 4, 4).'.XXXX.'.substr($card_number_encode, -4);
        return $card_number_encode;
    }

    public static function base64URLEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64URLDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function maxString($str, $max, $append = ' ...', $search = array(' ', '.', ',', ';', '/'), $strip = true)
    {
        $str = trim($str);
        if ($strip) {
            $str = self::strip($str);
        }
        $length = $max;
        if (strlen($str) > $max) {
            while (!in_array(substr($str, $length, 1), $search) && $length > 0) {
                $length--;
            }
            while (in_array(substr($str, $length, 1), $search) && $length > 0) {
                $length--;
            }
            if ($length <= 0) {
                $str = substr($str, 0, $max) . $append;
            } else {
                $str = substr($str, 0, $length + 1) . $append;
            }
        }
        return $str;
    }

    public static function getNewFileName($file_name, $new_name, &$extension = '')
    {
        $postion = strrpos($file_name, '.');
        if ($postion !== false) {
            $extension = substr($file_name, $postion + 1);
            return $new_name . '.' . $extension;
        }
        return $file_name;
    }
}
