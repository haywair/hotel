<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/7
 */

namespace app\common\base;

use app\common\model\Bnb;
use app\common\model\OrderBnb;
use app\common\model\OrderUserClean;
use app\common\model\Users;
use think\Exception;

class UserCleanOrder
{
    public static $OrderStatus_Cancel = 0;
    public static $OrderStatus_unVerify = 10;
    public static $OrderStatus_unPay = 20;
    public static $OrderStatus_Paid = 30;
    public static $OrderStatus_PasswordSent = 40;
    public static $OrderStatus_Finish = 50;


    /**
     * 生成用户保洁订单数据
     *
     * @param $userid
     * @param $order_sn
     * @param $clean_numbers
     * @return Error
     */
    public function getCleanOrderData($userid, $order_sn, $clean_numbers)
    {
        $error = $this->getCleanOrderDataInfo($userid, $order_sn, $clean_numbers);
        if ($error->checkResult()) {
            $data = $error->getData();
            $data['price'] = $this->getCleanOrderPrice($data['bnb'], $data['clean'],$data['order']);
            $error->setOk($data);
        }
        return $error;
    }

    /**
     *  保存用户保洁数据到数据库
     *
     * @param $userid
     * @param $order_sn
     * @param $clean_numbers
     * @return Error
     */
    public function saveCleanOrder($userid, $order_sn, $clean_numbers)
    {
        $error = $this->getCleanOrderData($userid, $order_sn, $clean_numbers);
        if ($error->checkResult()) {
            $data = $error->getData();

            $model_clean = new OrderUserClean();
            try {
                $model_clean->startTrans();

                $orderdata = $this->createCleanOrderData($data['user'], $data['bnb'], $data['order'], $data['price']);

                $order_id = $model_clean->createOrder($orderdata);
                if ($order_id) {
                    $orderdata['id'] = $order_id;
                }

                $error->setOk($orderdata);

                $model_clean->commit();

            } catch (Exception $e) {

                $model_clean->rollback();

                $error->setError($e->getCode(), $e->getMessage(), null);
            }

        }

        return $error;
    }


    /**
     * 整理用户保洁订单数据
     *
     * @param $userinfo
     * @param $bnbinfo
     * @param $orderinfo
     * @param $price
     * @return array
     */
    private function createCleanOrderData($userinfo, $bnbinfo, $orderinfo, $price)
    {

        $now = time();

        $order = [];
        $order['status'] = self::$OrderStatus_unVerify;
        if (!config('setting.order_need_verify')) {
            $order['status'] = self::$OrderStatus_unPay;
        }

        $order['createtime'] = $now;
        $order['updatetime'] = 0;
        $order['clean_order_sn'] = $this->genOrderSn($userinfo['id'], $bnbinfo['id'], $bnbinfo['area_city_code']);
        $order['order_sn'] = $orderinfo['order_sn'];
        $order['user_id'] = $userinfo['id'];
        $order['clean_order_total'] = $price['clean_order_total'];
        $order['clean_numbers'] = $price['clean_numbers'];
        $order['free_numbers'] = $orderinfo['free_clean_numbers'];
        $order['price'] = $price['price'];
        $order['cancel_time'] = 0;
        $order['order_time'] = $now;
        $order['pay_time'] = 0;
        $order['pay_sn'] = "";
        $order['trade_no'] = "";

        return $order;
    }


    /**
     * 获取用户保洁订单价格
     *
     * @param $bnbinfo
     * @param $clean_numbers
     * @return array
     */
    private function getCleanOrderPrice($bnbinfo, $clean_numbers,$orderinfo)
    {
        $data = [];
        $data['price'] = $bnbinfo['fee_clean'];
        $data['clean_numbers'] = $clean_numbers;
        $data['clean_order_total'] = $data['price'] * ($data['clean_numbers']-$orderinfo['free_clean_numbers']);

        return $data;

    }

    /**
     * 整理并验证用户数据
     *
     * @param $userid
     * @param $order_sn
     * @param $clean_numbers
     * @return Error
     */
    private function getCleanOrderDataInfo($userid, $order_sn, $clean_numbers)
    {
        $error = new Error();
        $data = [];
        try {

            $clean_numbers = intval($clean_numbers);
            if ($clean_numbers <= 0) {
                throw new Exception("预订保洁次数不正确", 500);
            }

            if ($userid) {
                // 用户信息
                $user = (new Users())->getUserById($userid);
                if (!$user) {
                    throw new Exception("无法获取用户信息", 500);
                }
                $data['user'] = $user->toArray();
            } else {
                $data['user'] = ['id' => 0];
            }
            // 检查订单
            $order = (new OrderBnb())->getOrderBySn($order_sn);
            if (!$order) {
                throw new Exception("无法获取订单信息", 500);
            }

            if ($order['status'] <= self::$OrderStatus_unVerify) {
                throw new Exception("订单状态不正确", 500);
            }

            $data['order'] = $order->toArray();

            $bnb = (new Bnb())->getBnb($order['bnb_id']);
            if (!$bnb) {
                throw new Exception("获取民宿信息出错", 500);
            }

            if ($bnb['status'] < Bnb::$BnbStatus_OK) {
                throw new Exception('民宿当前状态不正确', 501);
            }

            if ($bnb['fee_clean'] <= 0) {
                throw new Exception('民宿保洁费用设置错误', 501);
            }


            if (($order['user_id'] != $userid) && ($userid)) {
                throw new Exception('您无权对此订单下保洁订单', 501);
            }

            $data['bnb'] = $bnb->toArray();

            $data['clean'] = $clean_numbers;

            $error->setOk($data);

        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage(), null);
        }

        return $error;
    }


    /**
     * 创建订单号 总共42位
     * @param $user_id
     * @param $bnb_id
     * @return string
     */
    private function genOrderSn($user_id, $bnb_id, $city_code)
    {
        $begin_string = "C"; // 1
        $userid_string = str_pad($user_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $bnb_string = str_pad($bnb_id, 7, "0", STR_PAD_LEFT); // 7
        $random_string = rand(10000, 99999); //5

        $ordersn = $begin_string . $time_string . $city_code . $userid_string . $bnb_string . $random_string; //42
        return $ordersn;
    }
}