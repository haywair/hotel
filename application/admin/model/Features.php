<?php

namespace app\admin\model;

use think\Model;

class Features extends Model
{
    // 表名
    protected $name = 'features';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $row->save(['weigh' => $row['id']]);
        });
    }

    
    public function getStatusList()
    {
        return ['-1' => __('Status -1'),' 0' => __('Status 0'),'1' => __('Status 1')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getFeaturesByBnbId($ids){
        return $this->where('id','IN',$ids)->where('status',config('state.state_ok'))->select();
    }




}
