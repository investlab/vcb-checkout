<?php

use common\components\utils\ObjInput;
use common\models\db\Branch;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết chi nhánh');
$this->params['breadcrumbs'][] = $this->title;

$class_status = [
    Branch::STATUS_ACTIVE => 'label label-success',
    Branch::STATUS_LOCK => 'label label-danger',
];
?>
<!-- Start .content-wrapper -->
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Chi tiết chi nhánh') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('branch/index') ?>">
                            <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
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
                <!-- Start col-lg-12 -->
                <div class="panel panel-primary">
                    <!-- Start .panel -->
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
                    </div>
                    <div class=panel-body>
                        <table class="table table-hover" width="100%">

                            <tr>
                                <th><?= Translate::get('Tên chi nhánh')?></th>
                                <td><?= !empty($branch['name'])? $branch['name']: '' ?></td>
                                <th><?= Translate::get('Trạng thái')?></th>
                                <td>
                                    <?php if (!empty($branch['status'])) { ?>
                                        <span class="<?= $class_status[$branch['status']]?>"><?= $status_arr[$branch['status']] ?></span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr>
                                <th><?= Translate::get('Tỉnh/thành phố')?></th>
                                <td><?= !empty($branch['city'])? $branch['city']: '' ?></td>
                                <th><?= Translate::get('Người tạo') ?></th>
                                <td><?= $branch['user_created'] ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <td>
                                    <?= Translate::get('Tạo')?>: <?= (!empty($branch['time_created']))? date('H:i d/m/Y', $branch['time_created']): '' ?>
                                    <hr>
                                    <?= Translate::get('Cập nhật')?>: <?= (!empty($branch['time_created']))? date('H:i d/m/Y', $branch['time_updated']): '' ?>
                                </td>
                                <th><?= Translate::get('Người cập nhật') ?></th>
                                <td><?= $branch['user_updated'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>