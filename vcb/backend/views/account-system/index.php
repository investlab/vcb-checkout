<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Account;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách ngân hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách số dư tài khoản hệ thống') ?></h1>
        </div>
    </div>
    <!-- End .page-header -->
</div>
<!-- End .row -->
<div class=outlet>

<div class=row>
<div class=col-md-12>
    <?php if(Account::checkSystemTotalBalance($total_balance)) :?>
    <div class="alert alert-success">
        <i class="glyphicon glyphicon-ok" style="font-size: 18px;"></i><?= Translate::get('Tổng số dư trên toàn hệ thống đúng') ?>: <strong><?=ObjInput::makeCurrency($total_balance)?></strong> VND
    </div>
    <?php else:?>
    <div class="alert alert-danger">
        <i class="glyphicon glyphicon-warning-sign" style="font-size: 18px;"></i><?= Translate::get('Tổng số dư trên toàn hệ thống đang sai lệch')?>: <strong><?=ObjInput::makeCurrency($total_balance)?></strong> VND
    </div>
    <?php endif;?>
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th width="35" class="text-center">ID</th>
                        <th><?= Translate::get('Tên tài khoản')?></th>
                        <th class="text-right"><?= Translate::get('Số dư khả dụng')?></th>
                        <th class="text-right"><?= Translate::get('Số dư chờ chuyển')?></th>
                        <th class="text-center"><?= Translate::get('Loại tiền tệ')?></th>
                        <th class="text-center"><?= Translate::get('Trạng thái')?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $key => $data):?>                            
                            <tr>
                                <th class="text-center">
                                    <?= $data['id'] ?>
                                </th>
                                <td>
                                    <?= Translate::get($data['name']) ?>
                                </td>
                                <td class="text-right">
                                    <span class="text-magenta"><?= ObjInput::makeCurrency($data['balance']) ?></span>
                                </td>
                                <td class="text-right">
                                    <span class="text-magenta"><?= ObjInput::makeCurrency($data['balance_pending']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?= $data['currency'] ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($data['status'] == Account::STATUS_ACTIVE) { ?>
                                        <span class="label label-success"><?= Translate::get($data['status_name']) ?></span>
                                    <?php } elseif ($data['status'] == Account::STATUS_LOCK) { ?>
                                        <span class="label label-danger"><?=Translate::get($data['status_name']) ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>        
                </table>
            </div>
        </div>
    </div>
</div>