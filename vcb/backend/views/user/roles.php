<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Phân quyền quản trị');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách tài khoản người dùng') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a href="<?= Yii::$app->urlManager->createUrl('user/index') ?>"
                           class="btn btn-default btn-sm"><?= Translate::get('Bỏ qua') ?></a>
                    </div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <div class=outlet>
        <div class=row>
            <div class="col-sm-7">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Tên người dùng') ?>:</label>

                        <div class="col-sm-9"><?= $user['fullname'] ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Tên đăng nhập') ?>:</label>

                        <div class="col-sm-9"><?= $user['username'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
                <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#modal-add-account">
                    <?= Translate::get('Thêm tài khoản') ?> <i class="glyphicon glyphicon-plus"></i>
                </button>
            </div>
        </div>
        <hr>
        <?php if (is_array($user_admin_account) && count($user_admin_account) > 0) { ?>
            <div class="tab">
                <ul class="nav nav-tabs tab-option" role="tablist">
                    <?php foreach ($user_admin_account as $k => $v) { ?>
                        <li class="link_tab_account <?= $k == 0 ? 'active' : '' ?>" id="tab_account_<?= $v['id'] ?>">
                            <a href="#div_account_<?= $v['id'] ?>" role="tab" data-toggle="tab">
                                <i class="glyphicon glyphicon-user"></i> <?= $v['name'] ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="tab-content">
                    <?php foreach ($user_admin_account as $k1 => $v1) { ?>
                        <div class="tab-pane div_tab_account <?= $k1 == 0 ? 'active' : '' ?>"
                             id="div_account_<?= $v1['id'] ?>">
                            <div class="row">
                                <div class="col-sm-7">
                                    <div class="panel panel-default" style="margin-bottom: 15px;">
                                        <div class="panel-heading"><h5><?= Translate::get('Thông tin tài khoản') ?></h5></div>
                                        <div class="panel-body">
                                            <div class="form-horizontal">
                                                <div class="form-group">
                                                    <label class="col-sm-3 text-right"><?= Translate::get('Tên tài khoản') ?>:</label>

                                                    <div class="col-sm-9">
                                                        <?= @$v1['name'] ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-3 text-right"><?= Translate::get('Nhóm quyền') ?>:</label>

                                                    <div class="col-sm-9">
                                                        <?= @$v1['group_name'] ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-3 text-right"><?= Translate::get('Trạng thái') ?>:</label>

                                                    <div class="col-sm-9">
                                                        <label class="label <?= @$v1['status_class'] ?>">
                                                            <?= @$v1['status_name'] ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-9 col-sm-offset-3">
                                                        <button class="btn btn-warning"
                                                                onclick="user.viewUpdateAdminAccount(<?= $v1['id'] ?>,<?= $v1['user_group_id'] ?>,'<?= $v1['name'] ?>',<?= $v1['status'] ?>)">
                                                            <?= Translate::get('Sửa thông tin') ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="col-sm-5">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><h5><?= Translate::get('Phân quyền') ?></h5></div>
                                        <div class="panel-body">
                                            <form id="form_list_roles" method="post"
                                                  action="<?= Yii::$app->urlManager->createUrl('user/set-roles') ?>">
                                                <h5>Check All <input type="checkbox" class="noStyle check-all"/>
                                                </h5>
                                                <ul class="list-right">
                                                    <?php
                                                    if (is_array($user_group_right_list[$v1['id']]) && count($user_group_right_list[$v1['id']]) > 0) {
                                                        foreach ($user_group_right_list[$v1['id']] as $keyR => $dataR) { ?>
                                                            <li>
                                                            <span class="line"><span>
                                                            <?php if (in_array($dataR['right_id'], $right_id_user_list[$v1['id']])) { ?>
                                                                <input type="checkbox" checked class="noStyle check-id"
                                                                       name="ids[]"
                                                                       value="<?= $dataR['right_id'] ?>"/>
                                                            <?php } else { ?>
                                                                <input type="checkbox" class="noStyle check-id"
                                                                       name="ids[]"
                                                                       value="<?= $dataR['right_id'] ?>"/>
                                                            <?php } ?>
                                                                    <?= Translate::get($dataR['right_name']) ?></span>
                                                            </span>
                                                                <?php if (is_array(@$dataR['lv2']) && count(@$dataR['lv2']) > 0) { ?>
                                                                    <ul>
                                                                        <?php foreach ($dataR['lv2'] as $k2 => $v2) { ?>
                                                                            <li>
                                                                        <span class="line"><span>
                                                                        <?php if (in_array($v2['right_id'], $right_id_user_list[$v1['id']])) { ?>
                                                                            <input type="checkbox" checked
                                                                                   class="noStyle check-id"
                                                                                   name="ids[]"
                                                                                   value="<?= $v2['right_id'] ?>"/>
                                                                        <?php } else { ?>
                                                                            <input type="checkbox"
                                                                                   class="noStyle check-id"
                                                                                   name="ids[]"
                                                                                   value="<?= $v2['right_id'] ?>"/>
                                                                        <?php } ?>
                                                                                <?= $v2['right_name'] ?></span>
                                                                        </span>
                                                                                <?php if (is_array(@$v2['lv3']) && count(@$v2['lv3']) > 0) { ?>
                                                                                    <ul>
                                                                                        <?php foreach ($v2['lv3'] as $k3 => $v3) { ?>
                                                                                            <li>
                                                                                            <span class="line"><span>
                                                                                            <?php if (in_array($v3['right_id'], $right_id_user_list[$v1['id']])) { ?>
                                                                                                <input type="checkbox"
                                                                                                       checked
                                                                                                       class="noStyle check-id"
                                                                                                       name="ids[]"
                                                                                                       value="<?= $v3['right_id'] ?>"/>
                                                                                            <?php } else { ?>
                                                                                                <input type="checkbox"
                                                                                                       class="noStyle check-id"
                                                                                                       name="ids[]"
                                                                                                       value="<?= $v3['right_id'] ?>"/>
                                                                                            <?php } ?>
                                                                                                    <?= $v3['right_name'] ?></span>
                                                                                            </span>
                                                                                            </li>
                                                                                        <?php } ?>
                                                                                    </ul>
                                                                                <?php } ?>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                <?php } ?>
                                                            </li>
                                                        <?php }
                                                    } ?>
                                                </ul>
                                                <hr>
                                                <input type="hidden" value="<?= $user['id'] ?>" name="user_id">
                                                <input type="hidden" value="<?= $v1['id'] ?>"
                                                       name="user_admin_account_id">
                                                <button class="btn btn-warning" type="submit">
                                                    <?= Translate::get('Cập nhật') ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div class="modal fade" id="ajax-dialog" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title title"></h4>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-account" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Translate::get('Thêm tài khoản') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin(['id' => 'add-user-admin-account-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('user/add-user-admin-account'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Nhóm quyền') ?>:<span
                                class="text-danger">*</span></label>

                        <div class="col-sm-9">
                            <?= $form->field($model_user_admin_account, 'user_group_id')
                                ->dropDownList($list_user_group, ['class' => 'form-control',])
                                ->label(false) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Tên tài khoản') ?>:<span
                                class="text-danger">*</span></label>

                        <div class="col-sm-9">
                            <?= $form->field($model_user_admin_account, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Trạng thái') ?>:</label>

                        <div class="col-sm-4">
                            <?= $form->field($model_user_admin_account, 'status')
                                ->dropDownList($list_status, ['class' => 'form-control',])
                                ->label(false) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Thêm') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>
                </div>
                <?= $form->field($model_user_admin_account, 'user_id')->label(false)
                    ->hiddenInput(array('value' => $user['id'])) ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-update-account" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Translate::get('Cập nhật tài khoản') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin(['id' => 'update-user-admin-account-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('user/update-user-admin-account'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Nhóm quyền') ?>:<span
                                class="text-danger">*</span></label>

                        <div class="col-sm-9">
                            <?= $form->field($model_user_admin_account, 'user_group_id')
                                ->dropDownList($list_user_group, ['class' => 'form-control', 'disabled' => true])
                                ->label(false) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Tên tài khoản') ?>:<span
                                class="text-danger">*</span></label>

                        <div class="col-sm-9">
                            <?= $form->field($model_user_admin_account, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 text-right"><?= Translate::get('Trạng thái') ?>:</label>

                        <div class="col-sm-4">
                            <?= $form->field($model_user_admin_account, 'status')
                                ->dropDownList($list_status, ['class' => 'form-control',])
                                ->label(false) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                            <button type="submit" class="btn btn-primary"> <?= Translate::get('Cập nhật')?></button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                        </div>
                    </div>
                </div>
                <?= $form->field($model_user_admin_account, 'user_id')->label(false)
                    ->hiddenInput(array('value' => $user['id'])) ?>
                <?= $form->field($model_user_admin_account, 'id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('a.ajax-link').click(function () {
        var title = $(this).attr('title');
        var url = $(this).attr('href');
//        alert(url);
        $.get(url, function (data) {
            if (data) {
                $('#ajax-dialog .title').html(title);
                setAjaxContent(data);
                $('#ajax-dialog').modal('show');
            }
        });
        return false;
    });

    function setAjaxContent(data) {
        var $obj_content = $(data).find('.ajax-content');
        if ($obj_content.length) {
            $('#ajax-dialog .modal-body').html($obj_content.html());
            setAjaxForm();
        } else {
            $(data).insertBefore('#ajax-dialog .modal-body');
        }
    }

    function setAjaxForm() {
        $('form.ajax-form').submit(function () {
            if ($(this).attr('method') == 'get') {
                $.get($(this).attr('action'), $(this).serializeArray(), function (data) {
                    if (data) {
                        setAjaxContent(data);
                    }
                });
            } else {
                $.post($(this).attr('action'), $(this).serializeArray(), function (data) {
                    if (data) {
                        setAjaxContent(data);
                    }
                });
            }
            return false;
        });
    }

    function submitRoles(obj, url, groupid) {
        if ($('input.check-id').length) {
//            $('#form_list_roles').attr('action', url);
//            console.log($('#form_list_roles').attr('action'));
            $('#form_list_roles').submit();
        } else {
            alert('Bạn chưa chọn dòng muốn xử lý');
        }
    }

    function setActiveAccount(admin_account_id) {
        console.log(admin_account_id);
        if (admin_account_id > 0) {
            $('.link_tab_account').each(function (i, obj) {
                if ($(this).hasClass("active")) {
                    $(this).removeClass('active');
                }
            });
            $('.div_tab_account').each(function (i, obj1) {
                if ($(this).hasClass("active")) {
                    $(this).removeClass('active');
                }
            });
            $('#tab_account_' + admin_account_id).addClass('active')
            $('#div_account_' + admin_account_id).addClass('active')
        }
    }

    $(document).ready(function () {
        $('input.check-all').on('change', function () {
            if (this.checked) {
                $(this).attr('checked', true);
                $('input.check-id').prop("checked", true);
//                $('input.check-id').addClass('check-id-checked').parent().addClass('checked');
            } else {
                $(this).attr('checked', false);
                $('input.check-id').prop('checked', false);
//                $('input.check-id').removeClass('check-id-checked').parent().removeClass('checked');
            }
        });
    });
    setActiveAccount(<?= $user_admin_account_id ?>);
</script>