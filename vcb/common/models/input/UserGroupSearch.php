<?php

namespace common\models\input;


use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\UserGroup;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class UserGroupSearch extends Model
{

    public $name;
    public $code;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status'], 'integer'],
            [['name', 'code'], 'string']
        ];
    }

    public function search()
    {

        $query = UserGroup::find()->orderBy('left ASC');


        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'name', trim($this->name)]);
        }
        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'code', trim($this->code)]);
        }

        if ($this->status > 0) {
            $query->andWhere(['=', 'status', trim($this->status)]);
        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $dataPage->data = $query->all();

        $lock = $query->andWhere("status = 2");
        $dataPage->totalLock = $lock->count();

        $dataPage->pagination = $paging;

        return $dataPage;
    }
} 