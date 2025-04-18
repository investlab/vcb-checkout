<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/19/2016
 * Time: 11:25 AM
 */

namespace backend\components;

use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\db\Right;
use common\models\db\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\web\View;
use common\components\utils\Translate;

class BackendController extends Controller {

    public $layout = 'main';
    public $userAdmin = array();
    protected $_pageTitleDefault = 'PayGate Backend';
    protected $_pageTitle = null;
    protected $_fieldName = '';
    protected $_pageDescription = 'PayGate Backend';
    protected $_pageKeyword = '';
    public $staticClient;

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
        $this->userAdmin = Weblib::getUserLogined();
        $this->getView()->registerJsFile(Yii::$app->urlManager->baseUrl . DS . 'js' . DS . 'plugins' . DS . 'core' . DS . 'pace' . DS . 'pace.min.js', ['position' => View::POS_END]);
        $this->getView()->registerJsFile(Yii::$app->urlManager->baseUrl . DS . 'js' . DS . 'pages' . DS . 'dashboard.min.js', ['position' => View::POS_END]);
        $this->getView()->registerJsFile(Yii::$app->urlManager->baseUrl . DS . 'js' . DS . 'common.js', ['position' => View::POS_END]);
        //$this->getView()->registerJsFile(Yii::$app->urlManager->baseUrl . DS . 'js' . DS . 'common.js', ['position' => View::POS_END]);

        $this->getView()->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::$app->urlManager->baseUrl . '/images/ico/favicon.ico']);
    }

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
                /* 'verbs' => [
                  'class' => VerbFilter::className(),
                  'actions' => [
                  'delete' => ['post'],
                  ],
                  ], */
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
            'qrcode' => [
                'class' => 'common\components\libs\MTQQrAction',
            ],
        ];
    }

    public function beforeAction($action) {
        if (in_array(strtolower($action->id), $GLOBALS['ACTION_NOT_VALIDATION_CSRF'])) {
            $this->enableCsrfValidation = false;
        }
        if (parent::beforeAction($action)) {
            if (!Yii::$app->user->isGuest) {
                if ($this->_checkRightAccess($action)) {
                    return true;
                } else {
                    echo Translate::get('Bạn không có quyền truy cập. Liên hệ bộ phận quản trị để được hỗ trợ.');
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    final protected function _checkRightAccess($action) {
        $action_right_code = $this->_getActionRightCode($action);
        if ($action_right_code != 'BACKEND::USER::LOGOUT') {
            if (Right::isCodeExists($action_right_code)) {
                if (User::hasRight($action_right_code)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $right_code = $this->_getRightCode();
                if (Right::isCodeExists($right_code)) {
                    if (User::hasRight($right_code)) {
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
        return 'BACKEND::' . strtoupper($this->id);
    }

    final protected function _getActionRightCode($action) {
        return 'BACKEND::' . strtoupper($this->id) . '::' . strtoupper($action->id);
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

}
