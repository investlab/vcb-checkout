<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 4/14/2016
 * Time: 3:00 PM
 */

namespace common\models\input;


use common\models\db\ResultCode;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class ResultCodeSearch extends Model
{
    public $code;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page'], 'integer'],
            [['code'], 'string'],
        ];
    }


    public function search()
    {
        $query = ResultCode::find()->orderBy('time_created DESC');

        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'code', trim($this->code)]);
        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $dataPage->data = $query->all();
        $dataPage->pagination = $paging;

        return $dataPage;

    }

} 