<?php
return [
    'id' => 'frontend',
    'name' => 'Cổng thanh toán Vietcombank',
    'language' => 'vi-VN',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'sale-app',
    'timeZone' => 'Asia/Ho_Chi_Minh', 
    'components' => [
        'request' => [
            'class' => '\common\components\libs\LocationRequest',
            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
            'csrfParam' => '_backendCSRF'
        ],
        'user' => [
            'identityClass' => 'common\models\db\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_checkoutUser', // unique for frontend
                'path' => '/checkout/web'  // correct path for the frontend app.
            ],
        ],
        'session' => [
            'name' => '_frontendSessionId', // unique for frontend
            //'savePath' => __DIR__ . '/../runtime/sessions', // a temporary folder on frontend
            'timeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
        ],
        'errorHandler' => [
            // 'errorAction' => 'default/error',
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
        'urlManagerFrontEnd' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '/v1.1/checkout/web',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'urlManager' => require(__DIR__ . '/urlManager.php'),

    ],
];
