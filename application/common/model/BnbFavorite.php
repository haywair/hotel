<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16 0016
 * Time: 11:45
 */

namespace app\common\model;

use think\Model;
class BnbFavorite extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getBnbFavoriteByUID($userId){
        if($userId){
            $data = $this
                -> alias('a')
                -> field('d.*,a.id as favoriteId,b.province_name,c.city_name')
                -> join('__BNB__ d','a.bnb_id = d.id','left')
                -> join('__AREA__ b','b.id = d.area_province_code','left')
                -> join('__AREA__ c','c.id = d.area_city_code','left')
                -> where('a.user_id',$userId)
                -> where('a.status',config('state.state_ok'))
                -> order('d.weigh desc,d.id desc')
                -> paginate(config('page.favorite_bnb_page'),false,['query' =>request()->param()]);
            return $data?$data:'';
        }
        return null;
    }

}