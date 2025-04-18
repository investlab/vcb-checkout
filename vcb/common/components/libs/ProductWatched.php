<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 23/08/2017
 * Time: 4:37 CH
 */

namespace common\components\libs;

use Yii;

class ProductWatched
{

    private static $items = null;

    public static function getItems()
    {
        self::load();
        return self::$items;
    }

    public static function addItem($item_id)
    {
        self::load();
        if (!isset(self::$items[$item_id])) {
            if (self::$items != null && count(self::$items) > 0) {
                $time = time();
                $key = '';
                foreach (self::$items as $k => $v) {
                    if (count(self::$items) >= 10 && $v['key'] < $time) {
                        $time = $v['key'];
                        $key = $k;
                    }
                }

                if ($key !== '') {
                    self::removeItem($key);
                }
            }

            if (count(self::$items) < 10) {
                self::$items[$item_id] = [
                    'key' => time(),
                    'value' => $item_id
                ];
            }
            self::save();
        }
    }

    public static function checkHas($item_id)
    {
        self::load();
        if (self::$items != null && count(self::$items) > 0) {
            if (array_key_exists($item_id, self::$items)) {
                return false;
            }
        }
        return true;
    }

    public static function removeItem($item_id)
    {
        self::load();
        if (array_key_exists($item_id, self::$items)) {
            unset(self::$items[$item_id]);
            self::save();
            return true;
        }
        return false;
    }

    public static function clear()
    {
        $session = Yii::$app->session;
        $session->set('PRODUCT_WATCHED_NEW', array(
            'items' => null
        ));
    }

    public static function load()
    {
        $session = Yii::$app->session;
        $product_watched = $session->get('PRODUCT_WATCHED_NEW');
        self::$items = $product_watched['items'];
    }

    public static function save()
    {
        $session = Yii::$app->session;
        $session->set('PRODUCT_WATCHED_NEW', array(
            'items' => self::$items
        ));
    }
}