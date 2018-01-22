<?php

namespace app\admin\model;

use think\Model;

class Users extends Model
{

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    //用户信息
    public function getUsersIdUsername($condition=[]){
        $users = $this->where($condition)->column('user_nickname','id');
        return $users?$users:'';
    }

}
