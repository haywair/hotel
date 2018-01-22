<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/6
 */

namespace app\common\base;

use app\common\model\OrderBnb;
use app\common\model\OrderUserClean;
use app\common\model\Pay;
use think\Exception;

class BnbPay
{

    public static $PayStatus_Deleted = -1;
    public static $PayStatus_Disabled = 0;
    public static $PayStatus_Unpay = 1;
    public static $PayStatus_Failed = 8;
    public static $PayStatus_Paid = 9;

    /**
     * 创建支付订单，用于单条订单数据
     *
     * @param $userid
     * @param $order_sn
     * @return Error
     */
    public function createPayOrder($userid, $order_sn)
    {
        $error = $this->getPayOrderData($userid, $order_sn);
        if ($error->checkResult()) {
            $data = $error->getData();
            $pay = [];

            $now = time();

            $pay['status'] = self::$PayStatus_Unpay;
            $pay['createtime'] = $now;
            $pay['updatetime'] = 0;
            $pay['pay_type'] = $data['order_type'];
            $pay['pay_sn'] = $this->genPaySn($userid);
            $pay['order_sn'] = $order_sn;
            $pay['users_id'] = $userid;
            $pay['pay_total'] = $data['pay_total'];
            $pay['pay_time'] = 0;
            $pay['trade_no'] = "";
            $pay['trade_source'] = "";

            $payid = (new Pay())->createPay($pay);
            if ($payid) {
                $pay['id'] = $payid;
                $error->setOk($pay);
            } else {
                $error->setError(580, '保存支付订单信息失败', null);
            }
        }

        return $error;
    }


    /**
     * 根据订单sn列表，获取支付数据
     * @param $userid
     * @param $order_sn
     * @return Error
     */
    public function getPayOrderData($userid, $order_sn)
    {
        $error = new Error();

        $paydata = [];
        $paydata['pay_total'] = 0.00;
        $paydata['order_sn'] = "";
        $paydata['order_type'] = "B";
        $paydata['user_id'] = 0;
        // 获取不同订单数据
        try {

            $order = substr($order_sn, 0, 1);
            if ($order == "B") {

                $e = $this->getBnbOrder($userid, $order_sn);
                if ($e->checkResult()) {
                    $bnb_orders = $e->getData();
                    if ($bnb_orders) {
                        $paydata['pay_total'] = $bnb_orders['pay_total'];
                        $paydata['order_sn'] = $bnb_orders['order_sn'];
                        $paydata['order_type'] = "B";
                        $paydata['user_id'] = $bnb_orders['user_id'];
                    }

                } else {
                    throw new Exception($e->getText(), $e->getCode());
                }


            } else if ($order == "C") {

                $e = $this->getCleanOrder($userid, $order_sn);
                if ($e->checkResult()) {
                    $clean_orders = $e->getData();
                    if ($clean_orders) {
                        $paydata['pay_total'] = $clean_orders['pay_total'];
                        $paydata['order_sn'] = $clean_orders['order_sn'];
                        $paydata['order_type'] = "C";
                        $paydata['user_id'] = $clean_orders['user_id'];
                    }
                } else {
                    throw new Exception($e->getText(), $e->getCode());
                }

            } else {
                throw new Exception($order_sn . "订单类型不正确", 504);
            }

            $error->setOk($paydata);
        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage());
        }

        return $error;
    }

    /**
     *  获取民宿订单数据
     * @param $user_id
     * @param $bnb_order_sn
     * @return Error
     */
    private function getBnbOrder($user_id, $bnb_order_sn)
    {

        $error = new Error();
        $datalist = [];
        try {
            if ($bnb_order_sn) {

                $orderlist = (new OrderBnb())->getOrderListBySnList([$bnb_order_sn]);
                if ($orderlist) {

                    if (isset($orderlist[$bnb_order_sn])) {
                        $order = $orderlist[$bnb_order_sn];
                        if ($order['status'] != BnbOrder::$OrderStatus_unPay) {
                            throw new Exception("订单" . $bnb_order_sn . "状态不正确", 570);
                        }

                        if ($order["user_id"] != $user_id) {
                            throw new Exception("订单" . $bnb_order_sn . "不属于此用户", 570);
                        }

                        $datalist['pay_total'] = $order['pay_total'];
                        $datalist['order_sn'] = $bnb_order_sn;
                        $datalist['order_id'] = $order['id'];
                        $datalist['user_id'] = $user_id;


                    } else {
                        throw new Exception("订单" . $bnb_order_sn . "不存在", 570);
                    }

                } else {
                    throw new Exception("获取订单信息失败", 570);
                }
            }
            $error->setOk($datalist);
        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage(), null);
        }
        return $error;
    }

    /**
     * 获取用户保洁订单数据
     * @param $user_id
     * @param $clean_order_sn
     * @return Error
     */
    private function getCleanOrder($user_id, $clean_order_sn)
    {

        $error = new Error();
        $datalist = [];
        try {
            if ($clean_order_sn) {

                $orderlist = (new OrderUserClean())->getOrderListBySnList([$clean_order_sn]);
                if ($orderlist) {

                    if (isset($orderlist[$clean_order_sn])) {

                        $order = $orderlist[$clean_order_sn];

                        if ($order['status'] != UserCleanOrder::$OrderStatus_unPay) {
                            throw new Exception("订单" . $clean_order_sn . "状态不正确", 570);
                        }

                        if ($order["user_id"] != $user_id) {
                            throw new Exception("订单" . $clean_order_sn . "不属于此用户", 570);
                        }

                        $datalist['pay_total'] = $order['pay_total'];
                        $datalist['order_sn'] = $clean_order_sn;
                        $datalist['order_id'] = $order['id'];
                        $datalist['user_id'] = $user_id;

                    } else {
                        throw new Exception("订单" . $clean_order_sn . "不存在", 570);
                    }

                } else {
                    throw new Exception("获取订单信息失败", 570);
                }
            }

            $error->setOk($datalist);
        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage(), null);
        }
        return $error;
    }


    /**
     * 支付订单号， 30位
     * @param $user_id
     * @return string
     */
    private function genPaySn($user_id)
    {
        $begin_string = "P"; // 1
        $userid_string = str_pad($user_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $random_string = rand(100000, 999999); //6

        $ordersn = $begin_string . $time_string . $userid_string . $random_string; //30
        return $ordersn;

    }
}