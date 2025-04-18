<?php


namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Weblib;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use DateTime;
use common\models\input\ResultCodeSearch;

class ResultCodeController extends BackendController
{

    public function actionIndex()
    {
        $search = new ResultCodeSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
        ]);
    }

    public function actionAdd()
    {
        //$model = new ResultCodeAddForm();

        return $this->render('add', [
        ]);
    }

    public function actionAddAuto()
    {
        $dir_path = ROOT_PATH . DS . 'common/models/business';
        $files = scandir($dir_path);
        $result = array();
        if (!empty($files)) {
            foreach ($files as $file_name) {
                if (substr($file_name, -4) == '.php') {
                    $file_path = $dir_path . DS . $file_name;
                    $content = file_get_contents($file_path);
                    $this->_getErrorMessage($result, $content);
                }
            }
        }
        if (!empty($result)) {
            foreach ($result as $value) {
                \common\components\utils\Translate::get($value, 'en-US');
                /*$inputs = array(
                    'code' => $value,
                    'content' => $value,
                    'language_code' => 'vi-VN',
                );
                \common\models\business\ResultCodeBusiness::add($inputs);*/
            }
            \common\components\utils\Translate::saveFile();
        }
    }

    private function _getValue($str)
    {
        $content = trim(str_replace('$error_message =', '', $str));
        if (preg_match('/^\'(.+)\'$/', $content, $part)) {
            return trim($part[1]);
        }
        return false;
    }

    private function _getErrorMessage(&$result, $content, $start = 0)
    {
        $start = strpos($content, '$error_message =', $start);
        if ($start !== false) {
            $end = strpos($content, ';', $start);
            if ($end !== false) {
                $temp = substr($content, $start, $end - $start);
                $value = $this->_getValue($temp);
                if ($value != false) {
                    $result[] = $value;
                }
                $this->_getErrorMessage($result, $content, $end);
            }
        }
        return $result;
    }

}
