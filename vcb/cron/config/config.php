<?php

return [
    'id' => 'cron',
    'name' => 'Cổng thanh toán Vietcombank',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'cron\controllers',
    'defaultRoute' => 'client',
    'components' => [
//        'request' => [
//            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
//            'csrfParam' => '_backendCSRF'
//        ],
        'user' => [
            'identityClass' => 'common\models\db\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_apiUser', // unique for frontend
                'path' => '/api/web'  // correct path for the frontend app.
            ],
        ],
//        'session' => [
//            'name' => '_creditSessionId', // unique for frontend
//            //'savePath' => __DIR__ . '/../runtime/sessions', // a temporary folder on frontend
//            'timeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
//        ],
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

        'urlManager' => require(__DIR__ . '/urlManager.php'),
    ],
];
