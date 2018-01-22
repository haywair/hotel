<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3 0003
 * Time: 14:12
 */

namespace app\common\model;

use think\Model;
class Signup extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    //申请详情
    public function getSignupInfo($id){
        $field = 'a.*,b.user_avatar as avatar,c.province_name,d.city_name';
        $data = $this->alias('a')
            -> field($field)
            -> join('__USERS__ b','a.users_id = b.id','left')
            -> join('__AREA__ c','a.province_code = c.id','left')
            -> join('__AREA__ d','a.city_code = d.id','left')
            -> where(['a.id'=>$id])
            -> find();
        return $data?$data:'';

    }
    //更新申请用户信息
    public function updateUserById($id,$data){
        if($id){
            return $this->where('id','=',$id)->update($data);
        }else{
            return null;
        }
    }
    /**
     * 民宿申请列表分页
     */
    public function getSingupPageList($condition=[],$order='',$limit=0){
        if(!$order){
            $order = 'a.weigh desc,a.id desc';
        }
        $data = $this
            -> alias('a')
            -> field('a.*,b.province_name,c.city_name,d.user_nickname as landlord,e.nickname as manager_name')
            -> join('__AREA__ b','b.id = a.province_code','left')
            -> join('__AREA__ c','c.id = a.city_code','left')
            -> join('__USERS__ d','d.id = a.users_id','left')
            -> join('__ADMIN__ e','a.admin_id = e.id','left')
            -> where($condition)
            -> order($order)
            -> select();
        return $data?$data:'';
    }
}