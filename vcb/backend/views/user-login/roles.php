<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Phân quyền quyền tài khoản merchant');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách quyền tài khoản merchant') ?></h1>
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
                'options' => ['enctype' => 'multipart/form-data'],
                'action' => Yii::$app->urlManager->createUrl('user-login/set-roles')
            ])
            ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th><?= Translate::get('Tài khoản') .' / Email' ?></th>
                        <td class="text-center"><?= $user_merchant['fullname'] .' / '. $user_merchant['email'] ?></td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <?= Translate::get('Danh sách quyền') ?>
                            <span id="span-select-all"><input type="checkbox" class="check-all noStyle"/> Chọn all</span>
                            <button type="submit" name="setRoll" class="btn btn-success">
                                <?= Translate::get('Phân quyền') ?>
                            </button>
                        </th>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <ul class="list-group">
                                <?php foreach ($right_merchant as $keyR => $dataR) { ?>

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
                            <a href="<?= Yii::$app->urlManager->createUrl('user-login/index') ?>"
                               class="btn btn-danger"><?= Translate::get('Quay lại') ?></a>
                        </td>
                    </tr>
                </table>
            </div>
            <input type="hidden" value="<?= $user_merchant['id'] ?>" name="user_merchant_id">
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">

    $(document).ready(function () {
        var is_check_all = true;
        $('input.check-id').each(function () {
            if($(this).prop('checked') == false) {
                is_check_all = false;
                return false;
            }
        });

        if (is_check_all) {
            $('input.check-all').prop('checked', true);
        }

        $('input.check-all').on('change', function () {
            if ($(this).prop('checked')) {
                $('input.check-id').prop('checked', true);
                $('input.check-id').addClass('check-id-checked').parent().addClass('checked');
            } else {
                $('input.check-id').prop('checked', false);
                $('input.check-id').removeClass('check-id-checked').parent().removeClass('checked');
            }
        });

    });

    function submitRoles(obj, url) {
        $('#form_list_roles').attr('action', url);
        $('#form_list_roles').submit();
    }
</script>