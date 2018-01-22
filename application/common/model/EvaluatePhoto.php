<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14 0014
 * Time: 10:00
 */

namespace app\common\model;

use think\Model;
class EvaluatePhoto extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function getEvalutePhotosBnb($evaluateId,$bnbId){
        $condition = [
            'status' => array('IN',config('state.state_ok').','.config('state.state_mark')),
            'bnb_id' => $bnbId,
            'evaluate_id' => $evaluateId
        ];
        $order = 'weigh desc,id desc';
        $data = $this-> where($condition)->order($order)->select();
        return  $data?$data:null;
    }
}