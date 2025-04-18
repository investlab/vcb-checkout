<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set Time Zone required tu PHP 5
error_reporting(E_ERROR);
require __DIR__ . '/../../common/config/helpers.php';
$debug_enable = false;
$debug_enable = @in_array(get_client_ip(), ["::1", "14.177.239.244", "101.99.7.213", "172.26.0.1"]);
//$debug_enable = false;
defined('YII_DEBUG') or define('YII_DEBUG', $debug_enable);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../../bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/config.php')
);

$application = new common\components\libs\LocationApplication($config);
if (MAINTENANCE_MODE) {
    header("Location: " . ROOT_URL . "vi" . DS . "checkout" . DS . "maintenance.php");
    die();
}

$application->run();
