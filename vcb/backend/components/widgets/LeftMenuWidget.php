<?php
namespace backend\components\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\components\libs\Weblib;
use common\components\libs\Tables;

class LeftMenuWidget extends Widget {

    public function init() {
        parent::init();
    }

    public function run() {
        return $this->render('left_menu_widget',[
            'root_url'=>ROOT_URL,
            'frontend_url'=>Yii::$app->homeUrl,
            'rights' => $this->_getRights(),
        ]);
    }
    
    private function _getRights() {
        $result = array();
        $right_ids = \common\models\db\User::getRightIds();
        if (!empty($right_ids)) {
            $right_info = Tables::selectAllDataTable("right", "id IN (".implode(',', $right_ids).") AND code LIKE 'BACKEND::%' AND level <= 2 AND status = ".\common\models\db\Right::STATUS_ACTIVE." ", "right.left ASC ");
            if ($right_info != false) {
                foreach ($right_info as $row) {
                    if ($row['link'] != '' || $row['level'] == 1) {
                        $row['link'] = $row['link'] != '' ? ROOT_URL . ROOT_FOLDER . $row['link'] : '';
                        if ($row['level'] == 1) {
                            $result[$row['id']] = $row;
                            $result[$row['id']]['childs'] = array();
                        } elseif ($row['level'] == 2) {
                            $result[$row['parent_id']]['childs'][$row['id']] = $row;
                        } 
                    }
                }
            }
        }
        return $result;
    }
}
