<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 14:47
 */

namespace common\models\input;


use common\components\libs\Tables;
use common\models\db\PartnerCardType;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class PartnerCardTypeSearch extends Model
{
    public $partner_card_id;
    public $partner_card_code;
    public $bill_type;
    public $card_type_id;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'bill_type', 'card_type_id', 'partner_card_id'], 'integer'],
            [['partner_card_code'], 'string'],
        ];
    }


    public function search()
    {
        $query = PartnerCardType::find()->orderBy('time_created desc');

        if ($this->partner_card_code != null && trim($this->partner_card_code) != "") {
            $query->andWhere(['LIKE', 'partner_card_code', trim($this->partner_card_code)]);
        }

        if (intval($this->bill_type) > 0) {
            $query->andWhere(['=', 'bill_type', $this->bill_type]);
        }
        if (intval($this->partner_card_id) > 0) {
            $query->andWhere(['=', 'partner_card_id', $this->partner_card_id]);
        }
        if (intval($this->card_type_id) > 0) {
            $query->andWhere(['=', 'card_type_id', $this->card_type_id]);
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

        $list = $query->asArray()->all();
        if ($list != null) {
            foreach ($list as $key => $data) {
                $partner_card_id = $data['partner_card_id'];
                if (intval($partner_card_id) > 0) {
                    $partner_card = Tables::selectOneDataTable("partner_card", ['id = :id', "id" => $partner_card_id]);
                    $list[$key]['partner_card_info'] = $partner_card;
                }
                $card_type_id = $data['card_type_id'];
                if (intval($card_type_id) > 0) {
                    $card_type = Tables::selectOneDataTable("card_type", ['id = :id', "id" => $card_type_id]);
                    $list[$key]['card_type_info'] = $card_type;
                }
            }
        }
        $dataPage->data = $list;

        $supplock = $query->andWhere("status =" . PartnerCardType::STATUS_LOCK);
        $dataPage->totalLock = $supplock->count();
        $dataPage->pagination = $paging;

        return $dataPage;

    }

} 