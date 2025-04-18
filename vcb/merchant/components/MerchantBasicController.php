<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/18/2016
 * Time: 2:42 PM
 */

namespace merchant\components;

use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;
use common\models\db\UserLogin;
use common\models\db\Right;
use common\components\utils\Translate;

class MerchantBasicController extends Controller {

    public $layout = 'main';
    protected $_pageTitleDefault = 'PAYGATE VIETCOMBANK - NGANLUONG.VN';
    protected $_pageTitle = null;
    protected $_pageDescription = 'PAYGATE VIETCOMBANK - NGANLUONG.VN';
    protected $_pageKeyword = '';

    public function init() {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
        $language = ObjInput::get('language', 'str', 'vi');
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'captcha',
                            'request-forget-password',

                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function () {
                    if (UserLogin::isLogin()) {
                        return $this->redirect(Yii::$app->getHomeUrl());
                    }
                },
            ],
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
        $urlPass = ['forget-password','request-forget-password'];
        if (in_array(strtolower($action->id), $GLOBALS['ACTION_NOT_VALIDATION_CSRF'])) {
            $this->enableCsrfValidation = false;
        }
        if ($action->id != 'captcha') {
            if (Yii::$app->controller->id != 'user-login' || $action->id != 'index') {
                if(in_array($action->id,$urlPass)){
                }else {
                    if (!UserLogin::isLogin()) {
                        $current_url = Yii::$app->request->url;
                        Yii::$app->session->set('current_url', $current_url);
                        $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['user-login/index'], HTTP_CODE));
                    } else {
                        if ($this->_checkRightAccess($action)) {
                            return true;
                        } else {
                            $this->redirectErrorPage('Bạn không có quyền truy cập. Liên hệ bộ phận quản trị để được hỗ trợ.', '');
                        }
                    }
                }
            }
        }
        return parent::beforeAction($action);
    }

    final protected function _checkRightAccess($action) {
        $action_right_code = $this->_getActionRightCode($action);
        if ($action_right_code != 'MERCHANT::USER-LOGOUT::INDEX') {
            if (Right::isCodeExists($action_right_code)) {
                if (UserLogin::hasRight($action_right_code)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $right_code = $this->_getRightCode();
                if (Right::isCodeExists($right_code)) {
                    if (UserLogin::hasRight($right_code)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    final protected function _getRightCode() {
        return 'MERCHANT::' . strtoupper($this->id);
    }

    final protected function _getActionRightCode($action) {
        return 'MERCHANT::' . strtoupper($this->id) . '::' . strtoupper($action->id);
    }

    public function afterAction($action, $result) {
        \common\components\utils\Translate::saveFile();
        return parent::afterAction($action, $result);
    }

    public function onSubmit($name) {
        $this->_fieldName = trim(ObjInput::get($name, 'str', '', 'POST'));
        if ($this->_fieldName <> '') {
            return true;
        }

        return false;
    }

    public function isPostField($name) {
        if (isset($_POST[$name])) {
            return true;
        }

        return false;
    }

    protected function _getPageDescription() {
        return $this->_pageDescription;
    }

    protected function _getPageKeyword() {
        return $this->_pageKeyword;
    }

    /*
     * Tao URL
     * @inputs: array, item 0
     * array[0] = module/controller/action
     * array[1] = params key=>value
     *
     * eg: $this->makeUrl(['default/index','id'=>3]);
     */

    public function makeUrl($params = array()) {
        return Yii::$app->urlManager->createAbsoluteUrl($params, HTTP_CODE);
    }

    public function redirectErrorPage($error_message, $back_url = '') {
        $error_message = urlencode(base64_encode(base64_encode($error_message)));
        $back_url = $back_url != '' ? urlencode($back_url) : $back_url;
        $url = Yii::$app->urlManager->createAbsoluteUrl(['error/index', 'error_message' => $error_message, 'back_url' => $back_url], HTTP_CODE);
        header('Location:' . $url);
        die();
    }

}
