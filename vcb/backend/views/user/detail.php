<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Chi tiết Quản trị');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Chi tiết Quản trị') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('user/index') ?>"><?=Translate::get('Quay lại') ?></a>
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
                <!-- Start col-lg-12 -->
                <div class="panel panel-primary">
                    <!-- Start .panel -->
                    <div class=panel-heading>
                        <h3 class=panel-title><?=Translate::get('Thông tin chung') ?></h3>
                    </div>
                    <div class=panel-body>
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th><?=Translate::get('Họ và tên') ?></th>
                                <td><?= $user['fullname'] ?></td>
                                <th><?=Translate::get('Ngày sinh') ?></th>
                                <td><?= $user['birthday'] != null ? date('d-m-Y', $user['birthday']) : '' ?></td>
                            </tr>
                            </thead>
                            <tr>
                                <th><?=Translate::get('Tên đăng nhập') ?></th>
                                <td><?= $user['username'] ?></td>
                                <th><?=Translate::get('Giới tính') ?></th>
                                <td><?= $user['gender'] == 1 ? Translate::get('Nam') : Translate::get('Nữ') ?></td>
                            </tr>
                            <tr>
                                <th><?=Translate::get('Email') ?></th>
                                <td><?= $user['email'] ?></td>
                                <th><?=Translate::get('Trạng thái') ?></th>
                                <td>
                                    <?php if ($user['status'] == 1) { ?>
                                        <span class="label label-success"> <?= Translate::get('Hoạt động') ?></span>
                                    <?php } elseif ($user['status'] == 2) { ?>
                                        <span class="label label-danger"> <?= Translate::get('Bị khóa') ?></span>
                                    <?php
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Số di động') ?></th>
                                <td><?= $user['mobile'] ?></td>
                                <th><?= Translate::get('Ngày tạo') ?></th>
                                <td><?= $user['time_created'] > 0 ? date('d-m-Y', $user['time_created']) : '' ?></td>
                            </tr>

                            <tr>
                                <th><?= Translate::get('Số cố định') ?></th>
                                <td><?= $user['phone'] ?></td>
                                <th><?= Translate::get('Ngày cập nhật') ?></th>
                                <td><?= $user['time_updated'] > 0 ? date('d-m-Y', $user['time_updated']) : '' ?></td>
                            </tr>
                        </table>

                    </div>
                </div>
                <!-- End .panel -->
            </div>

        </div>
        <!-- InstanceEndEditable -->
    </div>

</div>
