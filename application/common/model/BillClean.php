<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/20 0020
 * Time: 11:32
 */

namespace app\common\model;

use think\Model;
class BillClean extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 按月统计用户结算收益
     * @param $user_id
     * @return null
     */
    public function getCleanerMonthBill($user_id){
        $data = $this
            ->field("sum(clean_money) as totalmoney, count(*) as sheets,DATE_FORMAT(bill_date,'%Y-%m') month")
            -> where('cleaner_id',$user_id)
            -> where('status',1)
            -> group("DATE_FORMAT(bill_date, '%Y-%m')")
            -> order('bill_date desc')
            -> select();

        if($data){
            $returnData = [];
            foreach($data as $k => $v){
                $returnData[$v['month']]['money'] = $v['totalmoney'];
                $returnData[$v['month']]['month'] = $v['month'];
            }
            return $returnData;
        }else{
            return null;
        }
    }

    /**
     * 按日期统计用户结算收益
     * @param $user_id
     * @param $begin_date
     * @param $end_date
     * @return false|null|\PDOStatement|string|\think\Collection
     */
    public function getCleanerBillList($user_id,$begin_date,$end_date){
        $data = $this->field("sum(clean_money) as totalmoney, count(*) as sheets,bill_date")
            -> where('cleaner_id',$user_id)
            -> where('status',1)
            -> where('bill_date','>=',$begin_date)
            -> where('bill_date','<=',$end_date)
            -> group("bill_date")
            -> order('bill_date desc')
            -> select();
        return $data?$data:null;
    }
    /**
     * 结算信息数量
     */
    public function getBillCleanNum($condition=[]){
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
            -> join('__ORDER_CLEAN__ b','b.id = a.clean_order_id','left')
            -> join('__BNB__ c','c.id = a.bnb_id','left')
            -> where($condition)
            -> order('a.weigh desc,a.id desc')
            -> paginate(config('page.backend_bill_clean_page'),false,['query' =>request()->param()]);
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
                ->join('__USERS__ b','a.cleaner_id = b.id','left')
                ->join('__CLEANINFO__ e','a.cleaner_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_CLEAN__ d','a.clean_order_id = d.id','left')
                ->where('a.id','=',$id)
                ->find();
            return $data?$data:null;
        }

        return null;

    }

}