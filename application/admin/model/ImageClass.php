<?php

namespace app\admin\model;

use think\Model;

class ImageClass extends Model
{
    // 表名
    protected $name = 'image_class';
    
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
        return ['-1' => __('Status -1'),'0' => __('Status 0'),'1' => __('Status 1')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getList($condition=[],$field='*'){
        return $this->field($field)->where($condition)->select();
    }

    public function getClassData(){
        $condition = [
            'status' => config('state.state_ok'),
        ];
        $classData = $this->where($condition)->column('name','id');
        return $classData;
    }




}
