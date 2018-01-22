<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29 0029
 * Time: 15:08
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\UserVoucher as UV;
use app\common\model\OrderBnb;
use app\common\model\OrderClean;
use think\Controller;
use app\common\model\Voucher;
use app\common\model\Users;
use think\Request;
class UserVoucher extends Backend
{
    protected $model = null;
    protected $orderbnbModel = null;
    protected $ordercleanModel = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UV();
        $this->orderbnbModel = new OrderBnb();
        $this->ordercleanModel = new OrderClean();
        $this->view->assign("statusList", config('state.voucher_use_state'));
        //优惠券列表
        $voucherData = (new Voucher())->where('status',config('state.state_ok'))->column('name','id');
        $this->view->assign('voucherData',$voucherData);
        //用户列表
        $usersData = (new Users())->where('status',config('state.state_ok'))->column('user_nickname','id');
        $this->view->assign('usersData',$usersData);
    }
    /**
     * 查看
     */
    public function index(){
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加查询条件
            /*$filterData = [];
            $type = $this->request->request('type');
            if($type){
                $filterData = [
                    'type' => [
                        'key' => '=',
                        'value' => $type
                    ]
                ];
            }*/
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $total = $this->model->alias('a')
                -> field('a.*,b.user_nickname,c.name,c.type')
                -> join('__USERS__ b','a.users_id = b.id','left')
                -> join('__VOUCHER__ c','a.voucher_id = c.id','left')
                -> where($where)
                -> order($sort,$order)
                -> count();

            $list = $this->model->alias('a')
                -> field('a.*,b.user_nickname,c.name,c.type')
                -> join('__USERS__ b','a.users_id = b.id','left')
                -> join('__VOUCHER__ c','a.voucher_id = c.id','left')
                -> where($where)
                -> order($sort,$order)
                -> limit($offset, $limit)
                -> select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 绑定详情
     */
    public function more(){
        $id = Request::instance()->param('ids');
        if($id){

            $voucherData = $this->model->getVoucherById($id);
            $orderInfo = [];
            if($voucherData['status'] == 2){
                switch($voucherData['type']){
                    case config('voucher.voucher_bnb_type'):
                        $orderInfo = $this->orderbnbModel->getOrderById($voucherData['used_order_id']);
                        break;
                    case config('voucher.voucher_clean_type'):
                        $orderInfo = $this->ordercleanModel->getOrderById($voucherData['used_order_id']);
                        break;
                }
            }
            $voucherData['order_info'] = $orderInfo;
            $this->assign('row',$voucherData);
            return $this->fetch();
        }else{
            $this->error('无有效绑定记录参数');
        }
    }

}