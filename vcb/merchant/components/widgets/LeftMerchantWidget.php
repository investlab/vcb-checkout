<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace merchant\components\widgets;

use Yii;
use yii\helpers\Url;
use yii\base\Widget;
use yii\helpers\Html;
use common\components\utils\Translate;

class LeftMerchantWidget extends Widget
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $menu = $this->_getMenu();
        $this->_setMenuActive($menu);
        return $this->render('left_merchant_widget', [
            'menu' => $menu,
        ]);
    }
    
    private function _setMenuActive(&$menu) {
        $current_url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id.'/'.Yii::$app->controller->action->id]);
        foreach ($menu as $key => $item) {
            foreach ($item['notes'] as $sub_key => $sub_item) {
                if ($this->_isCurrentUrl($current_url, $sub_item)) {
                    $menu[$key]['class'] = 'active';
                    $menu[$key]['notes'][$sub_key]['class'] = 'act';
                    return;
                }
            }
        }
    }
    
    private function _isCurrentUrl($current_url, $sub_item) {
        if (strpos($sub_item['url'], $current_url) !== false) {
            return true;
        } elseif (isset($sub_item['others_url']) && !empty($sub_item['others_url'])) {
            foreach ($sub_item['others_url'] as $other_url) {
                if (strpos($other_url, $current_url) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function _getMenu() {
        return array(
            'Menu01' => [
                'title' => 'Quản trị tài khoản',
                'class' => '',
                'notes' => [
                    [
                        'title' => 'Thông tin tài khoản',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['user-info/index']),
                        'icon' => 'icon-Ainfo',
                        'class' => '',
                    ],
                    [
                        'title' => 'Đổi mật khẩu đăng nhập',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['user-info/change-password']),
                        'icon' => 'icon-pass',
                        'class' => '',
                    ],
                ],
            ],
            'Menu02' => [
                'title' => 'Tích hợp thanh toán',
                'class' => '',
                'notes' => [
                    [
                        'title' => 'Thông tin merchant',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['merchant/index']),
                        'icon' => 'icon-Ainfo',
                        'class' => '',
                        'others_url' => [
                            Yii::$app->urlManager->createAbsoluteUrl(['merchant/update']),
                        ]
                    ],
                    [
                        'title' => 'Đổi mật khẩu kết nối',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['merchant/change-password']),
                        'icon' => 'icon-pass',
                        'class' => '',
                    ],
                ],
            ],
            'Menu03' => [
                'title' => 'Giao dịch thanh toán',
                'class' => '',
                'notes' => [
                    [
                        'title' => 'Lịch sử giao dịch',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/index']),
                        'icon' => 'icon-history',
                        'class' => '',
                        'others_url' => [
                            Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/detail']),
                        ]
                    ],
//                    [
//                        'title' => 'Rút tiền',
//                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw']),
//                        'icon' => 'icon-wtdraw',
//                        'class' => '',
//                        'others_url' => [
//                            Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-detail']),
//                            Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-verify']),
//                            Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-cancel']),
//                        ]
//                    ],
                ],
            ],
            /*'Menu04' => [
                'title' => 'Giao dịch thẻ cào',
                'class' => '',
                'notes' => [
                    [
                        'title' => 'Thống kê sản lượng',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/index']),
                        'icon' => 'icon-Ainfo',
                        'class' => '',
                    ],
                    [
                        'title' => 'Tra cứu giao dịch thẻ cào',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/search']),
                        'icon' => 'icon-history',
                        'class' => '',
                        'others_url' => [
                            Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/detail']),
                        ]
                    ],
                    [
                        'title' => 'Rút tiền',
                        'url' => Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/withdraw']),
                        'icon' => 'icon-wtdraw',
                        'class' => '',
                        'others_url' => [
                            Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/withdraw-detail']),
                            Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/withdraw-verify']),
                            Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/withdraw-cancel']),
                        ]
                    ],
                ],
            ],*/
        );
    }
}