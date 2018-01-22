<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10 0010
 * Time: 15:43
 */

namespace app\common\model;

use Carbon\Carbon;
use think\Model;

class OrderClean extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public static $BnbCleanStatus_Waiting = 10;
    public static $BnbCleanStatus_ToCleaner = 15;
    public static $BnbCleanStatus_DoCleaner = 20;
    public static $BnbCleanStatus_FinishCleaner = 30;
    public static $BnbCleanStatus_BillCleaner = 40;

    public static $BnbCleanStatus_Cancel = 0;
    public static $BnbCleanStatus_Delete = -1;

    /**
     * 保洁订单数量
     */
    public function getOrderCleanNum($condition = [])
    {
        return $this->where($condition)->count();
    }


    public function checkCleanerStatus($cleaner_id, $date)
    {
        $clean_time = Carbon::createFromFormat("Y-m-d H:i:s", $date . " 00:00:00")->timestamp;
        $where = [];
        $where['status'] = ['in', [self::$BnbCleanStatus_ToCleaner, self::$BnbCleanStatus_DoCleaner]];
        $where['cleaner_id'] = $cleaner_id;
        $where['clean_start_time'] = $clean_time;

        return $this->where($where)->find();
    }


    public function getOrderById($id)
    {
        if ($id) {
            return $this->where('id', $id)->find();
        }
        return null;
    }


    public function getWaitingOrderList($start_time = 0)
    {
        $where = [];
        $where['status'] = self::$BnbCleanStatus_Waiting;
        if ($start_time > 0) {
            $where['clean_start_time'] = ['<=', $start_time];
        }
        return $this->where($where)->order("clean_start_time", 'asc')->select();
    }

    public function saveCleanerToOrder($clean_order_id, $cleaner_id)
    {
        $data = [];
        $data['updatetime'] = time();
        $data['status'] = self::$BnbCleanStatus_ToCleaner;
        $data['status'] = self::$BnbCleanStatus_DoCleaner; //跳过保洁人员接单步骤，分配工作必须完成
        $data['cleaner_id'] = $cleaner_id;

        return $this->where('id', $clean_order_id)->update($data);
    }


    public function updateCleanOrderFinish($clean_order_id)
    {
        $now = time();
        $data = [];
        $data['updatetime'] = $now;
        $data['status'] = self::$BnbCleanStatus_FinishCleaner;
        $data['work_end_time'] = $now;

        return $this->where('id', $clean_order_id)->update($data);
    }

    public function getOrderClean($cleaner_id, $state = [], $createtime = 0, $relation = "")
    {
        $condition = [];
        $condition['a.cleaner_id'] = $cleaner_id;
        //订单状态
        if ($state) {
            $condition['a.status'] = array('IN', $state);
        }
        //订单时间
        if ($createtime) {
            $condition['a.createtime'] = $createtime;
            if ($relation) {
                $condition['a.createtime'] = array($relation, $createtime);
            }
        }
        $data = $this->alias('a')->field('a.*,b.name,b.bnb_image,c.user_nickname')
            ->join('__BNB__ b', 'a.bnb_id = b.id', 'left')
            ->join('__USERS__ c', 'b.landlord_user = c.id', 'left')
            ->where($condition)
            ->order('createtime asc')
            ->select();

        return $data ? $data : null;
    }

    public function getCleanOrderById($id)
    {
        if ($id) {
            $list = $this
                ->alias('a')
                ->field('a.*,b.province_name,f.nickname,c.city_name,d.user_nickname,e.name')
                ->join('__AREA__ b', 'b.id = a.province_code', 'left')
                ->join('__AREA__ c', 'c.id = a.city_code', 'left')
                ->join('__USERS__ d', 'd.id = a.cleaner_id', 'left')
                ->join('__ADMIN__ f', 'f.id = a.verify_userid', 'left')
                ->join('__BNB__ e', 'e.id = a.bnb_id', 'left')
                ->where('a.id', '=', $id)
                ->find();
            return $list ? $list : null;
        } else {
            return null;
        }
    }

    public function createOrderClean($clean_dbdata)
    {
        return $this->insertGetId($clean_dbdata);
    }

    public function getOrderBySn($order_sn)
    {
        if ($order_sn) {
            $where = [];
            $where['order_sn'] = $order_sn;
            return $this->where($where)->find();
        }

        return null;
    }

    /**
     * 限定时间内未结算订单
     * @param int $start_time
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getWaitingBillOrderList($bill_time)
    {
        $where = [];
        $where['status'] = self::$BnbCleanStatus_FinishCleaner;
        $where['work_end_time'] = ['<=', $bill_time];
        $data = $this->where($where)->order("clean_start_time", 'asc')->select();
        foreach($data as $k=>$v){
            $count = (new OrderCleanPhoto())-> getOrderNoVefiryNum($v['id']);
            if($count > 0){
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * 审核通过并发放费用
     * @param $ordere_clean_id
     * @param $fee_cleaner
     * @param $verify_score
     * @return $this
     */
    public function updateCleanOrderVerify($ordere_clean_id, $fee_cleaner, $verify_score)
    {
        $data = [];
        $data['updatetime'] = time();
        $data['verify_time'] = time();
        $data['verify_score'] = $verify_score;
        $data['verify_time'] = self::$BnbCleanStatus_ToCleaner;
        $data['status'] = self::$BnbCleanStatus_BillCleaner; //跳过保洁人员接单步骤，分配工作必须完成
        $data['fee_cleaner'] = $fee_cleaner;

        return $this->where('id', $ordere_clean_id)->update($data);
    }


    public function cancelCleanOrder($clean_order_sn)
    {
        if ($clean_order_sn) {
            $cleanorder = $this->where('order_sn', $clean_order_sn)->find();

            $clean_array = [self::$BnbCleanStatus_Waiting, self::$BnbCleanStatus_ToCleaner, self::$BnbCleanStatus_DoCleaner];

            if (in_array($cleanorder['status'] , $clean_array)) {
                $data = [];
                $data['status'] = self::$BnbCleanStatus_Cancel;
                $data['updatetime'] = time();

                return $this->where('id', $cleanorder['id'])->update($data);


                //todo 发送保洁员消息，通知订单取消
            }
        }
        return null;
    }

    public function updateCleanOrderDate($clean_order_sn, $date_string)
    {
        if ($clean_order_sn) {
            $cleanorder = $this->where('order_sn', $clean_order_sn)->find();

            $clean_array = [self::$BnbCleanStatus_Waiting, self::$BnbCleanStatus_ToCleaner, self::$BnbCleanStatus_DoCleaner];

            if (in_array($cleanorder['status'] , $clean_array)) {
                $data = [];
                $data['clean_start_time'] = Carbon::createFromFormat('Y-m-d H:i:s', $date_string . " 00:00:00")->timestamp;
                $data['clean_end_time'] = $data['clean_start_time'];
                $data['updatetime'] = time();
                return $this->where('id', $cleanorder['id'])->update($data);


                //todo 发送保洁员消息，通知订单时间变化
            }
        }
        return null;
    }
    public function getLatestOrderByBnbId($bnb_id){
        if($bnb_id){
            $data = $this->where('bnb_id',$bnb_id)->where('status',self::$BnbCleanStatus_FinishCleaner)->order('work_end_time','desc')->limit(1)->select();
            return $data?$data:null;
        }
        return null;
    }
}