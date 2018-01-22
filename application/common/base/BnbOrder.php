<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/3
 */

namespace app\common\base;

use app\common\model\Area;
use app\common\model\Bnb;
use app\common\model\OrderBnb;
use app\common\model\Users;
use app\common\model\UserVoucher;
use Carbon\Carbon;
use think\Exception;

class BnbOrder
{
    public static $OrderStatus_Delete = -1;
    public static $OrderStatus_Cancel = 0;
    public static $OrderStatus_unVerify = 10;
    public static $OrderStatus_unPay = 20;
    public static $OrderStatus_Paid = 30;
    public static $OrderStatus_PasswordSent = 40;

    public static $OrderStatus_PartFinish = 45;
    public static $OrderStatus_Finish = 50;


    /**
     * 获取民宿订单数据
     *
     * @param $userid
     * @param $bnb_id
     * @param $begin_date
     * @param $end_date
     * @param $people_numbers
     * @param $contact_name
     * @param $contact_mobile
     * @param string $contact_content
     * @param int $voucher_id
     * @param int $clean_numbers
     * @return Error
     */
    public function getOrderData($userid, $bnb_id, $begin_date, $end_date, $people_numbers, $contact_name, $contact_mobile, $contact_content = '', $voucher_id = 0, $clean_numbers = 0)
    {
        $error = new Error();
        $error = $this->getOrderDataInfo($userid, $bnb_id, $voucher_id, $people_numbers, $contact_name, $contact_mobile, $contact_content, $begin_date, $end_date, $clean_numbers);
        if ($error->checkResult()) {
            $data = $error->getData();
            $orderdata = $this->showOrderData($data['user'], $data['bnb'], $data['date'], $data['area'], $data['contact'], $data['pricelist'], $data['voucher'], $data['clean']);
            $error->setOk($orderdata);
        }
        return $error;
    }

    /**
     * 保存民宿订单数据到数据库
     *
     * @param $userid
     * @param $bnb_id
     * @param $begin_date
     * @param $end_date
     * @param $people_numbers
     * @param $contact_name
     * @param $contact_mobile
     * @param string $contact_content
     * @param int $voucher_id
     * @param int $clean_numbers
     * @return Error
     */
    public function createBnbOrder($userid, $bnb_id, $begin_date, $end_date, $people_numbers, $contact_name, $contact_mobile, $contact_content = '', $voucher_id = 0, $clean_numbers = 0)
    {

        $error = new Error();

        $error = $this->getOrderData($userid, $bnb_id, $begin_date, $end_date, $people_numbers, $contact_name, $contact_mobile, $contact_content, $voucher_id, $clean_numbers);
        if ($error->checkResult()) {
            $dborder = $this->createOrderData($error->getData());
            $error = (new BnbOrderLogic())->onCreate($dborder);
        }
        return $error;
    }


    /**
     * 返回民宿订单数据，包含价格
     *
     * @param $userinfo
     * @param $bnbinfo
     * @param $dateinfo
     * @param $areainfo
     * @param $contactinfo
     * @param $pricelist
     * @param $voucherlist
     * @param $cleaninfo
     * @return array
     */
    private function showOrderData($userinfo, $bnbinfo, $dateinfo, $areainfo, $contactinfo, $pricelist, $voucherlist, $cleaninfo)
    {
        $order = [];

        $order['area'] = $areainfo;
        $order['user'] = $userinfo;
        $order['bnb'] = $bnbinfo;
        $order['date'] = $dateinfo;
        $order['contact'] = $contactinfo;
        $order['daylist'] = $pricelist;
        $order['price'] = $this->getOrderPrice($bnbinfo, $pricelist, $voucherlist, $cleaninfo, $dateinfo);
        $order['voucher'] = $voucherlist;
        $order['clean'] = $cleaninfo;
        return $order;
    }


    private function getOrderPrice($bnbinfo, $pricelist, $voucherinfo, $clean, $dateinfo)
    {
        $orderdata = [];


        $server_setting = config('setting.bnb_service');

        // 房间价格列表
        $price_bnb = 0.00;
        $pl = [];
        if ($pricelist) {
            foreach ($pricelist as $d => $p) {
                $pd = [];
                $pd['price'] = $p['price'];
                $price_bnb += $p['price'];
                $pd['week'] = $p['week'];
                $pd['name'] = $p['name'];
                $pl[$d] = $pd;
            }
        }
        $orderdata['price_list'] = serialize($pl);

        $orderdata['pay_total'] = 0.00; //暂时
        $orderdata['order_original_total'] = 0.00; //暂时
        $orderdata['order_actually_total'] = 0.00; //暂时
        $orderdata['deposit_order_total'] = 0.00; //暂时

        $orderdata['order_total'] = 0.00; // 暂时

        $orderdata['room_amount'] = $price_bnb;
        $orderdata['clean_amount'] = $bnbinfo['fee_clean'];
        $orderdata['service_amount'] = money_formater($price_bnb * $server_setting['precent'] / 100);
        if ($orderdata['service_amount'] < $server_setting['min_fee']) {
            $orderdata['service_amount'] = $server_setting['min_fee'];
        }

        $orderdata['promotion_total'] = 0.00; // 暂时

        if ($voucherinfo['id']) {
            $orderdata['voucher_id'] = $voucherinfo['list'][$voucherinfo['id']]['voucher_id'];
        } else {
            $orderdata['voucher_id'] = 0;
        }
        $orderdata['user_voucher_id'] = $voucherinfo['id'];
        $orderdata['voucher_amount'] = $voucherinfo['voucher_amount'];

        if ($orderdata['room_amount'] < $orderdata['voucher_amount']) {
            $orderdata['voucher_amount'] = $orderdata['room_amount'];
        }


        $orderdata['discount_amount'] = 0.00;
        $orderdata['discount_adminid'] = 0;
        $orderdata['discount_time'] = 0;

        $orderdata['free_clean_numbers'] = intval($dateinfo['night'] / intval(config('setting.bnb_free_clean_days')));

        $orderdata['addon_clean_numbers'] = $clean['numbers'];
        $orderdata['addon_clean_price'] = $bnbinfo['fee_clean'];
        if ($clean['numbers'] > 0) {
            $orderdata['addon_clean_amount'] = $clean['numbers'] * $bnbinfo['fee_clean'];
        } else {
            $orderdata['addon_clean_amount'] = 0.00;
        }


        $orderdata['deposit_state'] = 1;
        $orderdata['deposit_amount'] = $bnbinfo['fee_deposit'];
        $orderdata['deposit_deduction_amount'] = 0.00;
        $orderdata['deposit_return_amount'] = $bnbinfo['fee_deposit'];

        // 优惠总价
        $orderdata['promotion_total'] = $orderdata['discount_amount'] + $orderdata['voucher_amount'];

        // 计算总价

        $orderdata['order_original_total'] = $orderdata['room_amount'] + $orderdata['service_amount'] + $orderdata['addon_clean_amount']; // 包含优惠价格

        $orderdata['order_total'] = $orderdata['order_original_total'] - $orderdata['promotion_total'];

        $orderdata['deposit_order_total'] = $orderdata['deposit_deduction_amount'];

        $orderdata['order_actually_total'] = $orderdata['order_total'] + $orderdata['deposit_order_total'];

        $orderdata['pay_total'] = $orderdata['order_total'] + $orderdata['deposit_amount'];

        return $orderdata;
    }


    private function createOrderData($orderdata)
    {
        $order = [];

        $now = time();

        $needVerify = true;
        if (!config('setting.order_need_verify')) {
            $needVerify = false;
        }

        $order['status'] = self::$OrderStatus_unVerify;
        if (!$needVerify) {
            $order['status'] = self::$OrderStatus_unPay;
        }

        $order['createtime'] = $now;
        $order['updatetime'] = 0;

        $order['order_sn'] = $this->genOrderSn($orderdata['user']['id'], $orderdata['bnb']['id'], $orderdata['area']['city_code']);
        $order['province_code'] = $orderdata['area']['province_code'];
        $order['city_code'] = $orderdata['area']['city_code'];
        $order['bnb_id'] = $orderdata['bnb']['id'];
        $order['user_id'] = $orderdata['user']['id'];
        $order['in_date'] = $orderdata['date']['begin'];
        $order['out_date'] = $orderdata['date']['end'];
        $order['night'] = $orderdata['date']['night'];
        $order['in_hour'] = $orderdata['bnb']['in_hour'];
        $order['out_hour'] = $orderdata['bnb']['out_hour'];


        $order['people_numbers'] = $orderdata['bnb']['order_room_people'];
        $order['people_infos'] = '';
        $order['contact_name'] = $orderdata['contact']['name'];
        $order['contact_phone'] = "";
        $order['contact_email'] = "";
        $order['contact_mobile'] = $orderdata['contact']['mobile'];
        $order['contact_content'] = $orderdata['contact']['content'];

        $order['cancel_time'] = 0;
        $order['order_time'] = $now;
        $order['verify_time'] = 0;
        if (!$needVerify) {
            $order['verify_time'] = $now;
        }

        $order['pay_time'] = 0;
        $order['password_time'] = 0;
        $order['finish_time'] = 0;

        $order['live_out_date'] = $orderdata['date']['end'];
        $order['live_night'] = $orderdata['date']['night'];

        $orderdata_price = $this->getOrderPrice($orderdata['bnb'], $orderdata['daylist'], $orderdata['voucher'], $orderdata['clean'], $orderdata['date']);

        $order = array_merge($order, $orderdata_price);

        return ($order);
    }


    /**
     * 检查并返回用户输入信息
     *
     * @param $userid
     * @param $bnb_id
     * @param $voucher_id
     * @param $people_numbers
     * @param $contact_name
     * @param $contact_mobile
     * @param $contact_content
     * @param $begin_date
     * @param $end_date
     * @param $clean_numbers
     * @return Error
     */
    private function getOrderDataInfo($userid, $bnb_id, $voucher_id, $people_numbers, $contact_name, $contact_mobile, $contact_content, $begin_date, $end_date, $clean_numbers)
    {

        $error = new Error();

        $data = [];

        try {

            // 预订日期 必须是今日或之后的

            try {
                $begin = Carbon::createFromFormat('Y-m-d H:i:s', $begin_date . " 00:00:00");
                $end = Carbon::createFromFormat('Y-m-d H:i:s', $end_date . " 00:00:00");
                $now = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d", time()) . " 00:00:00");
            } catch (\Exception $e) {
                throw new Exception("请输入正确的入住离店日期", 500);
            }


            if ($begin->lt($now)) {
                throw new Exception("只能预订当天或之后的房间", 500);
            }
            if ($end->lte($begin)) {
                throw new Exception("离店日期必须晚于入住日期", 500);
            }

            // 用户信息
            $user = (new Users())->getUserById($userid);
            if (!$user) {
                throw new Exception("无法获取用户信息", 500);
            }
            $data['user'] = $user->toArray();


            // 联系人信息
            $contact = [];
            if ($contact_name == "") {
                $contact_name = $user['user_truename'];
            }

            if ($contact_mobile == "") {
                $contact_mobile = $user['user_mobile'];
            }

            $contact['name'] = $contact_name;
            $contact['mobile'] = $contact_mobile;
            $contact['content'] = $contact_content;

            $data['contact'] = $contact;  // 联系人生成订单时不强制检查，保存入数据库时，再检查

            // 获取民宿信息
            $bnb = (new Bnb())->getBnb($bnb_id);
            if (!$bnb) {
                throw new Exception('无法获取民宿信息', 501);
            }

            if ($bnb['status'] < Bnb::$BnbStatus_OK) {
                throw new Exception('民宿当前状态不正确', 501);
            }

            if ($bnb['room_people'] <= 0) {
                throw new Exception('没有填写入住人数', 501);
            }


            if ($bnb['room_people'] < $people_numbers) {
                throw new Exception('超过了民宿最多入住人数', 501);
            }

            $data['bnb'] = $bnb->toArray();
            $data['bnb']['order_room_people'] = $people_numbers;

            // 区域信息
            $area = (new Area())->getAreaByCityCode($bnb['area_city_code']);
            if (!$area) {
                throw new Exception('此地区房间没有进行售卖', 501);
            }
            $data['area'] = $area;

            // 房间预订情况，返回价格
            $pricelist = (new BnbPrice())->getBnbPriceList($bnb_id, $begin_date, $end_date, "", true, false);
            if ($pricelist) {
                foreach ($pricelist as $date => $price) {
                    if ($price['sell'] == 0) {
                        throw new Exception($date . "房间已经被预订了", 550);
                    }
                }
            }
            $data['pricelist'] = $pricelist;


            //用户优惠券信息
            $voucher_list = (new UserVoucher())->getUserVoucherList($userid, UserVoucher::$VoucherStatus_OK);
            if (($voucher_id != 0) && (!(in_array($voucher_id, array_keys($voucher_list))))) {
                throw new Exception('优惠券已使用或过期', 502);
            }

            $data['voucher']['list'] = $voucher_list;
            $data['voucher']['id'] = $voucher_id;
            $data['voucher']['voucher_amount'] = 0.00;

            // 计算优惠券是否适用于订单
            $price_bnb = array_sum(array_column($pricelist, "price"));

            if ($voucher_id != 0) {
                $vinfo = $voucher_list[$voucher_id];

                if ($vinfo['use'] == false) {
                    throw new Exception("选择的优惠券已使用或过期", 503);
                }
                if (($vinfo['price_over'] == 0) || ($vinfo['price_over'] <= $price_bnb)) {

                    if ($vinfo['price_discount'] > $price_bnb) {
                        $vinfo['price_discount'] = $price_bnb;
                    }
                    $data['voucher']['voucher_amount'] = $vinfo['price_discount'];
                } else {
                    throw new Exception("选择的优惠券不适用于此订单", 570);
                }
            }


            // 预订日期
            $data['date']['begin'] = $begin_date;
            $data['date']['end'] = $end_date;
            $data['date']['night'] = count($pricelist);

            // 附加保洁服务
            $data['clean']['numbers'] = $clean_numbers;

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
        $begin_string = "B"; // 1
        $userid_string = str_pad($user_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $bnb_string = str_pad($bnb_id, 7, "0", STR_PAD_LEFT); // 7
        $random_string = rand(10000, 99999); //5

        $ordersn = $begin_string . $time_string . $city_code . $userid_string . $bnb_string . $random_string; //42
        return $ordersn;
    }

    public function reckonOrderPrice($bnb_id, $begin_date, $end_date)
    {
        // 房间预订情况，返回价格
        $pricelist = (new BnbPrice())->getBnbPriceList($bnb_id, $begin_date, $end_date);
        $data['date']['begin'] = $begin_date;
        $data['date']['end'] = $end_date;
        $data['date']['night'] = count($pricelist) - 1;
        // 房间价格列表
        $price_bnb = 0.00;
        foreach ($pricelist as $d => $p) {
            $pd = [];
            $price = $p['price'];
            $price_bnb += $price;
        }
        $orderTotal = $price_bnb;
        return $orderTotal;
    }


    public function getOrderListByUser($userid, $now = 1, $page = 1, $numsprepage = 6)
    {
        $orderlist = [];
        $orderlist['page'] = [];
        $orderlist['order'] = [];

        $list = (new OrderBnb())->getOrderListByUserId($userid, $page, $numsprepage, $now);

        $model_bnb = new Bnb();
        $bnb_logic = new BnbOrderLogic();


        $bnbcache = [];

        if ($list) {
            foreach ($list as $order) {
                $o = [];
                $o['order_sn'] = $order['order_sn'];
                $o['night'] = $order['night'];
                $o['in_date'] = $order['in_date'];
                $o['out_date'] = $order['out_date'];
                $o['people_numbers'] = $order['people_numbers'];

                $bnb = [];
                if (isset($bnbcache[$order['bnb_id']])) {
                    $bnb = $bnbcache[$order['bnb_id']];
                } else {
                    $bnbdata = $model_bnb->getBnb($order['bnb_id']);
                    if ($bnbdata) {
                        $bnb['image'] = "/" . config('upload.upload')['thumb']['thumb1']['dir'] . "/" . $bnbdata['bnb_image'];
                        $bnb['name'] = $bnbdata['name'];
                        $bnb['bnb_id'] = $bnbdata['id'];
                        $bnbcache[$order['bnb_id']] = $bnb;
                    }
                }

                $o['bnb_image'] = $bnb['image'];
                $o['bnb_name'] = $bnb['name'];
                $o['bnb_id'] = $bnb['bnb_id'];

                $o['operate'] = ($bnb_logic->getOperateList())[$order['status']]['user'];
                $o['status'] = ($bnb_logic->getOperateList())[$order['status']]['order'];
                //代下单去除取消订单功能
                if(($order['status'] == self::$OrderStatus_Paid) && ($order['replaced_admin_id'] > 0)){
                    unset($o['operate'][$bnb_logic::$OPERATE_CANCEL]);
                }

                $o['pay_total'] = $order['pay_total'];
                $o['deposit_amount'] = $order['deposit_amount'];

                $o['is_evaluate'] = $order['is_evaluate'];
                $orderlist['order'][] = $o;
            }


            $orderlist['page']['page'] = $list->currentPage();
            $orderlist['page']['next'] = 0;
            if ($list->lastPage() > $list->currentPage()) {
                $orderlist['page']['next'] = 1;
            }
        }

        return $orderlist;
    }
    public function getOrderListByBnb($bnbid, $now = 1, $page = 1, $numsprepage = 6)
    {
        $orderlist = [];
        $orderlist['page'] = [];
        $orderlist['order'] = [];

        $list = (new OrderBnb())->getOrderListByBnbId($bnbid, $page, $numsprepage, $now);

        $model_bnb = new Bnb();
        $bnb_logic = new BnbOrderLogic();


        $bnbcache = [];

        if ($list) {
            foreach ($list as $order) {
                $o = [];
                $o['order_sn'] = $order['order_sn'];
                $o['night'] = $order['night'];
                $o['in_date'] = $order['in_date'];
                $o['out_date'] = $order['out_date'];
                $o['people_numbers'] = $order['people_numbers'];

                $bnb = [];
                if (isset($bnbcache[$order['bnb_id']])) {
                    $bnb = $bnbcache[$order['bnb_id']];
                } else {
                    $bnbdata = $model_bnb->getBnb($order['bnb_id']);
                    if ($bnbdata) {
                        $bnb['image'] = "/" . config('upload.upload')['thumb']['thumb1']['dir'] . "/" . $bnbdata['bnb_image'];
                        $bnb['name'] = $bnbdata['name'];
                        $bnb['bnb_id'] = $bnbdata['id'];
                        $bnbcache[$order['bnb_id']] = $bnb;
                    }
                }

                $o['bnb_image'] = $bnb['image'];
                $o['bnb_name'] = $bnb['name'];
                $o['bnb_id'] = $bnb['bnb_id'];

                $o['operate'] = ($bnb_logic->getOperateList())[$order['status']]['user'];
                $o['status'] = ($bnb_logic->getOperateList())[$order['status']]['order'];

                $o['pay_total'] = $order['pay_total'];
                $o['deposit_amount'] = $order['deposit_amount'];

                $o['is_evaluate'] = $order['is_evaluate'];
                $orderlist['order'][] = $o;
            }


            $orderlist['page']['page'] = $list->currentPage();
            $orderlist['page']['next'] = 0;
            if ($list->lastPage() > $list->currentPage()) {
                $orderlist['page']['next'] = 1;
            }
        }

        return $orderlist;
    }


}