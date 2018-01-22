<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/7
 */

namespace app\common\model;

use think\Model;

class Pay extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function createPay($paydata)
    {
        if ($paydata) {
            return $this->insertGetId($paydata);
        }
        return null;
    }

    public function getPayBySn($paysn)
    {
        if ($paysn) {
            return $this->where('pay_sn', $paysn)->find();
        }
        return null;
    }

    public function setPayStatus($paysn, $status, $pay_time, $trade_no, $trade_source)
    {

        $data = [];
        $data['status'] = $status;
        $data['pay_time'] = $pay_time;
        $data['trade_no'] = $trade_no;
        $data['trade_source'] = serialize($trade_source);
        $data['updatetime'] = time();

        $r = $this->where('pay_sn', $paysn)->update($data);
        if ($r) {
            return true;
        }

        return false;
    }

}