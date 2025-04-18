<?php
use yii\helpers\Html;
use common\components\utils\Strings;
use common\components\utils\ObjInput;

?>
    Chào <?= $user_login_temp_info['fullname'] ?>,<br>
    <br>
    Mã xác thực đăng ký tài khoản trên website <?= DOMAIN ?> của bạn là: <strong><?= $code ?></strong><br>
    Bạn cũng có thể click vào link dưới đây để xác thực đăng ký tài khoản:<br>
    <a href="<?= $url_verify ?>" target="blank" title="Link xác thực đăng ký tài khoản"><?= $url_verify ?></a>
    <br>
    BQT <?= DOMAIN ?>