<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 21/03/2018
 * Time: 4:01 CH
 */
namespace common\models\business;

use common\components\libs\Tables;
use common\models\db\News;
use common\models\db\RewriteRule;
use Yii;

class NewsBusiness
{
    public static function getById($id)
    {
        return News::findOne(['id' => $id]);
    }

    public static function getByIDToArray($id)
    {
        $data = News::findOne(['id' => $id]);
        if ($data != null) {
            return $data->toArray();
        }
        return $data;
    }

    public static function getRewriteRuleById($id)
    {
        return RewriteRule::findOne(['id' => $id]);
    }

    public static function getRewriteRuleByIDToArray($id)
    {
        $data = RewriteRule::findOne(['id' => $id]);
        if ($data != null) {
            return $data->toArray();
        }
        return $data;
    }

    public static function getStatus($status)
    {
        $status_name = [
            'name' => 'Không xác định',
            'class' => 'label-default',
            'check_lock' => false,
        ];
        switch ($status) {
            case News::STATUS_ACTIVE:
                $status_name['name'] = 'Đang hoạt động';
                $status_name['class'] = 'label-success';
                break;
            case News::STATUS_LOCK:
                $status_name['name'] = 'Đang khóa';
                $status_name['class'] = 'label-danger';
                $status_name['check_lock'] = true;
                break;
        };
        return $status_name;
    }

    /**
     *
     * @param params : news_category_id, title, description, content, image, time_publish, rewrite_rule, user_id
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;

        //------------
        if ($rollback) {
            $transaction = News::getDb()->beginTransaction();
        }
        $model = new News();
        $model->news_category_id = $params['news_category_id'];
        $model->title = $params['title'];
        $model->description = $params['description'];
        $model->content = $params['content'];
        $model->image = $params['image'];
        $model->time_publish = $params['time_publish'];
        $model->status = News::STATUS_ACTIVE;
        $model->time_created = time();
        $model->user_created = $params['user_id'];
        if ($model->validate() && $model->save()) {
            $id = $model->getDb()->lastInsertID;
            if (trim($params['rewrite_rule']) != '') {
                $rewrite_rule = new RewriteRule();
                $rewrite_rule->module = 'frontend2';
                $rewrite_rule->route = 'site/news-detail';
                $rewrite_rule->pattern = $params['rewrite_rule'];
                $rewrite_rule->defaults = json_encode(array('id' => $id));
                $rewrite_rule->position = 1;
                $rewrite_rule->status = RewriteRule::STATUS_ACTIVE;
                $rewrite_rule->time_created = time();
                $rewrite_rule->user_created = $params['user_id'];
                if ($rewrite_rule->validate() && $rewrite_rule->save()) {
                    $rewrite_rule_id = $rewrite_rule->getDb()->lastInsertID;
                    $model->rewrite_rule_id = $rewrite_rule_id;
                    if ($model->validate() && $model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi xảy ra khi gán url ID';
                    }
                } else {
                    $error_message = 'Có lỗi xảy ra khi gán url';
                }
            } else {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = 'Có lỗi khi thêm nhân sự';
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

    /**
     *
     * @param params : id, hrm_position_id, name, user_id, parent_id, refer_personnel_id, time_begin, note, group_telegram_ids, user_create
     * @param rollback
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = News::getDb()->beginTransaction();
        }
        $model = News::findOne(['id' => $params['id']]);
        $model->news_category_id = $params['news_category_id'];
        $model->title = $params['title'];
        $model->description = $params['description'];
        $model->content = $params['content'];
        $model->image = $params['image'];
        $model->time_publish = $params['time_publish'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate() && $model->save()) {
            if (trim($params['rewrite_rule']) != '') {
                $rewrite_rule = RewriteRule::findOne(['id' => $model->rewrite_rule_id]);
                if ($rewrite_rule != null && $rewrite_rule->pattern != trim($params['rewrite_rule'])) {
                    $rewrite_rule->pattern = $params['rewrite_rule'];
                    $rewrite_rule->time_updated = time();
                    $rewrite_rule->user_updated = $params['user_id'];

                    if ($rewrite_rule->validate() && $rewrite_rule->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi xảy ra khi sửa url';
                    }
                } else {
                    $rewrite_rule = new RewriteRule();
                    $rewrite_rule->module = 'frontend2';
                    $rewrite_rule->route = 'site/news-detail';
                    $rewrite_rule->pattern = $params['rewrite_rule'];
                    $rewrite_rule->defaults = json_encode(array('id' => $params['id']));
                    $rewrite_rule->position = 1;
                    $rewrite_rule->status = RewriteRule::STATUS_ACTIVE;
                    $rewrite_rule->time_created = time();
                    $rewrite_rule->user_created = $params['user_id'];

                    if ($rewrite_rule->validate() && $rewrite_rule->save()) {
                        $rewrite_rule_id = $rewrite_rule->getDb()->lastInsertID;
                        $model->rewrite_rule_id = $rewrite_rule_id;
                        if ($model->validate() && $model->save()) {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = 'Có lỗi xảy ra khi gán url ID';
                        }
                    } else {
                        $error_message = 'Có lỗi xảy ra khi gán url';
                    }
                }
            } else {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = 'Có lỗi khi sửa tin tức';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param params : id, status, user_update
     * @param rollback
     */
    static function changeNewsStatus($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = News::getDb()->beginTransaction();
        }
        //----------
        $model = News::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = $params['status'];
            $model->time_updated = time();
            $model->user_updated = $params['user_update'];
            if ($model->validate() && $model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi trong quá trình xử lý';
            }
        } else {
            $error_message = 'Tin tức không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }
}