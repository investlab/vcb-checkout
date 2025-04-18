<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 06/06/2016
 * Time: 2:51 PM
 */

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'home.html' => 'default/index',
        'login.html' => 'user/login',
        'logout' => 'user/logout',
        'user.html' => 'user/index',

    ],
];