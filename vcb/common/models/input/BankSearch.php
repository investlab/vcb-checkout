<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 4/14/2016
 * Time: 3:00 PM
 */

namespace common\models\input;


use common\models\db\Bank;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class BankSearch extends Model
{
    public $code;
    public $trade_name;
    public $name;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status'], 'integer'],
            [['code', 'trade_name', 'name'], 'string'],
        ];
    }


    public function search()
    {
        $query = Bank::find()->orderBy('time_created desc');

        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'code', trim($this->code)]);
        }
        if ($this->trade_name != null && trim($this->trade_name) != "") {
            $query->andWhere(['LIKE', 'trade_name', trim($this->trade_name)]);
        }
        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'name', trim($this->name)]);
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

        $dataPage->data = $query->all();

        $supplock = $query->andWhere("status =2");
        $dataPage->totalLock = $supplock->count();
        $dataPage->pagination = $paging;

        return $dataPage;

    }

} 