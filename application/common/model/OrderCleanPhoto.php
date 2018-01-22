<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/11
 */

namespace app\common\model;

use think\Model;

class OrderCleanPhoto extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getCleanPhoto($clean_photo_id, $clean_order_id)
    {
        $where = [];
        $where['id'] = $clean_photo_id;
        $where['status'] = 1;
        $where['order_clean_id'] = $clean_order_id;
        return $this->where($where)->find();
    }


    public function getOrderPhotoList($clean_order_id)
    {
        $where = [];
        $where['status'] = 1;
        $where['order_clean_id'] = $clean_order_id;
        return $this->where($where)->select();
    }

    public function updateCleanPhoto($clean_photo_id, $image, $distince=100, $needadmin = false)
    {
        $now = time();

        $data = [];
        $data['updatetime'] = $now;
        $data['upload_time'] = $now;
        $data['upload_image'] = $image;
        $data['compare_value'] = $distince;
        if ($needadmin) {
            $data['need_admin'] = 1;
        } else {
            $data['need_admin'] = 0;
        }

        return $this->where('id', $clean_photo_id)->update($data);
    }
    public function updateCleanPhotoById($clean_photo_id, $data){
        return $this->where('id',$clean_photo_id)->update($data);
    }
    public function getComparePhotoDataByID($id){
        if(!$id){
            return null;
        }
        return $this->where('id',$id)->find();
    }

    /**
     * 未通过验证的保洁图片数量
     * @param $clean_order_id
     * @return int|null|string
     */
    public function getOrderNoVefiryNum($clean_order_id){
        if($clean_order_id){
            $where['status'] = config('state.state_ok');
            $where['admin_verify_state'] = ['neq',config('state.state_ok')];
            $where['order_clean_id'] = $clean_order_id;
            return $this->where($where)->count();
        }
        return null;
    }
}