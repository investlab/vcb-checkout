<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\MerchantCardFeeBusiness;
use common\models\db\CardType;
use common\models\db\Merchant;
use common\models\db\MerchantCardFee;
use common\models\db\Partner;
use common\models\form\MerchantCardFeeForm;
use common\models\input\MerchantCardFeeSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class MerchantCardFeeController extends BackendController
{
    // Danh sách
    public function actionIndex()
    {
        $search = new MerchantCardFeeSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = MerchantCardFee::getStatus();
        $merchant_search_arr = Tables::selectAllDataTable("merchant");
        $card_type_search_arr = Tables::selectAllDataTable("card_type");
        $partner_search_arr = Tables::selectAllDataTable("partner");
        $bill_type_arr = MerchantCardFee::getBillType();
        $cycle_day_arr = $GLOBALS['CYCLE_DAYS'];


        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'bill_type_arr' => $bill_type_arr,
            'card_type_search_arr' => $card_type_search_arr,
            'partner_search_arr' => $partner_search_arr,
            'cycle_day_arr' => $cycle_day_arr,
        ]);
    }

// Thêm mới
    public function actionAdd()
    {
        $model = new MerchantCardFeeForm();
        $card_type_arr = Weblib::createComboTableArray('card_type', 'id', 'name', 'status = ' . CardType::STATUS_ACTIVE, Translate::get('Chọn loại thẻ'), true);
        $merchant_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, Translate::get('Chọn merchant'), true);
        $partner_arr = Weblib::createComboTableArray('partner', 'id', 'name', 'status = ' . Partner::STATUS_ACTIVE, Translate::get('Chọn đối tác'), true);
        $bill_type_arr = MerchantCardFee::getBillType();
        $cycle_day_arr = $GLOBALS['CYCLE_DAYS'];

        $errors = null;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('MerchantCardFeeForm');
            $params = array(
                'card_type_id' => $form['card_type_id'],
                'bill_type' => $form['bill_type'],
                'cycle_day' => $form['cycle_day'],
                'partner_id' => $form['partner_id'],
                'merchant_id' => $form['merchant_id'],
                'percent_fee' => $form['percent_fee'],
                'time_begin' => Yii::$app->formatter->asTimestamp($form['time_begin']),
                'currency' => $GLOBALS['CURRENCY']['VND'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = MerchantCardFeeBusiness::addAndActive($params);

            if ($result['error_message'] == '') {
                $message = 'Thêm phí thẻ cào thành công';
                $url = Yii::$app->urlManager->createAbsoluteUrl('merchant-card-fee/index');
                Weblib::showMessage($message, $url);
            } else {
                $errors = $result['error_message'];
            }
        }
        return $this->render('add', [
            'model' => $model,
            'errors' => $errors,
            'merchant_arr' => $merchant_arr,
            'card_type_arr' => $card_type_arr,
            'partner_arr' => $partner_arr,
            'bill_type_arr' => $bill_type_arr,
            'cycle_day_arr' => $cycle_day_arr,
        ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['merchant-card-fee/index'];
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $params = [
                'merchant_card_fee_id' => $id,
                'user_id' => Yii::$app->user->getId()

            ];
            $result = MerchantCardFeeBusiness::lock($params, true);
            if ($result['error_message'] == '') {
                $message = 'Khóa phí thẻ cào thành công';
            } else {
                $message = $result['error_message'];
            }
        } else {
            $message = 'Không tồn tại phí thẻ cào';
        }
        $url = Yii::$app->urlManager->createUrl($search);
        Weblib::showMessage($message, $url);
    }


} 