<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14 0014
 * Time: 9:42
 */

namespace app\common\model;

use think\Model;
class Evaluate extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function getEvaluteList($condition,$limit=0,$order='status desc,weigh desc,id desc'){
        $data = $this->alias('a')
            -> field('a.*,c.user_nickname,c.user_avatar,d.in_date,d.in_hour,b.name')
            -> join('__USERS__ c','a.user_id = c.id','left')
            -> join('__ORDER_BNB__ d','a.order_sn = d.order_sn','left')
            -> join('__BNB__ b','d.bnb_id = b.id','left')
            -> where($condition)
            -> order($order)
            -> limit($limit)
            -> select();
        //评价图片
        foreach($data as $k=>$v){
            if($v['photos'] > 0){
                $evaluatePhotos = (new \app\common\model\EvaluatePhoto())->getEvalutePhotosBnb($v['id'],$v['bnb_id']);
                $data[$k]['images'] = $evaluatePhotos;
            }
        }
        return  $data?$data:null;
    }
    public function getEvaluateNum($condition=[]){
        return $this->where($condition)->count();
    }
}