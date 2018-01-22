<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/15 0015
 * Time: 13:17
 */

namespace app\index\controller;

use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\controller\BnbBase;
use app\common\model\Help;
use app\common\model\Service;
use think\Request;
class Users extends BnbBase
{
    protected $users;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Users();
        $this->users = session(config('session.UserInfo'));
        $this->billLandlordModel = new \app\common\model\BillLandlord();
        $this->landlordModel = new \app\common\model\Landlordinfo();
        $this->orderBnbModel = new \app\common\model\OrderBnb();
        $this->evaluateModel = new \app\common\model\Evaluate();
        $this->userVoucherModel = new \app\common\model\UserVoucher();
        $this->bnbfavoriteModel = new \app\common\model\BnbFavorite();
        $this->withdrawModel = new \app\common\model\Withdraw();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/',
            'evaluate_path' => '/'.config('upload.evaluate')['thumb']['thumb']['dir'].'/'
        ];
    }
    public function index(){
        $this->assign('path',$this->imagePath);
        $userInfo = $this->model->getUserById($this->users['id']);
        $this->assign('userInfo',$userInfo);
        $this->assign('users',$this->users);
        $this->assign('title','会员中心');
        return $this->fetch();
    }
    public function edit(){
        $this->assign('title','我的修改');
        $userInfo = $this->model->get($this->users['id']);
        $this->assign('path',$this->imagePath);
        $this->assign('userInfo',$userInfo);
        return $this->fetch();
    }
    public function info($type=null){
        if($this->request->isPost()){
            $params = $this->request->request();
            $res = $this->model->updateUserById($this->users['id'], $params);
            if($res){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            if($type){
                switch($type){
                    case 'age':
                    case 'name':
                    case 'address':
                        $template = 'info';
                        break;
                    default:
                        $template = $type;
                        break;
                }
                $userInfo = $this->model->get($this->users['id']);
                $this->assign('type',$type);
                $this->assign('title',$type);
                $this->assign('path',$this->imagePath);
                $this->assign('userInfo',$userInfo);
                return $this->fetch($template);
            }
        }
    }

    /**
     * 我的钱包
     * @return mixed
     */
    public function pocket(){
        $userInfo = $this->model->get($this->users['id']);
        $this->assign('userInfo',$userInfo);
        $this->assign('title','我的钱包');
        return $this->fetch();
    }

    /**
     * 我的收藏
     * @return mixed
     */
    public function collect(){
        $bnbData = $this->bnbfavoriteModel->getBnbFavoriteByUID($this->users['id']);
        if($bnbData){
            //获取价格
            foreach($bnbData as $k=>$v){
                $today = date('Y-m-d');
                $price = (new BnbPrice())->getBnbPriceList($v['id'], $today, $today, $now_date = "", false);
                $bnbData[$k]['price'] = $price[$today]['price'];
            }
        }
        $total = $bnbData->total();
        if($this->request->isAjax()){
            if($bnbData){
                $listData = json_encode($bnbData);
                $this->success('加载成功', '', $listData);
            }else{
                $this->error('加载完毕');
            }
        }else {
            $this->assign('total',$total);
            $this->assign('bnbData',$bnbData);
            $this->assign('path',$this->imagePath);
            $this->assign('title','我的收藏');
            return $this->fetch();
        }

    }
    /**
     * 取消收藏
     */
    public function cancelCollect(){
        if($this->request->isAjax()){
            $favoriteId = $this->request->post('id');
            if(!$favoriteId){
                $this->error('未提供民宿信息');
            }
            $count = $this->bnbfavoriteModel->where('id',$favoriteId)->count();
            if($count <= 0){
                $this->error('无该民宿收藏记录');
            }
            $del = $this->bnbfavoriteModel->where('id',$favoriteId)->delete();
            if(!$del){
                $this->error('取消收藏失败');
            }
            $this->success('取消收藏成功');
        }
    }

    /**
     * 我的点评
     * @return mixed
     */
    public function evaluate(){
        $condition = [
            'a.status' => array('in',config('state.state_ok').','.config('state.state_mark')),
            'a.user_id' => $this->users['id']
        ];
        $data = $this->evaluateModel->getEvaluteList($condition);
        $this->assign('data',$data);
        $this->assign('path',$this->imagePath);
        $this->assign('title','我的点评');
        return $this->fetch();
    }

    /**
     * 服务协议
     * @return mixed
     */
    public function service(){
        $serviceData = (new Service())->getServiceContent();
        $this->assign('serviceData',$serviceData);
        $this->assign('title','服务协议');
        return $this->fetch();
    }

    /**
     * 我的优惠券
     * @return mixed
     */
    public function voucher(){
        $voucherData = $this->userVoucherModel->getUserVoucherList($this->users['id'], $state = 1);
        $this->assign('voucherData',$voucherData);
        $this->assign('title','我的优惠券');
        return $this->fetch();
    }

    /**
     * 房东收益
     * @return mixed
     */
    public function landlordBill(){
        //当月预定房客数
        $begin_date = date( 'Y-m-1',time());
        $mdays = date( 't', time());
        $end_date =date( 'Y-m-' . $mdays, time());
        $payState = [config('state.order_pay_state'),config('state.order_msg_state'),config('state.order_finish_state')];
        $usersNum = $this->orderBnbModel->getOrdersUserNum($begin_date,$end_date,$payState);
        //累计已支付的金额
        $orderTotal = $this->orderBnbModel->getOrderTotal(null,null,$payState);
        //预计收入
        $expectState = [
            config('state.order_create_state'),
            config('state.order_verify_state'),
        ];
        $expectTotal = $this->orderBnbModel->getOrderTotal(null,null,$expectState);
        //年度已支付
        $begin_date = date( 'Y-1-1',time());
        $end_date =date( 'Y-12-31', time());
        $yearTotal = $this->orderBnbModel->getOrderTotal($begin_date,$end_date,$payState);
        //现有收益
        $landlordData = $this->landlordModel->getLandlord($this->users['id']);
        $awardedTotal = $landlordData['money_total'] - $landlordData['money_out'];
        //总收入
        $allTotal = $landlordData['money_total'];

        $list = ['usersNum'=>$usersNum,'orderTotal'=>$orderTotal,'expectTotal'=>$expectTotal,'allTotal'=>$allTotal,
            'yearTotal'=>$yearTotal,'awardedTotal'=>$awardedTotal];
        $this->assign('list',$list);
        $this->assign('title','我的收益');
        return $this->fetch();
    }

    /**
     * 申请提现（房东）
     */
    public function withdraw(){
        if($this->request->isAjax()){
            $money = $this->request->post('money');
            if(!$money){
                $this->error('请输入申请提现金额');
            }

            $userInfo = $this->landlordModel->getLandlord($this->users['id']);
            if($money > $userInfo['money_total']){
                $this->error('收益金额不足，暂无法申请');
            }
            $data = [
                'money' =>  $money,
                'type'  =>  config('users.landlord_type'),
                'user_id'   =>  $this->users['id']
            ];
            $res = $this->withdrawModel->save($data);
            if($res){
                $this->success('申请提现成功');
            }else{
                $this->error('申请提现失败');
            }
        }
    }
    /**
     * 我的帮助
     * @return mixed
     */
    public function help(){
        $helpData = (new Help())->getHelpList();
        $this->assign('helpData',$helpData);
        $this->assign('title','我的帮助');
        return $this->fetch();
    }
}