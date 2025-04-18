<?php
define('DEFAULT_KEY', 'system');
define('DEFAULT_LANG', 'vi');
define('DEFAULT_MESSAGE_VI', 'Lỗi hệ thống');
define('DEFAULT_MESSAGE_EN', 'Eror system');

function get_string($key = DEFAULT_KEY, $errorDescription = DEFAULT_MESSAGE_VI)
{
    $key = strtolower($key);
    $lang = DEFAULT_LANG;

    $fileLanguage = 'system.php';

    if (isset($key) && $key != DEFAULT_KEY) {
        $expKey = explode('_', $key, 2);
        if (isset($expKey[0]) && $expKey[0] <> '') {
            $fileLanguage = strtolower($expKey[0]) . '.php';
        }
    }

    $fileLanguage = LIBS . 'language' . DS . 'messages' . DS . $lang . DS . $fileLanguage;

    if (file_exists($fileLanguage)) {
        require_once $fileLanguage;
        if (isset($string[$key]) && $string[$key] <> '') {
            $errorDescription = $string[$key];
        }
    }

    return $errorDescription;
}
