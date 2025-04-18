<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 14:02
 */

namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ViewLogController extends BackendController
{

    public function actionIndex()
    {
        $type = ObjInput::get('type', 'str', 'checkout_version_1_0');
        $date = ObjInput::get('date', 'str', '');
        $content = '';
        $types = $this->_getTypes();
        $file_path = $this->_getFilePath($types, $type, $date);
        if ($file_path != false) {
            $content = $this->_getContent($file_path);
        }
        return $this->render('index', [
            'file_path' => $file_path,
            'content' => $content,
            'date' => $date,
            'types' => $types,
            'type' => $type,
        ]);
    }

    private function _getContent($file_path)
    {
        //return file_get_contents($file_path);
        $content = '';
        $file = fopen($file_path, "r");
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $line = htmlentities($line);
                $line = preg_replace('/^(\[[0-9\s,:\/]+\])/', '<strong class="text-primary">$1</strong>', $line);
                $content .= $line . '<br>';
            }
            fclose($file);
        }
        return $content;
    }

    private function _getTypes()
    {
        return array(
            'checkout_version_1_0' => array(
                'name' => 'checkout v1.0',
                'path' => LOG_PATH . 'api' . DS . 'checkout' . DS . 'version1.0' . DS,
            ),
            'checkout_order_callback' => array(
                'name' => 'checkout callback',
                'path' => LOG_PATH . 'checkout_order_callback' . DS,
            ),

            'nganluong_seamless' => array(
                'name' => 'nganluong seamless',
                'path' => LOG_PATH . 'nganluong_seamless' . DS,
            ),
            'nganluong' => array(
                'name' => 'nganluong',
                'path' => LOG_PATH . 'nganluong' . DS,
            ),
            'nganluong_transfer' => array(
                'name' => 'nganluong_transfer',
                'path' => LOG_PATH . 'nganluong'.DS.'transfer' . DS,
            ),
            'nganluong_withdraw' => array(
                'name' => 'nganluong_withdraw',
                'path' => LOG_PATH . 'nganluong'.DS.'withdraw' . DS,
            ),
            'alepay' => array(
                'name' => 'alepay',
                'path' => LOG_PATH . 'alepay'. DS,
            ),
            'alepay_notify' => array(
                'name' => 'alepay_notify',
                'path' => LOG_PATH . 'alepay'. DS. 'notify' . DS,
            ),
            'vcb_ecom' => array(
                'name' => 'vcb_ecom',
                'path' => LOG_PATH . 'vcb'. DS,
            ),
            'cybersouce' => array(
                'name' => 'cybersouce',
                'path' => LOG_PATH . 'cbs_stb'. DS . 'output' . DS,
            ),
            'cybersouce_vcb' => array(
                'name' => 'cybersouce_vcb',
                'path' => LOG_PATH . 'cbs_vcb'. DS . 'output' . DS,
            ),
            'console' => array(
                'name' => 'console',
                'path' => LOG_PATH . 'console' . DS,
            ),
            'refund_NL_call_back_now' => array(
                'name' => 'refund_NL_call_back_now',
                'path' => LOG_PATH . 'nganluong_call_back_refund' . DS,
            ),

        );
    }

    private function _getFilePath($types, $type, $date)
    {
        if (isset($types[$type]) && preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $part)) {
            $file_path = $types[$type]['path'] . $part[3] . $part[2] . $part[1] . '.txt';
            if (file_exists($file_path)) {
                return $file_path;
            }
        }
        return false;
    }

}
