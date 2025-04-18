<?php
use \yii\web\Request;

$backendUrl = (new \yii\web\Request())->getBaseUrl();
$baseSalesUrl = str_replace('/backend/web', '/sales/web', (new Request)->getBaseUrl());
return [
    'adminEmail' => 'no-reply-vietcombank@nganluong.vn',
    'supportEmail' => 'support@xyz.xxx',
    'user.passwordResetTokenExpire' => 2800,
    'backendUrl' => $backendUrl,
    'salesUrl' => $baseSalesUrl,
];
