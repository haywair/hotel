<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/3
 */

namespace app\common\model;

use think\Model;

class Voucher extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public static $Voucher_Type_BNB = 1;
    public static $Voucher_Type_CLEAN = 2;

    public function getVoucherByIds($id_array = [])
    {
        $voucher_list = [];

        $where = [];
        $where['status'] = ['gt', 0];
        if ($id_array) {
            $where['id'] = ['in', $id_array];
        }

        $list = $this->where($where)->order('price_discount', 'desc')->order('end_time', 'asc')->select();
        if ($list) {
            foreach ($list as $v) {

                $vc = [];
                $vc['voucher_id'] = $v['id'];
                $vc['name'] = $v['name'];
                if ($v['type'] == 2) {
                    $vc['type'] = self::$Voucher_Type_CLEAN;
                } else {
                    $vc['type'] = self::$Voucher_Type_BNB;
                }

                if ($v['status'] == 1) {
                    $vc['use'] = true;
                } else {
                    $vc['use'] = false;
                }

                $vc['price_over'] = $v['price_over'];
                $vc['price_discount'] = $v['price_discount'];

                $vc['start_time'] = $v['start_time'];
                $vc['end_time'] = $v['end_time'];

                $now = time();
                if ($vc['use']) {

                    if ((($vc['start_time'] != 0) && ($vc['start_time'] > $now)) || (($vc['end_time'] != 0) && ($vc['end_time'] < $now))) {
                        $vc['use'] = false;
                    }
                }

                $voucher_list[$v['id']] = $vc;
            }
        }

        return $voucher_list;
    }

    public function getVoucherInfo($id)
    {
        return $this->where('id', $id)->find();
    }


    public function getExpiredVoucher()
    {
        $now = time();

        $list = $this->where('status', "=", 1)->where(function ($q) use ($now) {
            $q->where("end_time", '<>', 0)->where('end_time', "<=", $now);
        })->select();

        return $list;
    }

    public function useVoucher($id , $num =1)
    {
        $num = intval($num);
        return $this->save(['user_use_counter'=>['exp' , 'user_use_counter+'.$num]] ,['id'=>$id]);
    }
}