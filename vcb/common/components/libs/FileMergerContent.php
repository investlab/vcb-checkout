<?php

namespace common\components\libs;

class FileMergerContent
{

    public static function getFileMergerName($dir_path, $extension, $file_paths, $prefix = '', $time_expired = 3600)
    {
        $file_name = self::getFileName($file_paths, $extension, $prefix);
        if ($file_name != false) {
            if (self::_isExpired($dir_path . $file_name, $time_expired)) {
                $content = self::mergerContent($file_paths, $extension);
                if ($content == '' || self::_makeFile($dir_path, $file_name, $content) == false) {
                    return false;
                }
            }
            return $file_name;
        }
        return false;
    }

    private static function _isExpired($file_path, $time_expired)
    {
        if (file_exists($file_path) && (filemtime($file_path) + $time_expired) > time()) {
            return false;
        }
        return true;
    }

    private static function _makeFile($dir_path, $file_name, $content)
    {
        if (is_dir($dir_path) || mkdir($dir_path, 0777, true)) {
            $file = fopen($dir_path . $file_name, 'w');
            if ($file) {
                fwrite($file, $content);
                fclose($file);
                return true;
            }
        }
        return false;
    }

    public static function getFileName($file_paths, $extension, $prefix = '')
    {
        if (!empty($file_paths)) {
            $temp = array();
            foreach ($file_paths as $file_path) {
                if (self::_getExtension($file_path) == $extension) {
                    $temp[] = $file_path;
                }
            }
            $temp = implode(',', $temp);
            return $prefix . md5($temp) . '.' . $extension;
        }
        return false;
    }

    public static function mergerContent($file_paths, $extension)
    {
        $result = '';
        if (!empty($file_paths)) {
            foreach ($file_paths as $file_path) {
                if (self::_getExtension($file_path) == $extension && file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    if ($content != false) {
                        $result .= $content;
                    }
                }
            }
        }
        return $result;
    }

    private static function _getExtension($file_path)
    {
        return substr($file_path, strrpos($file_path, '.') + 1);
    }
}

