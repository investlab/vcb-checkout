<?php
/**
 * Created by PhpStorm.
 * User: THU
 * Date: 6/11/2018
 * Time: 13:37
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\Cashout;
use common\models\db\Method;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\components\utils\Utilities;

$this->title = Translate::get('Lịch sử yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">

<h1 class="titlePage"><?= Translate::get('Lịch sử yêu cầu rút tiền') ?></h1>

<div class="dropSrchbox advance clearfix" id="enhance">
    <form class="form-horizontal" role=form>
        <div class="relt fixCol">
            <input type="text" class="form-control datepicker" placeholder="<?= Translate::get('Từ ngày') ?>"
                   name="time_created_from"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
            <i class="date"></i>
        </div>
        <div class="relt fixCol">
            <input type="text" class="form-control datepicker" placeholder="<?= Translate::get('Đến ngày') ?>"
                   name="time_created_to"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
            <i class="date"></i>
        </div>

        <div class="fixCol">
            <select class="form-control" name="payment_method_id">
                <?php
                foreach ($payment_method_search_arr as $key => $data) {
                    ?>
                    <option
                        value="<?= $key ?>" <?= (isset($search) && $search->payment_method_id == $key) ? "selected='true'" : '' ?> >
                        <?= Translate::get($data) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="fixCol">
            <select class="form-control" name="status_merchant" style="width: ">
                <option value="0"><?= Translate::get('Chọn trạng thái') ?></option>
                <?php
                foreach ($status_arr as $key => $rs) {
                    ?>
                    <option
                        value="<?= $key ?>" <?= (isset($search) && $search->status_merchant == $key) ? "selected='true'" : '' ?> >
                        <?= Translate::get($rs) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
            &nbsp;
            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw') ?>"
               class="btn btn-default">
                <?= Translate::get('Bỏ lọc') ?>
            </a>
        </div>
    </form>
</div>

<!-- begin status -->
<div class="statistic clearfix" style="display:">
        <span class="pdr15"><?= Translate::get('Tìm thấy') ?> <strong
                class="orrageFont"><?php echo $page->pagination->totalCount; ?></strong> <?= Translate::get('giao dịch') ?>
            ;
            <?= Translate::get('Tổng tiền') ?>: <strong
                class="orrageFont"><?php echo ObjInput::makeCurrency($page->total_amount) ?></strong> đ</span><span
        class="sprLine"></span>
        <span class="fr">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuWithdraw" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <?= Translate::get('Thao tác')?>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuWithdraw">
                    <li><a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw-add')?>"><?= Translate::get('Thêm yêu cầu rút tiền') ?></a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#" onclick="exportData.set('<?= Translate::get('Trích xuất giao dịch') ?>','<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order') . Utilities::buidLinkExcel('withdraw-export') ?>'); return false;"><?= Translate::get('Xuất excel') ?></a></li>
                </ul>
            </div>
        </span>
</div>
<?php
if (is_array($page->data) && count($page->data) == 0) {
    ?>

    <div class="alert alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy') ?>
        .
    </div>
<?php } ?>
<!-- begin table -->
<div class="table-responsive" style="padding-bottom: 50px;">
    <table class="table table-hover" cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr class="active">
            <th><?= Translate::get('Thông tin yêu cầu rút tiền') ?></th>
            <th><?= Translate::get('Tài khoản nhận') ?></th>
            <th width="120">
                <div align="center"><?= Translate::get('Số tiền rút') ?></div>
            </th>
            <th width="120"><?= Translate::get('Trạng thái') ?></th>
            <th width="120"><i class="fa fa-clock-o"></i><?= Translate::get('TG tạo') ?></th>
            <th width="80">
                <div align="center"><?= Translate::get('Thao tác') ?></div>
            </th>
        </tr>
        </thead>
        <?php
        if (is_array($page->data) && count($page->data) > 0) {
        foreach ($page->data as $key => $data) {
        ?>
        <tbody>
        <tr>
            <td>
                <?= Translate::get('Mã yêu cầu rút tiền') ?>:<span class="text-primary">
                         <?= isset($data['id']) && $data['id'] != null ? $data['id'] : "" ?>
                    </span><br>

                <div class="small">
                    <?php if(@$data['type'] != Cashout::TYPE_CHECKOUT_ORDER ) { ?>
                    <?= Translate::get('Thời gian') ?>: <span class="text-danger">
                            <?= isset($data['time_begin']) && intval($data['time_begin']) > 0 ? date('H:i,d-m-Y', $data['time_begin']) : '' ?>
                        </span> <?= Translate::get('đến') ?> <span
                        class="text-danger"> <?= isset($data['time_end']) && intval($data['time_end']) > 0 ? date('H:i,d-m-Y', $data['time_end']) : '' ?></span>
                    <br>
                    <?php }?>
                    <?= Translate::get('Hình thức') ?>:
                        <span class="text-primary">
                             <?= isset($data['payment_method_info']['name']) && $data['payment_method_info']['name'] != null ? Translate::get($data['payment_method_info']['name']) : "" ?>
                        </span><br>
                         <?= Translate::get('Reference Code') ?>:
                          <span class="text-primary">
                        <?=@$data['reference_code_merchant']?> </span><br>
                </div>
            </td>
            <td>
                <div class="small">
                <?php if (Method::isWithdrawIBOffline(@$data['method_info']['code'])) : ?>
                    <?= Translate::get('Số tài khoản') ?>: <span class="text-primary"><?= @$data['bank_account_code'] ?></span><br>
                    <?= Translate::get('Tên chủ tài khoản') ?>: <span class="text-primary"><?= @$data['bank_account_name'] ?></span><br>
                    <?= Translate::get('Chi nhánh') ?>: <span class="text-primary"><?= @$data['bank_account_branch'] ?></span><br>
                <?php elseif (Method::isWithdrawATMCard(@$data['method_info']['code'])): ?>
                    <?= Translate::get('Số thẻ') ?>: <span class="text-primary"><?= @$data['bank_account_code'] ?></span><br>
                    <?= Translate::get('Tên chủ thẻ') ?>: <span class="text-primary"><?= @$data['bank_account_name'] ?></span><br>
                    <?= Translate::get('Ngày phát hành') ?>: <span class="text-primary"><?= @$data['bank_card_month'] ?>/<?= @$data['bank_card_year'] ?></span><br>
                <?php elseif (Method::isWithdrawWallet(@$data['method_info']['code'])): ?>
                    <?= Translate::get('Email tài khoản') ?>: <span class="text-primary"><?= @$data['bank_account_code'] ?></span><br>
                <?php endif; ?>
                </div>
            </td>
            <td align="center">
                <p class="mrgbm5 bold fontS13"><?= isset($data['amount']) && $data['amount'] != null ? ObjInput::makeCurrency($data['amount']) : "" ?></p>

                <div class="small">
                    <hr>
                    <?= Translate::get('Phí rút') ?>: <span class="text-primary">
                            <?= isset($data['receiver_fee']) && $data['receiver_fee'] != null ? ObjInput::makeCurrency($data['receiver_fee']) : "" ?>
                        </span><br>
                </div>

            </td>
            <td>
                <?php if ($data['status'] == Cashout::STATUS_NEW) { ?>
                    <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_WAIT_VERIFY) { ?>
                    <span class="label label-primary"><?= Translate::get('Đợi merchant xác nhận') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_VERIFY) { ?>
                    <span class="label label-success"><?= Translate::get('Mới tạo') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_WAIT_ACCEPT) { ?>
                    <span class="label label-warning"><?= Translate::get('Đã gửi, đợi duyệt') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_REJECT) { ?>
                    <span class="label label-danger"><?= Translate::get('Từ chối') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_ACCEPT) { ?>
                    <span class="label label-success"><?= Translate::get('Đã duyệt') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_PAID) { ?>
                    <span class="label label-success"><?= Translate::get('Đã chuyển ngân') ?></span>
                <?php } elseif ($data['status'] == Cashout::STATUS_CANCEL) { ?>
                    <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                <?php } ?>
            </td>
            <td><span class="grayFont small"><?= date('H:i, d/m/Y', $data['time_created']) ?></strong></span></td>
            <td align="center">
                <div class="dropdown otherOptions">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false"><?= Translate::get('Thao tác') ?><span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a class="" target="_blank"
                               href="<?= Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-detail', 'id' => $data['id']]) ?>">
                                <?= Translate::get('Chi tiết') ?>
                            </a>
                        </li>
                        <?php if ($data['status'] == Cashout::STATUS_WAIT_VERIFY) { ?>
                            <li>
                                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-verify', 'id' => $data['id']]) ?>">
                                    <?= Translate::get('Xác nhận yêu cầu') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-cancel', 'id' => $data['id']]) ?>">
                                    <?= Translate::get('Hủy yêu cầu') ?>
                                </a>
                            </li>
                        <?php } ?>

                    </ul>
                </div>
            </td>
        </tr>
        <?php
        }
        } ?>
    </table>
</div>
</div>
<div class="box-control">
    <div class="pagination-router">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $page->pagination,
            'nextPageLabel' => Translate::get('Tiếp'),
            'prevPageLabel' => Translate::get('Sau'),
            'maxButtonCount' => 5
        ]); ?>
    </div>
</div>
