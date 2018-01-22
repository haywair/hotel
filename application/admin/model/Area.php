<?php

namespace app\admin\model;

use think\Model;

class Area extends Model
{
    // 表名
    protected $name = 'area';
    
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

    public function getProvinceList(){
        $list = $this->where(['status'=>1,'city_code'=>''])->column('province_name','id');
        return $list?$list:'';
    }
    public function getCitys($province){
        if(!$province){
            return '';
        }
        $where = [
            'province_code'=> $province,
            'city_code' => ['neq',''],
            'county_code' => '',
            'status' => 1
        ];
        $citys = $this->where($where)->column('city_name','id');
        return $citys?$citys:'';
    }





}
