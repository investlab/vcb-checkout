<?php


namespace common\models\input;

use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Validation;
use common\models\db\InstallmentConfig;
use common\models\db\User;
use common\models\output\DataPage;
use yii\base\Model;
use yii\data\Pagination;

class InstallmentConfigSearch extends Model
{
    public $pageSize;
    public $page;
    public $merchant_id;
    public $card_accept;
    public $cycle_accept;

    public function rules()
    {
        return [
            [['merchant_id', 'card_accept', 'cycle_accept'], 'required'],
            ['merchant_id', 'integer']
        ];
    }

    function getConditions(&$errors = array())
    {
        $conditions = array();
        if ($this->merchant_id != null && trim($this->merchant_id) != "") {
            $conditions[] = "merchant_id=" . trim($this->merchant_id);
        }
        if ($this->card_accept != null && trim($this->card_accept) != "") {
            $conditions[] = "card_accept LIKE '%" . trim($this->card_accept) . "%' OR cycle_accept LIKE '%" . trim($this->card_accept) . "%'";
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
            $count = Tables::selectCountDataTable("installment_config", $conditions);

        } else {
            $count = 0;
        }

        $dataPage = new DataPage();
        $paging = new Pagination(['totalCount' => $count]);
        $paging->setPageSize($this->pageSize <= 0 ? 10 : $this->pageSize);
        $paging->setPage($this->page <= 0 ? 0 : ($this->page - 1));
        if ($conditions != false) {
            $installments = Tables::selectAllDataTable("installment_config", $conditions, "id DESC", "id", $paging->getLimit(), $paging->getOffset());
            if ($installments != false) {
                foreach ($installments as $key => $installment) {
                    $installments[$key]['card_accept'] = json_decode($installment['card_accept'], true);
                    $cycle_accept = json_decode($installment['cycle_accept'], true);
                    $installments[$key]['cycle_accept'] = [];
                    $installments[$key]['operators'] = InstallmentConfig::getOperators($installment['status']);
                    foreach ($cycle_accept as $key_cycle => $cycle) {
                        $text_cycle = null;
                        if (!empty($cycle)) {
                            foreach ($cycle as $key_item => $item) {
                                $text_cycle[] = array_values($item)[0];
                            }
                        }
                        $installments[$key]['cycle_accept'][$key_cycle] = $text_cycle;
                    }
                }
                $dataPage->data = $installments;
            } else {
                $dataPage->data = array();
            }
        } else {
            $dataPage->data = array();
        }

        $dataPage->pagination = $paging;
        $dataPage->errors = $errors;
        $months = Tables::selectAllDataTable('installment_cycle','', '','','','','name');
        if (empty($month)) {
            foreach ($months as $key => $month) {
                $dataPage->period[] = ucwords($month['name']);
            }
        }

        return $dataPage;
    }
}