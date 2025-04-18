<?php

namespace common\components\libs;
use common\components\utils\Translate;
use common\models\business\ZoneBusiness;
use Yii;

class Weblib
{
    /*
     * Lấy thông tin chi tiết tài khoản đã đăng nhập vào hệ thống
     */

    public static function getUserLogined()
    {
        $data = false;
        $users = Yii::$app->getUser();
        if (isset($users->identity)) {
            $data = $users->identity;
        }

        return $data;
    }

    // Hiển thị thông báo khi hoàn thành 1 hành động.
    public static function showMessage($message, $url, $error = true)
    {
        echo '<div style="display:none;">
            <div id="ajax-result" class="modal fade" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title ajax-title">Thông báo</h4>
                        </div>
                        <div class="modal-body ajax-body">
                            <div class="alert ' . (Translate::get($error) ? 'alert-danger' : 'alert-success') . ' fade in">' . Translate::get($message) . '</div>
                            <div class="text-center">
                                <button class="btn btn-primary" data-dismiss="modal" >Đóng</button>
                            </div>
                            <script language="javascript">                            
                            $(".ajax-target").on("hidden.bs.modal", function () {
                                document.location.href = document.location.href;
                            });
                            </script>                           
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        echo '<script language="javascript">';
        echo 'alert("' . Translate::get($message) . '");';
        echo 'window.location.href = "' . $url . '";';
        echo '</script>';
        die;
    }

    /*
     * Tao mang du lieu cho combobox
     *
     * @input: table, columKey, columvalue, condition
     */

    public static function createComboTableArray($table, $columKey, $columValue, $condition = 1, $firstItemValue = 'Tất cả', $firstItem = true, $orderby = 'id')
    {
        $data = Tables::selectAllDataTable($table, $condition, $orderby);
        $dataReturn = array(
            '00' => $firstItemValue,
        );
        if ($firstItem == false) {
            $dataReturn = array();
        }
        if ($data) {
            foreach ($data as $c => $key) {
                $dataReturn[$key[$columKey]] = $key[$columValue];
            }
        }

        return $dataReturn;
    }


    public static function createComboApiArray($data, $columKey, $columValue, $firstItemValue = 'Tất cả', $firstItem = true)
    {
        $dataReturn = array(
            '00' => $firstItemValue,
        );
        if ($firstItem == false) {
            $dataReturn = array();
        }
        if ($data) {
            foreach ($data as $c => $key) {
                $dataReturn[$key[$columKey]] = $key[$columValue];
            }
        }

        return $dataReturn;
    }

    public static function getArraySelectBoxForTable($table, $col_key, $col_value, $condition = 1, $orderby = '', $result = array(), $remove_keys = array())
    {
        $data = Tables::selectAllDataTable($table, $condition, $orderby);
        if ($data != false) {
            return self::getArraySelectBoxForData($data, $col_key, $col_value, $result, $remove_keys);
        }
        return $result;
    }

    public static function getArraySelectBoxForData($data, $col_key, $col_value, $result = array(), $remove_values = array())
    {
        if (!empty($data)) {
            foreach ($data as $row) {
                if (!in_array($row[$col_key], $remove_values)) {
                    $result[$row[$col_key]] = self::_getValueArraySelectBox($row, $col_value);
                }
            }
        }
        return $result;
    }

    private static function _getValueArraySelectBox($row, $col_value)
    {
        if (is_array($col_value)) {
            $result = array();
            foreach ($col_value as $key) {
                $result[$key] = $row[$key];
            }
            return implode(' - ', $result);
        }
        return $row[$col_value];
    }

    public static function getValuesByKey($array, $key)
    {
        $result = array();
        if (!empty($array)) {
            foreach ($array as $row) {
                if ($row[$key] != null) {
                    $result[$row[$key]] = $row[$key];
                }
            }
        }
        return $result;
    }

    public static function makeCurrentUrl($change_params = array(), $remove_params = array())
    {
        $params = array(Yii::$app->controller->id . '/' . Yii::$app->controller->action->id);
        $current_params = isset($_GET) && !empty($_GET) ? $_GET : array();
        if (!empty($remove_params)) {
            foreach ($current_params as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $sub_key => $sub_value) {
                        if (in_array($sub_key, $remove_params)) {
                            unset($current_params[$key][$sub_key]);
                        }
                    }
                } else {
                    if (in_array($key, $remove_params)) {
                        unset($current_params[$key]);
                    }
                }
            }
        }
        if (!empty($change_params)) {
            foreach ($change_params as $key => $value) {
                $current_params[$key] = $value;
            }
        }
        if (!empty($current_params)) {
            foreach ($current_params as $key => $value) {
                $params[$key] = $value;
            }
        }
        return Yii::$app->urlManager->createAbsoluteUrl($params);
    }

    public static function redirectUrl($url)
    {
        header('Location:' . $url);
        die();
    }

    public static function getFullAddress($zone_id, $address)
    {
        $full_address = '';
        $wards = ZoneBusiness::getByID($zone_id);
        if ($wards != null && $wards->level == 4) {
            $wards_name = $wards->name;
            $district = ZoneBusiness::getByID($wards->parent_id);
        } else {
            $district = ZoneBusiness::getByID($zone_id);
            $wards_name = '';
        }
        if ($district != null && $district->level == 3) {
            $district_name = $district->name;
            $province = ZoneBusiness::getByID($district->parent_id);
        } else {
            $province = ZoneBusiness::getByID($zone_id);
            $district_name = '';
        }

        if ($province != null) {
            $province_name = $province->name;
        } else {
            $province_name = '';
        }
        if ($address != '') {
            $full_address = $address;
        }
        if ($full_address != '' && $wards_name != '') {
            $full_address .= ', ' . $wards_name;
        } else if ($full_address == '' && $wards_name != '') {
            $full_address = $wards_name;
        }
        if ($full_address != '' && $district_name != '') {
            $full_address .= ', ' . $district_name;
        } else if ($full_address == '' && $district_name != '') {
            $full_address = $district_name;
        }

        if ($full_address != '' && $province_name != '') {
            $full_address .= ', ' . $province_name;
        } else if ($full_address == '' && $province_name != '') {
            $full_address = $province_name;
        }

        return $full_address;
    }

    public static function getChecksum($data)
    {
        return md5($data . 'mac43ck5um');
    }

    public static function checkChecksum($data)
    {
        $checksum = Yii::$app->request->get('checksum');
        if (self::getChecksum($data) == $checksum) {
            return true;
        }
        return false;
    }

    public static function getIP()
    {
        $ip = trim(@$_SERVER['HTTP_X_REAL_IP']);
        if ($ip == '') {
            $ip = trim(@$_SERVER['REMOTE_ADDR']);
        }
        return $ip;
    }

    public static function getCardType($card_number)
    {
        if (substr($card_number, 0, 1) == '4') {
            return 'VISA';
        }
        if (substr($card_number, 0, 1) == '5') {
            return 'MASTERCARD';
        }
        return '';
    }
}
