<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Phân quyền quản trị';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$form = ActiveForm::begin(['id' => 'form_list_roles',
    'options' => ['enctype' => 'multipart/form-data']])
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>Danh sách quyền quản trị</h1>
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

            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>
                            Tài khoản: &nbsp; &nbsp;<?= $user['username'] ?>
                        </th>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <th>
                            Danh sách quyền &nbsp;
                            <input type="checkbox" class="check-all"/>
                            <button type="submit" name="setRoll" class="btn btn-success"
                                    onclick="submitRoles(this,
                                        '<?= Yii::$app->urlManager->createUrl('user/set-roles') ?>');">
                                Phân quyền
                            </button>
                        </th>
                        <td></td>

                    </tr>
                    <tr>
                        <td colspan="2">
                            <ul class="list-group">
                                <?php foreach ($user_group_right as $keyR => $dataR) { ?>

                                    <li class="list-group-item">

                                        <?php if (in_array($dataR['right_id'], $right_ids_user)) { ?>
                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', @$dataR['level']) . ' ' . '
                                    <input type="checkbox" checked  class="check-id" name="ids[]" value="' . $dataR['right_id'] . '" />
                                    '; ?>
                                        <?php } else { ?>
                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', @$dataR['level']) . ' ' . '
                                    <input type="checkbox" class="check-id" name="ids[]" value="' . $dataR['right_id'] . '" />
                                    '; ?>
                                        <?php } ?>

                                        <label><?= $dataR['right_name'] ?></label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
            <input type="hidden" value="<?= $user['id'] ?>" name="user_id">

        </div>
        <button type="submit" name="setRoll" class="btn btn-success"
                onclick="submitRoles(this,
                    '<?= Yii::$app->urlManager->createUrl('user/set-roles') ?>');">
            Phân quyền
        </button>
    </div>

</div>

<?php ActiveForm::end(); ?>

<script language="javascript" type="text/javascript">

    function submitRoles(obj, url, groupid) {
        if ($('div.checked input.check-id').length) {
            $('#form_list_roles').attr('action', url);
            $('#form_list_roles').submit();
        } else {
            alert('Bạn chưa chọn dòng muốn xử lý');
        }
    }

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