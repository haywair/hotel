<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/11
 */

namespace app\common\model;

use think\Model;

class BnbCleanPhoto extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getBnbCleanPhoto($bnb_id)
    {
        if ($bnb_id) {
            return $this->where('bnb_id', $bnb_id)->where('status',config('state.state_ok'))->select();
        }
        return null;
    }

    //民宿保洁图片列表
    public function getImagesListByBID($condition){
        $data = $this-> where($condition)
            -> order('weigh desc,id desc')
            -> paginate(config('page.images_page_size'),false,['query' =>request()->param()]);
        return $data?$data:'';
    }
    //民宿保洁图片列表
    public function getImagesList($condition){
        $data = $this
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> select();
        return $data?$data:'';
    }
    //更新保洁图片信息
    public function updateImage($id,$data){
        return $this->where('id',$id)->update($data);
    }
    /*
    *查看图片信息
    */
    public function getImageById($id){
        if($id){
            $data = $this-> where('id',$id)-> find();
            return $data?$data:null;
        }else{
            return null;
        }
    }

}