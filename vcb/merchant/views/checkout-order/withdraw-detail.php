<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\Cashout;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\db\Method;

$this->title = Translate::get('Thông tin yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?= Translate::get('Thông tin yêu cầu rút tiền') ?></h1>
    <div class="row">
        <div class="col-md-9">			
            <div class="form-horizontal pdtop2 mform" role="form"> 
                <?php if (Method::isWithdrawIBOffline($cashout['method_info']['code'])) : ?>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label bold"><?=Translate::get('Số tài khoản')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><strong class="fontS14 text-primary"><?= @$cashout['bank_account_code']?></strong></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Tên chủ tài khoản')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= @$cashout['bank_account_name']?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Chi nhánh')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"> <?= @$cashout['bank_account_branch'] ?></p>
                    </div>
                </div>
                <?php elseif (Method::isWithdrawATMCard($cashout['method_info']['code'])): ?>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label bold"><?=Translate::get('Số thẻ')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><strong class="fontS14 text-primary"><?= @$cashout['bank_account_code']?></strong></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Tên chủ thẻ')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= @$cashout['bank_account_name']?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Ngày phát hành thẻ')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"> <?= @$cashout['bank_card_month'].'/'.@$cashout['bank_card_year'] ?></p>
                    </div>
                </div>
                <?php elseif (Method::isWithdrawWallet($cashout['method_info']['code'])): ?>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label bold"><?=Translate::get('Email tài khoản')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><strong class="fontS14 text-primary"><?= @$cashout['bank_account_code']?></strong></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="hide-for-xs"><hr></div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Mã yêu cầu rút tiền')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= isset($cashout['id']) && $cashout['id'] != null ? $cashout['id'] : "" ?>
                        </p>
                    </div>
                </div>
                <?php if(@$cashout['type'] != Cashout::TYPE_CHECKOUT_ORDER ) { ?>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Thời gian')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><span class="text-danger">
                                <?= isset($cashout['time_begin']) && intval($cashout['time_begin']) > 0 ? date('H:i,d-m-Y', $cashout['time_begin']) : '' ?>
                        </span> <?=Translate::get('đến')?> <span class="text-danger"><?= isset($cashout['time_end']) && intval($cashout['time_end']) > 0 ? date('H:i,d-m-Y', $cashout['time_end']) : '' ?></span></p>
                    </div>
                </div>
                <?php } ?>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Số tiền rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><strong class="fontS14 text-primary"> <?= isset($cashout['amount']) && $cashout['amount'] != null ? ObjInput::makeCurrency($cashout['amount']) : 0 ?></strong> VND</p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Phí rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><?= isset($cashout['receiver_fee']) && $cashout['receiver_fee'] != null ? ObjInput::makeCurrency($cashout['receiver_fee']) : 0 ?>
                            &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND']?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Số tiền nhận được')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><strong class="fontS14 text-success">
                                <?=  ObjInput::makeCurrency(intval(@$cashout['amount']) - intval(@$cashout['receiver_fee'])) ?>
                        </strong> VND</p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Hình thức rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"> <?= isset($cashout['payment_method_info']['name']) && $cashout['payment_method_info']['name'] != null ? Translate::get($cashout['payment_method_info']['name']) : "" ?></p>
                    </div>
                </div>
                <div class="hide-for-xs"><hr></div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Thời gian tạo')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><?= isset($cashout['time_created']) && intval($cashout['time_created']) > 0 ? date('H:i,d-m-Y', $cashout['time_created']) : '' ?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Thời gian duyệt')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><?= isset($cashout['time_accept']) && intval($cashout['time_accept']) > 0 ? date('H:i,d-m-Y', $cashout['time_accept']) : '' ?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Thời gian chuyển ngân')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><?= isset($cashout['time_paid']) && intval($cashout['time_paid']) > 0 ? date('H:i,d-m-Y', $cashout['time_paid']) : '' ?></p>
                    </div>
                </div>
                <div class="form-group mline">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Trạng thái')?>:</label>
                    <div class="col-sm-8 pdr5 mgtop5">
                        <p class="form-control-static">
                            <?php if ($cashout['status'] == Cashout::STATUS_NEW) { ?>
                                <span class="label label-default"><?=Translate::get('Mới tạo')?></span>
                            <?php } elseif ($cashout['status'] == Cashout::STATUS_WAIT_VERIFY) { ?>
                                <span class="label label-primary"><?=Translate::get('Đợi merchant xác nhận')?></span>
                            <?php } elseif ($cashout['status'] == Cashout::STATUS_VERIFY) { ?>
                                <span class="label label-success"><?=Translate::get('Merchant đã xác nhận')?></span>
                            <?php } elseif ($cashout['status'] == Cashout::STATUS_WAIT_ACCEPT) { ?>
                                <span class="label label-warning"><?=Translate::get('Đã gửi, đợi duyệt')?></span>
                            <?php } elseif ($cashout['status'] == Cashout::STATUS_REJECT) { ?>
                            <span class="label label-danger"><?=Translate::get('Từ chối')?></span>
                            <br><br>
                        <div class="small">
                            <i class="text-danger"> <?=Translate::get('Lý do từ chối')?> :
                                <?= isset($cashout['reason_info']['name']) && $cashout['reason_info']['name'] != null ? $cashout['reason_info']['name'] : '' ?>
                                <br>
                                <?=Translate::get('Mô tả')?> : <?= $cashout['reason'] ?>
                            </i>
                        </div>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_ACCEPT) { ?>
                            <span class="label label-success"><?=Translate::get('Đã duyệt')?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_PAID) { ?>
                            <span class="label label-success"><?=Translate::get('Đã chuyển ngân')?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_CANCEL) { ?>
                            <span class="label label-danger"><?=Translate::get('Đã hủy')?></span>
                            <br><br>
                            <div class="small">
                                <i class="text-danger"> <?=Translate::get('Lý do hủy')?> :
                                    <?= isset($cashout['reason_info']['name']) && $cashout['reason_info']['name'] != null ? $cashout['reason_info']['name'] : '' ?>
                                    <br>
                                    <?=Translate::get('Mô tả')?> : <?= $cashout['reason'] ?>
                                </i>
                            </div>
                        <?php } ?>
                        </p><p></p>
                    </div>
                </div>

            </div>
            <!--begin button-->
            <div class="pdtop">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw') ?>" class="btn btn-danger"><?=Translate::get('Quay lại')?></a>
            </div>					
        </div>
    </div>	

</div>
