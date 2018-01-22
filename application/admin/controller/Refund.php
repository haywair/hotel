<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6 0006
 * Time: 10:24
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\base\BnbRefund;
use app\common\model\OrderBnb;
use app\common\model\OrderClean;
use app\common\model\Users;
use app\admin\model\Admin;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as WxConfigService;
use think\Controller;
use think\Request;
use Carbon\Carbon;
class Refund extends Backend
{
    protected $model = null;
    protected $usersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Refund');
    }
    //查看列表
    public function index($type=null)
    {

        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加查询条件
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            $filterData = $this->getSearchParams($params);
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.username as admin,d.order_sn,d.status as bnb_status')
                ->join('__USERS__ b','a.users_id = b.id','left')
                ->join('__ADMIN__ c','a.admin_id = c.id','left')
                ->join('__ORDER_BNB__ d','a.order_sn = d.order_sn','left')
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.username as admin,d.order_sn,d.status as bnb_status')
                ->join('__USERS__ b','a.users_id = b.id','left')
                ->join('__ADMIN__ c','a.admin_id = c.id','left')
                ->join('__ORDER_BNB__ d','a.order_sn = d.order_sn','left')
                ->where($where)
                ->order($sort,$order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 修改退款
     */
    public function edit($refund_sn=null){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $wx_app = new Application(WxConfigService::load());
            $bnbRefund = new BnbRefund($wx_app);

            $refund_sn = $params['refund_sn'];
            $pay_time = time();
            $pay_total = $params['pay_amount'];
            $pay_refund = $params['refund_amount'];
            $reason = $params['reason'];
            $admin_id = session(config('session.Admin'))['id'];
            $buildRefund = $bnbRefund->updateRefundOrder($wx_app,$refund_sn,$pay_time,$pay_total,$pay_refund,$reason,$admin_id);
            if($buildRefund->getCode() > 0){
                $this->error($buildRefund->getText());
            }
            $this->success('退款成功');

        }else{
            $refund_sn = Request::instance()->param('refund_sn');
            $refund_data = $this->model->getRefundOrderBySn($refund_sn);
            $this->assign('row',$refund_data);
            return $this->fetch();
        }
    }
    /**
     * 退保证金
     */
    public function desposit(){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $deposit_fee = 0.00;
            $service_fee = 0.00;
            if($params['deposit_deduction_amount'] >= 0){
                $deposit_fee = $params['deposit_deduction_amount'];
            }
            if(isset($params['service']) && $params['service']){
                $service_fee = $params['service'];
            }
            $deposit_reason = $params['deposit_deduction_reason']??'';
            $wx_app = new Application(WxConfigService::load());
            $res_refund = (new BnbRefund())->refundDepositServiceFee($wx_app,$params['refund_sn'],$deposit_fee,$service_fee,$deposit_reason);
            if($res_refund->getCode() > 0){
                $this->error($res_refund->getText());
            }
            $this->success('退款成功');

        }else{
            $refund_sn = Request::instance()->param('refund_sn');
            if(!$refund_sn){
                $this->error('未提供退款单号');
            }
            $refund_data = $this->model->getRefundOrderBySn($refund_sn);
            if(!$refund_data){
                $this->error('无退款单记录');
            }else if(!$refund_data['order_sn']){
                $this->error('无民宿订单记录');
            }

            $order_bnb_data = (new \app\common\model\OrderBnb())->getOrderBySn($refund_data['order_sn']);
            $bnb_data = (new \app\common\model\Bnb())->getBnb($order_bnb_data['bnb_id']);
            if($bnb_data['is_refund_fee_manage']){
                $refund_service_fee = $this->getCancelTimePrice($refund_data['order_sn']);
                $this->assign('refund_service_fee',$refund_service_fee);
            }
            $this->assign('orderdata',$order_bnb_data);
            $this->assign('row',$refund_data);
            return $this->fetch();
        }
    }
    /**
     * 详情
     */
    public function more(){
        $ids = Request::instance()->param('ids');
        $refundData = $this->model->getRefundOrderById($ids);
        $userInfo = (new Users())->getUserById($refundData['users_id']);
        $orderInfo = [];
        $adminInfo = [];
        switch($refundData['refund_type']){
            case 'A':
                $refundData['refund_type'] = '自动退款';
                break;
            case 'B':
                $refundData['refund_type'] = '民宿退款';
                $orderInfo = (new OrderBnb)->getOrderBySn($refundData['order_sn']);
                break;
            case 'C':
                $refundData['refund_type'] = '保洁退款';
                $orderInfo = (new OrderClean)->getOrderBySn($refundData['order_sn']);
                break;
            case 'D':
                $refundData['refund_type'] = '退还保证金';
                break;
        }
        if($refundData['admin_id'] > 0){
            $adminInfo = (new Admin())->getAdminById($refundData['admin_id']);
        }
        $list = ['row'=>$refundData,'orderInfo'=>$orderInfo,'userInfo'=>$userInfo,'adminInfo'=>$adminInfo];
        $this->assign($list);
        return $this->fetch();
    }
    private function getSearchParams($params){
        $filterData = [];
        if($params){
            foreach($params as $k=>$v){
                if($v) {
                    $relation = 'LIKE';
                    switch($k){
                        case 'user_nickname':
                            $k = 'b.user_nickname';
                            break;
                        case 'order_sn':
                            $k = 'd.order_sn';
                            break;
                        case 'pay_sn':
                            $relation = 'LIKE';
                            break;
                        case 'refund_sn':
                            $relation = 'LIKE';
                            break;
                        default:
                            $relation = '=';

                    }
                    $filterData[$k] =  [
                        'key' => $relation,
                        'value' => trim($v)
                    ];
                }
            }
        }
        return $filterData;
    }

    /**
     * 获取需要退除的管理费
     * @param $bnb_order_sn
     * @return float|string
     */
    public function getCancelTimePrice($bnb_order_sn){
        $orderdata = (new \app\common\model\OrderBnb())->getOrderBySn($bnb_order_sn);
        $cancel_stand_time = strtotime(date('Y-m-d',$orderdata['cancel_time']).' 12:00:00');
        $cancel_str_time = strtotime(date('Y-m-d',$orderdata['cancel_time']));
        $percent_fee = (new \app\common\model\Config())->getManageConfig('management_fee');
        if($orderdata['cancel_time'] < $cancel_stand_time){
            $cancel_day = date('Y-m-d',($cancel_stand_time - 13*3600));
        }else{
            $cancel_day = date('Y-m-d',$orderdata['cancel_time']);
        }
        $pricelist = (new \app\common\base\BnbPrice())->getBnbPriceList($orderdata['bnb_id'], $cancel_day,
            $orderdata['out_date'],"",  true, false);
        $service_fee = 0.00;
        foreach($pricelist as $price){
            $p_price = $price['price']?$price['price']:0;
            $service_fee += money_formater($p_price*$percent_fee/100);
        }
        return $service_fee;

    }
}