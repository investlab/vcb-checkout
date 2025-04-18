<?php
namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CreditAccountBusiness;
use common\models\business\InstallmentConfigBusiness;
use common\models\business\MerchantBusiness;
use common\models\db\Branch;
use common\models\db\CreditAccount;
use common\models\db\InstallmentConfig;
use common\models\db\Merchant;
use common\models\form\CreditAccountForm;
use common\models\form\MerchantChangePassForm;
use common\models\form\MerchantForm;
use common\models\input\MerchantSearch;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class MerchantController extends BackendController
{
    //    Danh sách
    public function actionIndex()
    {
        $search = new MerchantSearch();
        $active3D_arr = Merchant::getActive3D();
        $payment_arr = Merchant::getPaymentFlow();
        $search->setAttributes(Yii::$app->request->get());
        if (!Yii::$app->request->get()) {
            $search->status = 1;
           
        }
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = Merchant::getStatus();
        $logo_url = IMAGES_MERCHANT_URL;

        $model = new MerchantChangePassForm();
        $credit_account = new CreditAccountForm();
        $branchs = Weblib::createComboTableArray('branch', 'id', 'name', ['status' => Branch::STATUS_ACTIVE], 'Chọn chi nhánh');
            return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'logo_url' => $logo_url,
            'model' => $model,
            'check_all_operators' => Merchant::getOperatorsForCheckAll(),
            'credit_account' => $credit_account,
            'branchs' => $branchs,
            'active3D_arr' => $active3D_arr,
            'payment_arr' => $payment_arr
        ]);
    }

    // Thêm mới
    public function actionAdd()
    {
        $branchs = [];
        $model = new MerchantForm();
        $model->scenario = MerchantForm::SCENARIO_ADD;
        $active3D_arr = Merchant::getActive3D();
        $payment_arr = Merchant::getPaymentFlow();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('MerchantForm');
            $model->logo = UploadedFile::getInstance($model, 'logo');
            $logo_path = IMAGES_MERCHANT_PATH;

            if ($model->logo != null) {
                if ($model->logo->
                saveAs($logo_path . time() . $model->logo->name)
                ) {
                    $logo_name = time() . $model->logo->name;
                } else {
                    $logo_name = null;
                }
            } else {
                $logo_name = null;
            }

            $params = array(
                'name' => $form['name'],
                'partner_id' => 1,
                'logo' => $logo_name,
                'password' => trim($form['password']),
                'website' => $form['website'],
                'email_notification' => $form['email_notification'],
                'mobile_notification' => $form['mobile_notification'],
                'url_notification' => $form['url_notification'],
                'user_id' => Yii::$app->user->getId(),
                'merchant_code' => str_pad($form['merchant_code'],11,"0",STR_PAD_LEFT),
                'branch_id' => $form['branch_id'],
                'active3D' => $form['active3D'],
                'payment_flow' => $form['payment_flow']
            );

            $result = MerchantBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm Merchant thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('merchant/index');
            Weblib::showMessage($message, $url);
        } else {
            $branchs = Weblib::createComboTableArray('branch', 'id', 'name', ['status' => Branch::STATUS_ACTIVE], 'Chọn chi nhánh');
        }

        return $this->render('add', [
            'model' => $model,
            'branchs' => $branchs,
            'active3D_arr' => $active3D_arr,
            'payment_arr' => $payment_arr
        ]);
    }

    // Cập nhật
    public function actionViewUpdate()
    {
        $model = new MerchantForm();
        $active3D_arr = Merchant::getActive3D();
        $payment_arr = Merchant::getPaymentFlow();
        $id = ObjInput::get('id', 'int');
        $merchant = null;
        if (intval($id) > 0) {
            $merchant = Tables::selectOneDataTable("merchant", ["id = :id ", "id" => $id]);
            if ($merchant) {
                $model->id = $merchant['id'];
                $model->name = $merchant['name'];
                $model->website = $merchant['website'];
                $model->merchant_code = (!empty($merchant['merchant_code']))? $merchant['merchant_code']:'';
                $model->email_notification = $merchant['email_notification'];
                $model->mobile_notification = $merchant['mobile_notification'];
                $model->url_notification = $merchant['url_notification'];
                $model->branch_id = $merchant['branch_id'];
                $model->active3D = $merchant['active3D'];
                $model->payment_flow = $merchant['payment_flow'];
            }
        }
        $logo_url = IMAGES_MERCHANT_URL;
        $branchs = Weblib::createComboTableArray('branch', 'id', 'name', ['status' => Branch::STATUS_ACTIVE], 'Chọn chi nhánh');

        return $this->render('update', [
            'model' => $model,
            'logo_url' => $logo_url,
            'merchant' => $merchant,
            'branchs' => $branchs,
            'active3D_arr' => $active3D_arr,
            'payment_arr' => $payment_arr
        ]);
    }

    public function actionUpdate()
    {
        $model = new MerchantForm();
        $model->scenario = MerchantForm::SCENARIO_UPDATE;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $logo_path = IMAGES_MERCHANT_PATH;
        $logo_url = IMAGES_MERCHANT_URL;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('MerchantForm');

            $merchant = Tables::selectOneDataTable('merchant', "id = " . $form['id']);
            $model->logo = UploadedFile::getInstance($model, 'logo');

            if ($model->logo != null) {
                if ($model->logo->
                saveAs($logo_path . time() . $model->logo->name)
                ) {
                    if (trim($merchant['logo']) != null && file_exists($logo_url . $merchant['logo'])) {
                        unlink($logo_url . $merchant['logo']);
                    }
                    $logo_name = time() . $model->logo->name;

                } else {
                    $logo_name = null;
                }
            } else {
                $logo_name = null;
            }

            $params = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'logo' => $logo_name,
                'website' => $form['website'],
                'email_notification' => $form['email_notification'],
                'mobile_notification' => $form['mobile_notification'],
                'url_notification' => $form['url_notification'],
                'user_id' => Yii::$app->user->getId(),
                'merchant_code' => str_pad($form['merchant_code'],11,"0",STR_PAD_LEFT),
                'branch_id' => $form['branch_id'],
                'active3D' => $form['active3D'],
                'payment_flow' => $form['payment_flow']
            );

            $result = MerchantBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật Merchant thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('merchant/index');
            Weblib::showMessage($message, $url);
        }
    }

    public function actionTokenSecure()
    {
        $message = null;
        $search = ['merchant/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = MerchantBusiness::token($params, true);
            if ($result['error_message'] == '') {
                if ($result['resultToken'] == 0){
                    $message = Translate::get('Bật Token 3D-Secure thành công');
                }else{
                    $message = Translate::get('Tắt Token 3D-Secure thành công');
                }
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Merchant');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    public function actionPaymentFlow()
    {
        $message = null;
        $search = ['merchant/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = MerchantBusiness::paymentFlow($params, true);
            if ($result['error_message'] == '') {
                if ($result['resultpayment'] == 0){
                    $message = Translate::get('Chọn luồng thanh toán 3Ds thành công');
                }else{
                    $message = Translate::get('Chọn luồng thanh toán 3Ds2 thành công');
                }
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Merchant');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['merchant/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = MerchantBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa Merchant thành công');
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = Translate::get('Không tồn tại Merchant');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);


    }

    //  Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['merchant/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'id' => $id,
                'user_id' => Yii::$app->user->getId(),
            ];
            $result = MerchantBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa Merchant thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
        } else {
            $message = Translate::get('Không tồn tại Merchant');
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }

    // Thay đổi mật khẩu
    public function actionChangePassword()
    {
        $id = ObjInput::get('id', 'int');
        $model = MerchantChangePassForm::findBySql("SELECT * FROM merchant WHERE id = $id ")->one();
        if ($model == null) {
            Weblib::showMessage(Translate::get('Merchant không hợp lệ'), Yii::$app->urlManager->createAbsoluteUrl(['merchant/index']));
        } else {
            $message = '';
            $model->load(Yii::$app->request->get());
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    $inputs = array(
                        'id' => $model->id,
                        'new_password' => $model->new_password,
                        'user_id' => Yii::$app->user->getId()
                    );
                    $result = MerchantBusiness::changepassword($inputs);
                    if ($result['error_message'] == '') {
                        Weblib::showMessage(Translate::get('Cập nhật mật khẩu thành công'), Yii::$app->urlManager->createAbsoluteUrl(['merchant/index']), false);
                        die();
                    } else {
                        $message = Translate::get($result['error_message']);
                    }
                }
            }
        }
        return $this->render('change-password', [
            'model' => $model,
            'error_message' => $message,
        ]);
    }

    public function actionViewInstallment ()
    {
        $model = new MerchantForm();
        $id = ObjInput::get('id', 'int');
        $merchant = null;
        $arr_cycle = null;
        if (intval($id) > 0) {
            $merchant = Tables::selectOneDataTable("merchant", ["id = :id ", "id" => $id]);
            if ($merchant) {
                $model->id = $merchant['id'];
                $model->name = $merchant['name'];
            }
        }
        $banks = Tables::selectAllDataTable('bank', 'status= '.ACTIVE_STATUS,'', '','' , '','id,name,code');

        $installment_cycle = Tables::selectAllDataTable('installment_cycle', '', 'month asc');

        $installment_card = [
          'JCB' => 'Thẻ JCB',
          'VISA' => 'Thẻ Visa',
          'MASTERCARD' => 'Thẻ MasterCard'
        ];

        return $this->render('installment', [
            'model' => $model,
            'merchant' => $merchant,
            'banks' => $banks,
            'installment_cycle' => $installment_cycle,
            'installment_card' => $installment_card
        ]);
    }

    // Cấu hình trả góp merchant
    public function actionInstallment()
    {
        $model = new InstallmentConfig();
        $card_accept = null;
        $cycle_accept = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (Yii::$app->request->post()) {
            $data = Yii::$app->request->post();

            $installment = Tables::selectOneDataTable('installment_config', "merchant_id = " . $data['MerchantForm']['id']);
            if (!empty($data['installment_cycle'])) {
                foreach ($data['installment_cycle'] as $key => $cycle) {
                    $cycle_accept[$data['bank_code']][] = [$cycle => $cycle . ' Tháng'];
                }
            }
            if (!empty($data['installment_card'])) {
                foreach ($data['installment_card'] as $key => $card) {
                    $card_accept[$data['bank_code']][] = $card;
                }
            }
            $params = [
                'merchant_id' => $data['MerchantForm']['id'],
                'card_accept' => $card_accept,
                'cycle_accept' => $cycle_accept,
                'bank_code' => $data['bank_code']
            ];

            if (empty($installment)) {
                $result = InstallmentConfigBusiness::add($params);
            } else {
                $result = InstallmentConfigBusiness::update($params);
            }

            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật cấu hình trả góp thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('merchant/view-installment?id=' . $data['MerchantForm']['id']);
            Weblib::showMessage($message, $url);
        }
    }

    // Cấu hình tài khoản báo có merchant
    public function actionCreditAccount()
    {
        $model = new CreditAccountForm();
        $card_accept = null;
        $cycle_accept = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('CreditAccountForm');
            $form['branch_code'] = str_pad($form['branch_code'],6,"0",STR_PAD_LEFT);

            $credit_account = CreditAccount::findOne(['merchant_id' => $form['merchant_id']]);
            if (empty($credit_account)) {
                $result = CreditAccountBusiness::add($form);
            } else {
                $result = CreditAccountBusiness::update($form);
            }

            if ($result['error_message'] == '') {
                $message = "Cấu hình tài khoản báo có thành công";
            } else {
                $message = $result['error_message'];
            }
        } else {
            $merchant_id = ObjInput::get('id','int');
            $credit_account = CreditAccount::findOne(['merchant_id' => $merchant_id]);
            $data = [
                'merchant_id' => $merchant_id,
                'branch_code' => $credit_account['branch_code'],
                'account_number' => $credit_account['account_number'],
            ];
            return json_encode($data);
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('merchant/');
        Weblib::showMessage($message, $url);
    }

} 