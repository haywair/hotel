<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16 0016
 * Time: 9:43
 */
namespace app\index\validate;

use think\Validate;
class Signup extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'truename' => 'require|chs',
        'contact_mobile' => 'require|mobile',
        'info' => 'require',
        'street' => 'require'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'truename.require' => '业主名称必须',
        'truename.chs' => '业主名称必须是中文',
        'contact_mobile.require' => '联系电话不能为空',
        'contact_mobile.moible' => '联系电话必须是11位数字手机号码',
        'info' => '小区、房型信息不能为空',
        'street' => '街道地址不能为空'

    ];
}