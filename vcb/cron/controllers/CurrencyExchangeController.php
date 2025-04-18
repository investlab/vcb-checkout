<?php

namespace cron\controllers;

use common\components\libs\Tables;
use common\models\business\CurrencyExchangeBusiness;
use common\models\db\CurrencyExchange;
use cron\components\CronBasicController;

class CurrencyExchangeController extends CronBasicController
{
    public function actionIndex()
    {
        $context = stream_context_create(array('ssl' => array(
            'verify_peer' => false,
        )));
        libxml_set_streams_context($context);

        $xml = simplexml_load_file('https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx?b=1');
        $count = CurrencyExchange::find()->count();
        $error_message = '';
        if ((int)$count != count($xml->Exrate)) {
            \Yii::$app->db->createCommand("DELETE FROM currency_exchange")->execute();
            foreach ($xml->Exrate as $key => $value) {
                $data['CurrencyCode'] = $value['CurrencyCode']->__toString();
                $data['CurrencyName'] = $value['CurrencyName']->__toString();
                $data['Buy'] = $value['Buy']->__toString();
                $data['Transfer'] = $value['Transfer']->__toString();
                $data['Sell'] = $value['Sell']->__toString();
                $error_message = CurrencyExchangeBusiness::add($data);
            }
        } else {
            foreach ($xml->Exrate as $key => $value) {
                $data['CurrencyCode'] = $value['CurrencyCode']->__toString();
                $data['CurrencyName'] = $value['CurrencyName']->__toString();
                $data['Buy'] = $value['Buy']->__toString();
                $data['Transfer'] = $value['Transfer']->__toString();
                $data['Sell'] = $value['Sell']->__toString();
                $error_message = CurrencyExchangeBusiness::update($data);
            }
        }
        self::_writeLog(($error_message['error_message'] != '') ? json_decode($error_message['error_message']) : 'Update currency exchange: ' . 'Update currency exchange: ' . date('d/m/Y, H:i:s', time()));
        echo 'Update currency exchange: ' . 'Update currency exchange: ' . date('d/m/Y, H:i:s', time());
    }

    private static function _writeLog($data)
    {
        $file = fopen(ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'cron' . DS . 'currency_exchange' . DS . date('Ymd') . '.txt', 'a');
        if ($file) {
            fwrite($file, '[' . date('d/m/Y, H:i:s') . ']' . $data . "\n");
            fclose($file);
        }
    }
}