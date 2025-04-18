<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 31/03/2017
 * Time: 10:30 SA
 */
namespace common\models\input;

use common\components\libs\Tables;
use common\models\db\Account;
use common\models\db\Customer;
use common\models\db\MarketingCampaign;
use common\models\output\DataPage;
use common\util\TextUtil;
use yii\base\Model;
use yii\data\Pagination;

class AccountSearch extends Model
{
    public $cus_idnumber;
    public $cus_mobile;
    public $refer_type;
    public $refer_id;
    public $status;
    public $refer_list;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'cus_idnumber', 'cus_mobile', 'refer_id', 'refer_type'], 'integer'],
        ];
    }

    public function search()
    {
        $query = Account::find()->orderBy('time_created desc');
        $query_customer = Customer::find()->orderBy('time_created desc');
        $condition = '';
        $check_condition = false;
        if ($this->refer_id > 0) {
            $query->andWhere(['LIKE', 'refer_id', trim($this->refer_id)]);
        }
        if ($this->refer_type > 0) {
            $query->andWhere(['LIKE', 'refer_type', trim($this->refer_type)]);
        }
        if ($this->cus_idnumber != null && trim($this->cus_idnumber) != "") {
            if ($condition == '') {
                $condition = "id_number = '" . trim($this->cus_idnumber) . "'";
            } else {
                $condition .= "AND id_number = '" . trim($this->cus_idnumber) . "'";
            }
            $check_condition = true;
//            $query_customer->andWhere(['=', 'id_number', trim($this->cus_idnumber)]);
        }
        if ($this->cus_mobile != null && trim($this->cus_mobile) != "") {
            if ($condition == '') {
                $condition = "mobile = '" . trim($this->cus_mobile) . "' or mobile2 = '" . trim($this->cus_mobile) . "'";
            } else {
                $condition .= "AND mobile = '" . trim($this->cus_mobile) . "' or mobile2 = '" . trim($this->cus_mobile) . "'";
            }
            $check_condition = true;
//            $query_customer->andWhere('mobiles = ' . trim($this->cus_mobile) . ' or mobile2 = ' . trim($this->cus_mobile));
        }
        if ($condition == '') {
            $condition = '1 = 0';
        }

        $query_customer->andWhere($condition);
        $customer = $query_customer->one();
//        TextUtil::deBug($condition);
        if ($customer != null) {
            $query->andWhere(['=', 'customer_id', $customer->id]);
        } else if ($check_condition == true) {
            $query->andWhere('1 = 0');
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
            $data[$key]['cus_name'] = '';
            $data[$key]['cus_mobile'] = '';
            $data[$key]['cus_mobile2'] = '';
            $data[$key]['cus_idnumber'] = '';
            if ($value['customer_id'] != null || trim($value['customer_id']) != '') {
                $customer_data = Tables::selectOneDataTable('customer', 'id = ' . $value['customer_id']);
                if ($customer_data != null) {
                    $data[$key]['cus_name'] = $customer_data['name'];
                    $data[$key]['cus_mobile'] = $customer_data['mobile'];
                    $data[$key]['cus_mobile2'] = $customer_data['mobile2'];
                    $data[$key]['cus_idnumber'] = $customer_data['id_number'];
                }
            }
            $data[$key]['type_name'] = 'Không biết';
            if ($value['refer_type'] == Account::REFER_TYPE_MARKETING) {
                $data[$key]['type_name'] = 'Marketing';
            }

            if ($value['refer_type'] == Account::REFER_TYPE_VOUCHER) {
                $data[$key]['type_name'] = 'Voucher';
            }

            if ($value['refer_type'] == Account::REFER_TYPE_SYSTEMS) {
                $data[$key]['type_name'] = 'System';
            }
            $data[$key]['refer_name'] = '';
            $mc = MarketingCampaign::find()->andWhere(['=', 'id', $value['refer_id']])->one();
            if ($mc != null) {
                $data[$key]['refer_name'] = $mc->name;
            }
        }

        $dataPage->data = $data;
//        $supplock = $query->andWhere("status =2");
//        $dataPage->totalLock = $supplock->count();
        $dataPage->pagination = $paging;

        return $dataPage;

    }
}