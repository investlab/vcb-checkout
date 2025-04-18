<?php

namespace common\models\business;

use Yii;
use common\models\db\Account;
use common\models\db\Merchant;
use common\components\libs\Tables;

class AccountBusiness {

    /**
     * 
     * @param params : merchant_id, balance, currency, status, user_id
     * @param rollback
     */
    static function add($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", "id = " . $params['merchant_id'] . " AND status = " . Merchant::STATUS_ACTIVE);
        if ($merchant_info != false) {
            $model = new Account();
            $model->merchant_id = $params['merchant_id'];
            $model->balance = $params['balance'];
            $model->balance_freezing = 0;
            $model->balance_pending = 0;
            $model->currency = $params['currency'];
            $model->status = $params['status'];
            $model->time_created = time();
            $model->time_updated = time();
            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
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
     * @param params : account_id, user_id
     * @param rollback
     */
    static function active($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        $model = Account::findOne(["id" => $params['account_id'], "status" => Account::STATUS_LOCK]);
        if ($model) {
            $model->status = Account::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->time_active = time();
            $model->user_active = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi kích hoạt tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
            }
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
     * @param params : account_id, user_id
     * @param rollback
     */
    static function lock($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        $model = Account::findOne(["id" => $params['account_id'], "status" => Account::STATUS_ACTIVE]);
        if ($model) {
            $model->status = Account::STATUS_LOCK;
            $model->time_updated = time();
            $model->time_lock = time();
            $model->user_lock = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi khóa tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
            }
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
     * @param params : account_id, currency, amount, user_id
     * @param rollback
     */
    static function decreaseBalance($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance = balance - " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
                            . "AND balance >= " . $params['amount'] . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Số dư không đủ để thực hiện giao dịch';
                    }
                } else {
                    $error_message = 'Tài khoản đang bị khóa';
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
     * @param type $params : account_id, currency, amount, user_id
     * @param type $rollback
     * @return type
     */
    static function decreaseBalanceFreezing($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance_freezing = balance_freezing - " . $params['amount'] . ", "
                            . "balance = balance + " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
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
                    $error_message = 'Tài khoản đang bị khóa';
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
     * @param type $params : account_id, currency, amount, user_id
     * @param type $rollback
     * @return type
     */
    static function decreaseBalancePending($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance_pending = balance_pending - " . $params['amount'] . ", "
                            . "balance = balance + " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
                            . "AND balance_pending >= " . $params['amount'] . " ";
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
                    $error_message = 'Tài khoản đang bị khóa';
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
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }


    static function decreaseBalancePendingV2($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance_pending = balance_pending - " . $params['amount'] . ", "
                            . "balance = balance + " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . 0 . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
                            . "AND balance_pending >= " . $params['amount'] . " ";
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
                    $error_message = 'Tài khoản đang bị khóa';
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
     * @param type $params : merchant_id, currency, amount, user_id
     * @param type $rollback
     * @return type
     */
    static function decreaseBalancePendingByMerchantId($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        $account_id = Account::getAccountIdByMerchantId($params['merchant_id'], $params['currency']);
        if ($account_id != false) {
            $inputs = array(
                'account_id' => $account_id, 
                'currency' => $params['currency'], 
                'amount' => $params['amount'], 
                'user_id' => $params['user_id'],
            );
            $result = self::decreaseBalancePending($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
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
        return array('error_message' => $error_message);
    }

    /**
     * 
     * @param params : account_id, currency, amount, user_id
     * @param rollback
     */
    static function increaseBalance($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance = balance + " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " ";
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
                    $error_message = 'Tài khoản đang bị khóa';
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
     * @param type $params : account_id, currency, amount, user_id
     * @param type $rollback
     * @return type
     */
    static function increaseBalanceFreezing($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance_freezing = balance_freezing + " . $params['amount'] . ", "
                            . "balance = balance - " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
                            . "AND balance >= " . $params['amount'] . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Số dư không đủ để thực hiện giao dịch';
                    }
                } else {
                    $error_message = 'Tài khoản đang bị khóa';
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
     * @param type $params : account_id, currency, amount, user_id
     * @param type $rollback
     * @return type
     */
    static function increaseBalancePending($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                            . "balance_pending = balance_pending + " . $params['amount'] . ", "
                            . "balance = balance - " . $params['amount'] . ", "
                            . "time_updated = " . time() . ", "
                            . "user_updated = " . $params['user_id'] . " "
                            . "WHERE id = " . $model->id . " "
                            . "AND status = " . Account::STATUS_ACTIVE . " "
                            . "AND balance >= " . $params['amount'] . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Số dư không đủ để thực hiện giao dịch';
                    }
                } else {
                    $error_message = 'Tài khoản đang bị khóa';
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
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    public static function increaseBalanceCardVoucher($params, $rollback = true): array
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Account::getDb()->beginTransaction();
        }
        if ($params['amount'] > 0) {
            $model = Account::findOne(["id" => $params['account_id'], "currency" => $params['currency']]);
            if ($model) {
                if ($model->status == Account::STATUS_ACTIVE) {
                    $sql = "UPDATE " . Account::tableName() . " SET "
                        . "balance_card_voucher = balance_card_voucher + " . $params['amount'] . ", "
                        . "time_updated = " . time() . ", "
                        . "user_updated = " . $params['user_id'] . " "
                        . "WHERE id = " . $model->id . " "
                        . "AND status = " . Account::STATUS_ACTIVE . " ";
                    $connection = $model->getDb();
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
//                    $model->balance_card_voucher += $params['amount'];
//                    $model->time_updated = time();
//                    $model->user_updated = $params['user_id'];
                    if ($result) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi cập nhật số dư tài khoản';
                    }
                } else {
                    $error_message = 'Tài khoản đang bị khóa';
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

    public static function getBalanceCardVoucher($account_id)
    {
        $account = Account::findOne($account_id);
        return $account->balance_card_voucher;
    }

}
