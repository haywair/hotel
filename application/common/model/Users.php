<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\common\model;

use think\Model;

class Users extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getUserByOpenId($wxopenid)
    {
        if ($wxopenid) {
            return $this->where('wx_openid', "=", $wxopenid)->find();
        } else {
            return null;
        }
    }

    public function getUserById($user_id)
    {
        if ($user_id) {
            return $this->where('id', "=", $user_id)->find();
        } else {
            return null;
        }
    }

    //用户详情
    public function getUsersInfo($user_id)
    {
        if ($user_id) {
            $field = 'a.*,b.map_lng,b.map_lat,b.mapupdate_time,lastlogin_time,b.login_numbers,b.money,b.lastmessage_time,b.lastlogin_ip';
            $data = $this->alias('a')
                ->field($field)
                ->join('__USERINFO__ b', 'a.id = b.users_id', 'left')
                ->where(['a.id' => $user_id])
                ->find();
            return $data ? $data : '';
        } else {
            return null;
        }
    }

    //更新用户信息
    public function updateUserById($user_id, $data)
    {
        if ($user_id) {
            return $this->where('id', '=', $user_id)->update($data);
        } else {
            return null;
        }
    }


    public function checkCleanerStatus($user_id_list)
    {
        if ($user_id_list) {
            $list = $this->alias('u')
                ->field("u.id")
                ->join('__CLEANINFO__ c', 'u.id = c.users_id', 'left')
                ->where(['u.id' => ['in', $user_id_list], 'u.status' => "1", 'u.is_cleaner' => "1", 'c.is_order' => "1"])
                ->select();


            if ($list) {
                $data = [];
                foreach ($list as $u) {
                    $data[] = $u['id'];
                }

                return $data;
            }
            return null;

        }
        return null;
    }

    /**
     * 总会员数
     * @return int|string
     */
    public function getUsersNumber(){
        return $this->where('status',config('state.state_ok'))->count();
    }

    /**
     * 获取时间段内注册量
     * @param $begin_time
     * @param $end_time
     * @return int|string
     */
    public function getTimeUsersNumber($begin_time,$end_time){
        $where['status'] = config('sate.state_ok');
        $where['createtime'] = array('between',$begin_time.','.$end_time);
        return $this->where($where)->count();
    }

}