<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;

$this->title = "Quên mật khẩu tài khoản";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main clearfix">
    <h1 class="loginTitle" align="center">Quên mật khẩu đăng nhập</h1>
    <div class="RegisterBox">
        <div class="panel panel-primary">
            <div class="panel-body">
                <div class="media"> 
                    <span class="pull-left"> <i class="icon-mail"></i> </span>
                    <div class="media-body">
                        <p>Ngân Lượng đã gửi đường link kích hoạt yêu cầu lấy lại mật khẩu đăng nhập tài khoản tới địa chỉ email <strong>lehuyphuong1982@gmail.com</strong>. Bạn vui lòng truy cập hộp thư và bấm vào link kích hoạt để tiếp tục.</p>
                        <h5 class="bold">Bạn chưa nhận được email kích hoạt?</h5>
                        <p>- Vui lòng click <a href="" class="linktxt" data-toggle="modal" data-target="#ResendMail"> Kích vào đây</a> để hệ thống tự động gửi lại Email cho bạn.</p>
                        <p>- Bạn <strong>KHÔNG</strong> đăng nhập được vào Email? Vui lòng click <a href="/nganluong/userForgetPassword/AddNewEmail.html" class="linktxt">vào đây</a> để tiếp tục.</p>
                        <p>Hoặc, liên hệ Trung tâm Hỗ trợ Khách hàng Ngân Lượng để được trợ giúp.</p>
                    </div>
                    <div class="modal fade" id="ResendMail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">&gt;
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                    <h4 class="modal-title">Gửi lại Email kích hoạt tài khoản</h4>
                                </div>
                                <div class="modal-body"><form action="" method="post" enctype="multipart/form-data" name="" id="" onsubmit="" class="form-horizontal" role="form"><input type="hidden" name="form_id" value="f929960beaf58c97c2ef91d386b0eb9d">
                                        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
                                        <div class="form-horizontal pdtop2 mform2" role="form">
                                            <div class="form-group mobiCol">
                                                <label class="col-sm-4 control-label" for="">Email kích hoạt tài khoản:</label>
                                                <div class="col-sm-7">
                                                    <p class="form-control-static">lehuyphuong1982@gmail.com</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="" class="col-sm-4 control-label">Mã bảo mật:</label>
                                                <div class="col-sm-3 pdr5">
                                                    <input type="text" maxlength="3" id="" class="form-control" name="verify_image" autocomplete="off">
                                                </div>
                                                <div class="col-sm-3 pdl5">
                                                    <img id="ccaptcha" src="/nganluong/userForgetPassword/captcha/v/5b17aae679c8f.html" alt="">
                                                </div>
                                            </div>       
                                            <div class="form-group">
                                                <div class="col-sm-offset-4 col-sm-4 pdr5">
                                                    <input class="btn btn-block btn-primary" value="Gửi lại" type="submit">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>