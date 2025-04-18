<?php

namespace console\controllers;

use common\models\db\CardVoucher;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

class CardVoucherController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    /**
     */
    public function actionSetExpired()
    {
        $update = Yii::$app->db->createCommand()
            ->update(CardVoucher::tableName(), ['status' => CardVoucher::STATUS_EXPIRED, 'time_updated' => time()], 'time_expired <= ' . time());
        try {
            $update->execute();
        } catch (Exception $exception) {
            echo "<pre>";
            var_dump($exception);
            die();
        }
    }
}