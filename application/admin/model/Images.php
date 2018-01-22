<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27 0027
 * Time: 13:25
 */

namespace app\admin\model;

use think\Model;
class Images extends Model
{

    // 表名
    protected $name = 'images';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public function getBnbImages($bid){
        if($bid) {
            $data = $this->alias('a')
                ->field('a.*,b.name as class_name')
                ->join('__IMAGE_CLASS__ b', 'a.image_class_id = b.id', 'left')
                ->where(['a.bnb_id' => $bid, 'a.status' => config('state.state_ok')])
                ->select();
            return $data ? $data : null;
        }else{
            return null;
        }
    }
}