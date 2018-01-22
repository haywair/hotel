<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/1
 */

namespace app\common\model;

use think\Model;

class Cleaninfo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function updateUserLocation($user_id, $lng, $lat)
    {
        $data = [];
        $data['map_lng'] = $lng;
        $data['map_lat'] = $lat;
        $data['mapupdate_time'] = time();

        $r = $this->where('users_id', $user_id)->update($data);
        return $r;
    }

    public function updateCleaninfoByUId($user_id,$data){
        $r = $this->where('users_id', $user_id)->update($data);
        return $r;
    }
    /**
     * 根据用户id获取保洁信息
     */
    public function getCleanerByUID($uid,$field="*"){
        $data = $this->alias('a')
            -> field('a.*,b.province_name,c.city_name')
            -> join('__AREA__ b','b.id = a.province_code','left')
            -> join('__AREA__ c','c.id = a.city_code','left')
            -> where(['users_id'=>$uid])->find();
        return $data?$data:'';
    }



    public function getAllCleanerMap()
    {
        $data = [];

        $list = $this->field(['users_id', 'position_lng', 'position_lat'])->select();
        if ($list) {
            foreach ($list as $l) {
                $d = [];
                $d['user_id'] = $l['users_id'];
                $d['geo_lng'] = $l['position_lng'];
                $d['geo_lat'] = $l['position_lat'];
                $data[] = $d;
            }
        }
        return $data;
    }

}