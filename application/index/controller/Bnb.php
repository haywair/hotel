<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 13:12
 */

namespace app\index\controller;

use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\model\Bnb as BnbModel;
use app\admin\model\Features;
use app\common\controller\BnbBase;
use app\common\model\UserVoucher;
use Carbon\Carbon;

class Bnb extends BnbBase
{
    protected $bnbModel;
    protected $featureModel;
    public function _initialize()
    {

        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->bnbModel = new BnbModel();
        $this->featureModel = new Features();
        $this->imageModel = new \app\common\model\Images();
        $this->landlordModel = new \app\common\model\Landlordinfo();
        $this->evaluateModel = new \app\common\model\Evaluate();
        $this->evaluatePhotoModel = new \app\common\model\EvaluatePhoto();
        $this->bnbInfoModel = new \app\common\model\BnbInfo();
        $this->favoriteModel = new \app\common\model\BnbFavorite();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/'
        ];
    }


    public function getMaps($cityCode=null){
        $where = [];
        $where['a.status'] = array('IN',config('state.state_ok').','.config('state.state_mark'));
        $where['a.area_city_code'] = $cityCode;
        $bnbData = $this->bnbModel->getLandlordBnbList($where);
        $data = [];
        $today = date('Y-m-d');
        if($bnbData) {
            foreach ($bnbData as $k => $v) {
                $data[$k]['id'] = $v['id'];
                $data[$k]['longitude'] = $v['map_lng'];
                $data[$k]['latitude'] = $v['map_lat'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['address'] = $v['area_address'];
                $data[$k]['bnb_image'] = $_SERVER['HTTP_HOST'] . $this->imagePath['bnb_thumb_path'] . $v['bnb_image'];
                $prices = (new BnbPrice())->getBnbPriceList($v['id'], $today, $today, $today);
                $data[$k]['price'] = $prices[$today]['price'] ? ('￥' . $prices[$today]['price']) : '预定结束';
            }
        }
        $this->assign('title','民宿地址');
        $this->assign('path',$this->imagePath);
        $this->assign('data',json_encode($data));
        return $this->fetch();
    }

    /**
     * 详情
     * @param null $id
     * @return mixed
     */
    public function bnbInfo($id=null){
        if($id){
            $bnbData = $this->bnbModel->getBnb($id);
            //房东信息
            $landlord = $this->landlordModel->getLandlord($bnbData['landlord_user']);
            //房间图片
            $condition = [
                'a.status' => config('state.state_ok'),
                'a.bnb_id' => $id
            ];
            $images =  $this->imageModel->getImagesList($condition);
           // print_r($images);die();
            $this->assign('images',$images);
            //民宿设施
            if($bnbData['features_ids']) {
                $featureData = $this->featureModel->getFeaturesByBnbId($bnbData['features_ids']);
                $this->assign('featureData',$featureData);
            }
            //房间价格
            $nowDate = date('Y-m-d',time());
            $prices = (new BnbPrice())->getBnbPriceList($id, $nowDate, $nowDate, $nowDate);
            $bnbInfo = $this->bnbInfoModel->getBnbInfo($id);
            //浏览+1
            $infoData = [];
            if($bnbInfo){
                $infoData['numbers_view'] = $bnbInfo['numbers_view']+1;
                $infoData['bnb_id'] = $id;
                $this->bnbInfoModel->where('id',$bnbInfo['id'])->update($infoData);
            }else{
                $infoData['numbers_view'] = 1;
                $infoData['bnb_id'] = $id;
                $this->bnbInfoModel->save($infoData);
            }
            //房间评价
            $conEvaluate = [
                'a.status' => array('in',config('state.state_ok').','.config('state.state_mark')),
                'a.bnb_id' => $id
            ];
            $evaluateData = $this->evaluateModel->getEvaluteList($conEvaluate);
            $evaluateNum = $bnbInfo['numbers_score'];


            $this->assign('path',$this->imagePath);
            $this->assign('evaluateNum',$evaluateNum);
            $this->assign('evaluateData',$evaluateData[0]);
            $this->assign('landlord',$landlord);
            $this->assign('bnbData',$bnbData);
            $this->assign('price',$prices[$nowDate]['price']);
            $this->assign('title',$bnbData['name']);
            return $this->fetch();
        }
    }

    /**
     * 预约订单
     * @param null $bnb_id
     * @return mixed
     */
    public function order($bnb_id = null)
    {
            if ($bnb_id) {
            $dt = new Carbon();
            $days = [];
            $days['begin_date'] = $dt->toDateString();
            $dt->addDays(config('setting.price_max_day'));
            $days['end_date'] = $dt->toDateString();

            $bnbData = $bnbData = $this->bnbModel->getBnb($bnb_id);
            $orderData = (new BnbPrice())->getBnbJsDateList($bnb_id, $days['begin_date'], $days['end_date']);

            $this->assign('days', $days);
            $this->assign('price', $orderData);
                $this->assign('title', '民宿预定');
                $this->assign('bnbData', $bnbData);
                return $this->fetch();
            }
            $this->error('未选择民宿');
        }


    public function confirmOrder()
    {
        $begin_date = $this->request->get('begin_date');
        $end_date = $this->request->get('end_date');
        $bnbId = $this->request->get('bnb_id');
        $people_num = $this->request->get('people_num');
        $dataObj = (new BnbOrder())->getOrderData($this->users['id'], $bnbId, $begin_date, $end_date, $people_num, '', '');

        if ($dataObj->checkResult()) {
            $data = $dataObj->getData();
            //赠送保洁次数

            // 获取用户优惠券信息
            $model_user_voucher = new UserVoucher();
            $voucher_list = $model_user_voucher->getUserVoucherList($this->getUserID(), UserVoucher::$VoucherStatus_OK);
            $best_voucher = $model_user_voucher->getBestBnbVoucher($voucher_list , $data['price']['room_amount']);

            $this->assign('data', $data);
            $this->assign('voucher', $best_voucher);
            $this->assign('title', '确认订单');
            return $this->fetch();
        } else {
            $this->error($dataObj->getText());
        }
    }

    /**
     * 保存订单并跳转
     */
    public function saveOrder(){
        if($this->request->isAjax()){
            $begin_date =   $this->request->post('begin_date');
            $end_date   =   $this->request->post('end_date');
            $bnb_id     =   $this->request->post('bnb_id');
            $people_number = $this->request->post('people_number');
            $voucher = $this->request->post("voucher");

            if($begin_date && $end_date && $bnb_id && $people_number){
                $dataObj = (new BnbOrder())->createBnbOrder($this->users['id'], $bnb_id, $begin_date, $end_date, $people_number, '', '',"",$voucher,0);
                if($dataObj->getCode()>0){
                    $msg = $dataObj->getText();
                    $this->error($msg);
                }else{
                    $data = $dataObj->getData();
                    $this->success('生成订单成功','',$data);
                }
            }else{
                $this->error('订单数据不完整');
            }
        }
    }

    /**
     * 收藏民宿
     */
    public function collect(){
        if($this->request->isAjax()){
            $bnbId = $this->request->post('id');
            if(!$bnbId){
                $this->error('未提供要收藏的民宿信息');
            }
            $data = [
                'bnb_id'    =>  $bnbId,
                'user_id'   =>  $this->users['id']
            ];
            $count = $this->favoriteModel->where(['bnb_id'=>$bnbId,'user_id'=>$this->users['id']])->count();
            $bnbInfoData = $this->bnbInfoModel->getBnbInfo($bnbId);
            if($count > 0){
                $this->error('您已收藏了该民宿');
            }
            $res = $this->favoriteModel->save($data);
            //收藏+1
            $infoData = [];
            $infoData['bnb_id'] = $bnbId;
            if($bnbInfoData){
                $infoData['numbers_favourite'] =  $bnbInfoData['numbers_favourite']+1;
                $this->bnbInfoModel->where('id',$bnbInfoData['id'])->update($infoData);
            }else{
                $infoData['numbers_favourite'] =  1;
                $this->bnbInfoModel->save($infoData);
            }
            if($res){
                $this->success('收藏成功');
            }
            $this->error('收藏失败');
        }
    }

    /**
     * 获取预约订单显示价格
     */
    public function getOrderPrice(){
        if($this->request->isAjax()){
            $param = $this->request->request();
            if($param['id'] && $param['begin_date'] && $param['end_date']){
                $total = (new BnbOrder())->reckonOrderPrice($param['id'],$param['begin_date'],$param['end_date']);
                $days = (strtotime($param['end_date'])-strtotime($param['begin_date']))/(3600*24);
                $list = ['days'=>$days,'total'=>$total];
                $this->success('获取订单价格成功','',$list);
            }else{
                $this->error('获取订单价格失败');
            }
        }
    }

    /**
     * 生成预约时间内的价格
     * @param $bnb_id
     */
    private function createOrderPricelist($bnb_id){
        $orderDate_start = date('Y-m-01');
        $orderDate_end = date('Y-m-d',(time()+config('setting.price_max_day')*24*3600));
        $orderPrices = (new BnbPrice())->getBnbPriceList($bnb_id, $orderDate_start, $orderDate_end);
        $orderData = [];
        $i = 0;
        foreach($orderPrices as $k => $v){
            $orderData[$i]['date'] = $k;
            $orderData[$i]['price'] = $v['price'];
            $orderData[$i]['disabled'] = false;
            if( strtotime($k)< time()){
                $orderData[$i]['disabled'] = true;
            }
            $i = $i+1;
        }
        return $orderData;
    }

}