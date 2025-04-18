<?php

namespace common\models\business;

use common\models\db\Right;
use common\models\db\UserGroupRight;
use common\components\libs\Tables;
use Yii;

class UserRoleBusiness
{
    public static function getListRole()
    {
        return Right::find()->where(['type' => Right::TYPE_BACKEND])->addOrderBy('left')->all();
    }

    public static function getListGroupRoles()
    {
        return UserGroupRight::find()->all();
    }

    public static function getUserGroupRightByGroup($user_group_id)
    {
        $query = UserGroupRight::find();
        $query->andWhere(['user_group_id' => $user_group_id]);
        $data = $query->asArray()->all();
        return $data;
    }
} 