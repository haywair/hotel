<?php

namespace app\common\model;

use think\Model;

class ImageList extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];


    public function getImageByMd5($md5, $imagetype)
    {
        $img = $this->where('md5', $md5)->where('imagetype', $imagetype)->find();
        if ($img) {
            $data['id'] = $img['id'];
            $data['file'] = $img['file'];
            $data['imagetype'] = $img['imagetype'];
            return $data;
        }
        return null;
    }


    public function getImageListByType($imagetype, $offset, $limit, $sort, $order)
    {
        $where = ['imagetype' => $imagetype];

        $total = $this
            ->where($where)
            ->count();

        if ($total > 0) {

            $list = $this
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
        } else {
            $list = [];
        }

        $result = array("total" => $total, "rows" => $list);
        return $result;

    }
}
