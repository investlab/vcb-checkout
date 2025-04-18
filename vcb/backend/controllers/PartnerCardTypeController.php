<?php


namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\PartnerCardTypeBusiness;
use common\models\db\CardType;
use common\models\db\PartnerCardType;
use common\models\input\PartnerCardTypeSearch;
use Yii;

class PartnerCardTypeController extends BackendController
{

    public function actionIndex()
    {

        $bill_type_arr = PartnerCardType::getBillType();

        $card_type_ids = array();
        $card_types = Tables::selectAllDataTable('card_type', "status =" . CardType::STATUS_ACTIVE, "id asc");
        foreach ($card_types as $key => $data) {
            $card_type_ids[] = $data['id'];
        }
        $cycle_days = $GLOBALS['CYCLE_DAYS'];

        $bill_type = ObjInput::get('bill_type', 'int');
        $partner_card_types_info = Tables::selectAllDataTable('partner_card_type', ["bill_type = :bill_type", "bill_type" => $bill_type]);
        $partner_card_types = PartnerCardType::setRowsGetPartnerCard($partner_card_types_info);
        $result = array();
        foreach ($partner_card_types as $key => $row) {
            $result[$row['cycle_day']][$row['card_type_id']][] = $row;

        }

        return $this->render('index',
            [
                'bill_type_arr' => $bill_type_arr,
                'result' => $result,
                'card_types' => $card_types,
                'cycle_days' => $cycle_days,
            ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $partner_card_type = Tables::selectOneDataTable("partner_card_type", ["id = :id", "id" => $id]);
            $bill_type = $partner_card_type['bill_type'];
            $params = array(
                'partner_card_type_id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerCardTypeBusiness::lock($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Khóa kênh thẻ cào thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(['partner-card-type/index', 'bill_type' => $bill_type]);
            Weblib::showMessage($message, $url);
        }
    }

    // Mở khóa
    public function actionActive()
    {
        $message = null;

        $id = ObjInput::get('id', 'int');
        if (isset($id) && intval($id) > 0) {
            $partner_card_type = Tables::selectOneDataTable("partner_card_type", ["id = :id", "id" => $id]);
            $bill_type = $partner_card_type['bill_type'];
            $params = array(
                'partner_card_type_id' => $id,
                'user_id' => Yii::$app->user->getId()
            );
            $result = PartnerCardTypeBusiness::active($params);
            if ($result['error_message'] == '') {
                $message = Translate::get('Mở khóa kênh thẻ cào thành công');
            } else {
                $message = Translate::get($result['error_message']);
            }

            $url = Yii::$app->urlManager->createAbsoluteUrl(['partner-card-type/index', 'bill_type' => $bill_type]);
            Weblib::showMessage($message, $url);
        }
    }


} 