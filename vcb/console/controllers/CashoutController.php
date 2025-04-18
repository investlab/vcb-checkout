<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 11/03/2017
 * Time: 11:36 SA
 */

namespace console\controllers;

use yii\console\Controller;
use common\components\libs\Tables;
use common\models\db\PartnerPayment;
use common\models\db\Cashout;
use common\models\business\CashoutBusiness;
use common\components\utils\Strings;
use common\payments\NganLuongWithdraw;
use Yii;

class CashoutController extends Controller {

    public function init() {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    /**
     * 10 phut chay 1 lan
     */
    public function actionCheckNganLuongTransactionStatus() {
        $this->_writeLog('[Cashout::check-ngan-luong-transaction-status] start');
        $now = $start = time();
        while (time() - $start < 530) {
            $rows = self::_getCashoutsNganLuongWaitAccept();
            if ($rows != false) {
                foreach ($rows as $row) {
                    $this->_writeLog('[Cashout::check-ngan-luong-transaction-status] data: ' . json_encode($row));
                    set_time_limit(120);
                    $params = array(
                        'ref_code' => Cashout::getNganLuongAuthorizationReferenceCodeByCashoutId($row['id']),
                    );
                    $result = NganLuongWithdraw::CheckRequest($params, false);
                    if ($result['error_code'] == '00') {
                        $all = true;
                        foreach ($result['transactions'] as $transaction) {
                            if ($transaction['transaction_status'] != '00') {
                                $all = false;
                                break;
                            }
                        }
                        if ($all) {
                            $params = array(
                                'cashout_id' => $row['id'],
                                'time_paid' => time(),
                                'bank_refer_code' => '',
                                'receiver_fee' => 0,
                                'user_id' => 0,
                            );
                            $result = CashoutBusiness::updateStatusAcceptAndPaid($params);
                            $this->_writeLog('[Cashout::check-ngan-luong-transaction-status] updateStatusAcceptAndPaid: ' . json_encode($result));
                        }
                    }
                    $this->_writeLog('[Cashout::check-ngan-luong-transaction-status] result: ' . json_encode($result));
                    sleep(5);
                    if (time() - $start < 45) {
                        break;
                    }
                }
                break;
            } else {
                sleep(30);
            }
        }
        $this->_writeLog('[Cashout::check-ngan-luong-transaction-status] end');
    }

    private static function _getCashoutsNganLuongWaitAccept() {
        $partner_payment_id = PartnerPayment::getIdByCode('NGANLUONG');
        if ($partner_payment_id != false) {
            $cashout_info = Tables::selectAllDataTable("cashout", ["partner_payment_id = :partner_payment_id AND status = :status ", "partner_payment_id" => $partner_payment_id, "status" => Cashout::STATUS_WAIT_ACCEPT]);
            if ($cashout_info != false) {
                $cashout_info = Cashout::setRows($cashout_info);
                return $cashout_info;
            }
        }
        return false;
    }

    private static function _writeLog($data) {

        $file = ROOT_PATH . DS . 'console' . DS . 'cashout' . DS . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
