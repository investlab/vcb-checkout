<?php
namespace common\components\libs;

class GoogleMap
{
    public static function getPositionByAddress($address)
    {
        $url = 'http://maps.google.com/maps/api/geocode/json?address=' . urlencode($address);
        $result = self::_call($url);
        if ($result != false) {
            if (isset($result['results'][0]['geometry']['location']['lat']) && isset($result['results'][0]['geometry']['location']['lng'])) {
                return array(
                    'latitude' => $result['results'][0]['geometry']['location']['lat'],
                    'longitude' => $result['results'][0]['geometry']['location']['lng'],
                );
            }
        }
        return false;
    }

    public static function getDistanceByAddress($origin_address, $destination_address, $mode = 'driving')
    {
        $origin_position = self::getPositionByAddress($origin_address);
        if ($origin_position != false) {
            $destination_position = self::getPositionByAddress($destination_address);
            if ($destination_position != false) {
                return self::getDistanceByPosition($origin_position['latitude'], $origin_position['longitude'], $destination_position['latitude'], $destination_position['longitude'], $mode);
            }
        }
        return false;
    }

    /**
     *
     * @param type $origin_latitude
     * @param type $origin_longitude
     * @param type $destination_latitude
     * @param type $destination_longitude
     * @param type $mode = driving
     * @return false: error | number met : success
     */
    public static function getDistanceByPosition($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $mode = 'driving')
    {
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $origin_latitude . ',' . $origin_longitude . '&destinations=' . $destination_latitude . ',' . $destination_longitude . '&mode=' . $mode . '&language=pl-PL';
        $result = self::_call($url);
        if ($result != false) {
            if (isset($result['rows'][0]['elements'][0]['status']) && $result['rows'][0]['elements'][0]['status'] == 'OK' && isset($result['rows'][0]['elements'][0]['distance']['value'])) {
                return $result['rows'][0]['elements'][0]['distance']['value'] / 1000;
            }
        }
        return false;
    }

    private static function _call($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("cache-control: no-cache"));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if (!$err) {
            $result = json_decode($response, false);
            if ($result['status'] == 'OK') {
                return $result;
            }
        }
        return false;
    }
}

