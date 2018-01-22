<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/8
 */


namespace app\common\model;

use think\Model;

class OrderAddonClean extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public static $AddonCleanStatus_Disabled = 0;
    public static $AddonCleanStatus_OK = 10;
    public static $AddonCleanStatus_Order = 40;
    public static $AddonCleanStatus_Finish = 50;


    public static $CleanType_Order = 0;
    public static $CleanType_User = 1;


    public function createAddonCleanOrder($order_sn, $bnb_order_sn, $user_id, $numbers, $price, $clean_time = 0)
    {
        if ($numbers > 0) {

            $now = time();

            for ($i = 0; $i < $numbers; $i++) {

                $order = [];
                $order['status'] = self::$AddonCleanStatus_OK;
                $order['clean_type'] = self::$CleanType_Order;
                if ($user_id) {
                    $order['clean_type'] = self::$CleanType_User;
                }
                $order['order_sn'] = $order_sn;
                $order['bnb_order_sn'] = $bnb_order_sn;
                $order['user_id'] = $user_id;
                $order['price'] = $price;
                $order['createtime'] = $now;
                $order['updatetime'] = 0;

                if ($clean_time) {
                    $order['status'] = self::$AddonCleanStatus_Order;
                    $order['clean_time'] = $clean_time;
                }
                else
                {
                    $order['status'] = self::$AddonCleanStatus_OK;
                    $order['clean_time'] = 0;
                }
                $r = $this->insertGetId($order);
                if (!$r) {
                    return false;
                }
                else
                {
                    return $r;
                }
            }

            return true;
        }

        return false;
    }
    /**
     * 获取民宿订单下增加的保洁订单
     * @param string $orderSn
     * @return array
     */
    public function getCleanOrderByOrderSn($orderSn){
        if($orderSn){
            $condition = [
                'a.bnb_order_sn' => $orderSn,
                'a.status'   => ['gt',config('state.addon_clean_cancel_state')]
            ];
            $data = $this->alias('a')
                    ->  field('a.*,b.clean_start_time,b.clean_end_time,fee_clean,c.user_truename')
                    ->  join('__ORDER_CLEAN__ b','a.clean_order_sn = b.order_sn','left')
                    ->  join('__USERS__ c','a.cleaner_id = c.id','left')
                    ->  where($condition)
                    ->  select();
            return $data?$data:null;
        }
        return null;
    }


    public function getAddonCleanList($order_sn)
    {

        $data = [];

        if ($order_sn) {
            $cl = $this->where('bnb_order_sn', $order_sn)->where('clean_type', self::$CleanType_User)->order('id asc')->select();

            if ($cl) {
                foreach ($cl as $cleanorder) {
                    $data[$cleanorder['status']][] = $cleanorder->toArray();
                }
            }

        }
        return $data;
    }


    public function updateOrderCreated($addon_clean_id, $clean_order_sn , $clean_start_time)
    {
        $now = time();

        $data = [];
        $data['status'] = self::$AddonCleanStatus_Order;
        $data['clean_order_sn'] = $clean_order_sn;
        $data['updatetime'] = $now;
        $data['order_time'] = $now;
        $data['clean_time'] = $clean_start_time;

        return $this->where('id', $addon_clean_id)->update($data);
    }

    public function updateOrderCleanerId($clean_order_sn, $cleaner_id)
    {
        $now = time();
        $data = [];
        $data['updatetime'] = $now;
        $data['cleaner_id'] = $cleaner_id;
        $r = $this->where('clean_order_sn', $clean_order_sn)->update($data);
        return $r;
    }

    public function updateOrderFinish($clean_order_sn)
    {
        $now = time();
        $data = [];
        $data['updatetime'] = $now;
        $data['finish_time'] = $now;
        $data['status'] = self::$AddonCleanStatus_Finish;
        $r = $this->where('clean_order_sn', $clean_order_sn)->update($data);
        return $r;
    }


    public function updateOrderCancel($clean_order_sn , $user = false)
    {
        $now = time();
        $data = [];
        $data['updatetime'] = $now;
        $data['status'] = self::$AddonCleanStatus_Disabled;

        $where = [];
        $where['clean_order_sn'] = $clean_order_sn;
        $where['state'] = ['<>' , self::$AddonCleanStatus_Finish];

        if ($user)
        {
            $where['user_id'] =['<>', 0];
        }
        $r = $this->where($where)->update($data);
        return $r;
    }

    /**
     * 查询免费保洁使用次数
     * @param $order_sn
     * @return int|string
     */
    public function getFreeUseNum($order_sn){
        return $this->where('price','elt',0)->where('status','gt',self::$AddonCleanStatus_Disabled)->where('order_sn',$order_sn)->count();
    }

    /**
     * @param $order_sn
     */
    public function getUsedNumber($bnb_order_sn){
        $status = [self::$AddonCleanStatus_Order,self::$AddonCleanStatus_Finish];
        return $this->where('status','in',$status)->where('bnb_order_sn',$bnb_order_sn)->count();
    }


    public function getUserOrderClean($bnb_order_sn)
    {
        if ($bnb_order_sn)
        {
            return $this->where('status',"<>",self::$AddonCleanStatus_Disabled)->where('order_sn' , $bnb_order_sn)->where("user_id","<>",0)->select();
        }

        return null;
    }

    public function getAutoCleanOrder($bnb_order_sn)
    {
        if ($bnb_order_sn)
        {
            return $this->where('status',"<>",self::$AddonCleanStatus_Disabled)->where('order_sn' , $bnb_order_sn)->where("user_id","=",0)->find();
        }

        return null;
    }


    /**
     * 获取民宿订单所有可用保洁信息
     *
     * @param $bnb_order_sn
     * @return false|null|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderClean($bnb_order_sn)
    {
        if ($bnb_order_sn)
        {
            return $this->where('status',"in",[self::$AddonCleanStatus_Order , self::$AddonCleanStatus_OK])->where('order_sn' , $bnb_order_sn)->select();
        }

        return null;
    }




    public function updateUserCleanOrderCancel($addon_clean_id)
    {
        $now = time();
        $data = [];
        $data['updatetime'] = $now;
        $data['status'] = self::$AddonCleanStatus_Disabled;

        $where = [];
        $where['id'] = $addon_clean_id;
        $where['status'] = ['<>' , self::$AddonCleanStatus_Finish];

        $r = $this->where($where)->update($data);
        return $r;
    }
    public function getUsedCleanOrderFree($bnb_order_sn){
        if(!$bnb_order_sn){
            return null;
        }
        $state = [self::$AddonCleanStatus_Order,self::$AddonCleanStatus_Finish];
        $data = $this->where('bnb_order_sn',$bnb_order_sn)->where('price',0)->where('status','IN',$state)
            ->select();
        return $data?$data:null;

    }

}