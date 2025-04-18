<?php


namespace app\models\app;


use common\models\db\MyActiveRecord;

class UserLoginToken extends MyActiveRecord
{
    const STATUS_ACTIVE = 0;
    const STATUS_LOCK = 1;
    public static function tableName()
    {
        return 'user_login_token';
    }

}