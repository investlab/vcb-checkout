<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm mới quản trị');
$this->params['breadcrumbs'][] = $this->title;
?>
<select id="credit_partner_branchs" name="credit_partner_branch_ids[]">
<?php foreach ($credit_partner_branchs as $key=>$name) :?>
    <option value="<?=$key?>"><?=$name?></option>
<?php endforeach;?>
</select>