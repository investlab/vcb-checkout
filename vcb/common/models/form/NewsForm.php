<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 21/03/2018
 * Time: 4:18 CH
 */
namespace common\models\form;

use common\components\libs\Tables;
use common\components\utils\Validation;
use yii\base\Model;

class NewsForm extends Model
{

    public $id;
    public $news_category_id;
    public $title;
    public $description;
    public $content;
    public $image;
    public $time_publish;
    public $rewrite_rule;

    public function rules()
    {
        return [
            [['news_category_id', 'title'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['news_category_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id'], 'integer'],
            [['title', 'rewrite_rule'], 'string', 'max' => 255],
            [['content', 'description'], 'string'],
            [['image'], 'file', 'extensions' => ['jpg', 'jpge', 'png', 'gif'], 'maxSize' => 1024 * 1024 * 2],
            [['time_publish'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_category_id' => 'Danh mục tin tức',
            'title' => 'Tiêu đề tin',
            'description' => 'Mô tả',
            'content' => 'Nội dung',
            'image' => 'Ảnh đại diện',
            'time_publish' => 'Thời gian đăng tin',
            'rewrite_rule' => 'Rewrite Rule',
        ];
    }
}