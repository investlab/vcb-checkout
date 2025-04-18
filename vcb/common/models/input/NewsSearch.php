<?php

namespace common\models\input;

use common\models\business\NewsBusiness;
use common\models\db\News;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class NewsSearch extends Model
{
    public $title;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status'], 'integer'],
            [['title'], 'string'],
        ];
    }


    public function search()
    {
        $query = News::find()->orderBy('id desc');

        if ($this->title != null && trim($this->title) != "") {
            $query->andWhere(['LIKE', 'title', trim($this->title)]);
        }

        if ($this->status > 0) {
            $query->andWhere(['=', 'status', $this->status]);
        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $data = $query->asArray()->all();

        foreach ($data as $k => $v) {
            $status = NewsBusiness::getStatus($v['status']);
            if ($status != '') {
                $data[$k]['status_name'] = $status['name'];
                $data[$k]['status_class'] = $status['class'];
                $data[$k]['check_lock'] = $status['check_lock'];
            }
        }

        $dataPage->data = $data;
        $dataPage->pagination = $paging;

        return $dataPage;

    }
}