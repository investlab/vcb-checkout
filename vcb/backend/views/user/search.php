<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\utils\ObjInput;
use common\models\db\Product;

/* @var $this yii\web\View */
/* @var $model common\models\db\Product */
/* @var $form ActiveForm */
$this->title = 'Tìm nhân viên sales';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper index">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header>Tìm nhân viên sales</h1>
            </div>
        </div>
    </div>
    <div class="outlet ajax-content">
        <div class="well well-sm fillter">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Yii::$app->urlManager->createUrl(['user/search']), 'options' => ['class' => 'form-horizontal ajax-form', 'role' => 'form']]); ?>
            <div class="row">
                <div class="col-md-4">
                    <?php echo $form->field($model, 'user_group')->dropDownList($user_group, ['id' => 'user_group', 'class' => 'form-control'])->label(false); ?>
                </div>
                <div class="col-md-4">
                    <?php echo $form->field($model, 'username')->label(false)->textInput(array('class' => 'form-control', 'placeholder' => 'Họ tên-Tên đăng nhập-SĐT sales')) ?>
                </div>
                <div class="col-md-2"><?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-danger']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            <?php $form = ActiveForm::begin(['options' => ['id' => 'form_list']]); ?>
            <div class="clearfix" style="border-bottom:1px solid #dcdcdc; margin-bottom:15px; padding-bottom:10px">

            </div>
            <?php
            if (is_array($data) && count($data) == 0) {
                ?>

                <div class="alert alert-danger fade in">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>Thông báo</strong> Không tìm thấy kết quả nào phù hợp.
                </div>
            <?php } ?>
            <table class="table table-responsive table-striped" id="datatable">
                <thead>
                <tr>
                    <th width="35"></th>
                    <th align="center">Tên đăng nhập</th>
                    <th align="center">Tên nhân viên</th>
                    <th>Số điện thoại</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $row) {
                    ?>
                    <tr>
                        <th width="35"><input type="radio" class="select-id" value="<?= $row['id'] ?>"
                                              title="<?= $row['username'] ?>" onclick="selectId(this);"/></th>
                        <td>
                            <?= $row['username'] ?>
                        </td>
                        <td>
                            <?= $row['fullname'] ?>
                        </td>
                        <td>
                            <?= $row['mobile'] ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <script type="text/javascript">
                function selectId(obj, org) {
                    $('#sales_id').val($(obj).val());
                    $('#sales_name').val($(obj).attr('title'));
                    $('#ajax-dialog').modal('hide');
                }
            </script>
        </div>
    </div>
</div>