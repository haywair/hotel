<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/7
 */

namespace app\common\base;

use app\common\model\OrderBnb;
use app\common\model\OrderUserClean;
use app\common\model\Pay;
use app\common\model\Refund;
use app\common\model\Userinfo;
use Carbon\Carbon;
use think\Exception;

class BnbPaid
{
    private $wx_app = null;

    public function __construct($wx_app)
    {
        $this->wx_app = $wx_app;
    }

    /**
     * 用于微信支付回调，更新支付单，订单状态，并生成保洁订单。如果状态不正确，自动进行微信全额退款
     *
     * @param $pay_nofity
     * @return Error
     */
    public function paid($pay_nofity)
    {
        $pay_sn = $pay_nofity['out_trade_no'];
        $pay_total = $pay_nofity['total_fee'] / 100;
        $transaction_id = $pay_nofity['transaction_id'];
        $pay_time = Carbon::createFromFormat('YmdHis', $pay_nofity['time_end'])->timestamp;

        $user_id = 0;

        $order_data = [];

        $data = $this->getPayData($pay_sn, $pay_total);
        if ($data->checkResult()) {
            //获取并验证数据成功，更新订单状态
            $order_data = $data->getData();

            $user_id = $order_data['user_id'];

            if ($order_data['order_type'] == "B") {
                $paid_data = $this->makeBnbOrderPaid($order_data['order_sn'], $pay_sn, $transaction_id, $pay_time, $pay_nofity);
                $data = $paid_data;
            } else if ($order_data['order_type'] == "C") {
                $paid_data = $this->makeCleanOrderPaid($order_data['order_sn'], $pay_sn, $transaction_id, $pay_time, $pay_nofity);
                $data = $paid_data;
            }

        }

        if (!($data->checkResult())) {
            // 执行自动退款
            $bnbrefund = new BnbRefund();

            // 生成自动退款订单
            $refund_data = $bnbrefund->addRefundOrder($pay_sn, $pay_time, $user_id, Refund::$RefundType_Auto, $pay_total, $pay_total, $data->getText(), 0);
            if ($refund_data->checkResult()) {

                $refunddata = $refund_data->getData();
                // 不进行退款，放入计划任务中退款
                //$bnbrefund->doRefundOrder($refunddata['refund_sn'], 0);

                //不修改支付订单状态，保留原状态
            }
        }
        else
        {

            // 增加用户消费累计金额
            if ($user_id >0) {
                (new Userinfo())->setUserCostMoney($user_id, $pay_total);
            }

            if ($order_data['order_type'] == "B") {

                //发送支付消息
                //(new UserMessage())->sendMessage($order_data['order_sn'],config('message.order_pay_msg'));

                // 发送预订成功模板消息
                (new WxMessage())->orderpaid($order_data);
            }
        }


        return $data;
    }

    /**
     * 更新保洁订单支付状态
     *
     *
     * @param $order_sn
     * @param $pay_sn
     * @param $transaction_id
     * @param $pay_time
     * @param $pay_nofity
     * @return Error
     */
    private function makeCleanOrderPaid($order_sn, $pay_sn, $transaction_id, $pay_time, $pay_nofity)
    {
        $error = new Error();

        $model_order_user_clean = new OrderUserClean();

        try {
            $model_order_user_clean->startTrans();

            $order = $model_order_user_clean->getOrderBySn($order_sn);
            if (!$order) {
                throw new Exception("修改订单支付状态失败", 522);
            }
            $order['status'] = BnbOrder::$OrderStatus_Paid;
            $order['pay_sn'] = $pay_sn;
            $order['pay_time'] = $pay_time;
            $order['trade_no'] = $transaction_id;
            $rt = $order->save();
            if (!$rt) {
                throw new Exception("修改订单支付状态失败", 520);
            }

            // 生成付费保洁订单
            if ($order['clean_numbers'] > 0) {
                $r = $this->createCleanOrder($order_sn, $order['order_sn'], $order['user_id'], $order['clean_numbers'], $order['price']);
                if (!$r) {
                    throw new Exception("生成付费保洁订单失败", 520);
                }
            }

            // 修改支付单状态
            $r = (new Pay())->setPayStatus($pay_sn, BnbPay::$PayStatus_Paid, $pay_time, $transaction_id, $pay_nofity);
            if (!$r) {
                throw new Exception("修改支付订单状态失败", 520);
            }
            $model_order_user_clean->commit();

            $error->setOk($order->toArray());

        } catch (Exception $e) {
            $model_order_user_clean->rollback();
            $error->setError($e->getCode(), $e->getMessage());
        }


        return $error;
    }

    /**
     * 更新民宿订单支付状态
     *
     * @param $order_sn
     * @param $pay_sn
     * @param $transaction_id
     * @param $pay_time
     * @param $pay_nofity
     * @return Error
     */
    private function makeBnbOrderPaid($order_sn, $pay_sn, $transaction_id, $pay_time, $pay_nofity)
    {

        $error = new Error();

        $model_order_bnb = new OrderBnb();

        try {
            $model_order_bnb->startTrans();


            // 修改订单状态
            $order = $model_order_bnb->getOrderBySn($order_sn);
            if (!$order) {
                throw new Exception("修改订单支付状态失败", 522);
            }

            $order['status'] = BnbOrder::$OrderStatus_Paid;
            $order['pay_sn'] = $pay_sn;
            $order['pay_time'] = $pay_time;
            $order['trade_no'] = $transaction_id;
            $rt = $order->save();
            if (!$rt) {
                throw new Exception("修改订单支付状态失败", 520);
            }


            $clean = (new BnbCleanLogic())->onBnbOrderPaid($order);
            if (!$clean->checkResult())
            {
                throw new Exception("创建保洁订单失败", 520);
            }

            // 修改支付单状态
            $r = (new Pay())->setPayStatus($pay_sn, BnbPay::$PayStatus_Paid, $pay_time, $transaction_id, $pay_nofity);
            if (!$r) {
                throw new Exception("修改支付订单状态失败", 520);
            }

            $model_order_bnb->commit();

            $error->setOk($order->toArray());

        } catch (Exception $e) {
            $model_order_bnb->rollback();
            $error->setError($e->getCode(), $e->getMessage());
        }

        return $error;
    }

    /**
     * 获取并验证订单数据
     * @param $pay_sn
     * @param $pay_total
     * @return Error
     */
    private function getPayData($pay_sn, $pay_total)
    {
        $error = new Error();
        $pay = (new Pay())->getPayBySn($pay_sn);
        if (!$pay) {
            $error->setError(590, "无法获取支付单号" . ['pay_sn' => $pay_sn]);
        } else {

            if ($pay['status'] != BnbPay::$PayStatus_Unpay) {
                $error->setError(590, "支付单状态不正确", ['pay_sn' => $pay_sn]);
            } else {
                $error = (new BnbPay())->getPayOrderData($pay['users_id'], $pay['order_sn']);
                if ($error->checkResult()) {
                    $orderdata = $error->getData();
                    if ($pay['pay_total'] != $orderdata['pay_total']) {
                        $error->setError(599, "支付金额与订单金额不符。订单金额：" . $orderdata['pay_total'] . " 支付金额：" . $pay['pay_total']);
                    } else if ($pay['pay_total'] != $pay_total) {
                        $error->setError(599, "实际支付金额与订单金额不符。订单金额：" . $orderdata['pay_total'] . " 实际支付金额：" . $pay_total);
                    }
                }
            }
        }
        return $error;
    }


}