<?php

namespace app\admin\validate;

use think\Validate;

class Bnb extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
         'weigh' => 'require',
         'name' => 'require',
         'bnb_adwords' => 'require',
         'area_province_code' => 'require',
         'area_city_code' => 'require',
         'area_address' => 'require',
         'features_ids' => 'require',
         'room_space' => 'require',
         'room_people' => 'require',
         'room_bedroom' => 'require',
         'room_livingroom' => 'require',
         'room_bed' => 'require',
         'room_toilet' => 'require',
         'map_lng' => 'require',
         'map_lat' => 'require',
         'bnb_image' => 'require',
         'landlord_user' => 'require',
         'manager_user' => 'require',
         'in_hour' => 'require',
         'out_hour' => 'require',
         'fee_clean' => 'require',
         'fee_deposit' => 'require',
         'demo_content' => 'require',
         'live_content' => 'require',
         'traffic_content' => 'require',
         'attention_content' => 'require',
         'fee_cleaner' => 'require',
         'fee_landlord' => 'require'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name' => '民宿信息不完整，请完善民宿名称',
        'bnb_adwords' => '民宿信息不完整，请完善民宿广告词',
        'area_province_code' => '民宿信息不完整，请完善民宿所在省',
        'area_city_code' => '民宿信息不完整，请完善民宿所在市',
        'area_address' => '民宿信息不完整，请完善民宿所在的具体地址',
        'features_ids' => '民宿信息不完整，请完善民宿内包含的设施',
        'room_space' => '民宿信息不完整，请完善民宿面积',
        'room_people' => '民宿信息不完整，请完善民宿可容纳人数',
        'room_bedroom' => '民宿信息不完整，请完善民宿内卧室数量',
        'room_livingroom' => '民宿信息不完整，请完善民宿内客厅数量',
        'room_bed' => '民宿信息不完整，请完善民宿内床位数量',
        'room_toilet' => '民宿信息不完整，请完善民宿内卫生间数量',
        'map_lng' => '民宿信息不完整，请完善民宿所在的位置地图',
        'map_lat' => '民宿信息不完整，请完善民宿所在的位置地图',
        'bnb_image' => '民宿信息不完整，请完善民宿的房间展示图片',
        'landlord_user' => '民宿信息不完整，请完善民宿的房东',
        'manager_user' => '民宿信息不完整，请完善民宿的管理员',
        'in_hour' => '民宿信息不完整，请完善入住时间',
        'out_hour' => '民宿信息不完整，请完善退房时间',
        'fee_clean' => '民宿信息不完整，请完善保洁费用',
        'fee_deposit' => '民宿信息不完整，请完善保证金费用',
        'demo_content' => '民宿信息不完整，请完善房间介绍',
        'live_content' => '民宿信息不完整，请完善入住规则',
        'traffic_content' => '民宿信息不完整，请完善入住指引',
        'attention_content' => '民宿信息不完整，请完善注意事项',
        'fee_cleaner' => '民宿信息不完整，请完善保洁结算费用',
        'fee_landlord' => '民宿信息不完整，请完善房东结算费用'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
    
}
