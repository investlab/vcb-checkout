<?php

namespace common\models\input;

use common\components\libs\Tables;
use common\models\business\UserBusiness;
use common\models\db\Method;
use common\models\db\VoucherCustomer;
use common\models\output\DataPage;
use common\util\TextUtil;
use yii\base\Model;
use yii\data\Pagination;

class MethodSearch extends Model
{
    public $transaction_type_id;
    public $name;
    public $code;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['page', 'pageSize', 'status', 'transaction_type_id'], 'integer'],
            [['code', 'name'], 'string'],
        ];
    }

    public function search()
    {
        $query = Method::find()->orderBy('id desc');

        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'name', trim($this->name)]);
        }
        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'code', trim($this->code)]);
        }

        if ($this->transaction_type_id > 0) {
            $query->andWhere(['=', 'transaction_type_id', $this->transaction_type_id]);
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

        foreach ($data as $key => $value) {
            $user_created = UserBusiness::getByID($value['user_created']);
            $data[$key]['create_name'] = '';
            if ($user_created != null) {
                $data[$key]['create_name'] = $user_created->fullname;
            }

            $user_updated = UserBusiness::getByID($value['user_updated']);
            $data[$key]['update_name'] = '';
            if ($user_updated != null) {
                $data[$key]['update_name'] = $user_updated->fullname;
            }

            $transaction_type = Tables::selectOneDataTable("transaction_type", ["id = :id", "id" => $value['transaction_type_id']]);
            if ($transaction_type != null) {
                $data[$key]["transaction_type_name"] = $transaction_type['name'];
            }
        }

        $dataPage->data = $data;
        $dataPage->status = Method::getStatus();
        $dataPage->pagination = $paging;

        return $dataPage;
    }
}