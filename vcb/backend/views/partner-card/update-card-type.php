<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\PartnerCard;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật loại thẻ hỗ trợ');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>&nbsp;</h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('partner-card/index') ?>"><i
                                class="en-back"></i><?= Translate::get('Quay lại') ?>
                        </a>
                    </div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class=row>
            <div class=col-lg-12>
                <div class="panel panel-primary"
                ">
                <div class="panel-heading"><h4><?= Translate::get('Cập nhật loại thẻ hỗ trợ') ?></h4></div>
                <div class="panel-body">
                    <form id="update-card-type-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('partner-card/update-card-type') ?>">
                        <span><strong>Check All</strong> <input type="checkbox" class="noStyle check-all" /></span>
                        <button class="btn btn-warning" type="submit">
                            Cập nhật
                        </button>
                        <hr>
                        <?php if (isset($card_type) && $card_type != null) {
                            foreach ($card_type as $key => $data) {
                                ?>
                                <strong>
                                    <?= $data['name'] ?>
                                    <input type="hidden" value="<?= $data['id'] ?>" name="card_type_id">
                                </strong>
                                <ul class="list-right">
                                    <?php foreach ($data['cycle_days'] as $keyC => $dataC) {

                                        if (isset($data['cycle_day_in_pct']) && in_array($keyC, $data['cycle_day_in_pct'])) {
                                            ?>
                                            <li>
                                                <input type="checkbox" class="noStyle check-id" checked
                                                       name="cycle_days[<?= $data['id'] ?>][]"
                                                       value="<?= $keyC ?>"/> <?= $dataC ?>
                                            </li>
                                        <?php } else { ?>
                                            <li>
                                                <input type="checkbox" class="noStyle check-id"
                                                       name="cycle_days[<?= $data['id'] ?>][]"
                                                       value="<?= $keyC ?>"/> <?= $dataC ?>
                                            </li>
                                        <?php
                                        }
                                    } ?>
                                </ul>
                            <?php
                            }
                        } ?>
                        <input type="hidden" value="<?= $partner_card_id ?>" name="partner_card_id">

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('input.check-all').on('change', function () {
        if (this.checked) {
            $(this).attr('checked', true);
            $('input.check-id').prop("checked", true);
        } else {
            $(this).attr('checked', false);
            $('input.check-id').prop('checked', false);
        }
    });
</script>