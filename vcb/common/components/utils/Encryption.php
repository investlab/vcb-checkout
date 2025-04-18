<?php

namespace common\components\utils;

class Encryption
{
    /*
     * Ma hoa cho APP IOS
     * https://github.com/callmewhy/why-encrypt
     */

    static function encryptRSA($key, $data)
    {
        $lib_path = ROOT_PATH . DS . 'vendor' . DS . 'phpseclib';
        set_include_path($lib_path);
        include_once $lib_path . DS . 'Crypt' . DS . 'RSA.php';
        $rsa = new \Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_XML);
        //$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_XML);
        $rsa->loadKey($key);
        return $rsa->encrypt($data);
    }

    static function decryptRSA($key, $data)
    {
        $lib_path = ROOT_PATH . DS . 'vendor' . DS . 'phpseclib';
        set_include_path($lib_path);
        include_once $lib_path . DS . 'Crypt' . DS . 'RSA.php';
        $rsa = new \Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_XML);
        //$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_XML);
        $rsa->loadKey($key);
        return $rsa->decrypt($data);
    }

    public static function APPEncrypt($input, $key)
    {
        $sKey = self::pad_key($key);
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);

        return $data;
    }

    protected static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);

        return $text . str_repeat(chr($pad), $pad);
    }

    protected static function pad_key($key)
    {
        // key is too large
        if (strlen($key) > 32)
            return false;

        // set sizes
        $sizes = array(16, 24, 32);

        // loop through sizes and pad key
        foreach ($sizes as $s) {
            while (strlen($key) < $s)
                $key = $key . "\0";
            if (strlen($key) == $s)
                break; // finish if the key matches a size
        }

        // return
        return $key;
    }

    public static function APPDecrypt($sStr, $sKey)
    {
        $sKey = self::pad_key($sKey);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sKey, base64_decode($sStr), MCRYPT_MODE_ECB);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s - 1]);
        $decrypted = substr($decrypted, 0, -$padding);

        return $decrypted;
    }

    /*
     * Encrypt, Decrypt AES
     * http://www.phpaes.com/
     */

    public static function Encrypt($input, $key_seed)
    {
        $input = trim($input);
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $len = strlen($input);
        $padding = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);

        // generate a 24 byte key from the md5 of the seed
        $key = substr(md5($key_seed), 0, 24);
        $iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        // encrypt
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);
        // clean up output and return base64 encoded
        $encrypted_data = base64_encode($encrypted_data);
        return $encrypted_data;
    }

    public static function Decrypt($input, $key_seed)
    {
        $input = base64_decode($input);
        $key = substr(md5($key_seed), 0, 24);

        $text = mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, 'Mkd34ajdfka5');
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $packing = ord($text{strlen($text) - 1});

        if ($packing and ($packing < $block)) {
            for ($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--) {
                if (ord($text{$P}) != $packing) {
                    $packing = 0;
                }
            }
        }

        $text = substr($text, 0, strlen($text) - $packing);
        return $text;
    }

    /**
     * Ham ma haa OTP
     *
     * @param Strings  $otp
     *
     * @return unknown
     */
    public static function encodeOtp($Otp)
    {
        return md5($Otp . $GLOBALS['KEY_ENCODE_OTP']);
    }

    /**
     * Ham ma hoa mat khau,...
     *
     * @param  Strings $password
     *
     * @return unknown
     */
    public static function encodePassword($Password)
    {
        return sha1($GLOBALS['KEY_ENCODE_PASSWORD'] . $Password);
    }

    public static function encodePasswordAgent($Password)
    {
        return sha1($GLOBALS['KEY_ENCODE_PASSWORD_AGENT'] . $Password);
    }

    /**
     * Ham tao checksum
     *
     * @param unknown_type $Params
     * @return unknown
     */
    public static function makeApiChecksum($Params)
    {
        return hash_hmac('SHA1', $Params, $GLOBALS['API_PASSWORD']);
    }

    /**
     * Ham chuyen du lieu tu JSON sang ARRAY
     *
     * @param  Strings $Params
     *
     * @return array
     */
    public static function getApiParams($Params)
    {
        $Params = json_decode(base64_decode($Params), true);
        return $Params;
    }

    /**
     * Ham chuyen du lieu tu ARRAY sang JSON
     *
     * @param array $Params
     *
     * @return Strings
     */
    public static function makeApiReturnParams($Params)
    {
        return base64_encode(json_encode($Params));
    }
    
    public static function hashHmacSHA256($data, $key) {
        return hash_hmac('sha256', $data, $key);
    }
    
    public static function encryptAES($plain_text, $key, $cipher = 'aes-256-cbc') {
        $iv_len = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_len);
        $cipher_text = openssl_encrypt($plain_text, $cipher, $key, 0, $iv);
        return ['cipher_text' => base64_encode($cipher_text), 'iv' => bin2hex($iv)];
    }
    
    public static function decryptAES($cipher_text, $key, $iv, $cipher = 'aes-256-cbc') {
        $plain_text = openssl_decrypt(base64_decode($cipher_text), $cipher, $key, 0, hex2bin($iv));
        return $plain_text;
    }

    public static function EncryptTrippleDes($input, $key_seed){
        $input = trim($input);
        $encrypted_data = openssl_encrypt($input, 'des-ede3', $key_seed, OPENSSL_RAW_DATA);
        return base64_encode($encrypted_data);

    }


    public static function DecryptTrippleDes($data, $key_seed){
        $input = base64_decode($data);
        $output = openssl_decrypt($input, 'des-ede3', $key_seed, OPENSSL_RAW_DATA);
        return $output;
    }

}
