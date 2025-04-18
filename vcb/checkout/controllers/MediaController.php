<?php

namespace checkout\controllers;

use Yii;
use yii\web\Controller;

class MediaController extends Controller
{
//    const URL_ASSETS = 'https://vcb-assets.nganluong.vn';
    private $url_assets = 'https://vcb021.nganluong.vn';
    private $path_image_merchant_default = 'checkout' . DS . 'web' . DS . 'images' . DS;
    private $path_image_merchant = 'data' . DS . 'images' . DS . 'merchant' . DS;

    public function actionCheckoutMerchantLogo()
    {
        $path = Yii::$app->request->get('path');
        $response = \Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_RAW;
        $response->headers->add('content-type', 'image/jpg');
        if (trim($path) != "" && file_exists(IMAGES_MERCHANT_PATH . $path)) {

            $img_data = file_get_contents(IMAGES_MERCHANT_PATH . $path);
        } else {
            $img_data = file_get_contents(ROOT_PATH . DS .'checkout' . DS . 'web' . DS . 'images' . DS . "merchant_logo_default.png");

        }
        $response->data = $img_data;
        return $response;

    }
}