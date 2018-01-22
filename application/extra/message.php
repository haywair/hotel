<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/19 0019
 * Time: 11:44
 */
return [
    'message_type'  =>  [
        'B' =>  '民宿订单消息',
        'C' =>  '保洁订单消息',
        'P' =>  '支付消息',
        'Q' =>  '问卷消息'
    ],
    'order_bnb_msg'     =>  'B',//民宿订单消息
    'order_clean_msg'   =>  'C',//保洁订单消息
    'order_pay_msg'     =>  'P',//支付消息
    'questionary_reply_msg'     =>  'Q',//问卷消息
    'order_clean_cancel_msg'    =>  'C_CANCEL',//保洁订单取消消息
    'order_clean_change_msg'    =>  'C_CHANGE',//保洁订单更改消息

    'questionary_url'   =>  '',//调查问卷url

    'qustionary_score'  =>  [
        60  =>  [0,0.6],
        70  =>  [0.6,0.7],
        80  =>  [0.7,0.8],
        90  =>  [0.8,0.9],
        100 =>  [0.9,1]
    ],

];