<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1 0001
 * Time: 9:28
 */

namespace app\index\controller;

use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\BuildRefund;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\controller\BnbBase;
use app\common\controller\BnbTheme;
use app\common\model\OrderAddonClean;
use app\common\model\OrderUserClean;
use think\Request;
class OrderClean extends BnbBase
{
    protected $orderModel;
    public function _initialize()
    {

        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->orderModel = new \app\common\model\OrderClean();
        $this->orderBnbModel = new \app\common\model\OrderBnb();
        $this->bnbModel = new \app\common\model\Bnb();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/'
        ];
    }
    
    //购买保洁次数
    public function orderCleanNum(){
        if($this->request->isAjax()){
            $order_sn = Request::instance()->param('order_sn');
            $clean_numbers = Request::instance()->param('clean_numbers');
            $text = '';
            //验证数据
            $checkObj = (new UserCleanOrder())->getCleanOrderData($this->users['id'], $order_sn, $clean_numbers);
            if($checkObj->getCode()>0){
                $text = $checkObj->getText();
                $this->error($text);
            }
            //保存
            $saveObj = (new UserCleanOrder())-> saveCleanOrder($this->users['id'], $order_sn, $clean_numbers);
            if($saveObj->getCode()>0){
                $text = $saveObj->getText();
                $this->error($text);
            }
            $data = $saveObj->getData();
            $this->success('购买保洁次数成功','',$data);
        }
    }
    //保洁支付
    public function cleanNumPay(){
        $clean_order_sn = Request::instance()->param('id');
        if(!$clean_order_sn){
            $this->error('无购买订单信息');
        }
        $cleanData = (new OrderUserClean())->getOrderBySn($clean_order_sn);
        $this->assign('data',$cleanData);
        $this->assign('title', '保洁订单支付');
        return $this->fetch();

    }
    //使用保洁
    public function useClean(){
        if($this->request->isAjax()){
            $bnb_order_sn = Request::instance()->param('bnb_order_sn');
            $order_date = Request::instance()->param('order_date');
            if(!$bnb_order_sn || !$order_date){
                $this->error('请提供预约时间和预约单号');
            }
            //用户购买信息
            $userOrderClean = (new OrderUserClean())->getOrderByBnbOrderSn($bnb_order_sn);

            $addonCleanNum = (new OrderAddonClean)->where('bnb_order_sn',$bnb_order_sn)->count();
            if($addonCleanNum <= 0) {
                $price = $userOrderClean['price'];
                $cleanpay_number = $userOrderClean['clean_numbers'] - $userOrderClean['free_numbers'];
                $freeR = (new OrderAddonClean())->createAddonCleanOrder($userOrderClean['clean_order_sn'],
                    $bnb_order_sn, $this->users['id'], $userOrderClean['free_numbers'], 0);
                $payR = (new OrderAddonClean())->createAddonCleanOrder($userOrderClean['clean_order_sn'],
                    $bnb_order_sn, $this->users['id'], $cleanpay_number, $price);
            }
            //验证
            $checkObj = (new BnbClean())->getBnbCleanData($this->users['id'], $bnb_order_sn, $order_date);
            if($checkObj->getCode() > 0){
                $text = $checkObj->getText();
                $this->error($text);
            }

            $cleanData = $checkObj->getData();
            $cleanDbData = (new BnbClean())->getBnbCleanDbData($cleanData);
            //保存
            $createObj = (new BnbClean())->createBnbCleanDbOrder($cleanData['addon_clean_id'],  $cleanDbData);
            if($createObj->getCode() > 0){
                $text = $createObj->getText();
                $this->error($text);
            }
            $orderCleanData = $createObj->getData();
            //更新

            $this->success('预约使用成功','',$orderCleanData);
        }
    }
}