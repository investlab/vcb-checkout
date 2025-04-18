<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/20/2016
 * Time: 10:02 PM
 */

namespace common\models\business;


use common\models\db\ResultCode;
use common\models\db\ResultCodeLanguage;
use common\components\libs\Tables;
use Yii;

class ResultCodeBusiness
{

    /**
     *
     * @param type $params : code, content, language_code, description
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = ResultCode::getDb()->beginTransaction();
        }
        $result_code_info = Tables::selectOneDataTable("result_code", ["code = :code", "code" => $params['code']]);
        if ($result_code_info == false) {
            $model = new ResultCode();
            $model->code = $params['code'];
            $model->description = $params['description'];
            $model->time_created = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    //------------
                    $model_language = new ResultCodeLanguage();
                    $model_language->language_id = \common\models\db\Language::getIdByCode($params['language_code']);
                    $model_language->result_code_id = $id;
                    $model_language->result_code = $params['code'];
                    $model_language->content = $params['content'];
                    $model_language->time_created = time();
                    if ($model_language->validate() && $model_language->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi thêm mã trả về';
                    }
                } else {
                    $error_message = 'Có lỗi khi thêm  mã trả về';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Mã trả về đã tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

} 