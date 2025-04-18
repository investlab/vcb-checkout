<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__FILE__));


define('ROOT_URL', 'http://localhost/xpay/nl_x_pay/');
define('ROOT_FOLDER', '');

define('DOMAIN', 'XPay.com');

define('LIBS', ROOT_PATH . DS . 'libraries' . DS);
define('CACHE_PATH', ROOT_PATH . DS . 'data' . DS . 'cache' . DS);
define('LOG_PATH', ROOT_PATH . DS . 'data' . DS . 'logs' . DS);
define('IMAGES_PATH', ROOT_PATH . DS . 'images' . DS);
define('IMAGES_URL', ROOT_URL . 'data/images/');
define('IMAGES_PAYMENT_METHOD_PATH', ROOT_PATH . DS . 'data' . DS . 'images' . DS . 'payment_method' . DS);
define('IMAGES_PAYMENT_METHOD_URL', ROOT_URL . '/data/images/payment_method/');

define('IMAGES_MERCHANT_PATH', ROOT_PATH . DS . 'data' . DS . 'images' . DS . 'merchant' . DS);
define('IMAGES_MERCHANT_URL', ROOT_URL . '/data/images/merchant/');

define('IMPORT_CASHOUT_REQUEST_PATH', ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'import_cashout_request' . DS);

//Config admin page
define('MAX_PAGE_DISPLAY', 10);
define('MAX_SIZE_PAGE', 25);
define('NEXT_PAGE', '>');
define('PREV_PAGE', '<');

define('NGANLUONG_URL', 'https://www.nganluong.vn/');
define('NGANLUONG_MERCHANT_ID', '53578');
define('NGANLUONG_MERCHANT_PASSWORD', '6c43ff2ba8b9aa1240c28be235b62d0e');
define('NGANLUONG_RECEIVER_EMAIL', 'haibt@peacesoft.net');


define('NGANLUONG_SANDBOX_URL', 'https://sandbox.nganluong.vn/nl35');
define('NGANLUONG_SANDBOX_MERCHANT_ID', '48001');
define('NGANLUONG_SANDBOX_MERCHANT_PASSWORD', '7cc7ce5682601f760f5b806b32fd391f');
define('NGANLUONG_SANDBOX_RECEIVER_EMAIL', 'user@yopmail.com');





define('CALL_API_WITHDRAW', true);

define('MAILER_HOST', 'smtp.gmail.com');
define('MAILER_USERNAME', 'manhthuongquan.ps@gmail.com');
define('MAILER_PASSWORD', 'mtq123456789');
define('MAILER_PORT', '587');
define('MAILER_ENCRYPTION' , 'tls');
define('MAILER_SOURCE' , 'sm.mtq@peacesoft.net');

define('ADD_TRANSLATE_AUTO', false);

//debug
define('SHOW_SQL', false);
$GLOBALS['DEBUG']['SQL'] = array();
