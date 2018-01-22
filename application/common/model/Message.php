<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17 0017
 * Time: 10:28
 */

namespace app\common\model;

use think\Model;
class Message extends Model
{

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    /**
     * 查询用户收到的消息
     * @param $userId
     * @param int $time 查询时间
     * @param string $relation 查询时间表达式 > < >= <= =
     * @param array $statecha
     */
    public function getMessageListUser($userId,$state=[],$time = 0,$relation = "=",$nums = 6,$page = 1){
        $wheretime_in = [];
        $wherestate_in = [];
        if($time){
            $wheretime_in = function ($query) use ($time,$relation) {
                $query->where("createtime", $relation, $time);
            };
        }
        if($state){
            $state = implode(',',$state);
            $wherestate_in = function ($query) use ($state) {
                $query->where("status", 'in', $state);
            };
        }
        $userIds = [0,$userId];

        $data = $this->where('to_userid','in',$userIds)->where(function ($query) use ($wheretime_in) {
                $query->where($wheretime_in);
            })->where(function ($query) use ($wherestate_in) {
                $query->where($wherestate_in);
            })->order('weigh desc,id desc')->paginate($nums , false , ['query'=>['page' , $page]]);
        return $data?$data:null;
    }

    public function getMessageInfoById($id){
        if($id){
            $data = $this
                -> alias('a')
                -> field('a.*,b.user_nickname as toName,c.user_nickname as fromName,d.name')
                -> join('__USERS__ b', 'a.to_userid = b.id', 'left')
                -> join('__USERS__ c', 'a.from_id = c.id', 'left')
                -> join('__MESSAGE_TYPE__ d', 'a.message_type_id = d.id', 'left')
                -> where('a.id',$id)
                -> find();
            return $data?$data:null;
        }
        return null;
    }
}