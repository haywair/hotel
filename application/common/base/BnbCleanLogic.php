<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/16
 */

namespace app\common\base;

use app\common\model\OrderAddonClean;
use app\common\model\OrderClean;
use Carbon\Carbon;
use think\Exception;

class BnbCleanLogic
{

    /*
     *  民宿订单付款，产生保洁订单
     */
    public function onBnbOrderPaid($order)
    {

        $error = new Error();

        try {
            // 生成免费保洁订单
            if ($order['free_clean_numbers'] > 0) {
                $r = $this->createCleanOrder($order['order_sn'], $order['order_sn'], $order['user_id'], $order['free_clean_numbers'], 0.00);
                if (!$r) {
                    throw new Exception("生成免费保洁订单失败", 520);
                }
            }

            // 生成付费保洁订单
            if ($order['addon_clean_numbers'] > 0) {
                $r = $this->createCleanOrder($order['order_sn'], $order['order_sn'], $order['user_id'], $order['addon_clean_numbers'], $order['addon_clean_price']);
                if (!$r) {
                    throw new Exception("生成付费保洁订单失败", 520);
                }
            }

            // 设置离店保洁时间
            $out_clean_time = Carbon::createFromFormat("Y-m-d H:i:s", $order['out_date'] . " 00:00:00")->timestamp;

            $auto_clean_id = $this->createCleanOrder($order['order_sn'], $order['order_sn'], 0, 1, 0.00, $out_clean_time);
            if (!$auto_clean_id) {
                throw new Exception("生成离店保洁订单失败", 520);
            }

            // 生成离店保洁订单
            $co = (new BnbClean())->updateAutoBnbCleanOrder($order['order_sn'] , date("Y-m-d",$out_clean_time));
            if (!$co->checkResult())
            {
                throw new Exception("生成保洁工作订单失败", 520);
            }

            $error->setOk();
        }
        catch(Exception $e)
        {
            $error->setError($e->getCode(),$e->getMessage());
        }

        return $error;
    }


    public function onBnbOrderCancel($order , $canceltime)
    {

        // 处理用户保洁订单
        $error = new Error();

        try {
            $oac = new OrderAddonClean();

            $act_out = $canceltime['time']['act_out'];

            $auto_date = "";
            if ($act_out)
            {
                $auto_date = $act_out->toDateString();
            }

            $cleanlist = $oac->getOrderClean($order['order_sn']);
            if ($cleanlist)
            {
                foreach($cleanlist as $clean)
                {
                    if ($clean['clean_order_sn'])
                    {
                        // 预约过
                        if ($clean['user_id'] == 0)
                        {
                            // 离店保洁订单
                            if ($auto_date)
                            {
                                //修改时间
                                (new OrderClean())->updateCleanOrderDate($clean['clean_order_sn'] , $auto_date);
                            }
                            else
                            {
                                //取消
                                (new OrderClean())->cancelCleanOrder($clean['clean_order_sn']);
                                $oac->updateUserCleanOrderCancel($clean['id']);
                            }
                        }
                        else
                        {
                            // 用户预约订单
                            (new OrderClean())->cancelCleanOrder($clean['clean_order_sn']);
                            $oac->updateUserCleanOrderCancel($clean['id']);
                        }
                    }
                    else
                    {
                        // 未预约过，直接取消
                        $oac->updateUserCleanOrderCancel($clean['id']);
                    }
                }
            }

            $error->setOk();
        }
        catch(Exception $e)
        {
            $error->setError($e->getCode(),$e->getMessage());
        }

        return $error;
    }
    

    /**
     * 创建保洁订单
     *
     * @param $order_sn
     * @param $bnb_order_sn
     * @param $user_id
     * @param $numbers
     * @param $price
     * @param clean_time 预设时间
     * @return bool
     */
    private function createCleanOrder($order_sn, $bnb_order_sn, $user_id, $numbers, $price, $clean_time=0)
    {
        $r = (new OrderAddonClean())->createAddonCleanOrder($order_sn, $bnb_order_sn, $user_id, $numbers, $price , $clean_time);
        return $r;
    }

}