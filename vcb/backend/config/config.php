<?php

return [
    'id' => 'backend',
    'name' => 'PayGate',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute' => 'default',
    'language' => 'en-US',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
            'csrfParam' => '_backendCSRF'   // giữ session
        ],
        'user' => [
            'identityClass' => 'common\models\db\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_backendUser', // unique for frontend
                'path' => '/backend/web'  // correct path for the frontend app.
            ],
        ],
        'session' => [
            'name' => '_backendSessionId', // unique for frontend
           // 'savePath' => __DIR__ . '/../../runtime/sessions', // a temporary folder on frontend
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
        'urlManager' => require(__DIR__ . '/urlManager.php'),

    ],
];
