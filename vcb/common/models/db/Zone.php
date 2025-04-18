<?php

namespace common\models\db;

use common\components\utils\Strings;
use common\components\utils\Validation;
use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "zone".
 *
 * @property integer $id
 * @property integer $zone_id
 * @property Strings $name
 * @property Strings $code
 * @property integer $position
 * @property integer $remote
 * @property integer $parent_id
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class Zone extends MyActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    const NEAR = 1;
    const FAR = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zone_id', 'name', 'code'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['zone_id', 'position', 'remote', 'parent_id', 'left', 'right', 'level', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'zone_id' => 'Zone ID',
            'name' => 'Tên',
            'code' => 'Mã',
            'position' => 'Vị trí',
            'remote' => 'Remote',
            'parent_id' => 'Parent ID',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getRemote()
    {
        return array(
            self::NEAR => 'Gần',
            self::FAR => 'Xa',
        );
    }

    protected function _setData($data_info)
    {
        $data = array();
        foreach ($data_info as $row) {
            $data[$row['parent_id']][] = $row;
        }
        return $data;
    }

    protected function _updateIndexCategory($table, $id = false)
    {
        $level = 2;
        $index = 0;
        $parent_id = 0;
        $data_info = Tables::selectAllDataTable($table, "1", "position ASC ");
        if ($data_info != false) {
            $data = $this->_setData($data_info);
            $queries = $this->_getQueryUpdateIndexCategoryByData($data, $table, $parent_id, $index, $level);
            if (!empty($queries)) {
                $connection = Yii::$app->getDb();
                $transaction = $connection->beginTransaction();
                foreach ($queries as $sql) {
                    set_time_limit(60);
                    $command = $connection->createCommand($sql);
                    $result = $command->execute();
                }
                $transaction->commit();
            }
        }
        return true;
    }

    protected function _getQueryUpdateIndexCategoryByData($data, $table, $parent_id = 0, &$index = 0, $level = 1)
    {
        $result = array();
        if (isset($data[$parent_id]) && !empty($data[$parent_id])) {
            foreach ($data[$parent_id] as $row) {
                set_time_limit(60);
                $index++;
                $result["id_" . $row['id']] = "UPDATE $table SET $table.level = $level, $table.left = $index, ";
                $temp = $this->_getQueryUpdateIndexCategoryByData($data, $table, $row['id'], $index, $level + 1);
                $index++;
                $result["id_" . $row['id']] .= "$table.right = $index WHERE $table.id = " . $row['id'] . " ;";
                if (!empty($temp)) {
                    $result = array_merge($result, $temp);
                }
            }
        }
        return $result;
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
            case "name":
                $name = Strings::_convertToSMS($this->name);
                if (!Validation::checkStringAndNumberSpace($name)) {
                    $this->addError($attribute, 'Tên không hợp lệ.');
                }
                break;
        }
    }

    public static function getCities(&$city_id = 0, $result = array())
    {
        $zone_info = Tables::selectAllDataTable(self::tableName(), "level = 2 AND status = " . self::STATUS_ACTIVE, "zone.left ASC ", "id");
        if ($zone_info != false) {
            foreach ($zone_info as $row) {
                if ($city_id == 0) {
                    $city_id = $row['id'];
                }
                $result[$row['id']] = Strings::strip($row['name']);
            }
        }
        return $result;
    }

    public static function getDistricts($city_id, &$district_id = 0, $result = array())
    {
        $zone_info = Tables::selectAllDataTable(self::tableName(), "parent_id = $city_id AND level = 3 AND status = " . self::STATUS_ACTIVE, "zone.left ASC ", "id");
        if ($zone_info != false) {
            foreach ($zone_info as $row) {
                if ($district_id == 0) {
                    $district_id = $row['id'];
                }
                $result[$row['id']] = Strings::strip($row['name']);
            }
        }
        return $result;
    }

    public static function getZones($district_id, &$zone_id = 0, $result = array())
    {
        $zone_info = Tables::selectAllDataTable(self::tableName(), "parent_id = $district_id AND status = " . self::STATUS_ACTIVE, "zone.left ASC ", "id");
        if ($zone_info != false) {
            foreach ($zone_info as $row) {
                if ($zone_id == 0) {
                    $zone_id = $row['id'];
                }
                $result[$row['id']] = Strings::strip($row['name']);
            }
        }
        return $result;
    }

    public static function getDistrictInSaleArea($city_id, $zone_ids, $result = array())
    {

        if (!empty($zone_ids)) {
            $zone_info = Tables::selectAllDataTable(self::tableName(),
                "parent_id = $city_id AND status = " . self::STATUS_ACTIVE .
                " AND id NOT IN ( " . implode(',', $zone_ids) . ")", "zone.left ASC ", "id");
        } else {
            $zone_info = Tables::selectAllDataTable(self::tableName(), "parent_id = $city_id AND status = " . self::STATUS_ACTIVE, "zone.left ASC ", "id");
        }
        if ($zone_info != false) {
            foreach ($zone_info as $row) {
//                if ($zone_id == 0) {
//                    $zone_id = $row['id'];
//                }
                $result[$row['id']] = Strings::strip($row['name']);
            }
        }
        return $result;
    }

    public static function getName($zone_id)
    {
        $zone_info = Tables::selectOneDataTable("zone", "id = $zone_id ");
        if ($zone_info != false) {
            if ($zone_info['level'] == 2) {
                return Strings::strip($zone_info['name']);
            } elseif ($zone_info['level'] == 3) {
                $city_info = Tables::selectOneDataTable("zone", "id = " . $zone_info['parent_id'] . " ");
                if ($city_info != false) {
                    return Strings::strip($city_info['name']);
                }
            } elseif ($zone_info['level'] == 4) {
                $city_info = Tables::selectOneDataTable("zone", "id IN (SELECT parent_id FROM zone WHERE id = " . $zone_info['parent_id'] . ") ");
                if ($city_info != false) {
                    return Strings::strip($city_info['name']);
                }
            }
        }
        return '';
    }

    public static function getCityId($zone_id)
    {
        $zone_info = Tables::selectOneDataTable("zone", "id = $zone_id AND level IN (3,4) ");
        if ($zone_info != false) {
            if ($zone_info['level'] == 3) {
                return $zone_info['parent_id'];
            } elseif ($zone_info['level'] == 4) {
                $zone_info = Tables::selectOneDataTable("zone", "id = " . $zone_info['parent_id'] . " ");
                if ($zone_info != false) {
                    return $zone_info['parent_id'];
                }
            }
        }
        return 0;
    }

    public static function getDistrictId($zone_id)
    {
        $zone_info = Tables::selectOneDataTable("zone", "id = $zone_id AND level = 4 ");
        if ($zone_info != false) {
            return $zone_info['parent_id'];
        }
        return 0;
    }

    public static function getNameByZoneIds($zone_ids)
    {
        $result = array();
        $zone_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $zone_ids) . ") ", "", "id");
        if ($zone_info != false) {
            $fullnames = array();
            $district_ids = array();
            $city_ids = array();
            foreach ($zone_info as $row) {
                $fullnames[$row['id']][] = Strings::strip($row['name']);
                if ($row['level'] == 3) {
                    $city_ids[$row['id']] = $row['parent_id'];
                } elseif ($row['level'] == 4) {
                    $district_ids[$row['id']] = $row['parent_id'];
                }
            }
            if (!empty($district_ids)) {
                $district_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $district_ids) . ") ", "", "id");
                if ($district_info != false) {
                    foreach ($district_ids as $zone_id => $district_id) {
                        if (array_key_exists($district_id, $district_info)) {
                            $city_ids[$zone_id] = intval(@$district_info[$district_id]['parent_id']);
                            $fullnames[$zone_id][] = Strings::strip(@$district_info[$district_id]['name']);
                        }
                    }
                }
            }
            if (!empty($city_ids)) {
                $city_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $city_ids) . ") ", "", "id");
                if ($city_info != false) {
                    foreach ($city_ids as $zone_id => $city_id) {
                        if (array_key_exists($city_id, $city_info)) {
                            $fullnames[$zone_id][] = Strings::strip(@$city_info[$city_id]['name']);
                        }
                    }
                }
            }

            foreach ($fullnames as $zone_id => $names) {
                if ($names) {
                    $result[$zone_id] = implode(', ', $names);
                }
            }
        }
        return $result;
    }

    public static function getNameByZoneId($zone_id)
    {
        $fullname = null;
        $zone_info = Tables::selectOneDataTable("zone", "id = $zone_id");
        if ($zone_info != false) {
            $fullname = null;
            $district_id = null;
            $city_id = null;
            $fullname .= Strings::strip($zone_info['name']);

            if ($zone_info['level'] == 3) {
                $city_id = $zone_info['parent_id'];
            } elseif ($zone_info['level'] == 4) {
                $district_id = $zone_info['parent_id'];
            }
        }
        if (!empty($district_id)) {
            $district_info = Tables::selectOneDataTable("zone", "id = $district_id ");
            if ($district_info != false) {
                $city_id = intval(@$district_info['parent_id']);
                $fullname .= ',' . Strings::strip(@$district_info['name']);
            }

        }
        if (!empty($city_id)) {
            $city_info = Tables::selectOneDataTable("zone", "id = $city_id ");
            if ($city_info != false) {
                $fullname .= ',' . Strings::strip(@$city_info['name']);
            }
        }

        return $fullname;
    }

    public static function getCityName($zone_ids)
    {
        $result = array();
        $zone_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $zone_ids) . ") ", "", "id");
        if ($zone_info != false) {
            $fullnames = array();
            $district_ids = array();
            $city_ids = array();
            foreach ($zone_info as $row) {
                $fullnames[$row['id']][] = Strings::strip($row['name']);
                if ($row['level'] == 3) {
                    $city_ids[$row['id']] = $row['parent_id'];
                } elseif ($row['level'] == 4) {
                    $district_ids[$row['id']] = $row['parent_id'];
                }
            }
            if (!empty($district_ids)) {
                $district_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $district_ids) . ") ", "", "id");
                if ($district_info != false) {
                    foreach ($district_ids as $zone_id => $district_id) {
                        if (array_key_exists($district_id, $district_info)) {
                            $city_ids[$zone_id] = intval(@$district_info[$district_id]['parent_id']);
                            $fullnames[$zone_id][] = Strings::strip(@$district_info[$district_id]['name']);
                        }
                    }
                }
            }
            if (!empty($city_ids)) {
                $city_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $city_ids) . ") ", "", "id");
                if ($city_info != false) {
                    foreach ($city_ids as $zone_id => $city_id) {
                        if (array_key_exists($city_id, $city_info)) {
                            $fullnames[$zone_id][] = Strings::strip(@$city_info[$city_id]['name']);
                        }
                    }
                }
            }

            foreach ($fullnames as $zone_id => $names) {
                if ($names) {
                    if (isset($names[2]) && $names[2] != null) {
                        $result[$zone_id] = $names[2];
                    }
                }
            }
        }
        return $result;
    }

    public static function getCityNameByZoneID($zone_id)
    {
        $fullname = null;
        $cityName = null;
        $zone_info = Tables::selectOneDataTable("zone", "id = $zone_id");
        if ($zone_info != false) {
            $fullname = null;
            $district_id = null;
            $city_id = null;
            $fullname .= Strings::strip($zone_info['name']);

            if ($zone_info['level'] == 3) {
                $city_id = $zone_info['parent_id'];
            } elseif ($zone_info['level'] == 4) {
                $district_id = $zone_info['parent_id'];
            }
        }
        if (!empty($district_id)) {
            $district_info = Tables::selectOneDataTable("zone", "id = $district_id ");
            if ($district_info != false) {
                $city_id = intval(@$district_info['parent_id']);
                $fullname .= ',' . Strings::strip(@$district_info['name']);
            }

        }
        if (!empty($city_id)) {
            $city_info = Tables::selectOneDataTable("zone", "id = $city_id ");
            if ($city_info != false) {
                $cityName = Strings::strip(@$city_info['name']);
                $cityID = $city_id;
            }
        }

        $city = array(
            'city_id' => $city_id,
            'city_name' => $cityName
        );

        return $city;
    }

    public static function getCityIdsByZoneIds($zone_ids, &$district_ids = array(), &$ward_ids = array())
    {
        $city_ids = array();
        $zone_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $zone_ids) . ") ", "", "id");
        if ($zone_info != false) {
            foreach ($zone_info as $row) {
                if ($row['level'] == 2) {
                    $city_ids[$row['id']] = $row['id'];
                } elseif ($row['level'] == 3) {
                    $city_ids[$row['id']] = $row['parent_id'];
                    $district_ids[$row['id']] = $row['id'];
                } elseif ($row['level'] == 4) {
                    $district_ids[$row['id']] = $row['parent_id'];
                    $ward_ids[$row['id']] = $row['id'];
                }
            }
            if (!empty($ward_ids)) {
                $ward_info = Tables::selectAllDataTable("zone", "id IN(" . implode(',', $ward_ids) . ") ", "", "id");
                if ($ward_info != false) {
                    $parent_ids = array();
                    foreach ($ward_info as $zone_id => $row) {
                        $parent_ids[$zone_id] = $row['parent_id'];
                    }
                    $district_info = Tables::selectAllDataTable("zone", "id IN (" . implode(',', $parent_ids) . ") ", "", "id");
                    foreach ($parent_ids as $zone_id => $parent_id) {
                        if (isset($district_info[$parent_id]['parent_id'])) {
                            $city_ids[$zone_id] = $district_info[$parent_id]['parent_id'];
                        }
                    }
                }
            }
        }
        return $city_ids;
    }

    public static function getCityKeyByCityName($city_name)
    {
        $city_key = trim($city_name);
        return $city_key;
    }

    public static function getCitiesForCheckImport()
    {
        $cities = array();
        $city_info = Tables::selectAllDataTable("zone", "level = 2 AND status = " . Zone::STATUS_ACTIVE);
        if ($city_info != false) {
            foreach ($city_info as $row) {
                $cities[self::getCityKeyByCityName($row['name'])] = $row;
            }
        }
        return $cities;
    }

    public static function getCodeByName($name)
    {
        $name = Strings::_convertToSMS(trim($name));
        $name = str_replace(' ', '-', $name);
        $name = str_replace('--', '-', $name);
        return strtoupper($name);
    }
}
