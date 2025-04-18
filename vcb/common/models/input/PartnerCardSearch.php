<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 13:16
 */

namespace common\models\input;


use common\models\db\PartnerCard;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PartnerCardSearch extends Model
{
    public $code;
    public $bill_type;
    public $name;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'bill_type'], 'integer'],
            [['code', 'name'], 'string'],
        ];
    }


    public function search()
    {
        $query = PartnerCard::find()->orderBy('time_updated desc');

        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'code', trim($this->code)]);
        }

        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'name', trim($this->name)]);
        }

        if (intval($this->bill_type) > 0) {
            $query->andWhere(['=', 'bill_type', $this->bill_type]);
        }

        if (intval($this->status) > 0) {
            $query->andWhere(['=', 'status', $this->status]);
        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $dataPage->data = $query->all();

        $supplock = $query->andWhere("status =" . PartnerCard::STATUS_LOCK);
        $dataPage->totalLock = $supplock->count();
        $dataPage->pagination = $paging;

        return $dataPage;

    }

} 