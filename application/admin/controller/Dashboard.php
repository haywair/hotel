<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\Users;
use app\common\model\Userinfo;
use app\common\model\OrderBnb;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $uploadmode = 'local';

        $usersModel = new Users();
        $userinfoModel = new Userinfo();
        $orderBnbModel = new OrderBnb();
        //总会员数
        $usersNum = $usersModel->getUsersNumber();//总用户数量
        //今日起始时间
        $beginToday = strtotime(date('Y-m-d'));
        $endToday = $beginToday+24*3600;
        //七日起始时间
        $beginSeven = $endToday - 24*3600*7;
        //30日内
        $beginMonth = $endToday - 3600*24*30;
        $endMonth = $endToday;
       /* $firstday = date('Y-m-01');
        $beginMonth = strtotime($firstday);
        $endMonth =  strtotime("$firstday +1 month -1 day")+24*3600;*/

        $todayRegistNum = $usersModel->getTimeUsersNumber($beginToday,$endToday);//今日注册量
        $sevenRegistNum = $usersModel->getTimeUsersNumber($beginSeven,$endToday);//七日注册量
        $viewNum = $userinfoModel->getUsersLoginNum();//总访问量
        $todayViewNum = $userinfoModel->getTimeLoginNum($beginToday,$endToday);//今日访问量
        $sevenViewNum = $userinfoModel->getTimeLoginNum($beginSeven,$endToday);//七日访问量
        $ordersNum = $orderBnbModel->getOrderBnbNum();//订单总量
        //今日订单
        $todayOrdersNum = $orderBnbModel->getTodayOrderNum();
        $unsettleOrder = $orderBnbModel->getUnsettledOrderNum();//未处理订单
        $orderMoney = $orderBnbModel->getOrderTotalMoney();

        $orderAll = $orderBnbModel->getOrderNumGroupByOrdertime($beginMonth,$endMonth);//30日内每日订单量
        $dealState = [strval(config('state.order_pay_state')),strval(config('state.order_msg_state')),strval(config
        ('state.order_finish_state'))];
        $orderDeal = $orderBnbModel->getOrderNumGroupByOrdertime($beginMonth,$endMonth,$dealState);//30日内每日成交订单量
        foreach($orderAll as $k=>$v){
            if(!isset($orderDeal[$k])){
                $orderDeal[$k] = 0;
            }
        }
        $orderDeal = $this->sortOrder($orderDeal);

        $this->view->assign([
            'totaluser'        => $usersNum,
            'totalviews'       => $viewNum,
            'totalorder'       => $ordersNum,
            'totalorderamount' => $orderMoney,
            'todayuserlogin'   => $todayViewNum,
            'todayusersignup'  => $todayRegistNum,
            'todayorder'       => $todayOrdersNum,
            'unsettleorder'    => $unsettleOrder,
            'sevendnu'         => $sevenRegistNum,
            'sevendau'         => $sevenViewNum,
            'paylist'          => $orderDeal,
            'createlist'       => $orderAll,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

    private function sortOrder($order_data){
        $timestrData = [];
        foreach($order_data as $k=>$v){
            $timestrData[strtotime($k)] = $v;
        }
        ksort($timestrData);
        $strData = [];
        foreach($timestrData as $kr=>$vr){
            $strData[date('Y-m-d',$kr)] = $vr;
        }
        return $strData;
    }

}
