<?php


namespace common\models\input;


use common\components\libs\Tables;
use yii\base\Model;
use yii\data\Pagination;
use common\models\db\LinkCard;
use common\models\output\DataPage;

class LinkCardSearch extends Model
{
    public $merchant_id;
    public $card_type;
    public $secure_type;
    public $partner_payment_id;
    public $customer_email;
    public $status;

    public $pageSize;
    public $page;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status', 'merchant_id', 'card_type', 'secure_type', 'partner_payment_id'], 'integer'],
            [['customer_email'], 'email'],
        ];
    }

    function getConditions(&$errors = [])
    {
        $conditions = [];

        if (intval($this->merchant_id) > 0) {
            $conditions[] = "merchant_id = " . trim($this->merchant_id);
        }
        if (intval($this->card_type) > 0) {
            $conditions[] = "card_type = " . trim($this->card_type);
        }
        if (intval($this->secure_type) > 0) {
            $conditions[] = "secure_type = " . trim($this->secure_type);
        }
        if (intval($this->partner_payment_id) > 0) {
            $conditions[] = "partner_payment_id = " . trim($this->partner_payment_id);
        }
        if (!empty($this->customer_email)) {
            $conditions[] = "customer_email = '" . trim($this->customer_email) ."'";
        }
        if (!empty($this->bank)) {
            $conditions[] = "bank = " . trim($this->bank);
        }
        if (intval($this->status) > 0) {
            $conditions[] = "status = " . trim($this->status);
        }

        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = 1;
        }

        return $conditions;
    }

    public function search()
    {
        $conditions = $this->getConditions($errors);
        //------------
        if ($conditions != false) {
            $count = Tables::selectCountDataTable("card_token", $conditions);
        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $link_cards = Tables::selectAllDataTable('card_token', $conditions, 'id DESC', 'id', $paging->getLimit(), $paging->getOffset());

            if (!empty($link_cards)) {
                $dataPage->data = $link_cards;

                foreach ($dataPage->data as $key => $val) {
                    $dataPage->data[$key]['operators'] = LinkCard::getOperatorsByStatus($val);
                }
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;

        return $dataPage;
    }
}