<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27 0027
 * Time: 17:33
 */

namespace app\common\model;

use think\Model;

class BnbInfo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getBnbInfo($bnb_id)
    {
        if ($bnb_id) {
            return $this->where('bnb_id', "=", $bnb_id)->find();
        }
        return null;
    }


    public function addBnbOrderNumbers($bnb_id, $nums = 1)
    {
        if ($bnb_id) {
            $data = [];

            $nums = intval($nums);
            $data['numbers_order'] = ['exp', 'numbers_order+' . $nums];
            $data['lastorder_time'] = time();
            return $this->update($data, ['bnb_id' => $bnb_id]);
        }
        return null;
    }
}