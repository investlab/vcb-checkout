<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/19/2016
 * Time: 11:25 AM
 */

namespace checkout\components;

use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\db\Right;
use common\models\db\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;
use common\models\business\BillBusiness;
use common\models\db\Bill;
use common\components\libs\Tables;
use common\models\db\CheckoutOrder;
use common\components\utils\Translate;

class CheckoutController extends Controller {

    public $layout = '';
    protected $_pageTitleDefault = 'Checkout';
    protected $_pageTitle = null;
    protected $_fieldName = '';
    protected $_pageDescription = '';
    protected $_pageKeyword = '';
    public $staticClient;
    public $checkout_order = null;

    public function init() {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
        $this->getView()->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::$app->urlManager->baseUrl . '/images/ico/favicon.ico']);
        $language = ObjInput::get('language', 'str', '');
        if ($language == 'en') {
            Yii::$app->session->set('language', 'en-US');
        } elseif ($language == 'vi') {
            Yii::$app->session->set('language', 'vi-VN');
        } else {
            if (!Yii::$app->session->get('language')) {
                Yii::$app->session->set('language', Yii::$app->language);
            }
        }
        Yii::$app->language = Yii::$app->session->get('language');
    }

    public function behaviors() {
        return [
        ];
    }

    public function actions() {
        return [
            'captcha' => [
                'class' => 'common\components\libs\MTQCaptchaAction',
                'transparent' => true,
                'maxLength' => 6,
                'minLength' => 6,
                'testLimit' => 1,
            ],
        ];
    }

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            $this->bill = $this->_getBill();
            if ($this->bill == false) {
                $this->redirectErrorPage(Translate::get('Đơn hàng không tồn tại, truy cập bị từ chối'));
            } else {
                return true;
            }
        }
        return false;
    }

    public function afterAction($action, $result) {
        \common\components\utils\Translate::saveFile();
        return parent::afterAction($action, $result);
    }

    protected function _getBill() {
        $token_code = ObjInput::get('token_code', 'str', '');
        if (CheckoutOrder::checkTokenCode($token_code, $this->checkout_order)) {
            if (intval($this->checkout_order['payment_transaction_id']) != 0) {
                $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . intval($this->checkout_order['payment_transaction_id']) . " ");
                $this->paymentTransaction = $payment_transaction_info;
            }
            return BillBusiness::getDataInfoById(intval($bill_id));
        } else {
            return false;
        }
        return false;
    }

    public function redirectErrorPage($error_message, $back_url = '') {
        $error_message = urlencode(base64_encode(base64_encode(Translate::get($error_message))));
        $back_url = $back_url != '' ? urlencode($back_url) : $back_url;
        $url = Yii::$app->urlManager->createAbsoluteUrl(['error/index', 'error_message' => $error_message, 'back_url' => $back_url], HTTP_CODE);
        header('Location:' . $url);
        die();
    }

}
