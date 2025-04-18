<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PartnerPaymentMethod;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Điều kênh thanh toán');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    PartnerPaymentMethod::STATUS_ACTIVE => 'bg bg-default',
    PartnerPaymentMethod::STATUS_LOCK => 'bg bg-danger',
);
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Điều kênh thanh toán') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew"></div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
            
            <div class="well well-sm fillter">
                <form class="form-horizontal" role=form>
                    <div class="row group-input-search">
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                            <input type="text" class="form-control" placeholder="<?= Translate::get('Mã phương thức') ?>"
                                   name="code"
                                   value="<?= (isset($code) && $code != null) ? Html::encode($code) : '' ?>">
                        </div>

                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                            <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                            <a href="<?= Yii::$app->urlManager->createUrl('partner-payment-method/index') ?>"
                               class="btn btn-default">
                                <?= Translate::get('Bỏ lọc') ?>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <div class="col-sm-12">
            <div class="row">
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th><?= Translate::get('Hình thức thanh toán') ?></th>
                            <?php
                            if (isset($enviroment_arr) && $enviroment_arr != null) {
                                foreach ($enviroment_arr as $enviroment) {
                                    ?>
                                    <th>
                                        <?= $enviroment ?>
                                    </th>
                                    <?php
                                }
                            } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (isset($payment_method_arr) && $payment_method_arr != null) {
                            foreach ($payment_method_arr as $keyPM => $payment_method) {
                                ?>
                                <tr class="<?=@$array_color[$data['status']]?>">
                                    <th>
                                        <?= Translate::get($payment_method['name']) ?> - <?= $payment_method['code'] ?>
                                    </th>
                                    <?php
                                    if (isset($enviroment_arr) && $enviroment_arr != null) {
                                        foreach ($enviroment_arr as $dataE) {
                                            ?>
                                            <td>
                                                <?php foreach ($partner_payment_method_arr as $keyPPM => $dataPPM) {
                                                    if ($dataPPM['enviroment'] == $dataE) {
                                                        if ($dataPPM['payment_method_id'] == $payment_method['id']) {
                                                            ?>
                                                            <?php if ($dataPPM['status'] == PartnerPaymentMethod::STATUS_ACTIVE) { ?>
                                                                <a onclick="confirm('<?= Translate::get('Khóa kênh') ?>', '<?= Yii::$app->urlManager->createUrl(['partner-payment-method/lock', 'id' => $dataPPM['id']]) ?>');
                                                                        return false;"
                                                                   href="<?= Yii::$app->urlManager->createUrl([Yii::$app->urlManager->createUrl('partner-payment-method/lock'), 'id' => $dataPPM['id']]) ?>"
                                                                   class="btn-xs btn-success"><?= Translate::get(@$dataPPM['partner_payment_name']) ?></a>
                                                            <?php } else { ?>
                                                                <a onclick="confirm('<?= Translate::get('Mở khóa kênh') ?>', '<?= Yii::$app->urlManager->createUrl(['partner-payment-method/active', 'id' => $dataPPM['id']]) ?>');
                                                                        return false;"
                                                                   href="<?= Yii::$app->urlManager->createUrl([Yii::$app->urlManager->createUrl('partner-payment-method/active'), 'id' => $dataPPM['id']]) ?>"
                                                                   class="btn-xs btn-danger"><?= Translate::get(@$dataPPM['partner_payment_name']) ?></a>
                                                            <?php } ?>
                                                            <br><br>
                                                            <?php
                                                        }
                                                    }
                                                }?>

                                            </td>
                                            <?php
                                        }
                                    } ?>
                                </tr>
                                <?php
                            }
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirm-dialog" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title title"></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <div class="alert alert-warning fade in" align="center">
                        <?= Translate::get('Bạn có chắc chắn muốn') ?> <strong class="title"> </strong> <?= Translate::get('này không') ?>?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#"><?= Translate::get('Xác nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">
    <?php echo Yii::$app->view->renderFile('@app/web/js/ajax.js', array()); ?>
    function confirm(title, url) {
        $('#confirm-dialog .title').html(title);
        $('#confirm-dialog').modal('show');
        $('#confirm-dialog .btn-accept').click(function () {
            document.location.href = url;
        });
    }

</script>