<?php

use common\components\utils\ObjInput;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
/** @var $model */
/** @var $checkout_order */

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
//echo strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code) . '.php';die;
?>
<?php require_once('includes/header.php') ?>
    <main>
        <div class="container">
            <div class="accordion box-collapse" id="accordionExample">
                <div class="card">
                    <div id="collapseOne" class="collapse show card-form" aria-labelledby="headingOne"
                         data-parent="#accordionExample">
                        <?php
                        echo Yii::$app->view->renderFile('@app/views/' . Yii::$app->controller->id . '/includes/verify/' . strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code) . '.php',
                            array('model' => $model, 'checkout_order' => $checkout_order)); ?>
                        <?php // ["version_1_0/index", "token_code" => $checkout_order['token_code']?>
                    </div>
                </div>
            </div>
    </main>

<?php require_once('includes/footer.php') ?>