<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/2
 */

namespace app\common\model;

use Overtrue\Pinyin\Pinyin;
use think\Model;

class Area extends Model
{
    public function getCityByName($name)
    {

        $citycode = config('setting.default_location_code');

        if ($name) {
            $where = [];
            $where['status'] = 1;
            $where['city_name'] = $name;
            $where['county_name'] = '';

            $c = $this->where($where)->find();
            if ($c) {
                $citycode = $c['id'];

                // 检查是否开放的省份
                $openprovince = config('setting.province_open');
                if (!(in_array($c['province_code'], $openprovince))) {
                    $citycode = config('setting.default_location_code');
                }
            }
        }

        return $this->getAreaByCityCode($citycode);
    }


    public function getAreaByCityCode($city_code)
    {
        if ($city_code) {
            $where = [];
            $where['status'] = 1;
            $where['city_code'] = $city_code;
            $where['county_code'] = '';

            $c = $this->where($where)->find();
            if ($c) {
                // 检查是否开放的省份
                $openprovince = config('setting.province_open');
                if (!(in_array($c['province_code'], $openprovince))) {
                    return null;
                }
                return ['city' => (new \app\common\base\Area())->foramtCityName($c['city_name']), 'code' => $c['id'] , 'province_code'=>$c['province_code'] , 'city_code'=>$c['city_code'] , 'county_code'=>$c['county_code']];
            }
        }
        return null;
    }

    public function updateCityLetter()
    {
        $pinyin = new Pinyin();


        $where = [];
        $where['city_name'] = ['<>', ''];
        $where['county_name'] = "";

        $list = $this->where($where)->select();
        foreach ($list as $c) {
            $cs = $pinyin->abbr($c['city_name']);
            if ($cs) {
                $letter = strtoupper(substr($cs, 0, 1));

                $w = [];
                $w['id'] = $c['id'];

                $this->where('id', $c['id'])->update(['letter' => $letter]);
            }
        }
    }


    public function updateCityPinYin()
    {
        $pinyin = new Pinyin();


        $where = [];
        $where['city_name'] = ['<>', ''];
        $where['county_name'] = "";

        $list = $this->where($where)->select();
        foreach ($list as $c) {
            $cs = $pinyin->abbr($c['city_name']);
            $fn = $pinyin->permalink($c['city_name'],"");
            if (($cs) && ($fn)) {

                $w = [];
                $w['id'] = $c['id'];

                $this->where('id', $c['id'])->update(['pinyin_1' => $cs , 'pinyin_2'=>$fn]);
            }
        }
    }


    public function getCityList()
    {
        $where = [];

        $where['province_code'] = ['in', config('setting.province_open')];
        $where['city_code'] = ['<>', ''];
        $where['county_code'] = ['=', ''];

        $list = $this->where($where)->select();
        return $list;
    }
    public function getCityNameByCode($cityCode)
    {
        $cityName = '';
        if ($cityCode) {
            $where = [];
            $where['status'] = 1;
            $where['city_code'] = $cityCode;
            $where['county_name'] = '';

            $c = $this->where($where)->find();
            if ($c) {
                if(in_array($cityCode,[110100,120100,500100,500200,310100])){
                    $cityName = $c['province_name'];
                }else{
                    $cityName = $c['city_name'];
                }
            }
        }

        return $cityName;
    }
}