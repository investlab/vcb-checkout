<?php
namespace common\components\utils;
class LogsFile
{

    /*
     * Thuc hien tao file log theo ten file va du lieu truyen vao
     * $file_name: duong dan file + ten file, ex: /logs/add-user-admin-2016-02-18.txt
     * $data: Du lieu ghi log la mot string KHONG phai mang
     */
    public static function writeFileLog($file_name, $data)
    {
        $fp = @fopen($file_name, 'a');
        if ($fp) {
            $line = date("H:i, d/m/Y:  ", time()) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
        }
    }

}
