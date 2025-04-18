<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/9/2016
 * Time: 9:43 AM
 */

namespace backend\controllers;


use backend\components\BackendController;
use common\components\libs\Weblib;
use common\components\utils\ObjInput;
use common\models\business\CustomerBusiness;
use common\models\business\OrganizationBusiness;

class GetAjaxController extends BackendController {
    public function actionGetDistrictByZoneId() {

        $zoneId = ObjInput::get('zone_id', 'int', '');
        $districtId = ObjInput::get('district_id', 'int', '');
        $district = Weblib::createComboTableArray('zone', 'id', 'name', "`parent_id` = '" . $zoneId . "' && `status` = 1 && `level` = 3 ", 'Quận/Huyện', true, 'name ASC');

        $option = '';
        if ($district) {
            foreach ($district as $c => $key) {
                if ($c == $districtId) {
                    $option .= '<option selected="selected" value="' . $c . '">' . $key . '</option>';
                } else {
                    $option .= '<option value="' . $c . '">' . $key . '</option>';
                }
            }
        }

        echo $option;
    }
    public function actionGetWardsByDistrictId() {

        $wardsId = ObjInput::get('wards_id', 'int', '');
        $districtId = ObjInput::get('district_id', 'int', '');
        $wards = Weblib::createComboTableArray('zone', 'id', 'name', "`parent_id` = '" . $districtId . "' && `status` = 1 && `level` = 4 ", 'Phường/Xã', true, 'name ASC');

        $option = '';
        if ($wards) {
            foreach ($wards as $c => $key) {
                if ($c == $wardsId) {
                    $option .= '<option selected="selected" value="' . $c . '">' . $key . '</option>';
                } else {
                    $option .= '<option value="' . $c . '">' . $key . '</option>';
                }
            }
        }

        echo $option;
    }
    public function actionGetInstallmentPeriod() {

        $installment_bank_id = ObjInput::get('installment_bank_id', 'int', '');
        $installment_period = ObjInput::get('installment_period', 'int', '');
        $period = Weblib::createComboTableArray('installment_bank_period', 'period', 'name', "`installment_bank_id` = '" . $installment_bank_id . "' && `status` = 4", 'Chọn kì trả góp', true, 'period ASC');

        $option = '';
        if ($period) {
            foreach ($period as $c => $key) {
                if ($c == $installment_period) {
                    $option .= '<option selected="selected" value="' . $c . '">' . $key . '</option>';
                } else {
                    $option .= '<option value="' . $c . '">' . $key . '</option>';
                }
            }
        }

        echo $option;
    }
    public function actionGetOrganizationByCustomer() {

        $customer_id = ObjInput::get('customer_id', 'int', '');
        $customer = CustomerBusiness::getByID($customer_id);

        $organization_id = null;
        if (isset($customer->customer_organization->organization_id) && $customer->customer_organization->organization_id != null) {
            $organization_id = $customer->customer_organization->organization_id;
        }

        $organization = OrganizationBusiness::getByID($organization_id);

        $organization_parent = null;
        $org_parent_id = null;
        if ($organization != null) {
            if ($organization->parent_id != null) {
                $org_parent_id = $organization->parent_id;
            }
        }


        if($org_parent_id != null){
            $organizationS = OrganizationBusiness::getAllOrgByParentID($org_parent_id);
        }else{
            $organizationS = OrganizationBusiness::getAllOrgByParentID($organization_id);
        }
        $option = '';
        if ($organizationS != null) {
            foreach ($organizationS as $c => $key) {
                if ($c == $organizationS) {
                    $option .= '<option selected="selected" value="' . $key['id'] . '">' . $key['name'] . '</option>';
                } else {
                    $option .= '<option value="' .$key['id'] . '">' . $key['name'] . '</option>';
                }
            }
        }
        echo $option;
    }

    public function actionGetSearchDistrictByZoneId() {

        $zoneId = ObjInput::get('zone_id', 'int', '');
        $districtId = ObjInput::get('district_id', 'int', '');
        $district = Weblib::createComboTableArray('zone', 'id', 'name', "`parent_id` = '" . $zoneId . "' && `status` = 1 && `level` = 3 ", 'Quận/Huyện', true, 'name ASC');

        $option = '';
        if ($district) {
            foreach ($district as $c => $key) {
                if ($c == $districtId) {
                    $option .= '<option selected="selected" value="' . $c . '">' . $key . '</option>';
                } else {
                    $option .= '<option value="' . $c . '">' . $key . '</option>';
                }
            }
        }

        echo $option;
    }
} 