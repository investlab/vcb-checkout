<?php

use common\models\db\Bank;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;
use yii\helpers\Url;

/** @var Object $model */
/** @var Object $models */
/** @var Array $checkout_order */

$device = CheckMobile::isMobile();
$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
if (isset($_GET['debug']) && $_GET['debug'] == 'duclm') {
    var_dump($checkout_order["merchant_id"]);
}
$countdown_timer = (@$checkout_order['time_limit'] - @$checkout_order['time_created']) - (time() - @$checkout_order['time_created']);
//$countdown_timer = 0; //==== BAT LEN NEU CAN DEBUG
?>



<?php include(__DIR__ . '/../version_3_0/includes/header.php'); ?>
<main>
    <div class="container">
        <div class="accordion box-collapse" id="accordionPayment">
            <?php foreach ($models as $key => $model): ?>
                <?php if ($model->payment_models): ?>
                    <div class="card">
                        <div class="card-header" id="<?= 'header' . strtolower($model->info['code']) ?>">
                            <button class="btn btn-link btn-block text-left collapsed" type="button"
                                    data-toggle="collapse"
                                    data-target="#collap<?= strtolower($model->info['code']) ?>"
                                    aria-expanded="false"
                                    aria-controls="collap<?= strtolower($model->info['code']) ?>">
                                <img src="<?= 'dist/images/' . str_replace('-', '_', $key) . '.svg' ?>" alt="">
                                <p>
                                    <?= Translate::get($model->info['name']) ?>
                                </p>
                                <span><i class="las la-angle-down"></i></span>
                            </button>
                        </div>
                        <div id="collap<?= strtolower($model->info['code']) ?>" class="collapse"
                             aria-labelledby="<?= 'header' . strtolower($model->info['code']) ?>"
                             data-parent="#accordionPayment"
                             style="">
                            <div class="card-body b-t-0" id="installment-body">
                                <form action="" class="form-group">
                                    <input type="text" class="form-control search-bank"
                                           placeholder="<?= Translate::get('Tìm kiếm ngân hàng') ?>">
                                    <i class="las la-search"></i>
                                </form>
                                <ul>
                                    <?php foreach ($model->payment_models as $key => $payment_model):
                                        $bank = Bank::findOne($payment_model->info['bank_id']);
                                        $dataName = "{$bank->name} {$bank->code}";
                                        if (strpos($key, '-TRA-GOP') && !empty($list_bank_installment)) {
                                            foreach ($list_bank_installment as $item => $value) :
                                                if ($key == $item) {
                                                    ?>
                                                    <li class="bank-item" data-name="<?= $dataName ?>">
                                                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl([
                                                            Yii::$app->controller->id . '/request',
                                                            'token_code' => $checkout_order['token_code'],
                                                            'payment_method_code' => $payment_model->info['code'],
                                                        ]) ?>">
                                                            <span>
                                                                <img src="dist/images/payment_method/<?= strtolower(@$payment_model->config['class']) ?>.png"
                                                                     alt="">
                                                            </span>
                                                        </a>
                                                    </li>

                                                <?php } endforeach;
                                        } else { ?>
                                            <li class="bank-item" data-name="<?= $dataName ?>">
                                                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl([
                                                    Yii::$app->controller->id . '/request',
                                                    'token_code' => $checkout_order['token_code'],
                                                    'payment_method_code' => $payment_model->info['code'],
                                                ]) ?>">
                                                    <span>
                                                        <img src="dist/images/payment_method/<?= strtolower(@$payment_model->config['class']) ?>.png"
                                                             alt="">
                                                    </span>
                                                </a>
                                            </li>
                                        <?php } endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <!-- TIMER-->
        <div id="cancel_url" value="<?= $checkout_order['cancel_url'] ?>"></div>
        <div class="text-center" style="margin-top: 1rem;">
            <p id="label_timer" style="font-size: 15px !important;" class="text-primary"></p>
            <div class="count text-center">
                <strong> <span id="timer" style="color: red; font-size: 1rem"> </span></strong>
            </div>
            <div hidden id="text_count_down"
                 value="<?= Translate::get('Thời hạn thanh toán còn lại') ?>"
                 data-token="<?= $checkout_order['token_code'] ?>"
                 data-url-detroy="<?= Url::to(['version_3_0/transaction-destroy-v2']) ?>"
            ></div>
            <div hidden id="hidden-time-created"
                 value="<?= $countdown_timer ?>"><?= $countdown_timer ?></div>
        </div>

        <!--  END TIMER-->
    </div>
</main>
<?php include(__DIR__ . '/../version_3_0/includes/footer.php'); ?>
<script>
    $(document).ready(function () {
        let key = 'is_show_btb_back_' + '<?=$checkout_order['token_code']?>';
        setItemWithExpiry(key, true, 86400000);
    })
</script>