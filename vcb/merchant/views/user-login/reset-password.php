<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;

use common\components\utils\Translate;
$this->title = "Đổi mật khẩu tài khoản";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?= Translate::get('Khai báo mật khẩu đăng nhập mới') ?></h1>
    <div class="RegisterBox">
        <div class="help-block"><?= Translate::get('Bạn vui lòng nhập mật khẩu đăng nhập mới và xác nhận bằng mật khẩu giao dịch để hoàn tất.') ?></div>
        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
        <div class="whitebox">
            <div class="panel-body"> <form action="" method="post" enctype="multipart/form-data" name="" id="" onsubmit="" class="form-horizontal" role="form"><input type="hidden" name="form_id">
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Email tài khoản Ngân Lượng') ?>:</label>
                        <div class="col-sm-8">
                            <p class="form-control-static bold"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Mật khẩu đăng nhập mới') ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="new_password" name="new_password" value="" placeholder="" type="password" data-trigger="focus" autocomplete="off" data-placement="bottom" data-content="<?= Translate::get('Mật khẩu từ 6 - 20 ký tự, không bao gồm khoảng trắng') ?>" data-original-title="" title="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Nhập lại mật khẩu') ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="re_password" name="re_password" value="" placeholder="" type="password" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"><?= Translate::get('Mật khẩu giao dịch') ?>:</label>
                        <div class="col-sm-8">
                            <input class="form-control mrgbm5" id="payment_password" name="payment_password" placeholder="" type="password" data-toggle="popover" data-trigger="focus" autocomplete="off" data-placement="bottom" data-content="<?= Translate::get('Mật khẩu từ 6 - 20 ký tự, không bao gồm khoảng trắng') ?>" value="" data-original-title="" title="">
                            <a href="" target="_blank" class="linktxt"><?= Translate::get('Quên mật khẩu giao dịch') ?></a> </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Mã bảo mật') ?>:</label>
                        <div class="col-sm-4 pdr5">
                            <input type="text" maxlength="3" class="form-control" name="verify_image" id="capcha" autocomplete="off">
                        </div>
                        <div class="col-sm-4 pdl5">
                            <img id="ccaptcha" src="" alt="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-4 pdr5">
                            <button type="submit" class="btn btn-block btn-success "><?= Translate::get('Hoàn tất') ?></button>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>