<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\ExportData;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\db\CardTransaction;
use common\models\db\Cashout;
use common\models\input\CardTransactionSearch;
use Yii;

class CardTransactionController extends BackendController {

    // Danh sách
    public function actionIndex()
    {
        $search = new CardTransactionSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $merchant_search_arr = Weblib::createComboTableArray('merchant', 'id', 'name', 1, Translate::get('Chọn merchant'), true, 'name ASC');
        $partner_card_search_arr = Weblib::createComboTableArray('partner_card', 'id', 'name', 1, Translate::get('Chọn đối tác gạch thẻ'), true, 'name ASC');
        $card_type_search_arr = Weblib::createComboTableArray('card_type', 'id', 'name', 1, Translate::get('Chọn loại thẻ'), true, 'name ASC');
        $status_arr = CardTransaction::getStatus();
        $bill_type_arr = CardTransaction::getBillType();
        $cycle_day_arr = $GLOBALS['CYCLE_DAYS'];

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'partner_card_search_arr' => $partner_card_search_arr,
            'card_type_search_arr' => $card_type_search_arr,
            'status_arr' => $status_arr,
            'merchant_search_arr' => $merchant_search_arr,
            'bill_type_arr' => $bill_type_arr,
            'cycle_day_arr' => $cycle_day_arr,
            'check_all_operators' => CardTransaction::getOperatorsForCheckAll(),
        ]);
    }

    // Chi tiết
    public function actionDetail()
    {
        $card_transaction_id = ObjInput::get('id', "int");
        $card_transaction = array();
        $cashout = array();

        if ($card_transaction_id > 0) {
            $card_transaction_info = Tables::selectOneDataTable('card_transaction', ['id = :id', "id" => $card_transaction_id]);
            if ($card_transaction_info) {
                $card_transaction = CardTransaction::setRow($card_transaction_info);
            }

            $cashout_id = $card_transaction['cashout_id'];
            if(intval($cashout_id) > 0) {
                $cashout_info = Tables::selectOneDataTable("cashout", ['id = :id', "id" => $cashout_id]);
                $cashout = Cashout::setRow($cashout_info);
            }
        }

        return $this->render('detail', [
            'card_transaction' => $card_transaction,
            'cashout' => $cashout,
        ]);
    }

    // Xuất excel
    public function actionExport()
    {
        $columns = array(
            'merchant_name' => array('title' => 'Merchant'),
            'merchant_refer_code' => array('title' => Translate::get('Mã tham chiếu merchant'),
            'bill_type_name' => array('title' => Translate::get('Loại hóa đơn')),
            'cycle_day_name' => array('title' => Translate::get('Kỳ thanh toán')),
            'card_type_name' => array('title' => Translate::get('Loại thẻ')),
            'card_code' => array('title' => Translate::get('Mã thẻ')),
            'card_serial' => array('title' => Translate::get('Serial thẻ')),
            'card_price' => array('title' => Translate::get('Mệnh giá thẻ')),
            'card_amount' => array('title' => Translate::get('Số tiền merchant nhận')),
            'partner_card_name' => array('title' => Translate::get('Đối tác gạch thẻ')),
            'partner_card_refer_code' => array('title' => Translate::get('Mã tham chiếu đối tác')),
            'partner_card_log_id' => array('title' => Translate::get('Log tham chiếu với đối tác')),
            'percent_fee' => array('title' => Translate::get('Phí thẻ cào')),
            'status_name' => array('title' => Translate::get('Trạng thái')),
            'time_created' => array('title' => Translate::get('Thời gian tạo')), 'type' => 'time')
        );
        //------------
        $search = new CardTransactionSearch();
        $search->setAttributes(Yii::$app->request->get());

        if (intval($search->time_created_from) > 0 && intval($search->time_created_to) > 0) {
            $file_name = "XPAY_GIAODICHTHECAO" . $search->time_created_from . "_" . $search->time_created_to . ".xls";
        } else {
            $file_name = "XPAY_GIAODICHTHECAO" . date("d-m-Y-H-i-s") . ".xls";
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