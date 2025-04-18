<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PartnerCardType;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Điều kênh thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Điều kênh thẻ cào') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew"></div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>

        <div class=row>

            <div class=tabs>
                <ul class="nav nav-tabs tab-option" role="tablist">
                    <?php foreach ($bill_type_arr as $k => $d) { ?>
                        <li class="link_tab_card <?= $k == $_GET['bill_type'] ? 'active' : '' ?>">
                            <a href="<?= Yii::$app->urlManager->createUrl(['partner-card-type/index', "bill_type" => $k]) ?>"

                                ><?= $d ?></a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="tab-content">

                    <div class="tab-pane div_tab_card <?= $_GET['bill_type'] != null ? 'active' : '' ?>"
                         id="link_<?= $_GET['bill_type'] ?>">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Kỳ thanh toán</th>
                                        <?php
                                        if (isset($card_types) && $card_types != null) {
                                            foreach ($card_types as $card_type) {
                                                ?>
                                                <th>
                                                    <?= $card_type['name'] ?>
                                                </th>
                                            <?php
                                            }
                                        } ?>
                                    </tr>
                                    </thead>
                                    <?php
                                    if (isset($cycle_days) && $cycle_days != null) {
                                        foreach ($cycle_days as $kCD => $cycle_day) {
                                            ?>
                                            <tr>
                                                <th>
                                                    <?= $cycle_day ?>
                                                </th>
                                                <?php
                                                if (isset($card_types) && $card_types != null) {
                                                    foreach ($card_types as $kCT => $card_type) {
                                                        ?>
                                                        <td>
                                                            <?php foreach ($result as $key => $data) {
                                                                if ($key == $kCD) {
                                                                    foreach ($data as $k1 => $d1) {
                                                                        if ($k1 == $card_type['id']) {
                                                                            foreach ($d1 as $k2 => $d2) {
                                                                                ?>
                                                                                <?php if ($d2['status'] == PartnerCardType::STATUS_ACTIVE) { ?>
                                                                                    <a onclick="confirm('<?= Translate::get('Khóa kênh') ?>', '<?= Yii::$app->urlManager->createUrl(['partner-card-type/lock', 'id' => $d2['id']]) ?>');
                                                                                        return false;"
                                                                                       href="<?= Yii::$app->urlManager->createUrl([Yii::$app->urlManager->createUrl('partner-card-type/lock'), 'id' => $d2['id']]) ?>"
                                                                                        class="btn-xs btn-success"> <?= @$d2['partner_card_info']['name'] ?></a>
                                                                                <?php }else{ ?>
                                                                                    <a onclick="confirm('<?= Translate::get('Mở khóa kênh') ?>', '<?= Yii::$app->urlManager->createUrl(['partner-card-type/active', 'id' => $d2['id']]) ?>');
                                                                                        return false;"
                                                                                       href="<?= Yii::$app->urlManager->createUrl([Yii::$app->urlManager->createUrl('partner-card-type/active'), 'id' => $d2['id']]) ?>"
                                                                                       class="btn-xs btn-danger"> <?= @$d2['partner_card_info']['name'] ?></a>
                                                                               <?php }?>
                                                                               <br><br>
                                                                            <?php
                                                                            }
                                                                        }
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

                                </table>
                            </div>
                        </div>
                    </div>


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