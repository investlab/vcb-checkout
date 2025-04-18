<?php

use \yii\web\Request;

require(__DIR__ . '/global_define.php');
//$baseUrl = str_replace('/frontend/web', '', (new Request)->getBaseUrl());
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/../vendor',
    'language' => 'en-US',
    'components' => [
        // 'request' => [
        // 'baseUrl' => $baseUrl,
        //  'cookieValidationKey' => 'kAlIWDMpHU4otJ4t5IYK2qWcEpTqspzT',
        // 'csrfParam' => '_backendCSRF'
        // ],
        /* 'errorHandler' => [
          'errorAction' => 'error/index',
          ], */
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.11.17;dbname=bank_paygate_1',
            'username' => 'user_paygate',
            'password' => 'Zi2df#DFg4Vnad',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'nickcv\mandrill\Mailer',
//            'apikey' => 'zNA6bRVrZBZ4cyUsLHbELA',
            'apikey' => 'md-UFeB1h0lxIGLQyCoXLPurg',
        ],
//        'mailer' => [
//            'class' => 'yii\swiftmailer\Mailer',
//            'messageConfig' => [
//                'charset' => 'UTF-8',
//                'from' => ['noreply@vietcombank.nganluong.vn' => 'Cổng thanh toán Vietcombank'],
//            ],
////            'viewPath' => '@common/mail',
//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host' => MAILER_HOST, // e.g. smtp.mandrillapp.com or smtp.gmail.com
//                'username' => MAILER_USERNAME,
//                'password' => MAILER_PASSWORD,
//                'port' => MAILER_PORT, // Port 25 is a very common port too
//                'encryption' => MAILER_ENCRYPTION, // It is often used, check your provider or mail server specs
//                'streamOptions' => [
//                    'ssl' => [
//                        'allow_self_signed' => true,
//                        'verify_peer' => false,
//                        'verify_peer_name' => false,
//                    ],
//                ]
//            ]
//        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index1.php
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
//            'suffix' => '.html',
            'rules' => [
            ],
        ],
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                   
                ],
            ],
        ],
    ],
    // 'bootstrap' => ['gii'],
    'modules' => [
    //'gii' => [
    //'class' => 'yii\gii\Module',
    //'allowedIPs' => [''],
    //],
    ],
    'params' => require(__DIR__ . '/params.php'),
];
