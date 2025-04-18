<?php
/*
use \yii\web\Request;
$request = new Request();
$baseUrl = str_replace('/merchant/web', '', $request->getBaseUrl());
*/
return [
    'id' => 'merchant',
    'name' => 'merchant',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'merchant\controllers',
    'defaultRoute' => 'user-login/index',
    'components' => [
        'request' => [
            'class' => '\common\components\libs\LocationRequest',
            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
            'csrfParam' => '_salesCSRF',
            //'baseUrl' => $baseUrl,
        ],
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // do not publish the bundle
                    'js' => [
                    ]
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\db\UserLogin',
            'enableAutoLogin' => false,
            'loginUrl' => ['user-login/index'],
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_merchantUser',
                'path' => '/merchant/web/user-login/index'
            ],
        ],
        'session' => [
            'name' => 'merchantSessionId',
            //'savePath' => __DIR__ . '/../runtime/sessions',
            'timeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
        ],
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
        'formatter' => [
            'dateFormat' => 'd-M-Y',
            'datetimeFormat' => 'd-M-Y H:i:s',
            'timeFormat' => 'H:i:s',

            'locale' => 'vi-VN', //your language locale
            'defaultTimeZone' => 'Asia/Ho_Chi_Minh', // time zone
        ],
//        'mailer' => [
//            'class' => 'yii\swiftmailer\Mailer',
//            'viewPath' => '@common/mail',
//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.gmail.com',
//                'username' => '',
//                'password' => '',
//                'port' => '587',
//                'encryption' => 'tls',
//            ],
//        ],
        'urlManager' => require(__DIR__ . '/urlManager.php'),
    ],
];
