<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerCardBusiness;
use common\models\business\PartnerCardTypeBusiness;
use common\models\db\CardType;
use common\models\db\PartnerCard;
use common\models\form\PartnerCardForm;
use common\models\input\PartnerCardSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerCardController extends BackendController
{

    //Danh sách
    public function actionIndex()
    {
        $search = new PartnerCardSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $model = new PartnerCardForm();
        $status_arr = PartnerCard::getStatus();
        $bill_type_arr = PartnerCard::getBillType();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'model' => $model,
            'status_arr' => $status_arr,
            'bill_type_arr' => $bill_type_arr
        ]);
    }

    // Thêm mới
    public function actionAdd()
    {
        $model = new PartnerCardForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {

            $form = Yii::$app->request->post("PartnerCardForm");
            $params = array(
                'name' => $form['name'],
                'code' => strtoupper($form['code']),
                'bill_type' => $form['bill_type'],
                'config' => $form['config'],
                'user_id' => Yii::$app->user->getId()
            );

            $result = PartnerCardBusiness::add($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Thêm đối tác gạch thẻ thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-card/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    //Cập nhật
    public function actionViewEdit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = ObjInput::get('id', 'int');
            $data = Tables::selectOneDataTable('partner_card', ['id = :id', 'id' => $id]);
            return json_encode($data);
        }
    }

    public function actionUpdate()
    {
        $model = new PartnerCardForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post("PartnerCardForm");
            $params = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'code' => strtoupper($form['code']),
                'config' => $form['config'],
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerCardBusiness::update($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật đối tác gạch thẻ thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl(["partner-card/index"]);
            Weblib::showMessage($message, $url);
        }
    }

    //Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['partner-card/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }
            $params = array(
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerCardBusiness::lock($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa đối tác gạch thẻ thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }

            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }


    }

    //Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['partner-card/index'];
        if (Yii::$app->request->post()) {
            $id = Yii::$app->request->post("id");

            if (Yii::$app->request->post("return_url")) {
                $search = [Yii::$app->request->post("return_url")];
            }

            $params = array(
                'id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerCardBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa đối tác gạch thẻ thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }

    //Cập nhật loại thẻ hỗ trợ
    public function actionViewUpdateCardType()
    {
        $card_type = Tables::selectAllDataTable("card_type", "status = " . CardType::STATUS_ACTIVE);
        $cycle_days = $GLOBALS['CYCLE_DAYS'];

        $partner_card_id = ObjInput::get('id', 'int');
        $partner_card = Tables::selectOneDataTable("partner_card", ["id = :id", "id" => $partner_card_id]);
        $parner_card_type = Tables::selectAllDataTable("partner_card_type", ["partner_card_id = :partner_card_id", "partner_card_id" => $partner_card_id]);

        foreach ($card_type as $key => $data) {
            $card_type[$key]['cycle_days'] = $cycle_days;
            $card_type[$key]['partner_card'] = $partner_card;
            foreach ($parner_card_type as $k => $d) {
                if ($data['id'] == $d['card_type_id'] && $d['partner_card_id'] == $partner_card_id) {
                    $card_type[$key]['cycle_day_in_pct'][] = $d['cycle_day'];
                }
            }
        }
        return $this->render('update-card-type', [
            'card_type' => $card_type,
            'partner_card_id' => $partner_card_id
        ]);
    }


    public function actionUpdateCardType()
    {
        if (Yii::$app->request->post()) {
            $cycle_days = Yii::$app->request->post("cycle_days");
            $partner_card_id = Yii::$app->request->post("partner_card_id");
            $card_type_id = Yii::$app->request->post("card_type_id");

            $card_types = array();
            $card_type_info = Tables::selectAllDataTable("card_type", "status = " . CardType::STATUS_ACTIVE);
            if ($card_type_info != false) {
                foreach ($card_type_info as $row) {
                    $card_types[$row['id']] = isset($cycle_days[$row['id']]) ? $cycle_days[$row['id']] : array();
                }
            }

            $params = array(
                'card_types' => $card_types,
                'partner_card_id' => $partner_card_id,
                'user_id' => Yii::$app->user->getId()
            );

            $result = PartnerCardTypeBusiness::updateMultiCardType($params);

            if ($result['error_message'] == '') {
                $message = Translate::get('Cập nhật loại thẻ hỗ trợ thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('partner-card');
            Weblib::showMessage($message, $url);
        }

    }


}