<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/23/2016
 * Time: 9:49 AM
 */

namespace common\models\input;


use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\Reason;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class ReasonSearch extends Model
{

    public $time_created_from;
    public $time_created_to;
    public $type;
    public $name;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'type'], 'integer'],
            [['name'], 'string'],
            [['time_created_from', 'time_created_to'], 'date'],
        ];
    }

    public function search()
    {

        $query = Reason::find()->orderBy('time_created desc');

        $errors = [];
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors[] = 'Ngày tạo từ không đúng định dạng';
            }
        }
        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors[] = 'Ngày tạo đến không đúng định dạng';
            }
        }

        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            $fromdate = FormatDateTime::toTimeBegin($this->time_created_from);
            $query->andWhere(['>=', 'time_created', $fromdate]);
        }
        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            $todate = FormatDateTime::toTimeEnd($this->time_created_to);
            $query->andWhere(['<=', 'time_created', $todate]);
        }

        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'name', trim($this->name)]);
        }
        if ($this->status > 0) {
            $query->andWhere(['=', 'status', trim($this->status)]);
        }
        if ($this->type > 0) {
            $query->andWhere(['=', 'type', trim($this->type)]);
        }

        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $dataPage->data = $query->all();
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;

    }

} 