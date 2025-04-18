<?php


namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerPaymentAccountBusiness;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\form\PartnerPaymentAccountForm;
use common\models\input\PartnerPaymentAccountSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerPaymentAccountController extends BackendController {
    // Danh sách
    public function actionIndex()
    {
        $search = new PartnerPaymentAccountSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = PartnerPaymentAccount::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");
        $partner_payment_search_arr = Tables::selectAllDataTable("partner_payment");

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'partner_payment_search_arr' => $partner_payment_search_arr,
            'check_all_operators' => PartnerPaymentAccount::getOperatorsForCheckAll(),
        ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['partner-payment-account/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            ];
            $result = PartnerPaymentAccountBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = 'Khóa tài khoản kênh thanh toán thành công.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản kênh thanh toán';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }



    public function actionDelete()
    {
        $message = null;
        $search = ['partner-payment-account/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            ];
            $result = PartnerPaymentAccountBusiness::delete($params);
            if ($result['error_message'] == '') {
                $message = 'Xóa tài khoản kênh thanh toán thành công.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản kênh thanh toán';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['partner-payment-account/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            ];
            $result = PartnerPaymentAccountBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = 'Mở khóa tài khoản kênh thanh toán thành công.';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại tài khoản kênh thanh toán';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Thêm mới
    public function actionAdd()
    {

        $model = new PartnerPaymentAccountForm();

        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $partner_payment_arr = Weblib::createComboTableArray('partner_payment', 'id', 'name', 'status = ' . PartnerPayment::STATUS_ACTIVE, Translate::get('Chọn kênh thanh toán'), true);

        $errors = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {

            $form = Yii::$app->request->post('PartnerPaymentAccountForm');

            $params = array(
                'merchant_id' => $form['merchant_id'],
                'partner_payment_id' => $form['partner_payment_id'],
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'partner_payment_account' => $form['partner_payment_account'],
                'partner_merchant_password' => $form['partner_merchant_password'],
                'partner_merchant_id' => $form['partner_merchant_id'],
                'token_key' => $form['token_key'],
                'checksum_key' => $form['checksum_key'],
                'transaction_key' => $form['transaction_key'],

                'user_id' => Yii::$app->user->getId(),

            );
            $result = PartnerPaymentAccountBusiness::add($params);


            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm tài khoản kênh thanh toán thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl('partner-payment-account/index');
                Weblib::showMessage($message, $url);
            } else {
                $errors = Translate::get($result['error_message']);
            }
        }
        return $this->render('add', [
            'model' => $model,
            'errors' => $errors,
            'merchant_arr' => $merchant_arr,
            'partner_payment_arr' => $partner_payment_arr
        ]);


    }

    public function actionGetFieldsByPartner() {
        $fields = [
            'partner_payment_account' => ['display' => false, 'label' => '', 'value' => ''],
            'partner_merchant_id' => ['display' => false, 'label' => '', 'value' => ''],
            'partner_merchant_password' => ['display' => false, 'label' => '', 'value' => ''],
            'token_key' => ['display' => false, 'label' => '', 'value' => ''],
            'checksum_key' => ['display' => false, 'label' => '', 'value' => ''],
            'transaction_key' => ['display' => false, 'label' => '', 'value' => '']
        ];
        $partner_payment_id = ObjInput::get('id', 'int');
        $partner_payment_info = PartnerPayment::getById($partner_payment_id);
        if ($partner_payment_info != false) {
            $partner_payment_code = $partner_payment_info['code'];
            switch ($partner_payment_code) {
                case 'NGANLUONG-SEAMLESS':
                case 'NGANLUONG':
                    $fields['partner_payment_account'] = ['display' => true, 'label' => 'Email Ngân Lượng', 'value' => NGANLUONG_RECEIVER_EMAIL];
                    $fields['partner_merchant_id'] = ['display' => true, 'label' => 'Merchant ID Ngân Lượng', 'value' => NGANLUONG_MERCHANT_ID];
                    $fields['partner_merchant_password'] = ['display' => true, 'label' => 'Mật khẩu kết nối Ngân Lượng', 'value' => NGANLUONG_MERCHANT_PASSWORD];
                    break;
                case 'ALEPAY':
                    $fields['token_key'] = ['display' => true, 'label' => 'Alepay Token Key', 'value' => ALEPAY_TOKEN_KEY];
                    $fields['checksum_key'] = ['display' => true, 'label' => 'Alepay Checksum Key', 'value' => ALEPAY_CHECKSUM_KEY];
                    break;
                case 'VCB':
                    $fields['partner_merchant_id'] = ['display' => true, 'label' => 'VCB ECOM MERCHANT ID', 'value' => VCB_ECOM_MERCHANT_ID];
                    break;
                case 'CYBER-SOURCE':
                    $fields['partner_merchant_id'] = ['display' => true, 'label' => 'CYBERSOURCE MERCHANT ID', 'value' => CBS_SOAP_MERCHANT_ID];
                    $fields['partner_merchant_password'] = ['display' => true, 'label' => 'CYBERSOURCE TRANSACTION KEY', 'value' => CBS_SOAP_TRANSACTION_KEY];
                    $fields['token_key'] = ['display' => true, 'label' => 'CYBERSOURCE FLEX ID', 'value' => CBS_FLEX_KEY_ID];
                    $fields['checksum_key'] = ['display' => true, 'label' => 'CYBERSOURCE SHARED SECRET KET', 'value' => CBS_FLEX_SHARED_SECRET_KEY];
                    break;
                case 'CYBER-SOURCE-VCB-3DS2':
                    $fields['partner_merchant_id'] = ['display' => true, 'label' => 'CBS MID', 'value' => ''];
                    $fields['partner_merchant_password'] = ['display' => true, 'label' => 'CBS OrgUnitId', 'value' => ''];
                    $fields['token_key'] = ['display' => true, 'label' => 'CBS ApiIdentifier', 'value' => ''];
                    $fields['checksum_key'] = ['display' => true, 'label' => 'CBS ApiKey', 'value' => ''];
                    $fields['transaction_key'] = ['display' => true, 'label' => 'CBS Soap Transaction Key', 'value' => ''];
                    break;
                case 'CYBER-SOURCE-VCB':
                    $fields['partner_merchant_id'] = ['display' => true, 'label' => 'CYBERSOURCE MERCHANT ID', 'value' => CBS_SOAP_MERCHANT_ID];
                    $fields['partner_merchant_password'] = ['display' => true, 'label' => 'CYBERSOURCE TRANSACTION KEY', 'value' => CBS_SOAP_TRANSACTION_KEY];
                    break;
                case 'MPOS':
                    $fields['partner_payment_account'] = ['display' => true, 'label' => 'Tài khoản đăng nhập App MPOS', 'value' => ''];
                    break;
            }
        }
        echo json_encode($fields);
        exit();
    }

} 