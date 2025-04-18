<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set Time Zone required tu PHP 5

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'dev');


ini_set("display_startup_errors","1");
ini_set("display_errors","1");

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../../bootstrap.php');
require(__DIR__ . '/../../common/components/language/GetLanguageKeyString.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'), require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/config.php')
);

$application = new yii\web\Application($config);
$application->run();

common\components\utils\Translate::saveFile();
