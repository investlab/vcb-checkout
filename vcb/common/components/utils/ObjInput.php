<?php
namespace common\components\utils;
class ObjInput
{

    protected static $arrValueGet = array();

    function __construct()
    {

    }

    /* Function name: params
     * @param $varName params name
     * @param $type type data respose
     * @param $defaultValue
     * @param $method GET or POST or FILES, default ""
     * @return value input
     * @note not empty $varName, $type
     * @author <thong.tnv@gmail.com>
     */

    public static function get($varName, $type, $defaultValue = '', $method = '', $expand = false)
    {
        $value = null;
        switch ($method) {
            case 'GET' :
                if (isset(self::$arrValueGet [$varName])) {
                    return self::$arrValueGet [$varName];
                } else {
                    $value = isset($_GET[$varName]) ? trim($_GET[$varName]) : '';
                    break;
                }
            case 'POST' :
                if (!$expand && isset($_POST[$varName])) {
                    if (is_array($_POST[$varName])) {
                        $value = $_POST[$varName];
                    } else {
                        $value = trim($_POST[$varName]);
                    }
                } elseif (isset($_POST[$varName])) {
                    if (is_array($_POST[$varName])) {
                        $value = $_POST[$varName];
                    } else {
                        $value = trim($_POST[$varName]);
                    }
                }
                break;
            case 'FILES' :
                $value = $_FILES[$varName];
                break;
            default :
                if (!empty($_POST[$varName]))
                    $value = $_POST[$varName];
                else if (!empty($_GET[$varName]))
                    $value = $_GET[$varName];
                else if (!empty($_REQUEST[$varName]))
                    $value = $_REQUEST[$varName];
                else if (!empty($_FILES[$varName]))
                    $value = $_FILES[$varName];
                break;
        }
        if ((!$value && $expand == false) || ($expand == true && is_null($value))) {
            $value = $defaultValue;
        }

        $value = self::doConvertType($value, $type);
        if ($method == 'GET' && !isset(self::$arrValueGet [$varName]))
            self::$arrValueGet [$varName] = $value;

        if (!is_array($value))
            $value = trim($value);

        return $value;
    }

    private static function doConvertType($value, $type)
    {
        switch ($type) {
            case 'str' :
                $newValue = $value;
                break;
            case 'def' :
                $newValue = $value;
                break;
            case 'int' :
                $newValue = (int)$value;
                break;
            case 'g_int' :
                $newValue = self::_getGroupInt($value);
                break;
            case 'double' :
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
                $newValue = doubleval($value);
                break;
            case 'currency_format' :
                $newValue = self::makeCurrency($value);
                break;
            default :
                if (get_magic_quotes_gpc() == 0) {
                    $newValue = htmlspecialchars($value);
                }
                break;
        }

        return $newValue;
    }

    private static function _getGroupInt($values)
    {
        $result = array();
        if (is_array($values) && !empty($values)) {
            foreach ($values as $key => $value) {
                $result[$key] = (int)$value;
            }
        }
        return $result;
    }

    /**
     * Remove HTML tags, including invisible text such as style and
     * script code, and embedded objects.  Add line breaks around
     * block-level tags to prevent word joining after tag removal.
     */
    function removeTagsHtml($string)
    {
        $string = html_entity_decode($string);
        $text = preg_replace(
            array(
                '@<(.*?)body(.*?)>@siu',
                '@<(.*?)meta(.*?)>@siu',
                '@<(.*?)head(.*?)>@siu',
                '@<(.*?)link(.*?)>@siu',
                '@<(.*?)script(.*?)>@siu',
                '@<(.*?)object(.*?)>@siu',
                '@<(.*?)embed(.*?)>@siu',
                '@<(.*?)applet(.*?)>@siu',
                '@<(.*?)noframes(.*?)>@siu',
                '@<(.*?)noscript(.*?)>@siu',
                '@<(.*?)noembed(.*?)>@siu',
                '@<(.*?)iframe(.*?)>@siu',
                '@<(.*?)frameset(.*?)>@siu',
                '@<(.*?)frame(.*?)>@siu',
                '@<(.*?)>@siu',
                ////////////////////////
                '@</?((address)|(blockquote)|(del))@siu',
                '@</?((form)|(button)|(input))@siu',
                '@</?((select)|(optgroup)|(option)|(textarea))@siu',
            ), '', $string);

        return $text;
    }

    static function stripTags($text)
    {
        if (is_array($text)) {
            $data = array();
            foreach ($text as $c => $key) {
                $data[$c] = self::removeTagsHtml($key);
            }
            $text = $data;
        } else {
            $text = self::removeTagsHtml($text);
        }

        return $text;
    }

    /*
     * Kiem tra ma captcha trong yii
     */
    static function captchaVerify($verifyImage)
    {
        $captcha = Yii::app()->getController()->createAction('captcha');
        $captcha = $captcha->verifyCode;

        return ($captcha == $verifyImage) ? true : false;
    }

    /*
     * Dinh dang tien te theo chuan tien te quoc te
     */
    static function makeCurrency($value)
    {
        if (isset($value) && $value > 0) {
            $value = number_format($value, 0, '.', ',');
            $value = str_replace('.00', '', $value);

            return $value;
        }

        return 0;
    }

    static function formatCurrencyNumber($string)
    {
        $string = trim($string);
        $string = str_replace('.', '', $string);
        $string = str_replace(',', '', $string);

        return $string;
    }
}

?>