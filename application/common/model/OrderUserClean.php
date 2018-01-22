<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/7
 */

namespace app\common\model;

use think\Model;

class OrderUserClean extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function createOrder($orderdata)
    {
        if ($orderdata) {
            $order_id = $this->insertGetId($orderdata);
            return $order_id;
        }
        return null;
    }

    public function getOrderListBySnList($order_sn_list)
    {

        if ($order_sn_list) {
            $where = [];
            $where['clean_order_sn'] = ['in', $order_sn_list];
            $orderlist = $this->where($where)->select();

            $data = [];
            if ($orderlist) {
                foreach ($orderlist as $order) {
                    $d['id'] = $order['id'];
                    $d['status'] = $order['status'];
                    $d['user_id'] = $order['user_id'];
                    $d['pay_total'] = $order['clean_order_total'];
                    $data[$order['clean_order_sn']] = $d;
                }
            }

            return $data;
        }
        return null;
    }


    public function getOrderBySn($clean_order_sn)
    {
        if ($clean_order_sn) {
            $where = [];
            $where['clean_order_sn'] = $clean_order_sn;
            return $this->where($where)->find();
        }

        return null;
    }

    public function getOrderByBnbOrderSn($order_sn)
    {
        if ($order_sn) {
            $where = [];
            $where['order_sn'] = $order_sn;
            return $this->where($where)->find();
        }

        return null;
    }
}