<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27 0027
 * Time: 13:21
 */
return [

    'state_delete' => '-1',
    'state_ok' => '1',
    'state_disable' => '0',
    'state_mark' => '2',
    'state' => [
         '已删除',
         '隐藏',
         '正常',
         '推荐'
    ],
    //订单状态
    'order_del_state' => -1,
    'order_cancel_state'=> 0,
    'order_create_state' => 10,
    'order_veriry_state' => 20,
    'order_pay_state' => 30,
    'order_msg_state' => 40,
    'order_part_state' => 45,
    'order_finish_state' => 50,

    'order_del_text'=> '已删除',
    'order_cancel_text'=> '已取消',
    'order_create_text' => '订单生成',
    'order_veriry_text' => '审核完成',
    'order_pay_text' => '支付成功',
    'order_msg_text' => '密码已发送',
    'order_finish_part_text' => '部分入住完成',
    'order_finish_text' => '已完成',

    'orderState' =>[
        -1 => '已删除',
        0 => '已取消',
        10 => '订单生成',
        20 => '审核完成',
        30 => '支付成功',
        40 => '密码已发送',
        45 => '部分入住完成',
        50 => '已完成'
    ],
    //保证金状态
    'deposit_state' =>  [
        0 => '未收取',
        1 => '已收取',
        2 => '部分退还',
        3 => '全额退还'
    ],
    'deposit_unpay_state' => 0,
    'deposit_pay_state' => 1,
    'deposit_partback_state' => 2,
    'deposit_allback_state' => 3,

    //订单_附加的保洁订单

    'addon_clean_state' => [
        0   =>  '已取消',
        10  =>  '等待预约',
        40  =>  '预约时间',
        50  =>  '订单完成'
    ],
    'addon_clean_cancel_state' => 0,
    'addon_clean_wait_state'    =>  10,
    'addon_clean_order_state'   =>  40,
    'addon_clean_finish_state'  =>  50,
    //保洁订单状态
    'order_clean_delete' => '-1',
    'order_clean_disable' => '0',
    'order_clean_wait' => '10',
    'order_clean_allot' => '15',
    'order_clean_accept' => '20',
    'order_clean_finish' => '30',
    'order_clean_verify' => '40',

    'order_clean_state' => [
        -1  =>  '已删除',
        0   =>  '隐藏',
        10  =>  '等待保洁',
        15  =>  '分配保洁人员',
        20  =>  '保洁人员接单',
        30  =>  '保洁完成',
        40  =>  '完成审核，费用发放'
    ],
    //提现状态
    'withdraw_state' => [
        1 =>    '用户申请',
        2 =>    '审核通过',
        3 =>    '审核不通过',
        4 =>    '提现完成',
        5 =>    '提现失败'
    ],
    'withdraw_state_apply'      =>  1,
    'withdraw_state_success'    =>  2,
    'withdraw_state_fail'       =>  3,
    'withdraw_state_finish'     =>  4,
    'withdraw_state_failed'     =>  5,
    //退款状态
    'refund_del_state'      =>  -1,
    'refund_disable_state'  =>  0,
    'refund_wait_state'     =>  1,
    'refund_fail_state'     =>  8,
    'refund_success_state'  =>  9,
    'refund_state'  =>  [
        -1  =>  '已删除',
        0   =>  '隐藏',
        1  =>  '等待退款',
        8  =>  '退款失败',
        9  =>  '退款成功',
    ],
    //优惠券状态
    'voucher_del'       =>  -1,
    'voucher_disable'   =>  0,
    'voucher_ok'        =>  1,
    'voucher_used'      =>  2,
    'voucher_passed'    =>  3,
    'voucher_use_state' =>  [
        '-1'    =>  '已删除',
        '0'     =>  '隐藏',
        '1'     =>  '正常',
        '2'     =>  '已使用',
        '3'     =>  '已过期'
    ],
    //保洁图片审核状态
    'clean_photo_wait'  =>  0,
    'clean_photo_success'   =>  1,
    'clean_photo_fail'      =>  2,

    //order_user_clean
    'user_clean_cancel'     =>  0,
    'user_clean_created'    =>  10,
    'user_clean_verify'     =>  20,
    'user_clean_pay'        =>  30,
    'user_clean_ordered'    =>  40,
    'user_clean_finish'     =>  50,
];