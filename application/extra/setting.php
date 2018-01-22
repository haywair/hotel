<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

return [

    'Wechat_need_subscribe' => true, // 微信端需要订阅才能访问
    'Wechat_user_location' => true, // 获取用户位置信息

    'location' => [
        'update_interval' => 5 * 60, //用户位置更新间隔时间，秒
    ],

    'default_location_code' => 370100, // 默认城市坐标
    'default_location_name' => "济南", // 默认城市坐标

    'price_max_day' => 90, // 最大价格显示天数

    'order_need_verify' => false, // 订单是否需要审核

    'bnb_service' => [      // 民宿服务费
        'precent' => 5,     // 房间费的百分比
        'min_fee' => 0.00, // 最少收取费用
    ],

    'bnb_free_clean_days' => 7, // N日赠送免费保洁一次

    'clean_order_auto_day' => 50, //提前N天保洁订单自动分配保洁员
    'clean_order_begin_hour' => 9, // 相隔一天以上的订单，分配订单开始时间
    'clean_order_end_hour' => 20, // 相隔一天以上的订单，分配订单结束时间
    'clean_order_auto_bill_day' => 10, //保洁完成N天后自动结算
    'questionary_full_score' => 10,//问卷调查选项分值

    'withdraw_auto_day' => 3,//提现申请N后自动审核并完成提现
    'msg_auto_send_bnb_day'  => 3,//入住n天前自动发送消息
    'msg_auto_send_clean_hour'  => 3,//保洁前n小时自动发送消息
    'msg_auto_quesionay_hour'    => 3,//入住前n小时自动发送调查问卷

    'province_open' => [    //城市列表，开放的省份
        370000
    ],

    'clean_photo_distince' => 20, //图片对比最大距离

    'upload_evaluate_photo_num' => 3,//可上传订单评价图片
    'upload_evaluate_text' => "真不错！",//默认评价文字

    'autorefund_max_hour' => 24,    // 入住时间24小时以外，自动退款

    'withdraw_cleaner_type'     => 1,//保洁提现
    'withdraw_landlord_type'    =>  2,//房东提现

    'auto_cancel_order_max_minute' => 30 , //30分钟不付款自动取消订单

];