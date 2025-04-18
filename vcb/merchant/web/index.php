<?php

//ini_set('display_errors', false);
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set Time Zone required tu PHP 5

require __DIR__ . '/../../common/config/helpers.php';
$debug_enable = false;
$debug_enable = @in_array(get_client_ip(), ["::1", "14.177.239.244", "101.99.7.213", "172.26.0.1"]);
//$debug_enable = false;
defined('YII_DEBUG') or define('YII_DEBUG', $debug_enable);
define('YII_ENV', 'prod');

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../../bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local-merchant.php'),
    require(__DIR__ . '/../config/config.php')
);


$application = new common\components\libs\LocationApplication($config);
$application->run();