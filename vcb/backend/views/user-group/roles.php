<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Phân quyền quyền quản trị');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách quyền quản trị') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew"></div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <div class=outlet>
        <div class=row>
            <?php
            $form = ActiveForm::begin(['id' => 'form_list_roles',
                'options' => ['enctype' => 'multipart/form-data']])
            ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th><?= Translate::get('Nhóm quản trị') ?></th>
                        <td><?= $user_group['name'] ?></td>
                    </tr>
                    <tr>
                        <th>
                            <?= Translate::get('Danh sách quyền') ?> &nbsp;
                            <input type="checkbox" class="check-all noStyle"/>
                            <button type="submit" name="setRoll" class="btn btn-success"
                                    onclick="submitRoles(this,
                                        '<?= Yii::$app->urlManager->createUrl('user-group/set-roles') ?>');">
                                <?= Translate::get('Phân quyền') ?>
                            </button>
                        </th>
                        <td></td>

                    </tr>
                    <tr>
                        <td colspan="2">
                            <ul class="list-group">
                                <?php foreach ($right as $keyR => $dataR) { ?>

                                    <li class="list-group-item">

                                        <?php if (in_array($dataR['id'], $right_ids)) { ?>
                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $dataR['level'] - 1) . ' ' . '
                                    <input type="checkbox" checked  class="check-id noStyle" name="ids[]" value="' . $dataR['id'] . '" />
                                    '; ?>
                                        <?php } else { ?>
                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $dataR['level'] - 1) . ' ' . '
                                    <input type="checkbox" class="check-id noStyle" name="ids[]" value="' . $dataR['id'] . '" />
                                    '; ?>
                                        <?php } ?>

                                        <label><?= Translate::get($dataR['name']) ?></label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="submit" name="setRoll" class="btn btn-success"><?= Translate::get('Phân quyền') ?></button>
                        </td>
                    </tr>
                </table>
            </div>
            <input type="hidden" value="<?= $user_group['id'] ?>" name="group_id">
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">

    $(document).ready(function () {
        $('input.check-all').on('ifChanged', function () {
            if (this.checked) {
                $(this).attr('checked', true);
                $('input.check-id').attr('checked', true);
                $('input.check-id').addClass('check-id-checked').parent().addClass('checked');
            } else {
                $(this).attr('checked', false);
                $('input.check-id').attr('checked', false);
                $('input.check-id').removeClass('check-id-checked').parent().removeClass('checked');
            }
        });
    });
</script>