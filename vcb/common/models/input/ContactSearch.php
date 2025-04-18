<?php

namespace common\models\input;


use common\models\db\Contact;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class ContactSearch extends Model
{
    public $phone;
    public $email;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status'], 'integer'],
            [['phone', 'email'], 'string'],
        ];
    }


    public function search()
    {
        $query = Contact::find()->orderBy('time_created desc');

        if ($this->phone != null && trim($this->phone) != "") {
            $query->andWhere(['LIKE', 'phone', trim($this->phone)]);
        }
        if ($this->email != null && trim($this->email) != "") {
            $query->andWhere(['LIKE', 'email', trim($this->email)]);
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

        $query->andWhere('status = ' . Contact::STATUS_UNREAD);
        $dataPage->unread = $query->count();
        $dataPage->pagination = $paging;
        return $dataPage;
    }
} 