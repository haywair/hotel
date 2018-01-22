<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/13
 */

namespace app\cron\controller;

use app\common\base\BnbRefund;
use app\common\base\CleanOrder;
use app\common\base\UserWithdraw;
use app\common\base\BuildRefund;
use think\Controller;
use app\common\base\Cron;


class Index extends Controller
{
    public function index()
    {
        echo '';
    }


    public function minutes()
    {
        $cron = new Cron();

        // 自动给保洁订单分配保洁员
        (new CleanOrder())->allocWaitingCleanerOrder();

        //自动审核完成保洁订单
        (new CleanOrder())->finishCleanerOrder();
        //自动审核完成提现*/
        (new UserWithdraw())->WaitingWithdrawOrder();

        // 处理自动退款订单
        $cron->autoRefund();

        // 取消N分钟未付款的订单
        $cron->autoCancelOrder();

        //自动生成保证金退款订单
        (new BnbRefund())->autoCreateDespositRefund();
        // 自动完成到时订单
        $cron->autoFinishOrder();

        // 优惠券自动过期
        $cron->autoExpiredVoucher();
       
    }  
    public function hours()
    {

    }

    //todo 用户优惠券过期
}