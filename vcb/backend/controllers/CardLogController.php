<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\CardLogBusiness;
use common\models\db\CardLogFull;
use common\models\form\CardLogUpdateSuccessForm;
use common\models\input\CardLogSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class CardLogController extends BackendController
{

    // Danh sách
    public function actionIndex()
    {
        $search = new CardLogSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');
        $partner_card_search_arr = Weblib::createComboTableArray('partner_card', 'id', 'name', 1, Translate::get('Chọn đối tác gạch thẻ'), true, 'name ASC');
        $card_type_search_arr = Weblib::createComboTableArray('card_type', 'id', 'name', 1, Translate::get('Chọn loại thẻ'), true, 'name ASC');
        $card_status_arr = CardLogFull::getCardStatus();
        $transaction_status_arr = CardLogFull::getTransactionStatus();
        $backup_status_arr = CardLogFull::getBackupStatus();
        $bill_type_arr = CardLogFull::getBillType();
        $cycle_day_arr = $GLOBALS['CYCLE_DAYS'];

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'partner_card_search_arr' => $partner_card_search_arr,
            'card_type_search_arr' => $card_type_search_arr,
            'card_status_arr' => $card_status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'transaction_status_arr' => $transaction_status_arr,
            'backup_status_arr' => $backup_status_arr,
            'bill_type_arr' => $bill_type_arr,
            'cycle_day_arr' => $cycle_day_arr,
            'check_all_operators' => CardLogFull::getOperatorsForCheckAll(),
        ]);
    }

    // Cập nhật thẻ thành công
    public function actionUpdateSuccess()
    {
        $model = new CardLogUpdateSuccessForm();
        $card_log_id = ObjInput::get('id', "int");
        $card_log = array();

        if ($card_log_id > 0) {
            $card_log_info = Tables::selectOneDataTable('card_log_full', ['id = :id', "id" => $card_log_id]);
            if ($card_log_info) {
                $card_log = CardLogFull::setRow($card_log_info);
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $model->id = $card_log_id;

        if (Yii::$app->request->post()) {
            $form = Yii::$app->request->post('CardLogUpdateSuccessForm');

            $params = array(
                'card_log_id' => $form['id'],
                'partner_card_refer_code' => $form['partner_card_refer_code'],
                'card_price' => ObjInput::formatCurrencyNumber($form['card_price']),
                'user_id' => Yii::$app->user->getId()
            );
            $result = CardLogBusiness::updateCardStatusSuccess($params);
            if ($result['error_message'] == '') {
                $message = 'Cập nhật thẻ thành công';
            } else {
                $message = $result['error_message'];
            }
            $url = Yii::$app->urlManager->createAbsoluteUrl('card-log/index');
            Weblib::showMessage($message, $url);
        }
        return $this->render('update-success', [
            'model' => $model,
            'card_log' => $card_log,
        ]);
    }

    // Chi tiết
    public function actionDetail()
    {
        $card_log_id = ObjInput::get('id', "int");
        $card_log = array();

        if ($card_log_id > 0) {
            $card_log_info = Tables::selectOneDataTable('card_log_full', ['id = :id', "id" => $card_log_id]);
            if ($card_log_info) {
                $card_log = CardLogFull::setRow($card_log_info);
            }
        }
        return $this->render('detail', [
            'card_log' => $card_log,
        ]);
    }

    // Xuất excel
    public function actionExport()
    {
        $columns = array(
            'merchant_name' => array('title' => 'Merchant'),
            'merchant_refer_code' => array('title' => Translate::get('Mã tham chiếu merchant')),
            'bill_type_name' => array('title' => Translate::get('Loại hóa đơn')),
            'cycle_day_name' => array('title' => Translate::get('Kỳ thanh toán')),
            'card_type_name' => array('title' => Translate::get('Loại thẻ')),
            'card_code' => array('title' => Translate::get('Mã thẻ')),
            'card_serial' => array('title' => Translate::get('Serial thẻ')),
            'card_price' => array('title' => Translate::get('Mệnh giá thẻ')),
            'card_amount' => array('title' => Translate::get('Số tiền trả merchant')),
            'partner_card_name' => array('title' => Translate::get('Đối tác gạch thẻ')),
            'partner_card_refer_code' => array('title' => Translate::get('Mã tham chiếu đối tác')),
            'partner_card_log_id' => array('title' => Translate::get('Log tham chiếu với đối tác')),
            'percent_fee' => array('title' => Translate::get('Phần trăm phí')),
            'card_status_name' => array('title' => Translate::get('Trạng thái thẻ')),
            'time_created' => array('title' => Translate::get('Thời gian tạo'), 'type' => 'time'),
            'time_card_updated' => array('title' => Translate::get('Thời gian thẻ bị gạch'), 'type' => 'time')
        );
        //------------
        $search = new CardLogSearch();
        $search->setAttributes(Yii::$app->request->get());

        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "XPAY_LOGTHECAO" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "XPAY_LOGTHECAO" . date("d-m-Y-H-i-s") . ".xls";
        }
        //----------
        $obj = new ExportData(200);
        if ($obj->init($file_name, $columns, Yii::$app->user->getId())) {
            $data = $search->searchForExport($obj->getOffset(), $obj->getLimit());
            $result = $obj->process($data);
            echo json_encode($result);
        }
        die();
    }

} 