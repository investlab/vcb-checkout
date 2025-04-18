<?php

namespace common\components\utils;

use Yii;

class Translate
{

    public static $content = array();

    private static function _getFilePath($language_code)
    {
        return ROOT_PATH . DS . 'common' . DS . 'messages' . DS . $language_code . '.php';
    }

    public static function get($content, $language_code = null)
    {
        $content = trim($content);
        if ($language_code == null) {
            $language_code = Yii::$app->language;
        }
        if ($language_code == 'vi-VN') {
            return $content;
        }
        if (!isset(self::$content[$language_code])) {
            $file_path = self::_getFilePath($language_code);
            if (file_exists($file_path)) {
                self::$content[$language_code] = require_once($file_path);
            } else {
                self::$content[$language_code] = array();
            }
        }
        if (!array_key_exists($content, self::$content[$language_code])) {
            $content = trim($content);
            self::$content[$language_code][$content] = $content;
        }
        return stripslashes(self::$content[$language_code][$content]);
    }

    public static function getV1($content, $language_code = null)
    {
        $content = trim($content);
        if ($language_code == null) {
            $language_code = Yii::$app->language;
        }
        if ($language_code == 'vi-VN') {
            return $content;
        }
        if (!isset(self::$content[$language_code])) {
            $file_path = self::_getFilePath($language_code);
            if (file_exists($file_path)) {
                self::$content[$language_code] = require_once($file_path);
            } else {
                self::$content[$language_code] = array();
            }
        }
        if (!array_key_exists($content, self::$content[$language_code])) {
            $content = trim($content);
            self::$content[$language_code][$content] = Strings::_convertToVCBSMSV1($content);
        }
        return stripslashes(self::$content[$language_code][$content]);
    }

    /**
     * Lấy nội dung đã dịch dựa trên ngôn ngữ và nội dung đầu vào.
     *
     * @param string $content Nội dung cần dịch.
     * @param string|null $language_code Mã ngôn ngữ (mặc định là null, sẽ sử dụng ngôn ngữ hiện tại của ứng dụng).
     * @param string $regex_text Biểu thức chính quy để xác định mẫu nội dung (mặc định là chuỗi rỗng).
     *
     * @return string Nội dung đã dịch hoặc nội dung gốc nếu không tìm thấy bản dịch.
     */
    public static function getV3($content, $language_code = null, $regex_text = '')
    {
        $content = trim($content);
        if ($language_code == null) {
            $language_code = Yii::$app->language;
        }
        if ($language_code == 'vi-VN') {
            return $content;
        }

        // Kiểm tra xem dữ liệu ngôn ngữ đã được tải chưa
        if (!isset(self::$content[$language_code])) {
            $file_path = self::_getFilePath($language_code);
            if (file_exists($file_path)) {
                self::$content[$language_code] = require_once($file_path);
            } else {
                self::$content[$language_code] = [];
            }
        }

        if ($regex_text !== '') {
            $pattern = '/^(' . $regex_text . ')(.+)$/';
            if (preg_match($pattern, $content, $matches)) {
                $fixedText = trim($matches[1]); // "Thanh toán bằng thẻ ATM ngân hàng"
                $variable = trim($matches[2]);  // Tên ngân hàng (Ví dụ: "Vietcombank")
                // Chuẩn bị chuỗi tìm kiếm với placeholder {bank}
                $placeholderText = $fixedText . ' {variable}';
                // Tìm chuỗi dịch trong tệp ngôn ngữ
                if (array_key_exists($placeholderText, self::$content[$language_code])) {
                    // Thay thế {bank} bằng tên ngân hàng thực tế trong chuỗi dịch
                    $translated_content = str_replace('{variable}', $variable,
                        self::$content[$language_code][$placeholderText]);
                    return stripslashes($translated_content);
                } else {
                    // Nếu không tìm thấy chuỗi mẫu, trả về chuỗi gốc
                    return $content;
                }
            }
        }
        // Kiểm tra xem đoạn text nhập vào có chứa tên ngân hàng không


        // Nếu không khớp với mẫu hoặc không chứa placeholder, dịch bình thường
        if (!array_key_exists($content, self::$content[$language_code])) {
            self::$content[$language_code][$content] = $content;
        }
        return stripslashes(self::$content[$language_code][$content]);
    }


    public static function saveFile()
    {
        if (ADD_TRANSLATE_AUTO) {
            if (!empty(self::$content)) {
                foreach (self::$content as $language_code => $rows) {
                    if ($language_code != 'vi-VN') {
                        $file = fopen(self::_getFilePath($language_code), 'w');
                        if ($file) {
                            $string = "<?php";
                            fwrite($file, $string . "\n");
                            $string = "return [";
                            fwrite($file, $string . "\n");
                            foreach ($rows as $key => $value) {
                                $string = "\t" . "'" . $key . "' => '" . addslashes($value) . "',";
                                fwrite($file, $string . "\n");
                            }
                            $string = "];";
                            fwrite($file, $string . "\n");
                            fclose($file);
                        }
                    }
                }
            }
        }
    }
}
