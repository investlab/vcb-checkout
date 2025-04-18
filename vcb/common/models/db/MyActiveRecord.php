<?php

namespace common\models\db;

use Yii;
use common\models\db\Right;
use common\models\db\User;
use common\components\libs\Tables;

class MyActiveRecord extends \yii\db\ActiveRecord
{

    public static $list_remove_opeators = null;

    protected static function _getCalledClassName()
    {
        return get_called_class();
    }

    final public static function getOperatorsForCheckAll()
    {
        $opetators = array();
        $class_name = self::_getCalledClassName();
        $temp = $class_name::getOperators();
        foreach ($temp as $key => $row) {
            if (@$row['check-all'] == true) {
                $opetators[$key] = $row;
            }
        }
        return $class_name::getOperatorsForUser(false, $opetators, $class_name);
    }

    final public static function getOperatorsForUser($data, $operators)
    {
        $module = Yii::$app->controller->module->id;
        $router = Yii::$app->controller->id;
        $class_name = self::_getCalledClassName();
        if ($class_name::$list_remove_opeators == null) {
            $class_name::_setListRemoveOperatorsForUser($module, $router, $class_name);
        }
        if (!empty($class_name::$list_remove_opeators)) {
            foreach ($class_name::$list_remove_opeators as $action => $operator) {
                if (array_key_exists($action, $operators)) {
                    unset($operators[$action]);
                }
            }
        }
        return $operators;
    }

    final protected static function _setListRemoveOperatorsForUser($module, $router, $class_name)
    {
        $operators = $class_name::getOperator();
        if (!empty($operators)) {
            foreach ($operators as $action => $operator) {
                if (isset($operator['router']) && !empty($operator['router'])) {
                    $action_right_code = strtoupper($module . '::' . $operator['router'] . '::' . $action);
                } else {
                    $action_right_code = strtoupper($module . '::' . $router . '::' . $action);
                }
                if (!self::_hasRight($action_right_code)) {
                    $class_name::$list_remove_opeators[$action] = $operator;
                }
            }
        }
    }

    final protected static function _hasRight($action_right_code)
    {
        if (Right::isCodeExists($action_right_code)) {
            if (User::hasRight($action_right_code)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public static function getOperator()
    {
        return array();
    }

    public static function findBySql($sql, $params = [])
    {
        if (!empty($params)) {
            Tables::setParamsAndConditions($sql, $params);
        }
        return parent::findBySql($sql, $params);
    }
}
