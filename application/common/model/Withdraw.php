<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11 0011
 * Time: 13:36
 */

namespace app\common\model;

use think\Model;
class Withdraw extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 提现记录详情
     * @param $id
     * return array
     */
    public function getWithdrawById($id){
        if($id){
            $data = $this->alias('a')
                    ->  field('a.*,b.user_nickname,c.nickname,b.wx_openid')
                    ->  join('__USERS__ b','a.user_id = b.id','left')
                    ->  join('__ADMIN__ c','a.admin_id = c.id','left')
                    ->  where('a.id',$id)
                    ->  find();
            return $data?$data:null;
        }
        return null;
    }

    /**
     * 更新提现信息
     * @param $id
     * @param $data
     * return string
     */
    public function updateWithdraw($id,$data){
        return $this->where('id',$id)->update($data);
    }

    /**
     * 获取限定时间内未审核发放的提现申请
     * @param $timestamp
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getWaitingWithdrawList($timestamp){
        $where = [];
        $where['status'] = config('state.state_ok');
        $where['withdraw_status'] = config('state.state_ok');
        $where['createtime'] = ['elt',$timestamp];
        return $this->where($where)->order('createtime asc')->select();
    }
}

