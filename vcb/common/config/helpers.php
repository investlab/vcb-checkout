<?php

use Elastic\Apm\ElasticApm;

$req_id = uniqid();

if (class_exists('Elastic\Apm\ElasticApm')) {
    $transaction_apm = ElasticApm::getCurrentTransaction();
    $req_id = $transaction_apm->getTraceId();
}

define('REQ_ID', $req_id);
if (!function_exists("d")) {
    function d($data)
    {
        if (is_null($data)) {
            $str = "<i>NULL</i>";
        } elseif ($data == "") {
            $str = "<i>Empty</i>";
        } elseif (is_array($data)) {
            if (count($data) == 0) {
                $str = "<i>Empty array.</i>";
            } else {
                $str = "<table style=\"border-bottom:0px solid #000;\" cellpadding=\"0\" cellspacing=\"0\">";
                foreach ($data as $key => $value) {
                    $str .= "<tr><td style=\"background-color:#003bb3; color:#FFF;border:1px solid #000;\">" . $key . "</td><td style=\"border:1px solid #000;\">" . d($value) . "</td></tr>";
                }
                $str .= "</table>";
            }
        } elseif (is_resource($data)) {
            while ($arr = mysql_fetch_array($data)) {
                $data_array[] = $arr;
            }
            $str = d($data_array);
        } elseif (is_object($data)) {
            $str = d(get_object_vars($data));
        } elseif (is_bool($data)) {
            $str = "<i>" . ($data ? "True" : "False") . "</i>";
        } else {
            $str = $data;
            $str = preg_replace("/\n/", "<br>\n", $str);
        }
        return $str;
    }
}

if (!function_exists("dnl")) {
    function dnl($data)
    {
        echo d($data) . "<br>\n";
    }
}

if (!function_exists("dd")) {
    function dd($data)
    {
        echo dnl($data);
        exit;
    }

}

if (!function_exists("ddt")) {
    function ddt($message = "")
    {
        echo "[" . date("Y/m/d H:i:s") . "]" . $message . "<br>\n";
    }
}

if (!function_exists("pr")) {
    function pr($data){
        echo '<pre>';
        print_r($data);
        die();
    }

}

if (!function_exists("uniqidReal")) {
    function uniqidReal($lenght = 13) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }
}

if (!function_exists("get_client_ip")) {
    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}




