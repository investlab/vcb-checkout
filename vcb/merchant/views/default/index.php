<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\AdvZone;
use common\components\utils\Strings;

$this->title = 'Trang chủ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage">Thông tin tài khoản</h1>
    <div class="graybg pdtop2 clearfix" style="border-bottom:1px solid #dcdcdc">
        <div class="col-md-12">
            <div style="position:relative; overflow: hidden; float: left;">
                <img class="thumbnail" src="images/logo.png" style="max-width: 200px; z-index: 1;"/>
                <div style="position:absolute; z-index: 2; right: 1px; top:1px; background: #DDD; padding: 2px 5px 2px 5px;"><a class="linktxt font11" href="/nganluong/userEmail/changeRequest.html"><i class="fa fa-edit"></i></a></div>
            </div>
        </div>
        <div class="col-md-6">
            <table class="table table-responsive">
                <tbody>
                    <tr>
                        <td scope="row">Tên merchant:</td>
                        <td>Lê Huy Phương</td>
                    </tr>
                    <tr>
                        <td scope="row">Website:</td>
                        <td>https://www.nganluong.vn</td>
                    </tr>
                    <tr>
                        <td scope="row">Email nhận thông báo:</td>
                        <td>lehuyphuong1982@gmail.com [ <a class="linktxt font11" href="/nganluong/userEmail/changeRequest.html"><i class="fa fa-edit"></i></a> ]</td>
                    </tr>
                </tbody>
            </table>
            <hr>
            <div style="margin-bottom:15px" align="center">
                <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/change-password'])?>" class="btn btn-primary">Đổi mật khẩu kết nối</a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="UserBox">
                <table class="table table-responsive">
                    <tbody>
                        <tr>
                            <td scope="row">Tên tài khoản:</td>
                            <td>Lê Huy Phương</td>
                        </tr>
                        <tr>
                            <td scope="row">Địa chỉ Email:</td>
                            <td>lehuyphuong1982@gmail.com</td>
                        </tr>
                        <tr>
                            <td scope="row">Điện thoại di động:</td>
                            <td>0977-827-477</td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <div style="margin-bottom:15px" align="center">
                    <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['user-info/change-password'])?>" class="btn btn-primary">Đổi mật khẩu đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>
