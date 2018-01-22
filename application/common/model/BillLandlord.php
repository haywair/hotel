<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17 0017
 * Time: 9:11
 */

namespace app\common\model;

use think\Model;
class BillLandlord extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getBnbMoneyLandlord($landlordId,$state=[]){
        if($landlordId){
            $wherestate_in = [];
            if($state){
                $state = implode(',',$state);
                $wherestate_in = function ($query) use ($state) {
                    $query->where("status", 'in', $state);
                };
            }
            $total = $this->field('sum(bnb_money) as total')->where('landlord_id',$landlordId)->where(function ($query)
            use ($wherestate_in) {
                $query->where($wherestate_in);
            })->select();
            return $total[0]['total']?$total[0]['total']:0;
        }
        return null;
    }
    /**
     * 结算信息数量
     */
    public function getBillLandlordNum($condition=[]){
        return $this->where($condition)->count();
    }
    /**
     * 结算列表
     * @param array $condition
     */
    public function getListData($condition=[]){
        $data = $this
            -> alias('a')
            -> field('a.*,b.order_sn,c.name')
            -> join('__ORDER_BNB__ b','b.id = a.order_id','left')
            -> join('__BNB__ c','c.id = a.bnb_id','left')
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> paginate(config('page.backend_bill_landlord_page'),false,['query' =>request()->param()]);
        return $data?$data:null;
    }
    /**
     * 结算详情
     * @param $id
     * @return array|false|null|\PDOStatement|string|Model
     */
    public function getBillById($id){
        if($id) {
            $data = $this
                ->alias('a')
                ->field('a.*,b.user_nickname,c.name,d.order_sn,e.contact_mobile')
                ->join('__USERS__ b','a.landlord_id = b.id','left')
                ->join('__LANDLORDINFO__ e','a.landlord_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_BNB__ d','a.order_id = d.id','left')
                ->where('a.id','=',$id)
                ->find();
            return $data?$data:null;
        }

        return null;

    }
}