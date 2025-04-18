<?php

return [
    'class' => '\common\components\libs\RewriteUrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '<controller>/<action>/<card_token_id:[A-Z0-9\-]+>' => '<controller>/<action>',
    ],
];