<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/10
 */

namespace app\common\base;

use app\admin\model\Bnb;
use app\common\model\BnbCleanPhoto;
use app\common\model\OrderAddonClean;
use app\common\model\OrderBnb;
use app\common\model\OrderClean;
use app\common\model\OrderCleanPhoto;
use Carbon\Carbon;
use think\Exception;

class BnbClean
{

    /**
     * 获取保洁订单数据
     *
     * @param $user_id
     * @param $bnb_order_sn
     * @param $clean_date
     * @param $clean_demo
     * @return Error
     */
    public function createBnbCleanOrder($user_id, $bnb_order_sn, $clean_date, $clean_demo)
    {
        $error = $this->getBnbCleanData($user_id, $bnb_order_sn, $clean_date);
        if ($error->checkResult()) {
            $data = $error->getData();
            $data['clean_demo'] = $clean_demo;

            $bc = $this->getBnbCleanDbData($data);

            $error = $this->createBnbCleanDbOrder($data['addon_clean_id'], $bc);

        }

        return $error;
    }

    /**
     *  更新离店订单
     *
     * @param $bnb_order_sn
     * @param $clean_date
     * @return Error
     */
    public function updateAutoBnbCleanOrder($bnb_order_sn, $clean_date)
    {
        $error = $this->getAutoBnbCleanData($bnb_order_sn,$clean_date);
        if ($error->checkResult())
        {
            $data = $error->getData();
            $bc = $this->getBnbCleanDbData($data);
            $error = $this->createBnbCleanDbOrder($data['addon_clean_id'], $bc);
        }

        return $error;
    }


    /**
     * 创建保洁订单
     *
     * @param $addon_clean_id
     * @param $clean_dbdata
     * @return Error
     */
    public function createBnbCleanDbOrder($addon_clean_id, $clean_dbdata)
    {
        $error = new Error();
        $model_orderclean = new OrderClean();

        try {
            $cid = $model_orderclean->createOrderClean($clean_dbdata);

            if (!$cid) {
                throw new Exception("生成保洁订单失败", 570);
            }


            //更新用户保洁数据
            $r = (new OrderAddonClean())->updateOrderCreated($addon_clean_id, $clean_dbdata["order_sn"] , $clean_dbdata['clean_start_time']);
            if (!$r) {
                throw new Exception("生成保洁订单失败", 571);
            }

            // 图片对比表
            if ($clean_dbdata['photo_compare']) {
                $bnbcleanphoto = (new BnbCleanPhoto())->getBnbCleanPhoto($clean_dbdata['bnb_id']);
                if ($bnbcleanphoto) {
                    $cplist = [];
                    $now = time();
                    foreach ($bnbcleanphoto as $bcp) {
                        $cp = [];
                        $cp['status'] = "1";
                        $cp['createtime'] = $now;
                        $cp['updatetime'] = 0;
                        $cp['order_clean_id'] = $cid;
                        $cp['name'] = $bcp['name'];
                        $cp['image'] = $bcp['image'];
                        $cp['upload_time'] = 0;
                        $cp['upload_image'] = "";
                        $cp['compare_value'] = 0.00;
                        $cp['need_admin'] = "0";
                        $cp['admin_id'] = 0;
                        $cp['admin_verify_state'] = "0";
                        $cplist[] = $cp;
                    }

                    if ($cplist) {
                        (new OrderCleanPhoto())->insertAll($cplist);
                    }
                }
            }


            $error->setOk($addon_clean_id);

        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }

    /**
     * 保洁订单数据
     *
     * @param $cleandata
     * @return array
     */
    public function getBnbCleanDbData($cleandata)
    {
        $now = time();

        $dbdata = [];
        $dbdata['status'] = OrderClean::$BnbCleanStatus_Waiting;
        $dbdata['createtime'] = $now;
        $dbdata['updatetime'] = 0;
        $dbdata['order_sn'] = $this->genCleanOrderSn($cleandata['bnbinfo']['area_county_code']);
        $dbdata['province_code'] = $cleandata['bnbinfo']['area_province_code'];
        $dbdata['city_code'] = $cleandata['bnbinfo']['area_county_code'];
        $dbdata['address'] = $cleandata['bnbinfo']['area_address'];
        $dbdata['map_lng'] = $cleandata['bnbinfo']['map_lng'];
        $dbdata['map_lat'] = $cleandata['bnbinfo']['map_lat'];
        $dbdata['bnb_id'] = $cleandata['bnbinfo']['id'];
        $dbdata['demo_content'] = isset($cleandata['clean_demo'])?$cleandata['clean_demo']:'';
        $dbdata['contact_name'] = $cleandata['orderinfo']['contact_name'];
        $dbdata['contact_mobile'] = $cleandata['orderinfo']['contact_mobile'];
        $dbdata['room_space'] = $cleandata['bnbinfo']['room_space'];
        $dbdata['fee_clean'] = $cleandata['bnbinfo']['fee_cleaner'];
        $dbdata['admin_id'] = 0;
        $dbdata['order_time'] = $now;
        $dbdata['clean_start_time'] = Carbon::createFromFormat("Y-m-d H:i:s", $cleandata['clean_date'] . " 00:00:00")->timestamp;;
        $dbdata['clean_end_time'] = $dbdata['clean_start_time'];
        $dbdata['force_cleaner'] = 0;
        $dbdata['cleaner_id'] = 0;
        $dbdata['work_end_time'] = 0;
        $dbdata['photo_compare'] = 1;
        $dbdata['verify_time'] = 0;
        $dbdata['verify_score'] = 0;
        $dbdata['verify_userid'] = 0;
        $dbdata['fee_cleaner'] = 0;
        $dbdata['addon_clean_id'] = $cleandata['addon_clean_id'];

        return $dbdata;
    }


    /**
     *  获取用户保洁数据
     *
     * @param $user_id
     * @param $bnb_order_sn
     * @param string $clean_date
     * @return Error
     */
    public function getBnbCleanData($user_id, $bnb_order_sn, $clean_date = "")
    {
        $error = new Error();

        try {

            // 判断订单状态
            $bnb_order = (new OrderBnb())->getOrderBySn($bnb_order_sn);
            if (!$bnb_order) {
                throw new Exception("无法获取订单信息", 540);
            }

            if (($bnb_order['user_id'] != $user_id) && ($user_id !=0)) {
                throw new Exception("没有权限操作此订单", 541);
            }

            if (($bnb_order['status'] != BnbOrder::$OrderStatus_Paid) && ($bnb_order['status'] != BnbOrder::$OrderStatus_PasswordSent)) {
                throw new Exception("订单状态不正确", 542);
            }


            try {
                $begin = Carbon::createFromFormat('Y-m-d', $bnb_order['in_date']);
                $end = Carbon::createFromFormat('Y-m-d', $bnb_order['out_date']);
                if ($clean_date) {
                    $cleantime = Carbon::createFromFormat('Y-m-d', $clean_date);
                }
                $now = Carbon::createFromFormat('Y-m-d', date("Y-m-d", time()));

            } catch (\Exception $e) {
                throw new Exception("请输入正确的保洁日期", 500);
            }
            if (isset($cleantime)) {
                if (($cleantime->lt($begin)) || ($end->lt($cleantime))) {
                    throw new Exception("只能预订订单日期内的日期", 500);
                }
                if (($cleantime->eq($begin)) || ($end->eq($cleantime))) {
                    throw new Exception("入住和离店当天不需要额外的保洁", 500);
                }

                if ($cleantime->lte($now)) {
                    throw new Exception("只能预订明天开始的保洁", 500);
                }
            }
            // 判断是否有额外保洁

            $cleanlist = (new OrderAddonClean())->getAddonCleanList($bnb_order_sn);
            if ((!$cleanlist) || (!(isset($cleanlist[OrderAddonClean::$AddonCleanStatus_OK])))) {
                throw new Exception("您现在没有可用的保洁服务，请先购买", 502);
            }

            // 检查保洁日期是否重复
            if ($clean_date) {
                if (isset($cleanlist[OrderAddonClean::$AddonCleanStatus_Order])) {
                    $aco = $cleanlist[OrderAddonClean::$AddonCleanStatus_Order];
                    foreach ($aco as $co) {
                        $dt = date('Y-m-d', $co['clean_time']);
                        if ($dt == $clean_date) {
                            throw new Exception("您已经预订过当日的保洁服务了", 502);
                        }
                    }

                }
            }


            // 获取民宿信息
            $bnbinfo = (new Bnb())->getBnbInfo($bnb_order['bnb_id']);
            if (!$bnbinfo) {
                throw new Exception("无法获取民宿信息", 502);
            }

            if ($bnbinfo['status'] < \app\common\model\Bnb::$BnbStatus_OK) {
                throw new Exception("民宿状态不正确", 502);
            }

            $cleandata = [];

            $cd = $cleanlist[OrderAddonClean::$AddonCleanStatus_OK][0];

            $cleandata['addon_clean_id'] = $cd['id'];
            $cleandata['bnb_id'] = $bnb_order['bnb_id'];
            $cleandata['bnb_order_sn'] = $cd['bnb_order_sn'];
            $cleandata['order_sn'] = $cd['order_sn'];
            $cleandata['user_id'] = $cd['user_id'];
            $cleandata['price'] = $cd['price'];

            if ($now->lte($begin)) {
                $cleandata['begin_date'] = $begin->addDay()->format("Y-m-d");
            } else {
                $cleandata['begin_date'] = $now->addDay()->format("Y-m-d");
            }
            $cleandata['end_date'] = $end->addDay(-1)->format("Y-m-d");

            if ($clean_date) {
                $cleandata['clean_date'] = $clean_date;
            }

            $cleandata['bnbinfo'] = $bnbinfo->toArray();
            $cleandata['orderinfo'] = $bnb_order->toArray();

            $error->setOk($cleandata);

        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }

    /**
     * 获取离店保洁订单数据
     *
     * @param $bnb_order_sn
     * @param string $clean_date
     */
    public function getAutoBnbCleanData($bnb_order_sn, $clean_date = "")
    {
        $error = new Error();

        try {

            // 判断订单状态
            $bnb_order = (new OrderBnb())->getOrderBySn($bnb_order_sn);
            if (!$bnb_order) {
                throw new Exception("无法获取订单信息", 540);
            }

            // 获取民宿信息
            $bnbinfo = (new Bnb())->getBnbInfo($bnb_order['bnb_id']);
            if (!$bnbinfo) {
                throw new Exception("无法获取民宿信息", 502);
            }

            if ($bnbinfo['status'] < \app\common\model\Bnb::$BnbStatus_OK) {
                throw new Exception("民宿状态不正确", 502);
            }

            $clean_out = (new OrderAddonClean())->getAutoCleanOrder($bnb_order_sn);
            if (!$clean_out)  {
                throw new Exception("获取离店保洁数据失败", 502);
            }


            $cleandata = [];


            $cleandata['addon_clean_id'] = $clean_out['id'];
            $cleandata['bnb_id'] = $bnb_order['bnb_id'];
            $cleandata['bnb_order_sn'] = $clean_out['bnb_order_sn'];
            $cleandata['order_sn'] = $clean_out['order_sn'];
            $cleandata['user_id'] = $clean_out['user_id'];
            $cleandata['price'] = $clean_out['price'];


            $end = Carbon::createFromFormat('Y-m-d', $bnb_order['out_date']);

            if ($clean_date) {
                $cleandata['clean_date'] = $clean_date;
            }
            else
            {
                $cleandata['clean_date'] = $end->format('Y-m-d');
            }

            $cleandata['bnbinfo'] = $bnbinfo->toArray();
            $cleandata['orderinfo'] = $bnb_order->toArray();

            $error->setOk($cleandata);

        } catch (Exception $e) {
            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }


    /**
     * 保洁订单编号 30位
     *
     * @param $citycode
     * @return string
     */
    private function genCleanOrderSn($citycode)
    {
        if (!$citycode) {
            $citycode = "370100";
        }
        $begin_string = "E"; // 1
        $time_string = date('YmdHis', time()); //14
        $random_string = rand(100000000, 999999999); //9

        $ordersn = $begin_string . $citycode . $time_string . $random_string; //30
        return $ordersn;
    }


}