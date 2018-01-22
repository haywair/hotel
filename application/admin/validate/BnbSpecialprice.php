<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 13:17
 */

namespace app\admin\validate;

use think\Validate;
class BnbSpecialprice extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'bnb_id' => 'require',
        'price_type' => 'require',
        'begin_date' => 'require',
        'end_date' => 'require',
        'price' => 'require|number',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'bnb_id.require' => '请选择房间',
        'price_type.require' => '请选择活动价格类型',
        'begin_date' => '请选择开始日期',
        'end_date' => '请选择结束日期',
        'price.require' => '请输入价格',
        'price.number' => '价格必须是大于0的数字'
    ];
}