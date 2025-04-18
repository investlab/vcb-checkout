<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form', 'options' => ['class' => 'form-horizontal p25']]); ?>
    <?php if ($error != '') { ?>
        <div class="col-lg-12">
            <div class="alert alert-danger fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?=Translate::get($error)?>!
            </div>
        </div>
    <?php } ?>
    
    <div class="form-group">
        <?php
        echo $form->field($model, 'username', [
            'template' =>
            '<div class=col-lg-12>{input}<i class="ec-user s16 left-input-icon"></i>
                {error}{hint}
            </div>'])->label(false)->textInput(['autofocus' => true, 'placeholder' => Translate::get('Tên đăng nhập'), 'class' => "form-control left-icon", 'id' => 'username']);
        ?>
    </div>
    <div class="form-group">
        <?php
        echo $form->field($model, 'password', [
            'template' =>
            '<div class=col-lg-12>{input}
                <i class="ec-locked s16 left-input-icon"></i>
                {error}{hint}
            </div>'])->label(false)->passwordInput(['placeholder' => Translate::get('Mật khẩu'), 'class' => "form-control left-icon", 'id' => 'password']);
        ?>
    </div>
    <div class="form-group">
        <div class="col-lg-12 captcha">
            <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                'options' =>['class' => 'form-control right-icon text-uppercase'],
                'template' => '{input}{image}',
            ])->label(false) ?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-8">
        </div>
        <!-- col-lg-12 end here -->
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-4">
            <!-- col-lg-12 start here -->
            <?php echo Html::submitButton(Translate::get('Đăng nhập'), ['class' => 'btn btn-primary pull-right', 'name' => 'login-button']) ?>
        </div>
        <!-- col-lg-12 end here -->
    </div>
<?php ActiveForm::end(); ?>