<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\LinkCardBusiness;
use common\models\db\LinkCard;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\User;
use common\models\form\LinkCardForm;
use common\models\input\LinkCardSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class LinkCardController extends BackendController
{
    public static $arr_status = [
        LinkCard::STATUS_NEW => [
            'name' => 'Mới tạo',
            'class' => 'label label-default'
        ],
        LinkCard::STATUS_ACTIVE => [
            'name' => 'Đã xác thực',
            'class' => 'label label-success'
        ],
        LinkCard::STATUS_CANCEL => [
            'name' => 'Đã huỷ',
            'class' => 'label label-danger'
        ],
        LinkCard::STATUS_EXPIRE => [
            'name' => 'Hết hạn',
            'class' => 'label label-primary'
        ],
        LinkCard::STATUS_LOCK => [
            'name' => 'Đã khoá',
            'class' => 'label label-warning'
        ],
        LinkCard::STATUS_PROCESS => [
            'name' => 'Đang xác thực',
            'class' => 'label label-primary'
        ],
        LinkCard::STATUS_WAIT => [
            'name' => 'Chờ xác thực',
            'class' => 'label label-default'
        ],
    ];

    private static $arr_action = [
//        'lock-link' => 'Khoá liên kết',
        'active-link' => 'Mở khoá liên kết',
        'unlink' => 'Huỷ liên kết',
        'confirm' => 'Xác thực liên kết',
    ];

    private static $card_types = [
        '' => 'Chọn loại thẻ',
        LinkCard::TYPE_VISA => 'Thẻ Visa',
        LinkCard::TYPE_MASTERCARD => 'Thẻ Mastercard',
        LinkCard::TYPE_JCB => 'Thẻ JCB',
        LinkCard::TYPE_AMEX => 'Thẻ Amex',
    ];

    public function actionIndex()
    {
        $search = new LinkCardSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $partner_payments = Weblib::createComboTableArray('partner_payment', 'id', 'name', ['status' => PartnerPayment::STATUS_ACTIVE], 'Chọn nhà cung cấp');
        $merchants = Weblib::createComboTableArray('merchant', 'id', 'name', ['status' => Merchant::STATUS_ACTIVE], 'Chọn merchant');

        $model = new LinkCardForm();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model' => $model,
            'partner_payments' => $partner_payments,
            'merchants' => $merchants,
            'arr_status' => self::$arr_status,
            'card_types' => self::$card_types,
        ]);
    }


    public function actionDetail() {
        $id = Yii::$app->request->get('id');
        $arr_user_action = [];

        $link_card = LinkCard::findOne(['id' => $id]);
        $merchant = Merchant::findOne(['id' => $link_card->merchant_id]);
        $partner_payment = PartnerPayment::findOne(['id' => $link_card->partner_payment_id]);
        $user_actions = json_decode($link_card['user_action'], true);
        if (!empty($user_actions)) {
            foreach ($user_actions as $key => $val) {
                $user_id = array_keys($val)[0];
                $action_key = array_values($val)[0];
                $user = User::getUserInfo($user_id);
                $arr_user_action[] = [
                  'name' => $user['fullname'],
                  'action' => self::$arr_action[$action_key],
                ];
            }
        }

        return $this->render('detail', [
            'link_card' => $link_card,
            'merchant_name' => $merchant['name'],
            'partner_payment_name' => $partner_payment['name'],
            'arr_status' => self::$arr_status,
            'arr_user_action' => $arr_user_action,
            'card_types' => self::$card_types,
        ]);
    }

    public function actionConfirm() {
        $message = null;
        $base_url = ['link-card/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];

            $result = LinkCardBusiness::confirm($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Xác thực liên kết thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Thẻ liên kết');
        }

        $url = Yii::$app->urlManager->createUrl($base_url);
        Weblib::showMessage($message, $url);
    }

    public function actionUnlink() {
        $message = null;
        $base_url = ['link-card/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];

            $result = LinkCardBusiness::unlink($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Huỷ liên kết thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Thẻ liên kết');
        }

        $url = Yii::$app->urlManager->createUrl($base_url);
        Weblib::showMessage($message, $url);
    }

    public function actionLockLink() {
        $message = null;
        $base_url = ['link-card/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];

            $result = LinkCardBusiness::lockLink($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa liên kết thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Thẻ liên kết');
        }

        $url = Yii::$app->urlManager->createUrl($base_url);
        Weblib::showMessage($message, $url);
    }

    public function actionActiveLink() {
        $message = null;
        $base_url = ['link-card/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];

            $result = LinkCardBusiness::activeLink($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa liên kết thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Thẻ liên kết');
        }

        $url = Yii::$app->urlManager->createUrl($base_url);
        Weblib::showMessage($message, $url);
    }
}