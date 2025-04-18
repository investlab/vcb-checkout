<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set Time Zone required tu PHP 5
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../../bootstrap.php');
require(__DIR__ . '/../../common/components/language/GetLanguageKeyString.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/config.php')
);


$application = new yii\web\Application($config);
$application->run();
