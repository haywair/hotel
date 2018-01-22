<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8 0008
 * Time: 11:31
 */

namespace app\common\model;

use think\Model;
class OrderCleanScore extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 用户订单问卷评分数量   *
     * @param $order_sn
     * @param $user_id
     * @return int|null|string
     */
    public function getUserScoreNumOrderSn($order_sn){
        if($order_sn){
            return $this-> where('clean_order_sn',$order_sn)->count();
        }
        return null;
    }

    /**
     * 用户保洁订单问卷评分总数
     * @param $order_sn
     * @param $user_id
     * @return null
     */
    public function getUserScoreTotalOrderSn($order_sn){
        if($order_sn){
            $scoreData =  $this->field('sum(score) as score_total')
                       -> where('clean_order_sn',$order_sn)
                       -> select();
            return $scoreData?$scoreData[0]['score_total']:0;
        }
        return null;
    }
    public function getOrderCleanNumBySn($clean_order_sn,$bnb_order_sn){
        if(!$clean_order_sn || !$bnb_order_sn){
            return null;
        }
        $count = $this->where('clean_order_sn',$clean_order_sn)->where('bnb_order_sn',$bnb_order_sn)->where('status',
            config('state.state_ok'))->count();
        return $count;
    }
}