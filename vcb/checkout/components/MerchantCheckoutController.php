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
use common\models\business\MerchantConfigBusiness;
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
use common\models\db\Merchant;

class MerchantCheckoutController extends Controller
{

    public $layout = '';
    protected $_pageTitleDefault = 'Vietcombank - NganLuong.vn';
    protected $_pageTitle = null;
    protected $_fieldName = '';
    protected $_pageDescription = '';
    protected $_pageKeyword = '';
    public $staticClient;
    public $checkout_order = null;
    public $token_code = null;
    public $transaction = false;

    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
        $this->getView()->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::$app->urlManager->baseUrl . '/images/ico/favicon.ico']);

//        $language = ObjInput::get('language', 'str', '');
        $current_url = Yii::$app->request->url;
        if (strpos($current_url, '/en/') !== false) {
            Yii::$app->session->set('language', 'en-US');
        } elseif (strpos($current_url, '/vi/') !== false) {
            Yii::$app->session->set('language', 'vi-VN');
        } else {
            Yii::$app->session->set('language', 'vi-VN');
        }
        Yii::$app->language = Yii::$app->session->get('language');
    }

    public function behaviors()
    {
        return [

        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'common\components\libs\MTQCaptchaAction',
                'transparent' => true,
                'maxLength' => 3,
                'minLength' => 3,
                'testLimit' => 1,
            ],
        ];
    }

    public function beforeAction($action)
    {
//        echo "<pre>";
//        var_dump($action->id);
//        die();
        if ($action->id == 'check-enroll') {
            $this->enableCsrfValidation = false;
        }
        if ($action->id == 'setup-author') {
            $this->enableCsrfValidation = false;
        }
        if ($action->id == 'update-success') {
            return true;
        }
        if ($action->id == 'update-send-mail') {
            return true;
        }
        if ($action->id == 'callback') {
            return true;
        }
        if ($action->id == 'view-bill') {
            return true;
        }
        if ($action->id == 'get-many-receipts') {
            return true;
        }
        if ($action->id == 'download') {
            return true;
        }
        if ($action->id == 'export-receipts') {
            return true;
        }
        if ($action->id == 'get-installment-packages') {
            return true;
        }
        if ($action->id == 'get-installment-fee') {
            return true;
        }
        if ($action->id == 'transaction-destroy-v2') {
            return true;
        }
        if (parent::beforeAction($action)) {
            if ($action->id == 'get-check-excluded-date') {
                return true;
            }
            if ($action->id == 'check-enroll') {
                return true;
            }
            if ($action->id == 'setup-author') {
                return true;
            }
            if ($action->id == 'update-success') {
                return true;
            }
            if ($action->id == 'callback') {
                return true;
            }
            if ($action->id != 'captcha') {
                $this->checkout_order = $this->_getCheckoutOrder();
                if ($this->checkout_order == false) {
                    $this->redirectErrorPage('Đơn thanh toán không tồn tại, truy cập bị từ chối');
                } else {
                    $this->_setLanguage();
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    protected function _setLanguage()
    {
        Yii::$app->language = \common\models\db\Language::getCodeById($this->checkout_order['language_id']);
    }

    protected function _getCheckoutOrder()
    {
        $this->token_code = ObjInput::get('token_code', 'str', '');
        if (CheckoutOrder::checkTokenCode($this->token_code, $checkout_order)) {
            $checkout_order['token_code'] = $this->token_code;
            $checkout_order['merchant_info'] = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $checkout_order['merchant_id']]);
            $checkout_order['merchant_config'] = MerchantConfigBusiness::getConfigByMerchantId($checkout_order['merchant_id']);
            $checkout_order['user_info'] = Tables::selectOneDataTable("user_login", ["merchant_id = :merchant_id", "merchant_id" => $checkout_order['merchant_id']]);
            if (intval($checkout_order['transaction_id']) != 0) {
                $this->transaction = Tables::selectOneDataTable("transaction", "id = " . intval($checkout_order['transaction_id']) . " ");
                if ($this->transaction != false) {                    
                    if (trim($this->transaction['partner_payment_info']) != '') {
                        $this->transaction['partner_payment_info'] = json_decode($this->transaction['partner_payment_info'], true);
                    } else {
                        $this->transaction['partner_payment_info'] = array();
                    }                    
                }
            }
            return $checkout_order;
        } else {
            return false;
        }
        return false;
    }

    public function redirectErrorPage($error_message, $back_url = '')
    {
        $error_message = urlencode(base64_encode(base64_encode($error_message)));
        $back_url = $back_url != '' ? urlencode($back_url) : $back_url;
        $url = Yii::$app->urlManager->createAbsoluteUrl(['error/index', 'error_message' => $error_message, 'back_url' => $back_url], HTTP_CODE);
        header('Location:' . $url);
        die();
    }
} 