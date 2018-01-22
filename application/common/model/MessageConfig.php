<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17 0017
 * Time: 11:02
 */

namespace app\common\model;

use think\Model;
class MessageConfig extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getStatusList()
    {
        return ['-1' => '已删除','0' =>'隐藏','1' =>'正常'];
    }
}