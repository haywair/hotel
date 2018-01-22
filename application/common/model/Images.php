<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/7 0007
 * Time: 14:56
 */

namespace app\common\model;

use think\Model;
class Images extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];
    //民宿图片列表
    public function getImagesListByBID($condition){
        $data = $this
            -> alias('a')
            -> field('a.*,b.name as className')
            -> join('__IMAGE_CLASS__ b','a.image_class_id = b.id','left')
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> paginate(config('page.images_page_size'),false,['query' =>request()->param()]);
        return $data?$data:'';
    }
    //民宿图片列表
    public function getImagesList($condition){
        $data = $this
            -> alias('a')
            -> field('a.*,b.name as className')
            -> join('__IMAGE_CLASS__ b','a.image_class_id = b.id','left')
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> select();
        return $data?$data:'';
    }
    //更新图片信息
    public function updateImage($id,$data){
        return $this->where('id',$id)->update($data);
    }
    /*
     *查看图片信息
     */
    public function getImageById($id){
        if($id){
            $data = $this->alias('a')
                  -> field('a.*,b.name as className')
                  -> join('__IMAGE_CLASS__ b','a.image_class_id = b.id','left')
                  -> where('a.id',$id)
                  -> find();
            return $data?$data:null;
        }else{
            return null;
        }
    }
}