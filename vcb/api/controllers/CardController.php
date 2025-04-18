<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 8/12/2016
 * Time: 9:52 AM
 */

namespace api\controllers;

use Yii;
use api\components\ApiController;
use common\api\CardVersion1_0Api;
use common\components\utils\ObjInput;

class CardController extends ApiController
{

    public function actionVersion_1_0()
    {
        $obj = new CardVersion1_0Api();
        $obj->writeLog(json_encode(@$_REQUEST));
        //------------
        $version = ObjInput::get('version', 'str', '');
        if ($version == '1.0') {
            $func = ObjInput::get('func', 'str', '');
            $merchant_id = ObjInput::get('merchant_id', 'int', 0);
            $ref_code = ObjInput::get('ref_code', 'str', '');
            $checksum = ObjInput::get('checksum', 'str', '');
            if ($func == 'CardCharge') {
                $params = array(
                    'version' => $version,
                    'merchant_id' => $merchant_id,
                    'pin_card' => ObjInput::get('pin_card', 'str', ''),
                    'card_serial' => ObjInput::get('card_serial', 'str', ''),
                    'type_card' => ObjInput::get('type_card', 'str', ''),
                    'ref_code' => $ref_code,
                    'client_fullname' => ObjInput::get('client_fullname', 'str', ''),
                    'client_email' => ObjInput::get('client_email', 'str', ''),
                    'client_mobile' => ObjInput::get('client_mobile', 'str', ''),
                    'checksum' => $checksum,
                );
                $result = $obj->cardCharge($params);
                if ($result['error_code'] == '00') {
                    $response = array('error_code' => '00');
                    $response = array_merge($response, $result['response_data']);
                } else {
                    $response = array(
                        'error_code' => $result['error_code'],
                        'merchant_id' => $merchant_id,
                        'pin_card' => $params['pin_card'],
                        'card_serial' => $params['card_serial'],
                        'type_card' => $params['type_card'],
                        'ref_code' => $params['ref_code'],
                        'client_fullname' => $params['client_fullname'],
                        'client_email' => $params['client_email'],
                        'client_mobile' => $params['client_mobile'],
                        'card_amount' => 0,
                        'transaction_amount' => 0,
                        'transaction_id' => '',
                    );
                }
            } elseif ($func == 'GetTransactionDetail') {
                $params = array(
                    'version' => $version,
                    'merchant_id' => $merchant_id,
                    'ref_code' => $ref_code,
                    'checksum' => $checksum,
                );
                $result = $obj->getTransactionDetail($params);
                $response = array('error_code' => $result['error_code']);
                $response = array_merge($response, $result['response_data']);
            } else {
                $response = array('error_code' => '02');
            }
        } else {
            $response = array('error_code' => '02');
        }
        $this->_setHeader(200);
        echo json_encode($response);
        exit();
    }
}
