<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 21/03/2018
 * Time: 3:54 CH
 */

namespace backend\controllers;

use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\FormatDateTime;
use common\components\utils\ObjInput;
use common\models\business\NewsBusiness;
use common\models\business\NewsImageContentBusiness;
use common\models\db\News;
use common\models\form\NewsForm;
use common\models\input\NewsSearch;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class NewsController extends BackendController
{
    public function actionIndex()
    {
        $search = new NewsSearch();
        $search->setAttributes(Yii::$app->request->get());
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $page = $search->search();

        $status_arr = News::getStatus();
        $image_url = IMAGES_NEWS_URL;

        return $this->render('index', [
            'page' => $page,
            'search' => $search,
            'status_arr' => $status_arr,
            'image_url' => $image_url,
            'create_link' => Yii::$app->urlManager->createAbsoluteUrl('news/create'),
            'update_link' => Yii::$app->urlManager->createAbsoluteUrl('news/update'),
        ]);
    }

    public function actionCreate()
    {
        $model = new NewsForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $time_publish = $model->time_publish != '' ? FormatDateTime::dateToTimestamp($model->time_publish) : '';

                $model->image = UploadedFile::getInstance($model, 'image');
                $image_path = IMAGES_NEWS_PATH;

                if ($model->image != null) {
                    if ($model->image->
                    saveAs($image_path . time() . $model->image->name)
                    ) {
                        $image_name = time() . $model->image->name;
                    } else {
                        $image_name = null;
                    }
                } else {
                    $image_name = null;
                }
                $params = array(
                    'news_category_id' => $model->news_category_id,
                    'title' => $model->title,
                    'description' => $model->description,
                    'content' => $model->content,
                    'rewrite_rule' => $model->rewrite_rule,
                    'image' => $image_name,
                    'time_publish' => $time_publish,
                    'user_id' => Yii::$app->user->getId()
                );

                $result = NewsBusiness::add($params);
                if ($result['error_message'] == '') {
                    $message = 'Thêm tin tức thành công .';
                } else {
                    $message = $result['error_message'];
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl('news/index');
                Weblib::showMessage($message, $url);
            }
        }

        $news_category = Weblib::createComboTableArray('news_category', 'id', 'name', 1, '-- Chọn danh mục --', true);

        return $this->render('add', [
            'model' => $model,
            'news_category' => $news_category,
        ]);
    }

    // Sửa
    public function actionUpdate()
    {
        $id = ObjInput::get('id', 'int', '');
        $news = NewsBusiness::getById($id);
        $model = new NewsForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $time_publish = $model->time_publish != '' ? FormatDateTime::dateToTimestamp($model->time_publish) : '';

                $image = UploadedFile::getInstance($model, 'image');
                $image_path = IMAGES_NEWS_PATH;

                if (isset($image)) {
                    $image_name = time() . $image->name;
                    if ($image->saveAs($image_path . $image_name)
                    ) {
                        $default_path = IMAGES_NEWS_PATH . $news['image'];
                        if (file_exists($default_path)) {
                            @unlink($default_path);
                        }
                    }
                } else {
                    $image_name = $news['image'];
                }

                $model->content = NewsImageContentBusiness::changeImageContent($model->content, $model->id, $model->title);
                $params = array(
                    'id' => $model->id,
                    'news_category_id' => $model->news_category_id,
                    'title' => $model->title,
                    'description' => $model->description,
                    'content' => $model->content,
                    'rewrite_rule' => $model->rewrite_rule,
                    'image' => $image_name,
                    'time_publish' => $time_publish,
                    'user_id' => Yii::$app->user->getId()
                );
                $result = NewsBusiness::update($params);
                if ($result['error_message'] == '') {
                    $message = 'Sửa tin tức thành công .';
                } else {
                    $message = $result['error_message'];
                }
                $url = Yii::$app->urlManager->createAbsoluteUrl('news/index');
                Weblib::showMessage($message, $url);
            }
        }

        if ($news != null) {
            $rewrite_rule = NewsBusiness::getRewriteRuleByIDToArray($news['rewrite_rule_id']);
            $rewrite_rule_pattern = '';
            if ($rewrite_rule != null) {
                $rewrite_rule_pattern = $rewrite_rule['pattern'];
            }
            $model->id = $news['id'];
            $model->news_category_id = $news['news_category_id'];
            $model->title = $news['title'];
            $model->description = $news['description'];
            $model->content = $news['content'];
            $model->rewrite_rule = $rewrite_rule_pattern;
            $model->image = $news['image'];
            $model->time_publish = $news['time_publish'] > 0 ? date('d-m-Y', $news['time_publish']) : '';
        } else {
            $url = Yii::$app->urlManager->createAbsoluteUrl('news/index');
            Weblib::showMessage('Không tìm thấy tin tức này', $url);
        }

        $image_url = IMAGES_NEWS_URL;
        $news_category = Weblib::createComboTableArray('news_category', 'id', 'name', 1, '-- Chọn danh mục --', true);

        return $this->render('update', [
            'model' => $model,
            'news_category' => $news_category,
            'image_url' => $image_url,
        ]);
    }

    // Khóa
    public function actionLock()
    {
        $message = null;
        $search = ['news/index'];
        if (Yii::$app->request->post()) {
            $id = ObjInput::get('id', 'int', '');

            $params = array(
                'id' => $id,
                'status' => News::STATUS_LOCK,
                'user_update' => Yii::$app->user->getId()
            );
            $result = NewsBusiness::changeNewsStatus($params);

            if ($result['error_message'] == '') {
                $message = 'Khóa tin tức thành công.';
            } else {
                $message = $result['error_message'];
            }

            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }

    // Mở khóa
    public function actionActive()
    {
        $message = null;
        $search = ['news/index'];
        if (Yii::$app->request->post()) {
            $id = ObjInput::get('id', 'int', '');

            $params = array(
                'id' => $id,
                'status' => News::STATUS_ACTIVE,
                'user_update' => Yii::$app->user->getId()
            );
            $result = NewsBusiness::changeNewsStatus($params);

            if ($result['error_message'] == '') {
                $message = 'Mở khóa tin tức thành công.';
            } else {
                $message = $result['error_message'];
            }

            if (Yii::$app->request->get()) {
                $search = $search + Yii::$app->request->get();
            }
            $url = Yii::$app->urlManager->createUrl($search);
            Weblib::showMessage($message, $url);
        }
    }
}