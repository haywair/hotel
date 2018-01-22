<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 10:56
 */

namespace app\admin\model;

use think\model;
class BnbWeekprice extends Model
{
    // 表名
    protected $name = 'bnb_weekprice';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}