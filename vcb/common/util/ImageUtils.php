<?php

namespace common\util;

class ImageUtils
{

    public static $types = array('1' => 'gif', '2' => 'jpg', 3 => 'png');

    public static function checkImageSize($file, $size = 2000000)
    {
        if ($file == null || !is_array($file) || $file['size'] > $size) {
            return false;
        }
        return true;
    }

    public static function checkImageExtension($file)
    {
        $imageFileType = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file == null || !is_array($file) || ($imageFileType != "JPG" && $imageFileType != "PNG" && $imageFileType != "JPEG" && $imageFileType != "GIF")) {
            return false;
        }
        return true;
    }

    public static function isDataImageBase64($data)
    {
        if (preg_match('/^data:image\/\w+;base64,/', $data)) {
            return true;
        }
        return false;
    }

    public static function uploadImageBase64($data, $dir_path, $image_name)
    {
        try {
            $data = preg_replace('/^data:image\/\w+;base64,/', '', trim($data));
            if (is_dir($dir_path) || mkdir($dir_path, 0777, true)) {
                if (file_put_contents($dir_path . DS . $image_name, base64_decode($data)) !== false) {
                    return true;
                }
            }
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    /**
     *
     * @param type $image_path
     * @param type $image_new_path
     * @param type $new_with
     * @param type $new_height
     * @param type $jpeg_quality
     * @return boolean
     */
    public static function resizeImage($image_path, $image_new_path, $new_with, $new_height, $jpeg_quality = 80)
    {
        list($width, $height, $iType, $htmlattributes) = getimagesize($image_path);
        $image_stream = self::_getImageStream($image_path, self::$types[$iType]);
        if ($image_stream) {
            if (function_exists("imagecopyresampled")) {
                $resizedImageStream = imagecreatetruecolor($new_with, $new_height);
                if (self::$types[$iType] == 'png') {
                    self::_setTransparency($resizedImageStream, $image_stream);
                }
                imagecopyresampled($resizedImageStream, $image_stream, 0, 0, 0, 0, $new_with, $new_height, $width, $height);
            } else {
                $resizedImageStream = imagecreate($new_with, $new_height);
                if (self::$types[$iType] == 'png') {
                    self::_setTransparency($resizedImageStream, $image_stream);
                }
                imagecopyresized($resizedImageStream, $image_stream, 0, 0, 0, 0, $new_with, $new_height, $width, $height);
            }
            $dir_path = substr($image_new_path, 0, strrpos($image_new_path, DS));
            if (is_dir($dir_path) || mkdir($dir_path, 0777, true)) {
                imagejpeg($resizedImageStream, $image_new_path, $jpeg_quality);
                return true;
            }
        }
        return false;
    }

    private static function _setTransparency(&$new_image, $image_source)
    {
        $transparencyIndex = imagecolortransparent($image_source);
        $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
        if ($transparencyIndex >= 0) {
            $transparencyColor = imagecolorsforindex($image_source, $transparencyIndex);
        }
        $transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
        imagefill($new_image, 0, 0, $transparencyIndex);
        imagecolortransparent($new_image, $transparencyIndex);
    }

    private static function _getImageStream($image_path, $type)
    {
        switch ($type) {
            case 'gif':
                return @imagecreatefromgif($image_path);
                break;
            case 'jpg':
                return @imagecreatefromjpeg($image_path);
                break;
            case 'png':
                return @imagecreatefrompng($image_path);
                break;
        }
        return false;
    }
}
