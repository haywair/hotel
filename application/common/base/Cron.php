<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/5
 */

namespace app\common\base;

use app\common\model\OrderBnb;
use app\common\model\Refund;
use app\common\model\UserVoucher;
use app\common\model\Voucher;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as WxConfigService;

class Cron
{

    //自动退款
    public function autoRefund()
    {
        $refundlist = (new Refund())->getAutoRefundList();
        if (($refundlist) && (is_array($refundlist))) {

            $wxapp = new Application(WxConfigService::load());

            $bnb_refund = new BnbRefund();

            foreach ($refundlist as $refund) {
                $bnb_refund->doRefundOrder($wxapp, $refund['refund_sn'], 0);
            }
        }
    }

    // 不付款自动取消订单
    public function autoCancelOrder()
    {
        $max_minutes = config('setting.auto_cancel_order_max_minute');
        $time_before = time() - $max_minutes * 60;

        $order_list = (new OrderBnb())->where('status', BnbOrder::$OrderStatus_unPay)->where('verify_time', '<=', $time_before)->order('id asc')->select();

        if (($order_list) && (is_array($order_list))) {

            $bnborderlogic = new BnbOrderLogic();
            foreach ($order_list as $order) {
                $bnborderlogic->onCancel($order['order_sn'], 0, 0);
            }
        }
    }

    // 离店时间到 自动完成订单
    public function autoFinishOrder()
    {
        $thisdate = date('Y-m-d', time());

        $order_list = (new OrderBnb())->where('status', 'in', [BnbOrder::$OrderStatus_Paid, BnbOrder::$OrderStatus_PasswordSent])->where('out_date', '<=', $thisdate)->order('id asc')->select();

        if (($order_list) && (is_array($order_list))) {

            $time = date('H:i:s', time());

            foreach ($order_list as $order) {
                $bnborderlogic = new BnbOrderLogic();

                if (($order['out_date'] < $thisdate) || ($order['out_date'] == $thisdate && $order['out_hour'] <= $time)) {
                    // 完成订单
                    $bnborderlogic->onFinish($order['order_sn'], 0, 0);
                }
            }
        }
    }


    // 优惠券过期
    public function autoExpiredVoucher()
    {
        $voucher_list = (new Voucher())->getExpiredVoucher();
        if (($voucher_list) && (is_array($voucher_list))) {
            foreach ($voucher_list as $v) {
                // 优惠券过期，更新所有用户的优惠券信息
                (new UserVoucher())->save(['status' => 3], ['status' => 1, 'voucher_id' => $v['id']]);
            }
        }
    }


}