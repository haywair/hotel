<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/28 0028
 * Time: 15:05
 */

namespace app\common\model;

use think\Model;
class Service extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function getStatusList()
    {
        return ['-1' => '已删除','0' =>'隐藏','1' =>'正常'];
    }

    /**
     * 协议内容
     */
    public function getServiceContent(){
        $data = $this
            ->where('status','IN',config('state.state_ok'))->order('weigh desc,updatetime desc,id desc')->limit(1)
            ->select();
        return $data?$data[0]:null;
    }
}