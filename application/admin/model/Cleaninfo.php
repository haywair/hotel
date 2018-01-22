<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2 0002
 * Time: 17:55
 */

namespace app\admin\model;

use think\Model;
class Cleaninfo extends Model
{

    // 表名
    protected $name = 'cleaninfo';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {

    }
    /**
     * 根据用户id获取房东信息
     */
    public function getCleanerByUID($uid,$field="*"){
        $data = $this->alias('a')
              -> field('a.*,b.province_name,c.city_name')
              -> join('__AREA__ b','b.id = a.province_code','left')
              -> join('__AREA__ c','c.id = a.city_code','left')
              -> where(['users_id'=>$uid])->find();
        return $data?$data:'';
    }
}