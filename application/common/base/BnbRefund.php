<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/8
 */

namespace app\common\base;

use app\common\model\OrderAddonClean;
use app\common\model\OrderUserClean;
use app\common\model\Refund;
use Carbon\Carbon;
use think\Exception;
use app\common\model\Bnb;
use app\common\model\OrderBnb;

class BnbRefund
{
    /**
     * 创建退款订单
     *
     * @param $pay_sn
     * @param $pay_time
     * @param $user_id
     * @param $refund_type
     * @param $pay_total
     * @param $pay_refund
     * @param $reason
     * @param $admin_id
     * @return Error
     */
    public function addRefundOrder($pay_sn, $pay_time, $user_id, $refund_type, $pay_total, $pay_refund, $reason, $admin_id,$order_sn='')
    {

        $error = new Error();
        $model_refund = new Refund();
        try {

            $now = time();
            $refund = [];
            $refund['createtime'] = $now;
            $refund['updatetime'] = 0;
            $refund['status'] = Refund::$RefundStatus_Waiting;
            $refund['refund_sn'] = $this->genRefundSn($user_id);
            $refund['users_id'] = $user_id;
            $refund['refund_type'] = $refund_type;
            $refund['pay_sn'] = $pay_sn;
            $refund['pay_time'] = $pay_time;
            $refund['pay_amount'] = $pay_total;
            $refund['refund_time'] = 0;
            $refund['refund_amount'] = $pay_refund;
            $refund['reason'] = $reason;
            $refund['admin_id'] = $admin_id;
            $refund['refund_id'] = "";
            $refund['refund_source'] = "";
            $refund['order_sn'] = $order_sn;

            $refund_id = $model_refund->createRefund($refund);
            if (!$refund_id) {
                throw new Exception("生成退款单失败", 601);
            }

            $refund['id'] = $refund_id;
            $error->setOk($refund);

        } catch (Exception $e) {

            $error->setError($e->getCode(), $e->getMessage());

        }

        return $error;
    }
    /**
     * 更新退款订单
     *
     * @param $pay_sn
     * @param $pay_time
     * @param $user_id
     * @param $refund_type
     * @param $pay_total
     * @param $pay_refund
     * @param $reason
     * @param $admin_id
     * @return Error
     */
    public function updateRefundOrder($wxapp,$refund_sn,$pay_time,$pay_total,$pay_refund,$reason,$admin_id)
    {

        $error = new Error();
        $model_refund = new Refund();
        $refundData = $model_refund->getRefundOrderBySn($refund_sn);

        try {
            if (!$refundData) {
                throw new Exception("获取退款单信息", 601);
            }
            if ($refundData['status'] != Refund::$RefundStatus_Waiting) {
                throw new Exception("退款单状态不正确", 602);
            }
            if($pay_refund > $pay_total){
                throw new Exception("退款金额不能大于支付金额", 606);
            }
            $model_refund->startTrans();
            $now = time();
            $refund = [];
            $refund['updatetime'] = $now;
            $refund['pay_time'] = $pay_time;
            $refund['pay_amount'] = $pay_total;
            $refund['refund_amount'] = $pay_refund;
            $refund['reason'] = $reason;
            $refund['admin_id'] = $admin_id;
            $refund['refund_source'] = '';

            $refund_id = $model_refund->where('refund_sn',$refund_sn)->update($refund);
            if (!$refund_id) {
                throw new Exception("更新退款单失败", 601);
            }
            $error = $this->doRefundOrder($wxapp, $refund_sn, $admin_id);
            if($error->getCode()>0){
                throw new Exception($error->getText(), 608);
            }
            $error->setOk($refundData);
            $model_refund->commit();
        } catch (Exception $e) {
            $model_refund->rollback();
            $error->setError($e->getCode(), $e->getMessage());
        }

        return $error;
    }


    /**
     * 进行退款订单的退款操作
     *
     * @param $wxapp
     * @param $refund_sn
     * @param int $admin_id
     * @return Error
     */
    public function doRefundOrder($wxapp, $refund_sn, $admin_id = 0)
    {
        $error = new Error();
        $model_refund = new Refund();
        try {

            $refund_order = $model_refund->getRefundOrderBySn($refund_sn);
            if (!$refund_order) {
                throw new Exception("获取退款单信息", 601);
            }

            $old_status = $refund_order['status'];

            if ($old_status != Refund::$RefundStatus_Waiting) {
                throw new Exception("退款单状态不正确", 602);
            }

            // 开始退款
            $payment = $wxapp->payment;

            if ((new WechatEmu())->isWechatEmu()) {
                $refund_result['result_code'] = "SUCCESS";
                $refund_result['refund_id'] = "Emu_Refund_id";
                $refund_result['Wechat_Emu'] = true;


            } else {
                $refund_result = $payment->refund($refund_order['pay_sn'], $refund_order['refund_sn'], $refund_order['pay_amount'] *100, $refund_order['refund_amount']*100, $admin_id);
            }

            $new_status = Refund::$RefundStatus_Waiting;

            if ($refund_result['result_code'] == "SUCCESS") {
                $new_status = Refund::$RefundStatus_Finish;
            } else {
                $new_status = Refund::$RefundStatus_Failed;
            }

            $now = time();
            $refund_order['status'] = $new_status;
            $refund_order['refund_time'] = $now;
            $refund_order['admin_id'] = $admin_id;
            $refund_order['admin_time'] = $now;
            $refund_order['refund_id'] = $refund_result['refund_id'];
            $refund_order['refund_source'] = serialize($refund_result);

            $r = $refund_order->save();
            if (!$r) {
                throw new Exception("保存退款单失败", 602);
            }
            $error->setOk($refund_order);

        } catch (Exception $e) {

            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }

    /**
     * 生成退款单号 30位
     *
     * @param $user_id
     * @return string
     */
    public function genRefundSn($user_id)
    {

        $begin_string = "R"; // 1
        $userid_string = str_pad($user_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $random_string = rand(100000, 999999); //6

        $ordersn = $begin_string . $time_string . $userid_string . $random_string; //30
        return $ordersn;
    }

    /**
     * 订单取消，是否退款
     * @param $order_data
     * @param $canceltime
     */
    public function getOrderRefund($order_data , $canceltime)
    {
        $refund_status = [BnbOrder::$OrderStatus_Paid, BnbOrder::$OrderStatus_PasswordSent];

        if ($order_data) {
            if (in_array($order_data['status'], $refund_status)) {

                if ($canceltime['state']['is_autorefund_time']) {
                    // 自动全额退款

                    $refund = $this->addRefundOrder($order_data['pay_sn'], $order_data['pay_time'], $order_data['user_id'], Refund::$RefundType_Auto, $order_data['pay_total'], $order_data['pay_total'], "用户主动取消订单", 0);

                    if ($refund->checkResult()) {

                        return true;

                    } else {
                        return false;
                    }

                } else {
                    // 人工审核

                    // 计算入住天数


                    if (!$canceltime['state']['is_checkin']) // 未到入住时间
                    {
                        $refund = $this->addRefundOrder($order_data['pay_sn'], $order_data['pay_time'], $order_data['user_id'], Refund::$RefundType_BnbOrder, $order_data['pay_total'], $order_data['pay_total'], "用户主动取消订单，未到入住时间", 0);
                        if ($refund->checkResult()) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    else    //已到入住时间
                    {

                        $inday_list = $canceltime['datelist'];

                        $pricelist = unserialize($order_data['price_list']);

                        $room_price = 0.00;
                        $total_price = 0.00;
                        foreach($pricelist as $tprice){
                            $p_price = $tprice['price'] ?? 0;
                            $total_price += $p_price;
                        }

                        foreach ($inday_list as $day) {
                            $price = $pricelist[$day]['price'] ?? 0;
                            $room_price += $price;
                        }
                        //$refund_money = $order_data['pay_total'] - $room_price;
                        $clean_money = $this->getRefundCleanFee($order_data['bnb_id'],$order_data['order_sn'],$inday_list);
                        $refund_money = $total_price - $room_price - $clean_money;

                        $refund = $this->addRefundOrder($order_data['pay_sn'], $order_data['pay_time'], $order_data['user_id'], Refund::$RefundType_BnbOrder, $order_data['pay_total'], $refund_money, "用户主动取消订单，入住" . count($inday_list) . "天,房费总计" . $room_price . "元", 0);

                        if ($refund->checkResult()) {
                            return true;
                        } else {
                            return false;
                        }
                    }


                }
            }
        }

        return true;
    }

    /**
     * 退款订单退除保证金和管理费
     * @param $wxapp
     * @param $bnb_order_sn
     * @param $deposit_amount
     * @param $deposit_fee
     * @param $service_fee
     * @param string $deposit_reason
     * @return Error
     * @throws \think\exception\PDOException
     */
    public function refundDepositServiceFee($wxapp,$refund_sn,$deposit_fee,$service_fee = 0,$deposit_reason = ''){

        $refund_model = new Refund();
        $orderbnb_model = new OrderBnb();
        $refunddata = $refund_model-> getRefundOrderByBnbOrderDeposit($refund_sn);
        $orderdata =  $orderbnb_model->getOrderBySn($refunddata['order_sn']);
        $adminid = session(config('session.Admin'))['id'];
        $error = new Error();
        //生成退款单数据
        if(!$refunddata){
            $error->setError(500,'退款单不存在');
        }

        if(!$refunddata['status'] != $refund_model::$RefundStatus_Waiting){
            $error->setError(500,'退款单状态不正确');
        }
        $refundMoney = $service_fee + $deposit_fee;
        $reason = "保证金共计 {$orderdata['deposit_amount']} 元,退款保证金 {$deposit_fee} 元";
        if($service_fee > 0){
            $reason .= ',退管理费 {$service_fee} 元';
        }
        $refund_updata = [
            'reason'    =>  $reason,
            'refund_amount' => $refundMoney,
            'status'        => Refund::$RefundStatus_Finish
        ];

        //订单保证金数据
        $deposit_state =  $orderbnb_model::$DepositStatus_Refund;
        if($orderdata['live_out_date'] == $orderdata['out_date']){
            $deposit_state =  $orderbnb_model::$DepositStatus_Partrefund;
        }
        $depositData = [
            'deposit_deduction_amount'  =>  $orderdata['deposit_amount'] - $deposit_fee,
            'deposit_deduction_reason'  =>  $deposit_reason,
            'deposit_deduction_adminid' =>  $adminid,
            'deposit_return_amount'     =>  $deposit_fee,
            'deposit_state'             =>  $deposit_state,
            'deposit_return_time'       =>  time()
        ];

        try{
            $refund_model->startTrans();
            if($deposit_fee > $orderdata['deposit_amount']){
                throw new Exception("保证金退除金额不能大于实际收取的保证金金额", 500);
            }

            $resRefund = $refund_model->where('id',$refunddata['id'])->update($refund_updata);
            $payRefund = $this->doRefundOrder($wxapp,  $refunddata['refund_sn'], $adminid);
            if($payRefund->getCode() > 0){
                throw new Exception($payRefund->getText(), 500);
            }

            $resOrder = (new OrderBnb())->updateOrderByID($orderdata['id'], $depositData);
            $refund_model->commit();
            $error->setOk();
        }catch(\Exception $e){
            $refund_model->rollback();
            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }

    /**
     * 获取超出免费保洁次数的保洁费用
     * @param $bnb_id
     * @param $bnb_order_sn
     * @param $inday_list
     * @return float|int
     */
    public function getRefundCleanFee($bnb_id,$bnb_order_sn,$inday_list){
        $bnbInfo = (new Bnb())->getBnb($bnb_id);
        $cleanData = (new OrderAddonClean())->getUsedCleanOrderFree($bnb_order_sn);
        $orderdata = (new OrderBnb())->getOrderBySn($bnb_order_sn);
        $times_used = count($cleanData);
        $times_can = ceil(count($inday_list)/config('setting.bnb_free_clean_days'));
        $extra_times = $times_used - $times_can;
        $clean_money = 0.00;
        if($extra_times > 0){
            $clean_money = $extra_times*$bnbInfo['fee_clean'];
        }
        return $clean_money;
    }

    /**
     * 自动生成保证金退款订单
     */
    public function autoCreateDespositRefund(){
        $state = [config('state.order_part_state'),config('state.order_finish_state')];
        $orderBnb_model = new OrderBnb();
        $orderdata = $orderBnb_model->where('status','IN',$state)->select();
        if($orderdata){
            foreach($orderdata as $odata){
                //离店保洁订单
                $clean_out = (new OrderAddonClean())->getAutoCleanOrder($odata['order_sn']);
                if($clean_out['finish_time'] > 0){
                    $deposit_fee = 0.00;
                    if($odata['deposit_state'] == $orderBnb_model::$DepositStatus_Cashed){
                        $deposit_fee = $odata['deposit_amount'];
                        $reason = '退除保证金';
                        $refundObj = (new BnbRefund())->addRefundOrder($odata['pay_sn'], $odata['pay_time'],$odata['user_id'],Refund::$RefundType_Deposit,$odata['pay_total'],$deposit_fee,$reason, $admin_id = 0,$odata['order_sn']);
                        if($refundObj->getCode() > 0){
                            continue;
                        }
                    }

                }
            }
        }

    }





}