<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 10:34
 */

namespace app\admin\validate;

use think\Validate;
class BnbWeekprice extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'bnb_id' => 'require|number',
        'price_w1' => 'require|number',
        'price_w2' => 'require|number',
        'price_w3' => 'require|number',
        'price_w4' => 'require|number',
        'price_w5' => 'require|number',
        'price_w6' => 'require|number',
        'price_w7' => 'require|number',
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
}


