<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/21/2018
 * Time: 15:36
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CardLogFull;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\components\utils\Utilities;

$this->title = Translate::get('Tra cứu giao dịch thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
<h1 class="titlePage"><?= Translate::get('Tra cứu giao dịch thẻ cào') ?></h1>

<p class="descrip">Bạn có thể tra cứu (tìm kiếm) tình trạng thẻ cào đã nạp THÀNH CÔNG hoặc KHÔNG THÀNH CÔNG trong 02
    tháng gần đây (từ 20-04-2018 đến 20-06-2018). Nếu bạn muốn tra cứu các giao dịch từ trước, vui lòng liên hệ số
    hotline <strong class="text-danger"><?= $GLOBALS['FRONTEND_PAGE']['HOTLINE'] ?></strong> để được hỗ trợ.</p>

<form method="get">
    <div class="dropSrchbox advance clearfix">
        <div class="relt fixCol">
            <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Từ ngày') ?>"
                   name="time_created_from"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
            <i class="date"></i>


        </div>
        <div class="relt fixCol">
            <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                   name="time_created_to"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
            <i class="date"></i>
        </div>
        <div class="fixCol">
            <input type="text" class="form-control" placeholder="<?= Translate::get('Mã giao dịch') ?>"
                   name="id"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->id) : '' ?>">
        </div>
        <div class="fixCol">
            <input type="text" class="form-control" placeholder="<?= Translate::get('Mã tham chiếu') ?>"
                   name="merchant_refer_code"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->merchant_refer_code) : '' ?>">
        </div>
        <div class="clearfix"></div>
        <div class="fixCol" style="padding-top: 15px">
            <input type="text" class="form-control" placeholder="<?= Translate::get('Mã thẻ') ?>"
                   name="card_code"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->card_code) : '' ?>">
        </div>
        <div class="fixCol" style="padding-top: 15px">
            <input type="text" class="form-control" placeholder="<?= Translate::get('Serial thẻ') ?>"
                   name="card_serial"
                   value="<?= (isset($search) && $search != null) ? Html::encode($search->card_serial) : '' ?>">
        </div>
        <div class="fixCol" style="padding-top: 15px">
            <select class="form-control" name="card_type_id">
                <?php
                foreach ($card_type_search_arr as $key => $rs) {
                    ?>
                    <option
                        value="<?= $key ?>" <?= (isset($search) && $search->card_type_id == $key) ? "selected='true'" : '' ?> >
                        <?= $rs ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="fixCol" style="padding-top: 15px">
            <select class="form-control" name="card_status_merchant" style="width: ">
                <option value="0"><?= Translate::get('Chọn trạng thái') ?></option>
                <?php
                foreach ($card_status_arr as $key => $rs) {
                    ?>
                    <option
                        value="<?= $key ?>" <?= (isset($search) && $search->card_status_merchant == $key) ? "selected='true'" : '' ?> >
                        <?= $rs ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="clearfix"></div>

        <div class="fixCol" style="padding-top: 15px">
            <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm')?></button>
            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('card-transaction/search') ?>"
               class="btn btn-default">
                <?= Translate::get('Bỏ lọc')?>
            </a>
        </div>
    </div>
</form>
<!-- begin status -->
<div class="statistic clearfix" style="display:">
        <span class="pdr15"><?= Translate::get('Tổng số thẻ giao dịch') ?>: <strong
                class="orrageFont"><?php echo $page->pagination->totalCount; ?></strong>&nbsp;
            <?= Translate::get('Số thẻ thành công') ?>: <strong
                class="orrageFont"><?= (isset($page->count_success) ? $page->count_success : '0') ?></strong></span>
    <?= Translate::get('Tổng mệnh giá thẻ') ?>: <strong
        class="orrageFont"><?= ObjInput::makeCurrency($page->total_card_price) ?></strong> đ<span
        class="sprLine"></span>
</div>
<!-- begin table -->
<div class="table-responsive">
    <table class="table table-hover" cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr class="active">
            <th><i class="fa fa-clock-o"></i><?= Translate::get('Thời gian') ?></th>
            <th>
                <div><?= Translate::get('Mã giao dịch') ?></div>
            </th>

            <th>
                <div><?= Translate::get('Mã thẻ') ?></div>
            </th>
            <th>
                <div><?= Translate::get('Serial') ?></div>
            </th>
            <th>
                <div><?= Translate::get('Loại thẻ') ?></div>
            </th>
            <th>
                <div align="right"><?= Translate::get('Mệnh giá') ?></div>
            </th>
            <th>
                <div align="right"><?= Translate::get('Phí') ?></div>
            </th>
            <th>
                <div align="right"><?= Translate::get('Thực nhận') ?></div>
            </th>
            <th>
                <div><?= Translate::get('Mã tham chiếu') ?></div>
            </th>
            <th>
                <div><?= Translate::get('Tình trạng thẻ') ?></div>
            </th>
            <th>
                <div><?= Translate::get('Thao tác') ?></div>
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
                        <div class="small text-center">
                            <?php if (intval($data['time_created']) > 0): ?>
                                <?= date('H:i, d/m/Y', $data['time_created']) ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="small text-center">
                            <?= @$data['id'] ?>
                        </div>
                    </td>
                    <td>
                        <div class="small"><?= @$data['card_code'] ?></div>
                    </td>
                    <td>
                        <div class="small"><?= @$data['card_serial'] ?></div>
                    </td>
                    <td>
                        <img src="images/card/<?= strtolower($data['card_type_info']['code'])?>.png" title="<?= @$data['card_type_info']["name"] ?>" style="width: 50px;"/>
                    </td>
                    <td>
                        <div
                            class="small text-center text-primary"><?= $data['card_price'] > 0 ? ObjInput::makeCurrency(@$data['card_price']) : '-' ?></div>
                    </td>
                    <td>
                        <div
                            class="small text-center text-primary"><?= $data['card_price'] > 0 ? ObjInput::makeCurrency(@$data['card_price'] - @$data['card_amount']) : '-' ?></div>
                    </td>
                    <td>
                        <div
                            class="small text-center text-primary"><?= $data['card_price'] > 0 ? ObjInput::makeCurrency(@$data['card_amount']) : '-' ?></div>
                    </td>
                    <td>
                        <div class="small text-center"><?= @$data['merchant_refer_code'] ?></div>
                    </td>
                    <td class="text-center">
                        <?php if ($data['card_status'] == CardLogFull::CARD_STATUS_FAIL) { ?>
                            <span class="label label-danger"><?= Translate::get('Thẻ sai') ?></span>
                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_TIMEOUT) { ?>
                            <span class="label label-warning"><?= Translate::get('Timeout') ?></span>
                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_NO_SUCCESS) { ?>
                            <span class="label label-default"><?= Translate::get('Thẻ chưa bị gạch') ?></span>
                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_SUCCESS) { ?>
                            <span class="label label-success"><?= Translate::get('Thành công') ?></span>
                        <?php } ?>
                    </td>
                    <td align="center">
                        <div class="dropdown otherOptions">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false"><?= Translate::get('Thao tác') ?><span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a class="" target="_blank"
                                       href="<?= Yii::$app->urlManager->createAbsoluteUrl(['card-transaction/detail', 'id' => $data['id']]) ?>">
                                        <?= Translate::get('Chi tiết') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                </tbody>
            <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="11" class="warning" align="center">KHÔNG TỒN TẠI THÔNG TIN THẺ !</td>
            </tr>
        <?php } ?>
    </table>
</div>
</div>
<div class="box-control">
    <div class="pagination-router">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $page->pagination,
            'nextPageLabel' =>  Translate::get('Tiếp'),
            'prevPageLabel' =>  Translate::get('Sau'),
            'maxButtonCount' => 5
        ]); ?>
    </div>
</div>
