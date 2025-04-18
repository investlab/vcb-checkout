<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 06/06/2016
 * Time: 2:51 PM
 */

return [
    'class' => '\common\components\libs\RewriteUrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '<controller>/<action>/<token_code:[0-9]+-[A-Z0-9]+>/<payment_method_code:[A-Z0-9\-]+>' => '<controller>/<action>',
        '<controller>/<action>/<token_code:[0-9]+-[A-Z0-9]+>' => '<controller>/<action>',
    ],
];