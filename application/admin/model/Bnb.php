<?php

namespace app\admin\model;

use think\Model;

class Bnb extends Model
{
    // 表名
    protected $name = 'bnb';
    
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
        return ['-1' => __('Status -1'),' 0' => __('Status 0'),'1' => __('Status 1'),'2' => __('Status 2')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getBnbInfo($bid,$field=null){
        $fieldStr = $this->getFields($field);
        $data = $this->alias('a')
              -> field($fieldStr.'b.user_nickname,c.username,d.price_w1,d.price_w2,d.price_w3,d.price_w4,d.price_w5,d
              .price_w6,d.price_w7,e.city_name,f.province_name')
              -> join('__USERS__ b','b.id = a.landlord_user','left')
              -> join('__ADMIN__ c','c.id = a.manager_user','left')
              -> join('__BNB_WEEKPRICE__ d','d.bnb_id = a.id','left')
              -> join('__AREA__ e','a.area_city_code = e.id','left')
              -> join('__AREA__ f','a.area_province_code = f.id','left')
              -> where(['a.id'=>$bid])
              -> find();
        return $data?$data:'';

    }
    /**
     * 民宿列表
     */
    public function getBnbList($condition=[],$field=null,$limit=0,$order='a.weigh desc,a.id desc'){
        $fieldStr = $this->getFields($field);
        $data = $this->alias('a')
              -> field($fieldStr.'b.province_name,c.city_name')
              -> join('__AREA__ b','b.id = a.area_province_code','left')
              -> join('__AREA__ c','c.id = a.area_city_code','left')
              -> where($condition)
              -> order($order)
              -> select();
        return $data?$data:'';
    }
    /**
     * 民宿列表分页
     */
    public function getBnbPageList($condition=[]){
        $data = $this
            -> alias('a')
            -> field('a.*,b.province_name,c.city_name')
            -> join('__AREA__ b','b.id = a.area_province_code','left')
            -> join('__AREA__ c','c.id = a.area_city_code','left')
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> paginate(config('page.backend_bind_bnb_page'),false,['query' =>request()->param()]);
        return $data?$data:'';
    }
    /**
     * 修改民宿信息
     */
    public function updateBnbByBID($bid,$data){
        return $this->where(['id'=>$bid])->update($data);
    }
    /**
     * 处理字段信息
     */
    private function getFields($field){
        if($field&& is_array($field)){
            foreach($field as $k=>$v){
                $field[$k] = 'a.'.$v;
            }
            $fieldStr = implode(',',$field).',';
        }else if($field && is_string($field)){
            $fieldArr = explode(',',$field);
            foreach($fieldArr as $kr=>$vr){
                $fieldArr[$kr] = 'a.'.$vr;
            }
            $fieldStr = implode(',',$fieldArr).',';
        }else{
            $fieldStr = 'a.*,';
        }
        return $fieldStr;
    }
    /**
     * 查询房东所拥有的民宿
     */
    public function getLandlordBnb($landlord){
        if($landlord){
            $condition = [
                'landlord_user' => $landlord,
                'status' => array('gt',config('state.state_disable'))
            ];
            $data = $this->where($condition)->column('id');
            return $data?$data:null;
        }
        return null;
    }




}
