<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10 0010
 * Time: 16:28
 */

namespace app\admin\controller;
use app\common\controller\Backend;

use think\Controller;
use think\Request;
use app\common\base\AutoCleaner;
use app\common\base\CleanOrder;
use app\common\model\OrderClean as Oclean;
use app\common\model\OrderCleanPhoto;
use app\common\model\Bnb;

class OrderClean extends Backend
{
    protected $model = null;
    protected $imagepath = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Oclean();
        $this->imagePath = [
            'bnb_clean_photo_path' =>  '/'.config('upload.bnb_clean_photo')['thumb']['thumb']['dir'].'/',
            'upload_clean_photo_path' =>  '/'.config('upload.upload_clean_photo')['thumb']['thumb']['dir'].'/',
        ];
        $this->assign('path',$this->imagePath);
    }
    /**
     * 查看
     */
    public function index(){
        $type = 1;
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->displayindex();
            return $json;
        }
        $list = ['type'=>$type];
        $this->assign('list',$list);
        return $this->view->fetch('index');
    }
    public function progress(){
        $type = 2;
        if ($this->request->isAjax()) {
            $json = $this->displayindex();
            return $json;
        }
        $list = ['type'=>$type];
        $this->assign('list',$list);
        return $this->view->fetch('index');
    }
    public function cleanerOrder(){
        $this->request->filter(['strip_tags']);
        $params = Request::instance()->param();
        if ($this->request->isAjax()) {
            $json = $this->displayindex();
            return $json;
        }
        $cleaner = isset($params['cleaner'])?$params['cleaner']:'';
        $list = ['cleaner'=>$cleaner];
        $this->assign('list',$list);
        return $this->view->fetch('index');
    }
    public function displayindex(){
        //设置过滤方法

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加订单查询条件
            $filterData = [];
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            $filterData = $this->getSearchParams($params);

            $cleaner =  $this->request->get('cleaner');
            $type = $this->request->get('type');
            if($cleaner){
                $filterData['cleaner_id'] = [
                        'key' => '=',
                        'value' => $cleaner
                ];
            }
            if($type){
                $finshState = [config('state.order_clean_finish'),config('state.order_clean_verify')];
                $progressState = [config('state.order_clean_wait'),config('state.order_clean_allot'),config('state.order_clean_accept')];
                $searchState = ($type==1)?$finshState:$progressState;
                $filterData['status'] = [
                        'key' => 'IN',
                        'value' => implode(',',$searchState)
                ];
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                -> alias('a')
                -> field('a.*,b.province_name,e.room_space,c.city_name,e.name')
                -> join('__AREA__ b','b.id = a.province_code','left')
                -> join('__AREA__ c','c.id = a.city_code','left')
                -> join('__BNB__ e','e.id = a.bnb_id','left')
                -> where($where)
                -> order($sort, $order)
                -> limit($offset, $limit)
                -> count();

            $list = $this->model
                -> alias('a')
                -> field('a.*,b.province_name,e.room_space,c.city_name,e.name')
                -> join('__AREA__ b','b.id = a.province_code','left')
                -> join('__AREA__ c','c.id = a.city_code','left')
                -> join('__BNB__ e','e.id = a.bnb_id','left')
                -> where($where)
                -> order($sort, $order)
                -> limit($offset, $limit)
                -> select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
    }
    /**
     * @param null $ids
     * @return string
     * 详情
     */
    public function more($ids=NULL){
        if($ids){
            $row = $this->model->getCleanOrderById($ids);
            $this->view->assign("row", $row);
            //保洁对比图片
            $photoData = (new OrderCleanPhoto())->getOrderPhotoList($ids);
            $progressState = [config('state.order_clean_wait'),config('state.order_clean_allot'),config('state.order_clean_accept')];
            $this->assign('progressState',$progressState);
            $this->assign('photoData',$photoData);
            $this->assign('depositState',config('state.deposit_state'));
            $this->assign('stateList',config('state.order_clean_state'));
            return $this->view->fetch();
        }else{
            $this->error('无有效订单信息');
        }

    }

    /**
     * 审核保洁对比图片
     */
    public function verifyCleanPhoto(){
        if($this->request->isAjax()){
            $params = $this->request->post();
            if(!$params['id'] || !$params['status']){
                $this->error('无效修改');
            }
            $data['admin_verify_state'] = ($params['status'] == 'success')?config('state.clean_photo_success'):config
            ('state.clean_photo_fail');
            $res = (new OrderCleanPhoto())->updateCleanPhotoById($params['id'],$data);
            if($res){
                $this->success('审核完成');
            }
            $this->error('审核失败');
        }else{
            $this->error('异常请求');
        }
    }
    /**
     * 删除保洁对比图片
     */
    public function delComparePhoto(){
        $id = Request::instance()->param('id');
        if(!$id){
            $this->error('未提供删除图片信息');
        }
        $comparePhoto = (new OrderCleanPhoto())->getComparePhotoDataByID($id);
        if(!$comparePhoto){
            $this->error('无该图片记录');
        }
        $rel = (new OrderCleanPhoto())->where('id',$id)->delete();
        if(!$rel){
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }
    /**
     * 分配保洁员
     */
    public function allotClean(){
        $orderSn = Request::instance()->param('order_sn');
        if(!$orderSn){
            $this->error('无有效保洁订单信息');
        }
        $orderData = $this->model->getOrderBySn($orderSn);
        if(!$orderData){
            $this->error('无该保洁订单记录');
        }
        $bnbData = (new Bnb())->getBnb($orderData['bnb_id']);
        if(!$bnbData){
            $this->error('该民宿不存在');
        }
        $cleanDate = date('Y-m-d',$orderData['clean_start_time']);
        $cleaner = (new AutoCleaner())->getCleaner($bnbData['map_lng'], $bnbData['map_lat'],$cleanDate);
        if(!$cleaner){
            $this->error('暂无空闲保洁员可分配');
        }
        $result = (new CleanOrder())->allocCleanOrder($orderData, $cleaner);
        if(!$result){
            $this->error('分配保洁员失败');
        }
        $this->success('分配保洁员成功');
    }
    private function getSearchParams($params){
        $filterData = [];
        if($params){
            foreach($params as $k=>$v){
                if($v) {
                    $relation = '=';
                    switch($k){
                        case 'user_truename':
                            $k = 'b.user_truename';
                            $relation = 'LIKE';
                            break;
                        case 'name':
                            $k = 'e.name';
                            $relation = 'LIKE';
                            break;
                        case 'order_sn':
                        case 'room_space':
                        case 'fee_clean':
                        case 'contact_name':
                        case 'contact_mobile':
                            $relation = 'LIKE';
                            break;

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
}