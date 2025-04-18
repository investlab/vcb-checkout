<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 22/03/2018
 * Time: 10:26 SA
 */
namespace common\models\business;

use common\components\libs\Image;
use common\components\utils\Strings;
use common\models\db\NewsImageContent;
use Yii;

class NewsImageContentBusiness
{
    public static function changeImageContent($html, $news_id, $news_title)
    {
//        $product = ProductBusiness::getByID($product_id);
        $image_arr = Image::getImageUrl($html, $news_id);
//        var_dump($image_arr);die;
        if (isset($image_arr['image']) && count($image_arr['image']) > 0) {
            foreach ($image_arr['image'] as $k => $v) {
                $http_name = self::get_string_between($v, 'https://', '/');
                $image_type = '';
                if ($http_name != 'manhthuongquan.vn') {
                    $image_name = self::get_string_between($v, '/', '.png');
                    if ($image_name == false) {
                        $image_type .= '.jpg';
                    } else {
                        $image_type .= '.png';
                    }
                } else {
                    $http_name = self::get_string_between($v, 'http://', '/');
                    if ($http_name != '192.168.11.14') {
                        $image_name = self::get_string_between($v, '/', '.png');
                        if ($image_name == false) {
                            $image_type .= '.jpg';
                        } else {
                            $image_type .= '.png';
                        }
                    }
                }

                if ($image_type != '') {
                    $image_content = new NewsImageContent();
                    $image_content->news_id = $news_id;
                    $image_content->image_source = $v;
//                    $image_content->image = ROOT_URL . 'product_content/' . date('', time()) . '/' . $image_name;
                    $image_content->status = 1;
                    $image_content->time_created = time();
                    if ($image_content->validate() && $image_content->save()) {
                        $id = $image_content->getDb()->getLastInsertID();
                        $image_content->image = IMAGES_PRODUCT_CONTENT_URL . 'news_content/' . date('ymd', time()) . '/' . Strings::convertNameForUrl($news_title) . '-' . $id . $image_type;
                        $image_content->save();
                    }
                }
            }
        }

        $content = Image::setImageUrl($html, $news_id);
        return $content;
    }

    static function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strrpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}