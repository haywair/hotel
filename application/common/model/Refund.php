<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/9
 */

namespace app\common\model;

use think\Model;

class Refund extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public static $RefundType_Auto = "A";
    public static $RefundType_BnbOrder = "B";
    public static $RefundType_CleanOrder = "C";
    public static $RefundType_Deposit = "D";

    public static $RefundStatus_Deleted = -1;
    public static $RefundStatus_Disabled = 0;
    public static $RefundStatus_Waiting = 1;
    public static $RefundStatus_Failed = 8;
    public static $RefundStatus_Finish = 9;


    public function createRefund($refund_data)
    {
        if ($refund_data) {
            return $this->insertGetId($refund_data);
        }

        return null;
    }


    public function getRefundOrderBySn($refund_sn)
    {
        if ($refund_sn) {
            return $this->where('refund_sn', $refund_sn)->find();
        }
        return null;
    }

    public function getRefundOrderById($id)
    {
        if ($id) {
            return $this->where('id', $id)->find();
        }
        return null;
    }

    public function getRefundNum($condition=[]){
        return $this->where($condition)->count();
    }


    public function getAutoRefundList()
    {
        return $this->where('status', "=", self::$RefundStatus_Waiting)->where('refund_type', "=", self::$RefundType_Auto)->order('id','asc')->select();
    }
    public function getRefundOrderByBnbOrderDeposit($refund_sn)
    {
        if ($refund_sn) {
            return $this->where('refund_sn', $refund_sn)->where('refund_type','=',self:: $RefundType_Deposit)
                ->find();
        }
        return null;
    }
}