<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\business\InstallmentConfigBusiness;
use common\models\business\MerchantBusiness;
use common\models\db\InstallmentConfig;
use common\models\input\InstallmentConfigSearch;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class InstallmentConfigController extends BackendController
{
    public function actionIndex()
    {
        $search = new InstallmentConfigSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
        ]);
    }

    public function actionGetInfoConfig() {
        $data = $_POST;
        $cycle_bank = null;
        $card_bank = null;
        $month = null;

        $installment = Tables::selectAllDataTable('installment_config', 'merchant_id=' . $data['merchant_id']);
        $cycle_accept = json_decode($installment[0]['cycle_accept'], true);
        if (in_array($data['bank_code'], array_keys($cycle_accept))) {
            $cycle_bank = $cycle_accept[$data['bank_code']];
            if (!empty($cycle_bank)) {
                foreach ($cycle_bank as $key => $val) {
                    $month[] = array_keys($val)[0];
                }
            }
        }
        $card_accept = json_decode($installment[0]['card_accept'], true);
        if (in_array($data['bank_code'], array_keys($card_accept))) {
            $card_bank = $card_accept[$data['bank_code']];
        }

        return json_encode([
            'cycle_bank' => $month,
            'card_bank' => $card_bank,
        ]);
    }

    public function actionLockInstallment(){
        $params = [
            'merchant_id' => $_GET['id'],
            'lock' => true,
        ];
        $result = InstallmentConfigBusiness::lockInstallment($params);
        if ($result['error_message'] == '') {
            $message = Translate::get('Khoá cấu hình trả góp thành công');
        } else {
            $message = Translate::get($result['error_message']);
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('installment-config', HTTP_CODE);
        Weblib::showMessage($message, $url);
    }

    public function actionActiveInstallment(){
        $params = [
            'merchant_id' => $_GET['id'],
            'lock' => false,
        ];
        $result = InstallmentConfigBusiness::lockInstallment($params);
        if ($result['error_message'] == '') {
            $message = Translate::get('Mở cấu hình trả góp thành công');
        } else {
            $message = Translate::get($result['error_message']);
        }
        $url = Yii::$app->urlManager->createAbsoluteUrl('installment-config', HTTP_CODE);
        Weblib::showMessage($message, $url);
    }
}