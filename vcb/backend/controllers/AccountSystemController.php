<?php

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Tables;
use common\models\db\Account;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use DateTime;

class AccountSystemController extends BackendController
{

    public function actionIndex()
    {
        return $this->render('index', [
            'accounts' => $this->_getAccounts(),
        ]);
    }
    
    private function _getAccounts() {
        $result = array();
        $data = Tables::selectAllDataTable("account", ["merchant_id = 0"], "id ASC");
        $status = Account::getStatus();
        foreach ($data as $row) {
            $row['name'] = '';
            if ($row['id'] == Account::getMasterAccountId('VND')) {
                $row['name'] = 'Tài khoản MASTER';
            }
            if ($row['id'] == Account::getFeeAccountId('VND')) {
                $row['name'] = 'Tài khoản FEE';
            }
            $row['status_name'] = $status[$row['status']];
            $result[] = $row;
        }
        return $result;
    }
} 