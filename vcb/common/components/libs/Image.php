<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 20/07/2017
 * Time: 1:57 CH
 */
namespace common\components\libs;

use common\models\db\ProductImageContent;
use Yii;

class Image
{
    public static function getImageUrl($html, $product_id)
    {
        preg_match_all('/<img[^>]+>/i', $html, $result);
        $img = array();

        foreach ($result[0] as $k => $v) {
//                preg_match_all('/(src)=("[^"]*")/i',$v, $img[$k]);
            preg_match('/src="([^"]*)"/i', $v, $img[$k]);
        }
        $img_arr = [
            'product_id' => $product_id
        ];
        foreach ($img as $k1 => $v1) {
            $img_arr['image'][$k1] = $v1[1];
        }
        return $img_arr;
    }

    /*
     * ROOT_URL + 'product_content/170720/sam-sung-j7-prime-10.jpg'
     * sam-sung-j7-prime  : tên sản phẩm
     * -10  : product_image_content.id
     */
    public static function setImageUrl($html, $product_id)
    {
        $product_image_content = ProductImageContent::find()->andWhere(['product_id' => $product_id])->all();
        if ($product_image_content != null && count($product_image_content) > 0) {
            foreach ($product_image_content as $k => $v) {
                $html = str_replace($v['image_source'], $v['image'], $html);
            }
        }
        return $html;
    }

    public static function getImageType($image_url)
    {
        return substr($image_url, strrpos($image_url, '.') + 1);
    }
}