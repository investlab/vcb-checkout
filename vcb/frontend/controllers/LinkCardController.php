<?php

namespace frontend\controllers;

use common\components\utils\Validation;
use common\models\business\MerchantConfigBusiness;
use common\models\business\SendMailBussiness;
use common\models\db\Merchant;
use common\models\db\PartnerPaymentAccount;
use common\models\db\UserLogin;
use common\payments\CyberSourceVcb;
use common\payments\CyberSourceVcb3ds2;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use frontend\models\form\LinkCardForm;
use common\models\business\LinkCardBusiness;
use common\components\utils\Encryption;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\db\LinkCard;

class LinkCardController extends Controller
{

    public $layout = 'link_card';
    public $card_token;

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

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['verify3d', 'get-partner-payment-by-bank-code', 'process-card'])) {
            $this->enableCsrfValidation = false;
        }
        if (parent::beforeAction($action)) {
            if (!in_array($action->id, ['captcha', 'get-partner-payment-by-bank-code'])) {
                $card_token_id = Yii::$app->request->get('card_token_id');
                if (is_null($card_token_id)) {
                    $error_message = Translate::get("'Yêu cầu liên kết thẻ không hợp lệ'");
                    $this->redirectErrorPage($error_message);
                    return false;
                }
                $this->card_token = LinkCard::findOne(['token' => $card_token_id]);
                if (is_null($this->card_token)) {
                    $error_message = Translate::get('Yêu cầu liên kết thẻ không hợp lệ');
                    $this->redirectErrorPage($error_message);
                    return false;
                } else {
                    if ($this->card_token->info != '') {
                        $info = json_decode($this->card_token->info, true);
                        if (isset($info['language']) && $info['language'] == 'en') {
                            Yii::$app->language = 'en-US';
                        }
                    }
                }
                if ($this->card_token['status'] == LinkCard::STATUS_ACTIVE && $action->id != 'success') {
                    $this->redirect($this->getSuccessUrl());
                    return false;
                } elseif ($this->card_token['status'] == LinkCard::STATUS_WAIT && $action->id != 'review') {
                    $this->redirect($this->getReviewUrl());
                    return false;
                } elseif ($this->card_token['status'] == LinkCard::STATUS_CANCEL && $action->id != 'cancel') {
                    $this->redirect($this->getCancelUrl());
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function actionIndex()
    {
        $model = new LinkCardForm();
        $model->scenario = LinkCardForm::SCENARIO_INDEX;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $post_params = Yii::$app->request->post();
            $result_process_token = LinkCardBusiness::processCardToken([
                'card_number' => $model->card_number,
                'card_month' => $model->card_month,
                'card_year' => $model->card_year,
                'card_type' => $model->card_type,
                'card_token' => $this->card_token->getAttributes(),
                'payment_url' => $this->getVerify3dUrl(),
                'cvv_code' => $model->cvv_code,
                'ProcessorTransactionId' => $post_params['ProcessorTransactionId'],
                'jwt_back' => $post_params['jwt_back'],
            ]);

            if ($result_process_token['error_message'] == '') {
                $result_code = $result_process_token['result_code'];
                $xid = $result_process_token['xid'];
                $bank_trans_id = $result_process_token['bank_trans_id'];
                $token_cybersource = $result_process_token['token_cybersource'];
                $partner_payment_id = $result_process_token['partner_payment_id'];
                $verify_amount = $result_process_token['verify_amount'];
                $bank = $result_process_token['bank'];
                $card_type = $result_process_token['card_type'];
                $info = json_decode($this->card_token['info'], true);


                if ($result_code == 'ACCEPT') {
                    $token_cybersource_encrypt = Encryption::encryptAES($token_cybersource, $GLOBALS['AES_KEY']);
                    $token_merchant = Encryption::hashHmacSHA256(
                        $this->card_token['merchant_id'] . $token_cybersource_encrypt['cipher_text'], $GLOBALS['SHA256_KEY']);
                    $update_card_token_process = LinkCardBusiness::updateProcess([
                        'id' => $this->card_token['id'],
                        'token_cybersource' => $token_cybersource_encrypt['cipher_text'],
                        'token_merchant' => $token_merchant,
                        'card_number_mask' => Strings::encodeCreditCardNumber($model->card_number),
                        'card_number_md5' => Encryption::hashHmacSHA256($model->card_number, $GLOBALS['SHA256_KEY']),
                        'card_type' => $model->convertCardType($card_type),
                        'bank' => '',
                        'secure_type' => $result_process_token['cardIs3d'] ? LinkCard::SECURE_TYPE_3D : LinkCard::SECURE_TYPE_2D,
                        'partner_payment_id' => $partner_payment_id,
                        'verify_amount' => $verify_amount,
                        'iv' => $token_cybersource_encrypt['iv'],
                        'card_month' => $model->card_month,
                        'card_year' => $model->card_year,
                    ]);
                    if ($update_card_token_process['error_message'] == '') {
                        $merchant_info = Merchant::getById($this->card_token['merchant_id']);
                        $email = $this->card_token['customer_email'];
                        if ($merchant_info['is_sent'] && strpos($merchant_info['mail_sent'], ',')) {
                            $email_bcc = explode(',', $merchant_info['mail_sent']);
                        } elseif (Validation::isEmail(trim($merchant_info['mail_sent']))) {
                            $email_bcc = trim($merchant_info['mail_sent']);
                        } else {
                            $email_bcc = [];
                        }

                        SendMailBussiness::sendBCC(
                            $email,
                            Translate::get("Thanh toán thành công"),
                            'noti_link_card_success',
                            [
//                                'order_description' => $this->checkout_order['order_description'],
                                'order_code' => $info['customer_id'],
                                'mc_name' => $merchant_info['name'],
                                'time_paid' => time(),
                                'payment_name' => $info['first_name'] . ' ' . $info['last_name'],
                                'email' => $info['email'],
                                'address' => $info['street'],
                                'card_type' => $card_type,
                                'card_date' => $model->card_month . '/' . $model->card_year,
                            ], 'layouts/basic', [], $email_bcc
                        );

                        return $this->redirect($this->getVerify2dUrl());
                    } else {
                        $model->error_message = $update_card_token_process['error_message'];
                    }
                } elseif ($result_code == 'REVIEW') {
                    $token_cybersource_encrypt = Encryption::encryptAES($token_cybersource, $GLOBALS['AES_KEY']);
                    $token_merchant = Encryption::hashHmacSHA256(
                        $this->card_token['merchant_id'] . $token_cybersource_encrypt['cipher_text'],
                        $GLOBALS['SHA256_KEY']);
                    $update_card_token_process = LinkCardBusiness::updateProcess([
                        'id' => $this->card_token['id'],
                        'token_cybersource' => $token_cybersource_encrypt['cipher_text'],
                        'token_merchant' => $token_merchant,
                        'card_number_mask' => Strings::encodeCreditCardNumber($model->card_number),
                        'card_number_md5' => Encryption::hashHmacSHA256($model->card_number, $GLOBALS['SHA256_KEY']),
                        'card_type' => $model->convertCardType($model->card_type),
                        'bank' => '',
                        'secure_type' => LinkCard::SECURE_TYPE_2D,
                        'partner_payment_id' => $partner_payment_id,
                        'verify_amount' => $verify_amount,
                        'iv' => $token_cybersource_encrypt['iv'],
                    ]);
                    if ($update_card_token_process['error_message'] == '') {
                        $update_card_token_review = LinkCardBusiness::updateReview(['id' => $this->card_token['id']]);
                        if ($update_card_token_review['error_message'] == '') {
                            $this->redirect($this->getReviewUrl());
                        } else {
                            $model->error_message = $update_card_token_review['error_message'];
                        }
                    } else {
                        $model->error_message = $update_card_token_process['error_message'];
                    }
                } elseif ($result_code == '3D') {
                    $token_cybersource_encrypt = Encryption::encryptAES($token_cybersource, $GLOBALS['AES_KEY']);
                    $token_merchant = Encryption::hashHmacSHA256(
                        $this->card_token['merchant_id'] . $token_cybersource_encrypt['cipher_text'],
                        $GLOBALS['SHA256_KEY']);
                    $update_card_token_process = LinkCardBusiness::updateProcess([
                        'id' => $this->card_token['id'],
                        'token_cybersource' => $token_cybersource_encrypt['cipher_text'],
                        'token_merchant' => $token_merchant,
                        'card_number_mask' => Strings::encodeCreditCardNumber($model->card_number),
                        'card_number_md5' => Encryption::hashHmacSHA256($model->card_number, $GLOBALS['SHA256_KEY']),
                        'card_type' => $model->convertCardType($model->card_type),
                        'bank' => '',
                        'secure_type' => LinkCard::SECURE_TYPE_3D,
                        'partner_payment_id' => $partner_payment_id,
                        'verify_amount' => $verify_amount,
                        'iv' => $token_cybersource_encrypt['iv'],
                    ]);
                    if ($update_card_token_process['error_message'] == '') {
                        $this->redirect($this->getVerify3dUrl() . '?xid=' . $xid);
                    } else {
                        $model->error_message = $update_card_token_process['error_message'];
                    }
                } else {
                    $model->error_message = 'Lỗi không xác định';
                }
            } else {
                $model->error_message = $result_process_token['error_message'];
            }
        }
        $card_fullname = CyberSourceVcb::_convertName($this->card_token->getAttributes()['card_holder']);

        return $this->render('index', [
            'model' => $model,
            'index_url' => $this->getIndexUrl(),
            'card_name' => $card_fullname,
            'merchant_id' => $this->card_token['merchant_id'],
            'token' => $this->card_token['token'],
        ]);
    }

    /**
     * @throws \SoapFault
     */
    public function actionProcessCard(): array
    {
        $data = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = [
            'status' => false,
            'error_message' => ""
        ];

        $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber($data['payment_info']['card_number'], false);

        if ($type_card) {
            $partnerPaymentByBankCode = LinkCardBusiness::getPartnerPaymentByBankCode($type_card, $this->card_token->merchant_id);
            if ($partnerPaymentByBankCode['error_message'] == "") {
                $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->card_token->merchant_id, $partnerPaymentByBankCode['partner_payment']['id']);
                if ($partner_payment_account_info) {
                    $params = [
                        'partner_payment_account_info' => $partner_payment_account_info
                    ];

                    if (!isset($data['payment_info']['card_name']) || $data['payment_info']['card_name'] == "") {
                        $data['payment_info']['card_name'] = $this->card_token->card_holder;
                    }
                    $cyber_source = new CyberSourceVcb3ds2($params);
                    CyberSourceVcb3ds2::_processCardFullname($data['payment_info']['card_name'], $first_name, $last_name);
                    $customer_info = json_decode($this->card_token->info);
                    $inputs = array(
                        'city' => $customer_info->city,
                        'country' => 'VN',
                        'email' => $this->card_token->customer_email,
                        'phone' => $this->card_token->customer_mobile,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'postal_code' => $customer_info->postal_code,
                        'state' => $customer_info->state,
                        'address' => $customer_info->street,
                        'customer_id' => 0,
                        'account_number' => $data['payment_info']['card_number'],
                        'card_type' => $type_card,
                        'expiration_month' => $data['payment_info']['card_month'],
                        'expiration_year' => $data['payment_info']['card_year'],
                        'currency' => 'VND',
                        'amount' => "0",
                        'cvv_code' => $data['payment_info']['cvv_code'],
                        'client_ip' => @$_SERVER['REMOTE_ADDR'],
                        'order_code' => $this->card_token->token,
                    );

                    switch ($data['function']) {
                        case 'setup':
                        {
                            $run_setup = $cyber_source->stepOneAuthSetup($inputs);
                            if ($run_setup == null) {
                                $result['error_message'] = Translate::get("Lỗi kết nối đến kênh thanh toán");
                            } else {
                                if ($run_setup->reasonCode == 100) {
                                    $result['status'] = true;
                                    $result['error_message'] = "";

                                    $result['setup_response'] = [
                                        'referenceID' => $run_setup->payerAuthSetupReply->referenceID,
                                        'accessToken' => $run_setup->payerAuthSetupReply->accessToken,
                                        'deviceDataCollectionURL' => $run_setup->payerAuthSetupReply->deviceDataCollectionURL,
                                    ];
                                } else {
                                    $result['error_message'] = Translate::get("Thông tin thẻ không hợp lệ") . " - SETUP";
                                }
                            }
                            break;
                        }
                        case 'enrollment':
                        {
                            $inputs['referenceID'] = $data['referenceID'];

                            $run_enrollment = $cyber_source->checkEnroll($inputs);
                            if ($run_enrollment == null) {
                                $result['error_message'] = Translate::get("Lỗi kết nối đến kênh thanh toán");
                            } else {
                                $eci = '';
                                if (isset($run_enrollment->payerAuthEnrollReply->eci)) {
                                    $eci = $run_enrollment->payerAuthEnrollReply->eci;
                                } elseif (isset($run_enrollment->payerAuthEnrollReply->eciRaw)) {
                                    $eci = $run_enrollment->payerAuthEnrollReply->eciRaw;
                                }

                                if (CyberSourceVcb3ds2::checkChallenge($run_enrollment)) {
                                    $result['status'] = true;
                                    $result['error_message'] = "";

                                    $result['enrollment_info'] = [
                                        'challenge' => true,
                                        'paReq' => $run_enrollment->payerAuthEnrollReply->paReq,
                                        'acsURL' => $run_enrollment->payerAuthEnrollReply->acsURL,
                                        'authenticationTransactionID' => $run_enrollment->payerAuthEnrollReply->authenticationTransactionID,
                                    ];
                                } else if (isset($run_enrollment->payerAuthEnrollReply->paresStatus)
                                    && $run_enrollment->payerAuthEnrollReply->paresStatus == "Y"
                                    && $eci != null && !in_array($eci, ["00", "07"])
                                ) {
                                    $result['status'] = true;
                                    $result['error_message'] = "";

                                    $result['enrollment_info'] = [
                                        'challenge' => false,
                                        'authenticationTransactionID' => $run_enrollment->payerAuthEnrollReply->authenticationTransactionID,
                                    ];
                                } else {
                                    $result['error_message'] = Translate::get("Thông tin thẻ không hợp lệ") . " - Enroll";
                                }
                            }
                            break;
                        }
                    }
                } else {
                    $result['error_message'] = Translate::get("Tài khoản kênh thanh toán không hợp lệ");
                }
            } else {
                $result['error_message'] = Translate::get("Phương thức thanh toán không hợp lệ");
            }
        } else {
            $result['error_message'] = Translate::get("Đầu BIN không hợp lệ");
        }
        return $result;
    }

    public function actionVerify2d()
    {
        $model = new LinkCardForm();
        $model->scenario = LinkCardForm::SCENARIO_VERIFY_2D;
        $session = Yii::$app->session;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (empty($session->get('verify2d_submit'))) {
                $session->set('verify2d_submit', 0);
            }
            if (intval($model->verify_amount) != $this->card_token['verify_amount']) {
                $model->error_message = 'Số tiền xác thực không chính xác';
                $session->set('verify2d_submit', $session->get('verify2d_submit') + 1);
            } else {
                $update_card_token_success = LinkCardBusiness::updateSuccess(['id' => $this->card_token['id']]);
                if ($update_card_token_success['error_message'] == '') {
                    $this->redirect($this->getSuccessUrl());
                } else {
                    $model->error_message = $update_card_token_success['error_message'];
                }
            }
            if ($session->get('verify2d_submit') >= 3) {
                $model->error_message = 'Bạn đã nhập sai số tiền xác thực quá 3 lần';
                $session->set('verify2d_submit', 0);
                $update_card_token_cancel = LinkCardBusiness::updateCancel(['id' => $this->card_token['id']]);
                if ($update_card_token_cancel['error_message'] == '') {
                    $this->redirect($this->getCancelUrl());
                } else {
                    $model->error_message = $update_card_token_cancel['error_message'];
                }
            }
        }
        return $this->render('verify2d', [
            'model' => $model,
            'verify2d_url' => $this->getVerify2dUrl()
        ]);
    }

    public function actionVerify3d()
    {
        $xid = Yii::$app->request->get('xid');
        $session_info = Yii::$app->session->get('TOKEN_3D_' . $xid);
        if (empty($session_info)) {
            $session_info = Yii::$app->cache->get('TOKEN_3D_' . $xid);
        }

        if (empty($session_info)) {
            $this->redirectErrorPage('Yêu cầu không hợp lệ');
        }

        if (Yii::$app->request->isPost && !empty(Yii::$app->request->post('PaRes'))) {
            $paRes = Yii::$app->request->post('PaRes');
            $result_process_card_token_3d = LinkCardBusiness::processCardToken3d([
                'session_info' => $session_info,
                'paRes' => $paRes,
                'card_token' => $this->card_token
            ]);
            if ($result_process_card_token_3d['error_message'] == '') {
                $result_code = $result_process_card_token_3d['result_code'];
                $bank_trans_id = $result_process_card_token_3d['bank_trans_id'];
                if ($result_code == 'ACCEPT') {
                    Yii::$app->cache->delete('TOKEN_3D_' . $xid);
                    Yii::$app->session->remove('TOKEN_3D_' . $xid);

                    $update_card_token_success = LinkCardBusiness::updateSuccess(['id' => $this->card_token['id']]);
                    if ($update_card_token_success['error_message'] == '') {
                        $this->redirect($this->getSuccessUrl());
                    } else {
                        $this->redirectErrorPage($update_card_token_success['error_message']);
                    }
                } elseif ($result_code == 'REVIEW') {
                    $update_card_token_review = LinkCardBusiness::updateReview(['id' => $this->card_token['id']]);
                    if ($update_card_token_review['error_message'] == '') {
                        $this->redirect($this->getReviewUrl());
                    } else {
                        $this->redirectErrorPage($update_card_token_review['error_message']);
                    }
                } else {
                    $update_card_token_cancel = LinkCardBusiness::updateCancel(['id' => $this->card_token['id']]);
                    if ($update_card_token_cancel['error_message'] == '') {
                        $this->redirect($this->getCancelUrl());
                    } else {
                        $this->redirectErrorPage($update_card_token_cancel['error_message']);
                    }
                }
            } else {
                $update_card_token_cancel = LinkCardBusiness::updateCancel(['id' => $this->card_token['id']]);
                if ($update_card_token_cancel['error_message'] == '') {
                    $this->redirect($this->getCancelUrl());
                } else {
                    $this->redirectErrorPage($update_card_token_cancel['error_message']);
                }
            }
        }
        return $this->render('verify3d', [
            'session_info' => $session_info,
            'verify3d_url' => ROOT_URL . 'vi/frontend/link-card/verify3d/' . $this->card_token['id'] . '?xid=' . $xid
        ]);
    }

    public function actionSuccess()
    {
        return $this->render('success', [
            'card_token' => $this->card_token
        ]);
    }

    public function actionReview()
    {
        return $this->render('review');
    }

    public function actionCancel()
    {
        return $this->render('cancel');
    }

    private function getIndexUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/index', 'card_token_id' => $this->card_token['token']]);
    }

    private function getVerify2dUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/verify2d', 'card_token_id' => $this->card_token['token']]);
    }

    private function getVerify3dUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/verify3d', 'card_token_id' => $this->card_token['token']]);
    }

    private function getSuccessUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/success', 'card_token_id' => $this->card_token['token']]);
    }

    private function getReviewUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/review', 'card_token_id' => $this->card_token['token']]);
    }

    private function getCancelUrl()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/cancel', 'card_token_id' => $this->card_token['token']]);
    }

    private function redirectErrorPage($error_message)
    {
        $error_message = urlencode(base64_encode(base64_encode($error_message)));
        $url = Yii::$app->urlManager->createAbsoluteUrl(['error/index', 'error_message' => $error_message], HTTP_CODE);
        header('Location:' . $url);
        die();
    }

}
