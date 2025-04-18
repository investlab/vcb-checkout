<?php

namespace common\models\business;

use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\db\Account;
use common\models\db\CardVoucher;
use common\models\db\CardVoucherRequirement;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\util\Helpers;
use PHPExcel_IOFactory;
use Yii;
use yii\db\Exception;

class CardVoucherBusiness
{
    const JSON_FILE_PATH = "tmp/import/public";

    /**
     * @throws \yii\db\Exception
     */
    public static function add($params, $rollback = true)
    {
        $commit = false;
        if ($rollback) {
            $transaction = CardVoucher::getDb()->beginTransaction();
        }
        $model = new CardVoucher();
        $model->merchant_id = $params['merchant_id'];
        $model->card_number = $params['card_number'];
        $model->time_expired = strtotime($params['time_expired']);
        $model->user_created = $params['user_create'];
        $model->balance = $params['balance'];
        $model->status = CardVoucher::STATUS_NEW;
        if ($model->validate()) {
            if ($model->save()) {
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm thẻ voucher';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Exception
     * @throws Exception
     */
    public static function import($value, $preview = false)
    {
        if ($preview) {
            $file_excel = PHPExcel_IOFactory::load($value);
            $sheetData = $file_excel->getActiveSheet()->toArray(null, true, true, true);
            unset($sheetData[0], $sheetData[1]);
            $path_process = Helpers::initFolder(self::JSON_FILE_PATH);
            $import_id = uniqid(8) . "-" . time();
            $path_file_json = $path_process . DS . $import_id . ".json";
            $count_error = self::validateDataImport($sheetData);
            $data = json_encode($sheetData);
            $json_file = file_put_contents($path_file_json, $data);
            if ($json_file) {
                return [
                    'path' => $path_file_json,
                    'import_id' => $import_id,
                    'count_error' => $count_error,
                    'count_record' => count($sheetData),
                ];
            } else {
                return false;
            }
        } else {
            $params = [
                'merchant_id' => $value['B'],
                'card_number' => $value['C'],
                'time_expired' => str_replace("/", "-", $value['D']),
                'balance' => $value['E'] == null ? 0 : $value['E'],
                'user_create' => Yii::$app->getUser()->id,
            ];
            return self::add($params);
        }
    }

    private static function validateDataImport(&$data)
    {
        $count_error = 0;
        if (!is_array($data)) {
            return false;
        } else {
            $merchant_id_load = [];
            $card_number_load = [];

            foreach ($data as $key => $item) {
                $merchant_id = $item["B"];
                $card_number = $item["C"];
                $time_expired = $item["D"];
                $amount = $item["E"];

                if (
                    $merchant_id == null
                    && $card_number == null
                    && $time_expired == null
                ) {
                    $data[$key]['has_error'] = true;
                    $data[$key]['errors']["B"] = Translate::get("Merchant ID không hợp lệ");
                    $data[$key]['errors']["C"] = Translate::get("Số thẻ không hợp lệ");
                    $data[$key]['errors']["D"] = Translate::get("Thời gian hết hạn không hợp lệ");
                }
                if ($merchant_id != null) {
                    if (!in_array($merchant_id, $merchant_id_load)) {
                        $merchant = Merchant::find()->where(['id' => $merchant_id])->andWhere(['status' => Merchant::STATUS_ACTIVE])->exists();
                        if (!$merchant) {
                            $data[$key]['has_error'] = true;
                            $data[$key]['errors']["G"] = Translate::get("Merchant không tồn tại");
                        } else {
                            $merchant_id_load[] = $merchant_id;
                        }
                    }
                }
                if ($card_number != null) {
                    if (strlen($card_number) != 19) {
                        $data[$key]['has_error'] = true;
                        $data[$key]['errors']["C"] = Translate::get("Số thẻ phải bằng 19");
                    } else {
                        if (!in_array($card_number, $card_number_load)) {
                            $card_voucher = CardVoucher::find()->where(['card_number' => $card_number])->exists();
                            if ($card_voucher) {
                                $data[$key]['has_error'] = true;
                                $data[$key]['errors']["C"] = Translate::get("Số thẻ đã tồn tại");
                            } else {
                                $card_number_load[] = $card_number;
                            }
                        }
                    }
                }
                if ($amount != null && $amount != "" && !($amount >= 0)) {
                    $data[$key]['has_error'] = true;
                    $data[$key]['errors']["E"] = Translate::get("Số tiền không hợp lệ");
                }
                if ($time_expired != null) {
                    $test_arr = explode('/', $time_expired);
                    if (count($test_arr) == 3) {
                        if (!checkdate($test_arr[1], $test_arr[0], $test_arr[2])) {
                            $data[$key]['has_error'] = true;
                            $data[$key]['errors']["D"] = Translate::get("Ngày hết hạn không hợp lệ");
                        } else {
                            if (time() >= strtotime(str_replace("/", "-", $time_expired))) {
                                $data[$key]['has_error'] = true;
                                $data[$key]['errors']["D"] = Translate::get("Ngày hết hạn phải lớn hơn thời gian hiẹn tại");
                            }
                        }
                    } else {
                        $data[$key]['has_error'] = true;
                        $data[$key]['errors']["D"] = Translate::get("Ngày hết hạn không hợp lệ");
                    }
                }
                if (isset($data[$key]['has_error']) && $data[$key]['has_error']) {
                    $count_error++;
                }
            }
            return $count_error;
        }
    }

    public static function topUp($data)
    {
        if (!isset($data['id']) || !isset($data['balance'])) {
            $error_message = "Tham số đầu vào không hợp lệ";
        } elseif (!(intval(ObjInput::formatCurrencyNumber($data['balance'])) > 0)) {
            $error_message = "Số tiền không hợp lệ";
        } else {
            $card_voucher = CardVoucher::findOne($data['id']);
            if (!$card_voucher) {
                $error_message = "Không tồn tại bản ghi";
            } else {
                $merchant_info = Merchant::find()
                    ->where(['id' => $card_voucher->merchant_id])
                    ->andWhere(['status' => Merchant::STATUS_ACTIVE])
                    ->one();
                if (!$merchant_info) {
                    $error_message = "Merchant không tồn tại hoặc đang bị khoá";
                } else {
                    $account = $merchant_info->getAccount()->one();
                    if (!$account) {
                        $error_message = "Tài khoản kênh thanh toán không tồn tại";
                    } else {
                        if (ObjInput::formatCurrencyNumber($data['balance']) > $account->balance_card_voucher) {
                            $error_message = "Số dư Merchant không đủ";
                        } else {
                            $amount = ObjInput::formatCurrencyNumber($data['balance']);
                            $require = new CardVoucherRequirement();
                            $require->require_id = isset($data['require_id']) ? $data['require_id'] : Helpers::randomString();
                            $require->card_voucher_id = $card_voucher->id;
                            $require->type = CardVoucherRequirement::TYPE_TOP_UP;
                            $require->amount = $amount;
                            $require->status = CardVoucherRequirement::STATUS_NEW;
                            $require->user_created = Yii::$app->getUser()->id;
                            if ($require->validate()) {
                                if ($require->save()) {
                                    $account->balance_freezing_card_voucher += $amount;
                                    $account->balance_card_voucher -= $amount;
                                    $account->save();

                                    $error_message = '';
                                } else {
                                    $error_message = 'Có lỗi khi thêm yêu cầu';
                                }
                            } else {
                                $error_message = 'Tham số đầu vào không hợp lệ';
                            }
                        }
                    }
                }
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     * @throws Exception
     */
    public static function requirementAccept($id_requirement)
    {
        $commit = false;

//        $account_begin = Account::getDb()->beginTransaction();
        $card_voucher_requirement_begin = CardVoucherRequirement::getDb()->beginTransaction();

        $requirement = CardVoucherRequirement::find()
            ->where(['id' => $id_requirement])
            ->andWhere(['status' => CardVoucherRequirement::STATUS_NEW])
            ->one();
        if (!$requirement) {
            $error_message = 'Yêu cầu không hợp lệ';
        } else {
            $card_voucher = $requirement->getCardVoucher()->one();
            if (!$card_voucher) {
                $error_message = 'Yêu cầu không hợp lệ';
            } else {
                switch ($requirement->type) {
//                    Action Top Up
//                    case CardVoucherRequirement::TYPE_TOP_UP: {
//                        $account = $card_voucher->getMerchant()->one()->getAccount()->one();
//                        if (!$account) {
//                            $error_message = "Tài khoản kênh thanh toán không tồn tại";
//                        } else {
//                            $card_voucher->balance += $requirement->amount;
//                            if ($card_voucher->save()) {
//                                $account->balance_freezing_card_voucher -= $requirement->amount;
//                                if ($account->save()) {
//                                    $error_message = '';
//                                    $commit = true;
//                                } else {
//                                    $error_message = 'Có lỗi xảy ra';
//                                }
//                            } else {
//                                $error_message = 'Có lỗi xảy ra';
//                            }
//                        }
//                        break;
//                    }
//                    Action Active
//                    case CardVoucherRequirement::TYPE_LOCK: {
//                        $card_voucher->status = $requirement->card_voucher_status;
//                        if ($card_voucher->save()) {
////                            Clear all requirement same
//                            $clear_requirement = CardVoucherRequirement::find()
//                                ->where(['type' => CardVoucherRequirement::TYPE_LOCK])
//                                ->andWhere(['<>', 'id', $requirement->id])
//                                ->andWhere(['card_voucher_id' => $card_voucher->id])
//                                ->andWhere(['status' => CardVoucherRequirement::STATUS_NEW])
//                                ->all();
//                            foreach ($clear_requirement as $item) {
//                                $item->status = CardVoucherRequirement::STATUS_REJECT;
//                                $item->save();
//                            }
//                            $error_message = '';
//                            $commit = true;
//                        } else {
//                            $error_message = 'Có lỗi xảy ra';
//                        }
//                        break;
//                    }

//                    case CardVoucherRequirement::TYPE_ACTIVE: {
//                        $card_voucher->status = $requirement->card_voucher_status;
//                        if ($card_voucher->save()) {
////                            Clear all requirement same
//                            $clear_requirement = CardVoucherRequirement::find()
//                                ->where(['type' => CardVoucherRequirement::TYPE_ACTIVE])
//                                ->andWhere(['<>', 'id', $requirement->id])
//                                ->andWhere(['card_voucher_id' => $card_voucher->id])
//                                ->andWhere(['status' => CardVoucherRequirement::STATUS_NEW])
//                                ->all();
//                            foreach ($clear_requirement as $item) {
//                                $item->status = CardVoucherRequirement::STATUS_REJECT;
//                                $item->save();
//                            }
//                            $error_message = '';
//                            $commit = true;
//                        } else {
//                            $error_message = 'Có lỗi xảy ra';
//                        }
//                        break;
//                    }
                    case CardVoucherRequirement::TYPE_WITH_DRAW: {
                        $merchant = $card_voucher->getMerchant()->one();
                        $account = $card_voucher->getMerchant()->one()->getAccount()->one();
                        if (!$account) {
                            $error_message = "Tài khoản kênh thanh toán không tồn tại";
                        } else {
                            $payment_method_id = PaymentMethod::getPaymentMethodIdActiveByCode("HETHONG-WITHDRAW-CARD-VOUCHER");
                            if ($payment_method_id) {
                                $partner_payment_method = PartnerPaymentMethod::getByPaymentMethodId($payment_method_id);
                                if ($partner_payment_method) {
                                    $partner_payment = PartnerPayment::getById($partner_payment_method['partner_payment_id']);
                                    $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($merchant->id, $partner_payment['id']);
                                    if ($partner_payment_account_info) {
                                        $inputs = array(
                                            'checkout_order_id' => '0',
                                            'payment_method_id' => $payment_method_id,
                                            'partner_payment_id' => $partner_payment['id'],
                                            'partner_payment_method_refer_code' => $requirement->require_id,
                                            'amount' => $requirement->amount,
                                            'currency' => "VND",
                                            'user_id' => $requirement->user_created,
                                            'installment_conversion' => "",
                                            'installment_fee' =>  "",
                                            'installment_fee_merchant' => '',
                                            'installment_fee_buyer' => '',
                                            'requirement_id' => $requirement->id,
                                            'account_id' => $partner_payment_account_info['account_id'],
                                        );
                                        $result = TransactionBusiness::addWithdrawTransactionCardVoucher($inputs, false);
                                        if ($result['error_message'] == "") {
                                            $error_message = '';
                                            $commit = true;
                                        } else {
                                            $error_message = $result['error_message'];
                                        }
                                    } else {
                                        $error_message = "Chưa cấu hình tài khoản kênh thanh toán";
                                    }
                                } else {
                                    $error_message = "Chưa cấu hình kênh";
                                }
                            } else {
                                $error_message = "Chưa cấu hình kênh";
                            }
                        }
                        break;
                    }
//                    Action Lock
                    default: {
                        $error_message = "Lỗi không xác định";
                    }
                }
            }

        }
        if ($commit) {
            $requirement->status = CardVoucherRequirement::STATUS_ACCEPT;
            $requirement->save();
//            $account_begin->commit();
            $card_voucher_requirement_begin->commit();
        } else {
//            $account_begin->rollBack();
            $card_voucher_requirement_begin->rollBack();
        }
        return array('error_message' => $error_message);
    }

    /**
     * @throws Exception
     */
    public static function requirementReject($id_requirement)
    {
        $commit = false;

        $account_begin = Account::getDb()->beginTransaction();
        $card_voucher_requirement_begin = CardVoucherRequirement::getDb()->beginTransaction();

        $requirement = CardVoucherRequirement::find()
            ->where(['id' => $id_requirement])
            ->andWhere(['status' => CardVoucherRequirement::STATUS_NEW])
            ->one();
        if (!$requirement) {
            $error_message = 'Yêu cầu không hợp lệ';
        } else {
            switch ($requirement->type) {
//                    Action Top Up
                case CardVoucherRequirement::TYPE_TOP_UP: {
                    $account = $requirement->getCardVoucher()->one()->getAccount()->one();
                    $account->balance_freezing_card_voucher -= $requirement->amount;
                    $account->balance_card_voucher += $requirement->amount;
                    if ($account->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi xảy ra';
                    }
                    break;
                }
//                    Action Lock
                case CardVoucherRequirement::TYPE_LOCK:
                case CardVoucherRequirement::TYPE_ACTIVE: {
                    $error_message = '';
                    $commit = true;
                    break;
                }
//                Action Withdraw
                case CardVoucherRequirement::TYPE_WITH_DRAW: {
                    $card_voucher = $requirement->getCardVoucher()->one();
                    $card_voucher->balance_freezing -= $requirement->amount;
                    $card_voucher->balance += $requirement->amount;
                    if ($card_voucher->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi xảy ra';
                    }
                    break;
                }
                default: {
                    $error_message = "Lỗi không xác định";
                }
            }
        }
        if ($commit) {
            $account_begin->commit();
            $requirement->status = CardVoucherRequirement::STATUS_REJECT;
            $requirement->save();
            $card_voucher_requirement_begin->commit();
        } else {
            $account_begin->rollBack();
            $card_voucher_requirement_begin->rollBack();
        }


        return array('error_message' => $error_message);
    }

    /**
     * @throws \PHPExcel_Reader_Exception
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public static function requirementImport($value, $preview = false)
    {
        if ($preview) {
            $file_excel = PHPExcel_IOFactory::load($value);
            $sheetData = $file_excel->getActiveSheet()->toArray(null, true, true, true);
            unset($sheetData[0], $sheetData[1]);
            $path_process = Helpers::initFolder(self::JSON_FILE_PATH);
            $import_id = uniqid(8) . "-" . time();
            $path_file_json = $path_process . DS . $import_id . ".json";
            $count_error = self::validateDataImportRequirement($sheetData);
            $data = json_encode($sheetData);
            $json_file = file_put_contents($path_file_json, $data);
            if ($json_file) {
                return [
                    'path' => $path_file_json,
                    'import_id' => $import_id,
                    'count_error' => $count_error,
                    'count_record' => count($sheetData),
                ];
            } else {
                return false;
            }
        } else {
            $card_voucher = CardVoucher::find()->where(['card_number' => $value['B']])->one();
            $params = [
                'id' => $card_voucher->id,
                'balance' => $value['C'],
                'require_id' => $value['require_id'],
            ];
            return self::topUp($params);
        }
    }

    private static function validateDataImportRequirement(&$data)
    {
        $count_error = 0;
        if (!is_array($data)) {
            return false;
        } else {
            $card_number_load = [];
            $merchant_id_load = [];
            $merchant_load = [];

            foreach ($data as $key => $item) {
                $card_number = $item["B"];
                $amount = $item["C"];

                if (
                    $card_number == null
                    && $amount == null
                ) {
                    $data[$key]['has_error'] = true;
                    $data[$key]['errors']["B"] = Translate::get("Số thẻ không hợp lệ");
                    $data[$key]['errors']["C"] = Translate::get("Số tiền không hợp lệ");
                }

                if ($amount != null && $amount != "" && !($amount >= 0)) {
                    $data[$key]['has_error'] = true;
                    $data[$key]['errors']["D"] = Translate::get("Số tiền không hợp lệ");
                }
                if ($card_number != null) {
                    if (!in_array($card_number, $card_number_load)) {
                        $card_voucher = CardVoucher::find()
                            ->where(['card_number' => $card_number])
                            ->one();
                        if (!$card_voucher) {
                            $data[$key]['has_error'] = true;
                            $data[$key]['errors']["B"] = Translate::get("Số thẻ không tồn tại");
                        } else {
                            if (!in_array($card_voucher->status, [CardVoucher::STATUS_NEW, CardVoucher::STATUS_ACTIVE])) {
                                $data[$key]['has_error'] = true;
                                $data[$key]['errors']["B"] = Translate::get("Thẻ hết hạn hoặc đang bị khoá");
                            } else {
                                $card_number_load[] = $card_number;
//                              Load merchant info
                                if (!in_array($card_voucher->merchant_id, $merchant_id_load)) {
                                    $merchant = $card_voucher->getMerchant()->one();
                                    if (!$merchant) {
                                        $data[$key]['has_error'] = true;
                                        $data[$key]['errors']["B"] = Translate::get("Merchant không tồn tại");
                                    } else {
                                        if ($merchant->status == Merchant::STATUS_LOCK) {
                                            $data[$key]['has_error'] = true;
                                            $data[$key]['errors']["B"] = Translate::get("Merchant đang bị khoá");
                                        } else {
                                            $data[$key]['merchant_name'] = $merchant->name;
                                            $merchant_id_load[] = $merchant->id;
                                            $merchant_load[$merchant->id]['balance_top_up'] = 0;
                                            $merchant_load[$merchant->id]['name'] = $merchant->name;
                                            $account = $merchant->getAccount()->one();
                                            $merchant_load[$merchant->id]['balance_card_voucher'] = $account->balance_card_voucher;
                                        }
                                    }
                                } else {
                                    $data[$key]['merchant_name'] = $merchant_load[$card_voucher->merchant_id]['name'];
                                }
                            }
                        }
                    }
                }

                if (isset($data[$key]['has_error']) && $data[$key]['has_error']) {
                    $count_error++;
                } else {
                    $merchant_load[$card_voucher->merchant_id]['balance_top_up'] += $amount;
                    if ($merchant_load[$card_voucher->merchant_id]['balance_top_up'] > $merchant_load[$card_voucher->merchant_id]['balance_card_voucher']) {
                        $data[$key]['has_error'] = true;
                        $data[$key]['errors']["C"] = "Số dư merchant không đủ";
                        $count_error++;
                        $merchant_load[$card_voucher->merchant_id]['balance_top_up'] -= $amount;
                    }
                }
            }
            return $count_error;
        }
    }

    public static function active($value)
    {
        if (!isset($value['id']) || !isset($value['user_active'])) {
            $error_message = "Tham số đầu vào không hợp lệ";
        } else {
            $card_voucher = CardVoucher::findOne($value['id']);
            if (!$card_voucher) {
                $error_message = "Không tồn tại bản ghi";
            } else {
                if ($card_voucher->time_expired < time()) {
                    $error_message = "Thẻ đã hết hạn";
                    $card_voucher->status = CardVoucher::STATUS_EXPIRED;
                    $card_voucher->save();
                } else {
                    $require = new CardVoucherRequirement();
                    $require->require_id = isset($data['require_id']) ? $data['require_id'] : Helpers::randomString();
                    $require->card_voucher_id = $card_voucher->id;
                    $require->type = CardVoucherRequirement::TYPE_ACTIVE;
                    $require->status = CardVoucherRequirement::STATUS_NEW;
                    $require->card_voucher_status = CardVoucher::STATUS_ACTIVE;
                    $require->user_created = Yii::$app->getUser()->id;
                    if ($require->validate()) {
                        if ($require->save()) {
                            $error_message = '';
                        } else {
                            $error_message = 'Có lỗi khi thêm yêu cầu';
                        }
                    } else {
                        $error_message = 'Tham số đầu vào không hợp lệ';
                    }


//                    $card_voucher->time_active = time();
//                    $card_voucher->user_active = $value['user_active'];
//                    $card_voucher->status = CardVoucher::STATUS_ACTIVE;
//                    if ($card_voucher->save()) {
//                        $error_message = "";
//                    } else {
//                        $error_message = "Có lỗi xảy ra, vui lòng thử lại";
//                    }
                }


            }
        }
        return array('error_message' => $error_message);
    }

    public static function lock($value)
    {
        if (!isset($value['id']) || !isset($value['user_active'])) {
            $error_message = "Tham số đầu vào không hợp lệ";
        } else {
            $card_voucher = CardVoucher::findOne($value['id']);
            if (!$card_voucher) {
                $error_message = "Không tồn tại bản ghi";
            } else {
                $require = new CardVoucherRequirement();
                $require->require_id = isset($data['require_id']) ? $data['require_id'] : Helpers::randomString();
                $require->card_voucher_id = $card_voucher->id;
                $require->type = CardVoucherRequirement::TYPE_LOCK;
                $require->status = CardVoucherRequirement::STATUS_NEW;
                $require->card_voucher_status = CardVoucher::STATUS_LOCK;
                $require->user_created = Yii::$app->getUser()->id;
                if ($require->validate()) {
                    if ($require->save()) {
                        $error_message = '';
                    } else {
                        $error_message = 'Có lỗi khi thêm yêu cầu';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            }
        }
        return array('error_message' => $error_message);
    }

    public static function withdraw($data)
    {
        if (!isset($data['id']) || !isset($data['amount'])) {
            $error_message = "Tham số đầu vào không hợp lệ";
        } elseif (!(intval(ObjInput::formatCurrencyNumber($data['amount'])) > 0)) {
            $error_message = "Số tiền không hợp lệ";
        } else {
            $card_voucher = CardVoucher::findOne($data['id']);
            if (!$card_voucher) {
                $error_message = "Không tồn tại bản ghi";
            } elseif(!in_array($card_voucher->status, [CardVoucher::STATUS_ACTIVE, CardVoucher::STATUS_NEW], CardVoucher::STATUS_EXPIRED)){
                $error_message = "Trạng thái thẻ không hợp lệ";
            } else {
                $merchant_info = Merchant::find()
                    ->where(['id' => $card_voucher->merchant_id])
                    ->andWhere(['status' => Merchant::STATUS_ACTIVE])
                    ->one();
                if (!$merchant_info) {
                    $error_message = "Merchant không tồn tại hoặc đang bị khoá";
                } else {
                    $account = $merchant_info->getAccount()->one();
                    if (!$account) {
                        $error_message = "Tài khoản kênh thanh toán không tồn tại";
                    } else {
                        if (ObjInput::formatCurrencyNumber($data['amount']) > $card_voucher->balance) {
                            $error_message = "Số dư thẻ không đủ";
                        } else {
                            $amount = ObjInput::formatCurrencyNumber($data['amount']);

                            $require = new CardVoucherRequirement();
                            $require->require_id = isset($data['require_id']) ? $data['require_id'] : Helpers::randomString();
                            $require->card_voucher_id = $card_voucher->id;
                            $require->type = CardVoucherRequirement::TYPE_WITH_DRAW;
                            $require->amount = $amount;
                            $require->status = CardVoucherRequirement::STATUS_NEW;
                            $require->user_created = Yii::$app->getUser()->id;
                            if ($require->validate()) {
                                if ($require->save()) {
                                    $card_voucher->balance_freezing += $amount;
                                    $card_voucher->balance -= $amount;
                                    $card_voucher->save();
                                    $error_message = '';
                                } else {
                                    $error_message = 'Có lỗi khi thêm yêu cầu';
                                }
                            } else {
                                $error_message = 'Tham số đầu vào không hợp lệ';
                            }
                        }
                    }
                }
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     * @throws Exception
     */
    public static function decreaseBalanceFreezing($params, $rollback = true): array
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = CardVoucher::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = CardVoucher::findOne(["id" => $params['card_voucher_id']]);
            if ($model) {
                if (in_array($model->status, [CardVoucher::STATUS_ACTIVE, CardVoucher::STATUS_NEW], CardVoucher::STATUS_EXPIRED)) {
                    $sql = "UPDATE " . CardVoucher::tableName() . " SET "
                        . "balance_freezing = balance_freezing - " . $params['amount'] . ", "
                        . "time_updated = " . time() . " "
                        . "WHERE id = " . $model->id . " "
                        . "AND balance_freezing >= " . $params['amount'] . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi cập nhật số dư tài khoản';
                    }
                } else {
                    $error_message = 'Thẻ đang bị khoá, hoặc hết hạn';
                }
            } else {
                $error_message = 'Tài khoản không tồn tại';
            }
        } elseif ($params['amount'] == 0) {
            $error_message = '';
            $commit = true;
        } else {
            $error_message = 'Số tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit) {
//                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);


    }

    /**
     * @throws Exception
     */
    public static function decreaseBalance($params, $rollback = true): array
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = CardVoucher::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = CardVoucher::findOne(["id" => $params['card_voucher_id']]);
            if ($model) {
                if (in_array($model->status, [CardVoucher::STATUS_ACTIVE, CardVoucher::STATUS_NEW], CardVoucher::STATUS_EXPIRED)) {
                    $sql = "UPDATE " . CardVoucher::tableName() . " SET "
                        . "balance = balance - " . $params['amount'] . ", "
                        . "balance_freezing = balance_freezing + " . $params['amount'] . ", "
                        . "time_updated = " . time() . " "
                        . "WHERE id = " . $model->id . " "
                        . "AND balance >= " . $params['amount'] . " ";

//                        . "AND status = " . Account::STATUS_ACTIVE . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi cập nhật số dư tài khoản';
                    }
                } else {
                    $error_message = 'Thẻ đang bị khoá';
                }
            } else {
                $error_message = 'Tài khoản không tồn tại';
            }
        } elseif ($params['amount'] == 0) {
            $error_message = '';
            $commit = true;
        } else {
            $error_message = 'Số tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);


    }


}