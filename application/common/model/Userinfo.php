<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\common\model;

use think\Model;

class Userinfo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getUserInfo($user_id)
    {
        if ($user_id) {
            return $this->where('users_id', "=", $user_id)->find();
        } else {
            return null;
        }
    }

    public function updateUserLocation($user_id, $lng, $lat)
    {
        $data = [];
        $data['map_lng'] = $lng;
        $data['map_lat'] = $lat;
        $data['mapupdate_time'] = time();

        $r = $this->where('users_id', $user_id)->update($data);
        if ($r) {
            session(config('session.MapUpdateTime'), time());
        }

        //更新保洁表位置信息
        $ui = (new Users())->getUserById($user_id);
        if (($ui) && ($ui['is_cleaner'])) {
            (new Cleaninfo())->updateUserLocation($user_id, $lng, $lat);
        }
        return $r;
    }

    /**
     * 用户总访问量
     * @return mixed
     */
    public function getUsersLoginNum(){
        $userInfo = $this->field('sum(login_numbers) as number')->select();
        return $userInfo[0]['number'];
    }

    /**
     * 获取时间段内访问量
     * @param $begin_time
     * @param $end_time
     * @return int|string
     */
    public function getTimeLoginNum($begin_time,$end_time){
        $where['lastlogin_time'] = array('between',$begin_time.','.$end_time);
        return $this->where($where)->count();
    }

    /*
     *  增加用户消费累计金额
     */
    public function setUserCostMoney($user_id , $money)
    {
        $data = [];
        $data['money'] = ['exp', 'money+'.$money];
        return $this->update($data , ['users_id'=>$user_id]);
    }
}