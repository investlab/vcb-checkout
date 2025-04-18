<?php

namespace common\models\business;


use common\models\db\Zone;

class ZoneBusiness
{
    public static function getByID($id)
    {
        return Zone::findOne(['id' => $id]);
    }

    public static function getDistrictByZone($zone_id)
    {
        return Zone::find()->where(['parent_id' => $zone_id])->all();
    }

    public static function getDistrictByZoneToArray($zone_id)
    {
        return Zone::find()->where(['parent_id' => $zone_id])->asArray()->all();
    }

    public static function getListWardsIdByZoneId($zone_id)
    {
        $zone = self::getByID($zone_id);
        $list_id = [];
        if ($zone != null) {
            $province = self::getDistrictByZoneToArray($zone_id);
            if ($province != null) {
                $list_id = self::getIdByListZone($province);
            }
        }

        return $list_id;
    }

    public static function getIdByListZone($list_zone)
    {
        $list_zone_id = [];
        if ($list_zone != null && count($list_zone) > 0) {
            $list_id = [];
            if (count($list_zone[0]) > 1) {
                foreach ($list_zone as $k => $v) {
                    $list_zone_id[] = $v['id'];
                }
                $data = Zone::find()->where('parent_id IN (' . implode(',', $list_zone_id) . ')')->asArray()->all();
            } else {
                $data = Zone::find()->where('parent_id IN (' . implode(',', $list_zone) . ')')->asArray()->all();
            }

            if ($data != null) {
                foreach ($data as $k1 => $v1) {
                    $list_id[] = $v1['id'];
                }
            }
            return $list_id;
        } else {
            return $list_zone_id;
        }
    }

    public static function getParentId($zone_id)
    {
        $wards = self::getByID($zone_id);
        if ($wards != null && $wards->level == 4) {
            $wards_id = $wards->id;
            $district = self::getByID($wards->parent_id);
        } else {
            $district = self::getByID($zone_id);
            $wards_id = 0;
        }
        if ($district != null && $district->level == 3) {
            $district_id = $district->id;
            $province = self::getByID($district->parent_id);
        } else {
            $province = self::getByID($zone_id);
            $district_id = 0;
        }

        if ($province != null) {
            $province_id = $province->id;
        } else {
            $province_id = 0;
        }

        return [
            'wards_id' => $wards_id,
            'district_id' => $district_id,
            'province_id' => $province_id,
        ];
    }
} 