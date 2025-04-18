<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\components\utils\Strings;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property Strings $title
 * @property Strings $link
 * @property Strings $properties
 * @property Strings $params
 * @property integer $parent_id
 * @property Strings $refer_code
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property integer $position
 * @property integer $status
 */
class Menu extends MyActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['properties', 'params'], 'string'],
            [['parent_id', 'left', 'right', 'level', 'position', 'status'], 'integer'],
            [['title', 'link'], 'string', 'max' => 255],
            [['refer_code'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'link' => 'Link',
            'properties' => 'Properties',
            'params' => 'Params',
            'parent_id' => 'Parent ID',
            'refer_code' => 'Refer Code',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
            'position' => 'Position',
            'status' => 'Status',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getCategories($categories)
    {
        $result = Tables::selectAllDataTable("`right`", "1 ", "`left` ASC ");
        if ($result != false) {
            foreach ($result as $row) {
                $categories[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
        }
        return $categories;
    }

    public static function getByReferCode($refer_code)
    {
        $result = array();
        //----
        $menu = self::getCacheData();
        if ($menu !== false && !empty($menu)) {
            foreach ($menu as $sub_menu) {
                if ($sub_menu['refer_code'] == $refer_code) {
                    $result = $sub_menu['childs'];
                    self::_setMenu($result);
                }
            }
        }
        return $result;
    }

    public static function getCacheData()
    {
        try {
            if (!file_exists(CACHE_PATH . 'menutop.cache')) {
                self::makeCacheData();
            }
            $data = file_get_contents(CACHE_PATH . 'menutop.cache');
            if ($data != false) {
                $data = json_decode($data, true);
            }
            return $data;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function makeCacheData()
    {
        try {
            $file = fopen(CACHE_PATH . 'menutop.cache', 'w');
            if ($file) {
                fwrite($file, json_encode(self::_toArray()));
                fclose($file);
                return true;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    private static function _toArray($parent_id = 0)
    {
        $menu = array();
        $menu_info = Tables::selectAllDataTable("menu", "parent_id = $parent_id ", "menu.left ASC ");
        if ($menu_info != false) {
            foreach ($menu_info as $row) {
                $menu[$row['id']] = $row;
                $menu[$row['id']]['properties'] = json_decode($row['properties'], true);
                $menu[$row['id']]['childs'] = self::_toArray($row['id']);
                $menu[$row['id']]['has_child'] = !empty($menu[$row['id']]['childs']);
            }
        }
        return $menu;
    }

    private static function _setMenu(&$menus, &$parent = false)
    {
        $index = 0;
        if (!empty($menus)) {
            foreach ($menus as $key => $row) {
                $menus[$key]['title'] = Strings::strip(@$row['title']);
                $menus[$key]['first'] = $index == 0 ? 'first' : '';
                $menus[$key]['active'] = false;
                $menus[$key]['description'] = @$row['properties']['description'];
                $menus[$key]['class'] = @$row['properties']['class'];
                $menus[$key]['image'] = @$row['properties']['image'];
                $menus[$key]['link'] = $row['link'];
                if (self::_isActive(html_entity_decode($row['link']))) {
                    $menus[$key]['active'] = true;
                    if ($parent != false) {
                        $parent['active'] = true;
                    }
                }
                if (self::_setMenu($menus[$key]['childs'], $menus[$key]) != 0) {
                    $menus[$key]['has_child'] = true;
                } else {
                    $menus[$key]['has_child'] = false;
                }
                if ($menus[$key]['active'] == true && $parent != false) {
                    $parent['active'] = true;
                }
                //--------
                if ($row['status'] == 1) {
                    $index++;
                }
            }
        }
        return $index;
    }

    private static function _isActive($link)
    {
        return false;
        /*
        $params = self::_getParamsByLink($link);
        if (!empty($params)) {
            $_GET['page'] = @$_GET['page'] == '' ? 'home' : $_GET['page'];
            foreach ($params as $key => $value) {
                if (@$_GET[$key] != $value) {
                    return false;
                }
            }
        }
        return true;*/
    }

    private static function _getParamsByLink($link)
    {
        $link = html_entity_decode($link);
        $result = array();
        if (preg_match_all('/[\?\&^]([a-zA-Z0-9_]+)[=]([^\&]*)/', $link, $temp)) {
            $count = count($temp[1]);
            for ($i = 0; $i < $count; $i++) {
                $result[$temp[1][$i]] = urldecode(@$temp[2][$i]);
            }
        }
        return $result;
    }

    public static function updateMenuProductCategory()
    {
        $refer_code = 'product_category';
        $refer_id = self::_getReferId($refer_code);
        if ($refer_id != 0 && self::_deleteRefer($refer_code, $refer_id)) {
            if (self::_addMenuProductCategory($refer_code, $refer_id)) {
                $result = self::_updateIndexCategory('menu');
                self::makeCacheData();
            }
        }
    }

    private static function _getReferId($refer_code)
    {
        $menu_info = Tables::selectOneDataTable("menu", "refer_code = '" . $refer_code . "' ");
        if ($menu_info != false) {
            return $menu_info['id'];
        }
        return 0;
    }

    private static function _deleteRefer($refer_code, $refer_id)
    {
        $sql = "DELETE FROM menu WHERE refer_code LIKE '" . $refer_code . "_%' ";
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sql);
        $result = $command->execute();
        return true;
    }

    private static function _addMenuProductCategory($refer_code, $menu_parent_id, $parent_id = 0)
    {
        // insert menutop
        $category_info = Tables::selectAllDataTable("product_category", "parent_id = $parent_id AND menu = 1 AND status = " . ProductCategory::STATUS_ACTIVE . " AND level <= 3 ", "product_category.left ASC ");
        if ($category_info != false) {
            foreach ($category_info as $row) {
                $row['name'] = \common\components\utils\Strings::strip($row['name']);
                if ($row['level'] > 1) {
                    $link = Yii::$app->urlManagerFrontEnd->createUrl(['product/index', 'category_name' => Strings::convertNameForUrl($row['name']), 'category_id' => $row['id']]);
                } else {
                    $link = Yii::$app->urlManagerFrontEnd->createUrl(['product/product-category-main', 'category_name' => Strings::convertNameForUrl($row['name']), 'category_id' => $row['id']]);
                }
                $properties = array('description' => $row['description']);
                if (trim($row['image']) != '') {
                    $properties = $properties['image'] = IMAGES_PRODUCT_CATEGORY_URL . $row['image'];
                }
                $menu_id = self::_insertMenu($menu_parent_id, $row['name'], $link, $refer_code . '_' . $row['id'], $properties, 1);
                if ($menu_id) {
                    if (!self::_addMenuProductCategory($refer_code, $menu_id, $row['id'])) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    private static function _insertMenu($parent_id, $title, $link, $refer_code, $properties, $status)
    {
        $inputs = array(
            'title' => $title,
            'link' => $link,
            'properties' => json_encode($properties),
            'params' => '',
            'parent_id' => $parent_id,
            'refer_code' => $refer_code,
            'status' => $status,
            'position' => 1,
        );
        $obj = new Menu();
        $obj->setAttributes($inputs);
        if ($obj->validate()) {
            if ($obj->save()) {
                return $obj->getDb()->getLastInsertID();
            }
        }
        return false;
    }

    protected static function _updateIndexCategory($table, $id = false)
    {
        $level = 1;
        $index = 0;
        $parent_id = 0;
        if ($id !== false && is_numeric($id)) {
            $category = Tables::selectOneDataTable($table, "$table.id = $id ");
            if ($category != false) {
                $level = $category['level'] - 1 == 0 ? $level : $category['level'] - 1;
                $index = $category['left'] - 1 < 0 ? $index : $category['left'];
                $parent_id = $category['parent_id'];
            }
        }
        $queries = self::_getQueryUpdateIndexCategory($table, $parent_id, $index, $level);
        if (!empty($queries)) {
            $connection = Yii::$app->getDb();
            foreach ($queries as $sql) {
                $command = $connection->createCommand($sql);
                $result = $command->execute();
            }
        }
        return true;
    }

    protected static function _getQueryUpdateIndexCategory($table, $parent_id = 0, &$index = 0, $level = 1)
    {
        $result = array();
        $category = Tables::selectAllDataTable($table, "$table.parent_id = $parent_id ", "$table.position ASC, $table.id ASC ");
        if ($category != false) {
            foreach ($category as $row) {
                $index++;
                $result["id_" . $row['id']] = "UPDATE $table SET $table.level = $level, $table.left = $index, ";
                $temp = self::_getQueryUpdateIndexCategory($table, $row['id'], $index, $level + 1);
                $index++;
                $result["id_" . $row['id']] .= "$table.right = $index WHERE $table.id = " . $row['id'] . " ;";
                if (!empty($temp)) {
                    $result = array_merge($result, $temp);
                }
            }
        }
        return $result;
    }

}
