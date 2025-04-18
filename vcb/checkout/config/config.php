<?php

return [
    'id' => 'checkout',
    'name' => 'Cổng thanh toán Vietcombank',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'checkout\controllers',
    'defaultRoute' => 'sale-app',
    'components' => [
        'request' => [
            'class' => '\common\components\libs\LocationRequest',
            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
            'csrfParam' => '_backendCSRF',
            'enableCsrfCookie' => true,
            'csrfCookie' => [
                'httpOnly' => true, // Setting for Iframe
                'secure' => true, // Setting for Iframe
                'sameSite' => 'None', // Setting for Iframe
            ]
        ],
        'user' => [
            'identityClass' => 'common\models\db\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_checkoutUser', // Setting for Iframe
                'path' => '/;SameSite=None',  // Setting for Iframe
                'httpOnly'=> true, // Setting for Iframe
                'secure'=> true, // Setting for Iframe
            ],
        ],
        'session' => [
            'name' => '_checkoutSessionId', // unique for frontend
            //'savePath' => __DIR__ . '/../runtime/sessions', // a temporary folder on frontend
            'timeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'cookieParams' => [
                'httponly' => true, // Setting for Iframe
                'secure' => true, // Setting for Iframe
                'sameSite' => 'None', // Setting for Iframe
                'lifetime' => 30 * 24 * 3600, // Setting for Iframe
            ]
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
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning', 'error'],
                ],
            ],
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
