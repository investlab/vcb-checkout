<?php

namespace common\models\business;

use common\components\libs\Tables;
use common\models\db\CardDecline;
use common\payments\CyberSourceVcb3ds2;
use common\util\Helpers;
use yii\db\Exception;

class CardDeclineBusiness
{
    public static function checkCard($card_number)
    {
        if (Helpers::isCreditCard($card_number)) {
            $type_card = CyberSourceVcb3ds2::getTypeCardByFirstBINNumber($card_number, false);
            $first_six_digits = substr($card_number, 0, 6);
            $last_four_digits = substr($card_number, -4);
            if ($type_card == 'visa') {
                $previous_15_date = time() - 86400 * 15;
                $previous_30_date = time() - 86400 * 30;
                $check_15_date = CardDecline::find()
                    ->where(['first_six_digits' => $first_six_digits])
                    ->andWhere(['last_four_digits' => $last_four_digits])
                    ->andWhere(['IN', 'response_insights_category_code', CardDecline::LIST_CODE_BLOCK_FOREVER])
//                    ->andWhere(['>=', 'created_at', $previous_15_date])
                    ->exists();
                if (!$check_15_date) {
                    $check_30_date = CardDecline::find()
                        ->where(['first_six_digits' => $first_six_digits])
                        ->andWhere(['last_four_digits' => $last_four_digits])->andWhere(['>=', 'created_at', $previous_30_date])
                        ->andWhere(['IN', 'response_insights_category_code', CardDecline::LIST_CODE_BLOCK_30_DAY])
                        ->count();
                    if ($check_30_date < 15) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } elseif ($type_card == 'mastercard') {
                $previous_1_date = time() - 86400;
                $previous_30_date = time() - 86400 * 30;
                $check_30_date_master = CardDecline::find()
                    ->where(['first_six_digits' => $first_six_digits])
                    ->andWhere(['last_four_digits' => $last_four_digits])
                    ->andWhere(['IN', 'response_insights_category_code', CardDecline::LIST_CODE_BLOCK_30_DAY_MASTER])
                    ->andWhere(['>=', 'created_at', $previous_30_date])
                    ->exists();
                if (!$check_30_date_master) {
                    try {
                        $sql_check = "SELECT
                        COUNT(CASE WHEN created_at >= $previous_1_date THEN 1 END) AS declined_count_24h,
                        COUNT(CASE WHEN created_at >= $previous_30_date THEN 1 END) AS declined_count_30d
                    FROM
                        card_decline
                    WHERE
                        first_six_digits = '$first_six_digits'
                        AND last_four_digits = '$last_four_digits'";

                        $run_sql = Tables::selectOneBySql($sql_check);
                        if ($run_sql) {
                            if ($run_sql['declined_count_24h'] < 9 && $run_sql['declined_count_30d'] < 33) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } catch (Exception $exception) {
                        if (YII_DEBUG) {
                            echo $exception->__toString();
                        }
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }


    public static function addDeclineResponse($authorize_response, $params, $transaction_id): array
    {
        if ($params['card_type'] == 'visa') {
            $params = [
                'reason_code' => $authorize_response->reasonCode,
                'transaction_id' => $transaction_id,
                'first_six_digits' => substr($params['account_number'], 0, 6),
                'last_four_digits' => substr($params['account_number'], -4),
                'hash' => Helpers::hashCardNumber($params['account_number']),
            ];
            if (isset($authorize_response->ccAuthReply->processorResponse)) {
                $params['response_insights_category_code'] = $authorize_response->ccAuthReply->processorResponse;
            }
            return self::add($params);
        } elseif ($params['card_type'] == 'mastercard') {
            $params = [
                'reason_code' => $authorize_response->reasonCode,
                'transaction_id' => $transaction_id,
                'first_six_digits' => substr($params['account_number'], 0, 6),
                'last_four_digits' => substr($params['account_number'], -4),
                'hash' => Helpers::hashCardNumber($params['account_number']),
            ];
            if (isset($authorize_response->ccAuthReply->merchantAdviceCode)) {
                $params['response_insights_category_code'] = $authorize_response->ccAuthReply->merchantAdviceCode;
            }
            return self::add($params);
        }
        return [];
    }


    public static function add($params): array
    {
        $model = new CardDecline();

        $model->transaction_id = $params['transaction_id'];
        $model->first_six_digits = $params['first_six_digits'];
        $model->last_four_digits = $params['last_four_digits'];
        $model->response_code = $params['reason_code'];
        $model->hash = $params['hash'];
        $model->status = $params['status'] ?? 1;
        $model->response_insights_category_code = $params['response_insights_category_code'] ?? '';

        if ($model->validate()) {
            if ($model->save()) {
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm yêu bản ghi';
            }
        } else {
            $error_message = 'Có lỗi dữ liệu khi thêm yêu bản ghi';
        }
        return array('error_message' => $error_message);
    }
}