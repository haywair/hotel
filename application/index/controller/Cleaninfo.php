<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16 0016
 * Time: 10:24
 */

namespace app\index\controller;
use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\CleanOrder;
use app\common\base\Image;
use app\common\controller\BnbBase;
use app\common\model\OrderClean;
use app\index\validate\Signup;
use think\Request;

class Cleaninfo extends BnbBase
{
    public function _initialize()
    {
        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->model = new \app\common\model\Cleaninfo();
        $this->signupModel = new \app\common\model\Signup();
        $this->userModel = new \app\common\model\Users();
        $this->orderCleanModel = new \app\common\model\OrderClean();
        $this->orderCleanPhotoModel = new \app\common\model\OrderCleanPhoto();
        $this->billCleanModel = new \app\common\model\BillClean();
        $this->withdrawModel = new \app\common\model\Withdraw();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/',
            'bnb_clean_photo_path' =>  '/'.config('upload.bnb_clean_photo')['thumb']['thumb']['dir'].'/',
            'upload_clean_photo_path' =>  '/'.config('upload.upload_clean_photo')['thumb']['thumb']['dir'].'/',
        ];
    }

    /**
     * 保洁首页
     */
    public function index(){
        //任务推送
        $taskTime = time()-3600*24*2;
        $taskState = [config('state.order_clean_accept')];
        $taskData = $this->orderCleanModel->getOrderClean($this->users['id'],$taskState,$taskTime,'egt');
        //已完成
        $taskState = [config('state.order_clean_finish')];
        $finishData = $this->orderCleanModel->getOrderClean($this->users['id'],$taskState);
        $this->assign('path',$this->imagePath);
        $this->assign('taskData',$taskData);
        $this->assign('finishData',$finishData);
        $this->assign('title', '成为保洁');
        return $this->fetch();
    }
    /**
     * 注册成为保洁
     * @return mixed
     */
    public function registerCleaner(){
        if($this->request->isPost()){
            $params = $this->request->request();
            //用户个人信息
            $users = session(config('session.UserInfo'));
            $params['users_id'] = $users['id'];
            $params['type'] = config('users.cleaner_type');
            //重复申请验证失败
            $count = $this->signupModel->where('users_id',$users['id'])->where('type',config('users.cleaner_type'))->count();
            if($count){
                $this->error('您已申请成为保洁，请勿重复申请');
            }
            //保存信息
            $result = $this->signupModel->save($params);
            if($result){
                $this->success('恭喜您，成功注册为保洁');
            }
            $this->error('注册保洁失败');

        }else {
            $this->assign('title', '成为保洁');
            return $this->fetch();
        }
    }

    /**
     * 保洁图片对比
     * @param null $bnb_id
     * @return mixed
     */
    public function comparePhoto($order_clean_id=null){
        if($this->request->isAjax()){
            //对比原图片id、订单id、对比图片
            $compare_id = $this->request->post('compare_id');
            $order_clean_id = $this->request->post('order_clean_id');
            $image = $this->request->post('image');            
            if($compare_id && $order_clean_id && $image) {
                //图片对比
                $result = (new CleanOrder())->compareCleanPhoto($compare_id, $order_clean_id, $image);
                if($result){
                    //更新数据
                    $this->orderCleanPhotoModel->updateCleanPhoto($compare_id,$image,$result);
                    $this->success('照片对比完成','',$result);
                }else{
                    $this->error('未提供对比照片所需的文件');
                }
            }
            $this->error('未提供对比照片所需完整数据');
        }else {
            if ($order_clean_id) {
                //该订单所需对比的图片
                $photoData = $this->orderCleanPhotoModel->getOrderPhotoList($order_clean_id);
                $this->assign('order_id',$order_clean_id);
                $this->assign('photoNum',count($photoData));
                $this->assign('photoData', $photoData);
                $this->assign('path', $this->imagePath);
                $this->assign('title', '照片对比');
                return $this->fetch();
            }
            $this->error('未选择您要对比图片的订单');
        }
    }
    public function updateComparePhotos(){
        if($this->request->isAjax()){
            $params = $this->request->request();
            $order_clean_id = 0;
            foreach($params as $k=>$v){
                $params[$k]['updatetime'] = time();
                $params[$k]['upload_time'] = time();
                $params[$k]['compare_value'] = 0;
                $params[$k]['need_admin'] = 1;
                $order_clean_id = $v['order_clean_id'];
            }
            $data = $this->orderCleanPhotoModel->saveAll($params);
            if($data){
                $orderdata = [
                    'status'    =>  config('state.order_clean_finish'),
                    'work_end_time' => time()
                ];
                $res = (new OrderClean())->where('id',$order_clean_id)->update($orderdata);
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }
    }

    /**
     * 保洁收益
     */
    public function cleanerBill(){
        $cleanerInfo = $this->model->getCleanerByUID($this->users['id']);
        //按月统计保洁员收益
        $data = $this->billCleanModel->getCleanerMonthBill($this->users['id']);
        //保洁员现有收益
        $extraMoney = $cleanerInfo['money_total'] - $cleanerInfo['money_out'];
        //保洁员上月总收入
        $month = intval(date('m'));
        $year = intval(date('Y'));
        if($month-1==0){
            $prevMonth = 12;
            $year = $year -1;
        }
        $prevMonth = $month-1;

        $this->assign('timeKey',$year.'-'.$prevMonth);
        $this->assign('prevMonth',$prevMonth);
        $this->assign('extraMoney',$extraMoney);
        $this->assign('cleanerInfo',$cleanerInfo);
        $this->assign('data',$data);
        $this->assign('title','我的保洁收益');
        return $this->fetch();
    }

    /**
     * 保洁员每日收入统计
     * @param $month
     */
    public function billDetail($month){
        if($month){
            $begin_date = date('Y').'-'.$month.'-01';
            $days = date("t",strtotime($begin_date));
            $end_date = date('Y').'-'.$month.'-'.$days;
            $data = $this->billCleanModel->getCleanerBillList($this->users['id'],$begin_date,$end_date);
            //现有收益
            $cleanerInfo = $this->model->getCleanerByUID($this->users['id']);
            $extraMoney = $cleanerInfo['money_total'] - $cleanerInfo['money_out'];
            $this->assign('extraMoney',$extraMoney);
            $this->assign('title',$month.'月收益明细');
            $this->assign('data',$data);
            return $this->fetch();
        }
    }
    /**
     * 申请提现（保洁）
     */
    public function withdraw(){
        if($this->request->isAjax()){
            $money = $this->request->post('money');
            if(!$money){
                $this->error('请输入申请提现金额');
            }

            $userInfo = $this->model->getCleanerByUID($this->users['id']);
            if($money > $userInfo['money_total']){
                $this->error('收益金额不足，暂无法申请');
            }
            $data = [
                'money' =>  $money,
                'type'  =>  config('users.cleaner_type'),
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
     * 完成保洁订单审核
     */
    public function finishorder(){
        if($this->request->isAjax()){
            $order_id = Request::instance()->param('order_id');
            if(!$order_id){
                $this->error('未提供保洁单号');
            }
            $order_data = (new OrderClean())->getOrderById($order_id);
            if(!$order_data){
                $this->error('该保洁订单不存在');
            }
            //保洁已完成
            $data = [
                'status'    =>  config('state.order_clean_finish'),
                'work_end_time' => time()
            ];
            $res = $this->orderCleanModel->where('id',$order_id)->update($data);
            if(!$res){
                $this->error('更新保洁单失败！');
            }
            $this->success('保洁完成');

        }else{
            $this->error('异常请求');
        }
    }
}