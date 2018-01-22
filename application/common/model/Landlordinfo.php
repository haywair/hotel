<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 17:16
 */

namespace app\common\model;

use think\Model;
class Landlordinfo extends Model
{
    // 表名
    protected $name = 'landlordinfo';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];


    protected static function init()
    {

    }
    /**
     * 根据用户id获取房东信息
     */
    public function getLandloardByUID($uid){
        $data = $this->alias('a')
            -> field('a.*,b.province_name,c.city_name,d.user_nickname')
            -> join('__AREA__ b','b.id = a.province_code','left')
            -> join('__AREA__ c','c.id = a.city_code','left')
            -> join('__USERS__ d','a.users_id = d.id','left')
            -> where(['users_id'=>$uid])
            -> find();
        return $data?$data:'';
    }

    public function getLandlord($uid){
        return $this->where('users_id',$uid)->find();
    }
}