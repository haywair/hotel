<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/13
 */

namespace app\common\base;

use app\common\model\Area as areamodel;

class Area
{
    public function getCityList()
    {
        $data = [];


        $citylist = (new areamodel())->getCityList();
        if ($citylist) {
            foreach ($citylist as $c) {
                $d = [];
                $d['city_code'] = $c['city_code'];
                $d['city_name'] = $this->foramtCityName($c['city_name']);
                $d['map_lng'] = $c['map_lng'];
                $d['map_lat'] = $c['map_lat'];
                $d['letter'] = $c['letter'];
                $d['pinyin_1'] = $c['pinyin_1'];
                $d['pinyin_2'] = $c['pinyin_2'];
                $data[$c['letter']][] = $d;
            }
        }

        ksort($data);
        return $data;
    }


    public function foramtCityName($cityname)
    {
        $regex = '/(.+)((城区)|市|(地区)|盟)$/';

        if (preg_match($regex, $cityname, $matches)) {

            $cityname = $matches[1];
        }

        return $cityname;
    }

}