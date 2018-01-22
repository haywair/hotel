<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/3
 */

namespace app\common\model;

use think\Model;
class UserVoucher extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public static $VoucherStatus_Deleted = -1;
    public static $VoucherStatus_Disable = 0;
    public static $VoucherStatus_OK = 1;
    public static $VoucherStatus_Used = 2;
    public static $VoucherStatus_Expired = 3;

    public static $VoucherType_Bnb = 1;
    public static $VoucherType_Clean = 2;

    public function getUserVoucherList($user_id, $state = 99)
    {
        $user_voucher_list = [];

        $where = [];
        $where['users_id'] = $user_id;

        if ($state != 99) {
            $where['status'] = $state;
        } else {
            $where['status'] = ['gt', 0];
        }

        $list = $this->where($where)->select();
        if ($list) {
            $voucherlist = array_unique(array_column($list, 'voucher_id'));

            $now = time();
            $vlist = (new Voucher())->getVoucherByIds($voucherlist);
            if (($vlist) && (is_array($vlist))) {
                foreach ($vlist as $vid=>$vl)    {

                    foreach($list as $l)
                    {
                        if ($l['voucher_id'] == $vid)
                        {
                            if ($l['status'] != 1)
                            {
                                $vl['use'] = false;
                            }

                            if ((($vl['start_time'] != 0) && ($vl['start_time'] < $now)) || (($vl['end_time'] != 0) && ($vl['end_time'] > $now))) {
                                $vl['use'] = false;
                            }

                            $vl['bind_order_id'] = $l['bind_order_id'];
                            $vl['used_order_id'] = $l['used_order_id'];
                            $user_voucher_list[$l['id']] = $vl;
                        }
                    }

                }
            }


        }

        return $user_voucher_list;
    }


    public function getBestBnbVoucher($voucher_list, $room_amount)
    {
        $data = [];

        $best_price = 0;
        $best_expired = 0;
        $best_voucher_id = 0;

        $vlist_yes = [];
        $vlist_no = [];

        if (($voucher_list) && (is_array($voucher_list))) {

            $now = time();

            foreach ($voucher_list as $vid => $vou) {

                $u = false;

                if (($vou['use']) && ($vou['type'] == self::$VoucherType_Bnb)) {
                    if ((($vou['start_time'] == 0) || ($vou['start_time'] >= $now)) && (($vou['end_time'] == 0) || ($vou['end_time'] <= $now))) {
                        if (($vou['price_over'] == 0) || ($vou['price_over'] <= $room_amount)) {

                            $u = true;

                            if ($vou['price_discount'] > $best_price) {
                                $best_price = $vou['price_discount'];
                                $best_expired = $vou['end_time'];
                                $best_voucher_id = $vid;
                            }

                            if ($vou['price_discount'] == $best_price) {

                                if ($vou['end_time'] <= $best_expired) {
                                    $best_price = $vou['price_discount'];
                                    $best_expired = $vou['end_time'];
                                    $best_voucher_id = $vid;
                                }
                            }
                        }
                    }
                }

                $vou['id'] = $vid;
                if ($u) {
                    $vlist_yes[] = $vou;
                } else {
                    $vlist_no[] = $vou;
                }
            }
        }

        $data['best']['voucher_id'] = $best_voucher_id;
        $data['best']['price_discount'] = $best_price;
        $data['best']['expired'] = $best_expired;

        $data['list']['yes'] = $vlist_yes;
        $data['list']['no'] = $vlist_no;

        return $data;
    }


    public function setVoucherState($user_voucher_id, $status, $used_order_id = 0)
    {
        if ($user_voucher_id) {
            $data = [];
            $data['updatetime'] = time();
            $data['used_order_id'] = $used_order_id;
            $data['status'] = $status;

            return $this->save($data, ['id' => $user_voucher_id]);
        }

        return null;
    }
    public function getVoucherById($id){
        if($id) {
            $data = $this -> alias('a')
                -> field('a.*,b.user_nickname')
                -> join('__USERS__ b', 'a.users_id = b.id', 'left')
                -> where('a.id',$id)
                -> find();
            if($data) {
                $voucherInfo = (new Voucher())->getVoucherInfo($data['voucher_id']);
                $data['voucher'] = $voucherInfo;
                return $data;
            }
            return null;
        }
        return null;
    }
}