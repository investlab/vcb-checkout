<?php

namespace api\components;

use Yii;

/**
 * @author Le Huy Phuong <lehuyphuong1982@gmail.com>
 */
class MobileSession
{

    private static $life_time = 86400; // second
    private static $save_path = null;
    private static $session_id = null;

    public static function start($prefix = '')
    {
        if (self::_init($prefix)) {
            return true;
        }
        return false;
    }

    public static function setSessionId($session_id)
    {
        self::$session_id = $session_id;
    }

    public static function getSessionId()
    {
        return self::$session_id;
    }

    private static function _init($prefix)
    {
        if (self::$save_path == null) {
            self::$save_path = ROOT_PATH . DS . 'data' . DS . 'sessions' . DS;
            if (!is_dir(self::$save_path) && !mkdir(self::$save_path, 0777)) {
                return false;
            }
            self::clearAllSessionExpire(time() - self::$life_time);
        }
        self::_createSessionId($prefix);
        return true;
    }

    public static function load()
    {
        if (self::isNotExpire()) {
            $session_file = self::_getFilePath();
            return self::_loadData($session_file);
        }
        return false;
    }

    private static function _loadData($session_file)
    {
        $session_data = file_get_contents($session_file);
        return json_decode($session_data, true);
    }

    public static function get($key)
    {
        $session_data = self::load();
        if ($session_data != false) {
            if (array_key_exists($key, $session_data)) {
                return $session_data[$key];
            }
        }
        return null;
    }

    public static function set($data)
    {
        if (is_array($data)) {
            $old_data = self::load();
            if ($old_data != false) {
                $data = array_merge($old_data, $data);
            }
            return self::_save($data);
        }
        return false;
    }

    public static function destroy()
    {
        @unlink(self::_getFilePath());
        return true;
    }

    public static function isExists($session_file)
    {
        return file_exists($session_file);
    }

    public static function isNotExpire()
    {
        $session_file = self::_getFilePath();
        if (self::isExists($session_file) && !self::_isExpire($session_file)) {
            return true;
        }
        return false;
    }

    public static function clearAllSessionExpire($time)
    {
        if (self::$save_path != null) {
            $session_files = glob(self::$save_path . "sess_*");
            if (!empty($session_files)) {
                foreach ($session_files as $file) {
                    if (file_exists($file) && filemtime($file) + self::$life_time < $time) {
                        @unlink($file);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public static function getBySessionId($session_id, $key)
    {
        $session_file = ROOT_PATH . DS . 'data' . DS . 'sessions' . DS . 'sess_' . $session_id . '.txt';
        if (self::isExists($session_file) && !self::_isExpire($session_file)) {
            $session_data = self::_loadData($session_file);
            if ($session_data != false) {
                if (array_key_exists($key, $session_data)) {
                    return $session_data[$key];
                }
            }
        }
        return null;
    }

    private static function _isExpire($session_file)
    {
        if (filemtime($session_file) + self::$life_time < time()) {
            return true;
        }
        return false;
    }

    private static function _createSessionId($prefix = '')
    {
        if (self::$session_id == null) {
            self::setSessionId($prefix . date('Ymd') . uniqid() . rand(10, 99));
        }
    }

    private static function _getFilePath()
    {
        if (self::$save_path != null) {
            return self::$save_path . 'sess_' . self::$session_id . '.txt';
        }
        return false;
    }

    private static function _save($data)
    {
        $session_file = self::_getFilePath();
        return file_put_contents($session_file, json_encode($data));
    }

}
