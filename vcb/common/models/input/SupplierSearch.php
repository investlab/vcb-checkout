<?php

namespace common\models\input;


use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\business\SupplierProductBussiness;
use common\models\business\ZoneBusiness;
use common\models\db\Supplier;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class SupplierSearch extends Model
{
    public $time_create_from;
    public $time_create_to;
    public $name;
    public $code;
    public $status;
    public $delivery_status;
    public $supplier_contact;
    public $zone_id;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'delivery_status', 'status', 'zone_id'], 'integer'],
            [['name', 'code', 'supplier_contact'], 'string'],
            [['time_create_from', 'time_create_to'], 'safe'],
            [['time_create_from', 'time_create_to'], 'date', 'format' => 'dd-mm-yyyy'],
        ];
    }


    public function search()
    {
        $query = Supplier::find()->orderBy('supplier.time_created desc')->groupBy('supplier.id');
        $query->joinWith('supplier_contact');

        $errors = [];
        if ($this->time_create_from != null && trim($this->time_create_from) != "") {
            if (!Validation::isDate($this->time_create_from)) {
                $errors[] = 'Ngày tạo từ không đúng định dạng';
            }
        }
        if ($this->time_create_to != null && trim($this->time_create_to) != "") {
            if (!Validation::isDate($this->time_create_to)) {
                $errors[] = 'Ngày tạo đến không đúng định dạng';
            }
        }

        if ($this->time_create_from != null && trim($this->time_create_from) != "") {
            $time_create_from = FormatDateTime::toTimeBegin($this->time_create_from);
            $query->andWhere(['>=', 'supplier.time_created', $time_create_from]);
        }
        if ($this->time_create_to != null && trim($this->time_create_to) != "") {
            $time_create_to = FormatDateTime::toTimeEnd($this->time_create_to);
            $query->andWhere(['<=', 'supplier.time_created', $time_create_to]);
        }

        if ($this->name != null && trim($this->name) != "") {
            $query->andWhere(['LIKE', 'supplier.name', trim($this->name)]);
        }
        if ($this->code != null && trim($this->code) != "") {
            $query->andWhere(['LIKE', 'supplier.code', trim($this->code)]);
        }

        if ($this->status > 0) {
            $query->andWhere(['=', 'supplier.status', $this->status]);
        }
        if ($this->delivery_status > 0) {
            $query->andWhere(['=', 'supplier.delivery_status', $this->delivery_status]);
        }
        if ($this->supplier_contact != null && trim($this->supplier_contact) != "") {
            $query->andWhere(['LIKE', 'supplier_contact.name', trim($this->supplier_contact)]);
            $query->orWhere(['LIKE', 'supplier_contact.email', trim($this->supplier_contact)]);
            $query->orWhere(['LIKE', 'supplier_contact.phone', trim($this->supplier_contact)]);
        }
        if ($this->zone_id > 0) {
            $zone = ZoneBusiness::getListWardsIdByZoneId($this->zone_id);
            if ($zone != null) {
                $query->andWhere('supplier.zone_id IN (' . implode(',', $zone) . ')');
            }
        }


        $dataPage = new DataPage();

        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());

        $supplier_list = $query->asArray()->all();
        foreach ($supplier_list as $keyS => $dataS) {
            // Tên địa chỉ
            $zone_name = null;
            if (isset($dataS['zone_id']) && $dataS['zone_id'] > 0 && $dataS != null) {
                $zone_id = $dataS['zone_id'];
                $wardsList = ZoneBusiness::getByID($zone_id);
                if ($wardsList != null && $wardsList->level == 4) {
                    $district_id = $wardsList->parent_id;
                    $districtList = ZoneBusiness::getByID($district_id);
                    if ($districtList != null) {
                        $zone_id = $districtList->parent_id;
                        $city = ZoneBusiness::getByID($zone_id);
                        if ($city != null) {
                            $zone_name = $wardsList->name . '-' . $districtList->name . '-' . $city->name;
                        } else {
                            $zone_name = $wardsList->name . '-' . $districtList->name;
                        }
                    }
                } else if ($wardsList != null && $wardsList->level == 3) {
                    $zone_id = $wardsList->parent_id;
                    $city = ZoneBusiness::getByID($zone_id);
                    if ($city != null) {
                        $zone_name = $wardsList->name . '-' . $city->name;
                    } else {
                        $zone_name = $wardsList->name;
                    }
                }
            }
            $supplier_list[$keyS]['zone_name'] = $zone_name;
        }

        $dataPage->data = $supplier_list;

        $supplock = $query->andWhere("supplier.status = 2");
        $dataPage->totalsuppliernew = $supplock->count();
        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;

    }


} 