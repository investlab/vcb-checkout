<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use backend\models\form\PartnerPaymentAccountDeleteForm;
use common\models\db\PartnerPaymentAccount;
use common\components\libs\Tables;
use Yii;
use common\components\utils\Translate;

class PartnerPaymentAccountBusiness
{
    /**
     *
     * @param type $params : merchant_id, currency, partner_payment_id, partner_payment_account, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentAccount::getDb()->beginTransaction();
        }
        $params['partner_payment_account'] = trim($params['partner_payment_account']);
        //----------
        $account_id = \common\models\db\Account::getAccountIdByMerchantId($params['merchant_id'], $params['currency']);
        if ($account_id != false) {
            $check_exists = Tables::selectOneDataTable("partner_payment_account", ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND partner_payment_account = :partner_payment_account AND partner_merchant_password = :partner_merchant_password AND partner_merchant_id = :partner_merchant_id",
                'merchant_id' => $params['merchant_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_account' => $params['partner_payment_account'],
                'partner_merchant_password' => $params['partner_merchant_password'],
                'partner_merchant_id' => $params['partner_merchant_id'],
            ]);
            if ($check_exists == false) {
                $model = new PartnerPaymentAccount();
                $model->merchant_id = $params['merchant_id'];
                $model->account_id = $account_id;
                $model->currency = $params['currency'];
                $model->partner_payment_id = $params['partner_payment_id'];
                $model->partner_payment_account = $params['partner_payment_account'];
                $model->partner_merchant_password = $params['partner_merchant_password'];
                $model->partner_merchant_id = $params['partner_merchant_id'];
                $model->transaction_key = $params['transaction_key'];

                $model->token_key = $params['token_key'];
                $model->checksum_key = $params['checksum_key'];

                $model->status = PartnerPaymentAccount::STATUS_ACTIVE;
                $model->time_created = time();
                $model->time_updated = time();
                $model->user_created = $params['user_id'];


                if ($model->validate()) {

                    if ($model->save()) {
                        $id = $model->getDb()->getLastInsertID();
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi thêm kênh thanh toán';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Tài khoản kênh thanh toán đã tồn tại';
            }
        } else {
            $error_message = 'Tài khoản không tồn tại';
        }            
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : id, user_id
     * @param type $rollback
     * @return type
     */
    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentAccount::getDb()->beginTransaction();
        }
        $model = PartnerPaymentAccount::findOne(["id" => $params['id'], "status" => PartnerPaymentAccount::STATUS_ACTIVE]);
        if ($model != null) {
            $model->status = PartnerPaymentAccount::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi sửa tài khoản kênh thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản kênh thanh toán không hợp lệ hoặc không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }


    static function delete($params, $rollback = true)
    {
        $message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------

        $model = PartnerPaymentAccount::findOne(["id" => intval($params['id'])]);
        if ($model == null) {
            $message = Translate::get('Tham số đầu vào không hợp lệ, truy cập bị từ chối');
        } else {

            if ($model->delete()) {
                $message = '';
            } else {
                if (isset($model->errors['delete']) && !empty($model->errors['delete'])) {
                    $message = $model->errors['delete'][0];
                } else {
                    $message = Translate::get('Có lỗi trong quá trình xử lý');
                }
            }
        }

        return array('error_message' => $message);
    }

    /**
     *
     * @param type $params : id, user_id
     * @param type $rollback
     * @return type
     */
    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentAccount::getDb()->beginTransaction();
        }
        $model = PartnerPaymentAccount::findOne(["id" => $params['id'], "status" => PartnerPaymentAccount::STATUS_LOCK]);
        if ($model != null) {
            $model->status = PartnerPaymentAccount::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi sửa tài khoản kênh thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản kênh thanh toán không hợp lệ hoặc không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }
    
    /**
     *
     * @param type $params : id, balance, user_id
     * @param type $rollback
     * @return type
     */
    static function updateBalance($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentAccount::getDb()->beginTransaction();
        }
        $model = PartnerPaymentAccount::findOne(["id" => $params['id'], "status" => PartnerPaymentAccount::STATUS_ACTIVE]);
        if ($model != null) {
            $model->balance = $params['balance'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi sửa tài khoản kênh thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản kênh thanh toán không hợp lệ hoặc không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }
    
    /**
     * 
     * @param type $params: merchant_id, partner_payment_id, currency, user_id,
     * @param type $rollback
     * @return boolean
     */
    public static function updatePartnerPaymentBalanceByMerchant($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentAccount::getDb()->beginTransaction();
        }
        $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["id = :id ", "id" => $params['partner_payment_id']]);
        if ($partner_payment_info != false && $partner_payment_info['code'] == 'NGANLUONG') {
            $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", [
                "merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND currency = :currency AND status = :status ", 
                "merchant_id" => $params['merchant_id'],
                "partner_payment_id" => $params['partner_payment_id'],
                "currency" => $params['currency'], 
                "status" => PartnerPaymentAccount::STATUS_ACTIVE
            ]);
            if ($partner_payment_account_info != false) {
                $all = true;
                foreach ($partner_payment_account_info as $row) {
                    $inputs = array(
                        'merchant_id' => $row['partner_merchant_id'],
                        'merchant_password' => MD5($row['partner_merchant_password']),
                        'user_email' => $row['partner_payment_account'],
                    );
                    $result = \common\payments\NganLuongTransfer::getBalance($inputs);
                    if ($result['response_code'] === 'E00') {
                        $inputs = array(
                            'id' => $row['id'], 
                            'balance' => $result['response']['balance'], 
                            'user_id' => $params['user_id'],
                        );
                        $result = self::updateBalance($inputs, false);
                        if ($result['error_message'] != '') {
                            $error_message = $result['error_message'];
                            $all = false;
                            break;
                        }
                    } else {
                        $error_message = 'Có lỗi khi lấy số dư tài khoản Ngân Lượng';
                        $all = false;
                        break;
                    }   
                }
                if ($all) {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Không có tài khoản kênh thanh toán';
            }
        } else {
            $error_message = 'Kênh thanh toán không hỗ trợ lấy số dư';
        }   
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }
}