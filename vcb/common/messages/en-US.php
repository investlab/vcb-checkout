<?php

use yii\helpers\ArrayHelper;

const LANGUAGE_CODE = 'en-US';
$messages = ArrayHelper::merge(
    require_once LANGUAGE_CODE . DS . 'default.php',
    require_once LANGUAGE_CODE . DS . 'cyber-source.php',
);
return $messages;
