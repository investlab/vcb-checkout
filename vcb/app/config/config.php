<?php

return [
    'id' => 'app',
    'name' => 'Vietcombank',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['gii'],
    'controllerNamespace' => 'app\controllers',
    'defaultRoute' => 'client',
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ],
            'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
            'csrfParam' => '_backendCSRF'
        ],

        'user' => [
            'class' => '\yii\web\User',
            'identityClass' => 'common\models\db\UserLoginApp',
            'enableAutoLogin' => true,
            'loginUrl' => null,
            'authTimeout' => $GLOBALS['SESSION_LOGIN_TIMEOUT'],
            'identityCookie' => [
                'name' => '_appMerchantUser',
                'httpOnly' => true
            ],
        ],
        'session' => [
            'name' => '_creditSessionId', // unique for frontend
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

        'urlManager' => require(__DIR__ . '/urlManager.php'),
    ],
];
