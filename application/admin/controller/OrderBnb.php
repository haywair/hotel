<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6 0006
 * Time: 14:02
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use think\Request;
use app\admin\model\Admin;
use app\common\base\BnbPrice;
use app\common\model\OrderBnb as OB;
use app\common\model\Bnb;
use app\common\model\Voucher;
use app\admin\model\Area;
use app\common\base\BnbOrder;
use app\common\base\BnbCleanLogic;
use app\common\model\ReplacedSource;
use app\common\base\BnbOrderLogic;
class OrderBnb extends Backend
{
    protected $model = null;
    protected $bnbModel = null;
    protected $voucherModel = null;
    protected $adminModel = null;
    protected $sourceModel = null;
    protected $orderAddonCleanModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new OB();
        $this->voucherModel = new Voucher();
        $this->bnbModel = new Bnb();
        $this->adminModel = new Admin();
        $this->sourceModel = model('ReplacedSource');
        $this->orderAddonCleanModel = model('OrderAddonClean');
        $this->orderUserCleanModel = model('OrderUserClean');
    }
    /**
     * 查看
     */
    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        $params = $this->request->request();
        if(isset($params['landlord_id'])){
            $this->assign('landlord',$params['landlord_id']);
        }
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加民宿查询条件
            $filterData = [];
            //增加查询条件
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            $filterData = $this->getSearchParams($params);
            $landlord =  $this->request->get('landlord');
            if($landlord){
                $bnbIds = $this->bnbModel->getLandlordBnb($landlord);
                $filterData['bnb_id'] = [
                    'key' => 'IN',
                    'value' => implode(',',$bnbIds)
                ];

            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                -> alias('a')
                -> field('a.*,b.province_name,c.city_name,d.user_nickname,e.name,f.name as source_name')
                -> join('__AREA__ b','b.id = a.province_code','left')
                -> join('__AREA__ c','c.id = a.city_code','left')
                -> join('__USERS__ d','d.id = a.user_id','left')
                -> join('__BNB__ e','e.id = a.bnb_id','left')
                -> join('__REPLACED_SOURCE__ f','f.id = a.replaced_source_id','left')
                -> where($where)
                -> order($sort, $order)
                -> limit($offset, $limit)
                -> count();

            $list = $this->model
                -> alias('a')
                -> field('a.*,b.province_name,c.city_name,d.user_nickname,e.name,f.name as source_name')
                -> join('__AREA__ b','b.id = a.province_code','left')
                -> join('__AREA__ c','c.id = a.city_code','left')
                -> join('__USERS__ d','d.id = a.user_id','left')
                -> join('__BNB__ e','e.id = a.bnb_id','left')
                -> join('__REPLACED_SOURCE__ f','f.id = a.replaced_source_id','left')
                -> where($where)
                -> order($sort, $order)
                -> limit($offset, $limit)
                -> select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $sourceData = (new ReplacedSource())->where('status',config('state.state_ok'))->column('name','id');
        $this->assign('sourceData',json_encode($sourceData));
        return $this->view->fetch();

    }
    /**
     * @param null $ids
     * @return string
     * 详情
     */
    public function more($ids=NULL){
        if($ids){
            $row = $this->model->getOrderById($ids);
            //优惠券信息
            if($row['voucher_id']){
                $voucherData = $this->voucherModel->getVoucherByIds([$row['voucher_id']]);
                $this->assign('voucherData',current($voucherData));
            }
            //管理员下单
            if($row['replaced_admin_id'] > 0){
                $admin = $this->adminModel->where('id',$row['replaced_admin_id'])->column('username','id');
                $this->assign('admin',$admin[$row['replaced_admin_id']]);
            }
            //其他来源订单
            if($row['replaced_source_id'] > 0){
                $source = $this->sourceModel->where('id',$row['replaced_source_id'])->column('name','id');
                $this->assign('source',$source[$row['replaced_source_id']]);
            }
            //订单附加保洁订单数量
            $addonCleanNum = $this->orderAddonCleanModel->where('bnb_order_sn',$row['order_sn'])->count();
            //用户预购保洁订单数量
            $orderCleanNum = $this->orderUserCleanModel->where('order_sn',$row['order_sn'])->count();
            $cleanNum = [
                'addon_clean_num'=> $addonCleanNum,
                'order_clean_num'  => $orderCleanNum
            ];
            $noPayState = [config('state.order_create_state'),config('state.order_veriry_state')];
            $list = [
                'row'           =>  $row,
                'cleanNum'      =>  $cleanNum,
                'depositState'  =>  config('state.deposit_state'),
                'stateList'     =>  config('state.orderState'),
                'noPayState'    =>  $noPayState
            ];
            $this->assign($list);
            return $this->view->fetch();
        }else{
            $this->error('无有效订单信息');
        }

    }
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                if ($this->dataLimit)
                {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try
                {

                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    if(!$params['bnb_id']){
                        $this->error('未选择民宿');
                    }
                    //生成订单
                    $userid = 999999;
                    $createObj = (new BnbOrder())->createBnbOrder($userid, $params['bnb_id'], $params['in_date'], $params['out_date'], $params['people_numbers'], $params['contact_name'], $params['contact_mobile'], $contact_content = '',$voucher_id= 0,$clean_numbers = 0);
                    if($createObj->getCode() > 0){
                        $this->error($createObj->getText());
                    }
                    $orderId = $createObj->getData();

                    $adminId = session(config('session.Admin'))['id'];
                    $data['order_total']    = $params['order_total'];
                    $data['pay_time']       = time();
                    $data['status']         = config('state.order_pay_state');
                    $data['replaced_source_id'] = $params['replaced_source_id'];
                    $data['replaced_order_sn'] = $params['replaced_order_sn'];
                    $data['replaced_admin_id'] = $adminId;
                    $where = $this->model->where('id',$orderId)->update($data);
                    $orderData = $this->model->getOrderById($orderId);
                    $resClean = (new BnbCleanLogic())->onBnbOrderPaid($orderData);
                    if($resClean->getCode() > 0){
                        $this->error($resClean->getText());
                    }
                    $this->success('代下单成功');

                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->commonData();
        return $this->view->fetch();
    }
    public function editprice(){
        if($this->request->isAjax()){
            $order_sn = Request::instance()->param('order_sn');
            $order_money = Request::instance()->param('order_total');
            if(!$order_sn){
                $this->error('未提供订单单号');
            }
            if(!$order_money){
                $this->error('请填写修改后的订单总价格');
            }
            $orderInfo = $this->model->getOrderBySn($order_sn);
            if(!$orderInfo){
                $this->error('该订单不存在');
            }
            //创建和审核订单状态下可更改价格
            $orderEdit_state = [config('state.order_create_state'),config('state.order_veriry_state')];
            if(!in_array($orderInfo['status'],$orderEdit_state)){
                $this->error('该订单暂不支持修改价格');
            }
            // 计算总价
            $orderdata = [];
            $orderdata['order_total'] = $order_money;
            $orderdata['pay_total'] = $orderdata['order_total'] + $orderInfo['deposit_amount'];
            try {
                $this->model->startTrans();
                $res = $this->model->where('id',$orderInfo['id'])->update($orderdata);
                $logData = [
                    'admin_id'  =>  session(config('session.Admin'))['id'],
                    'username'  =>  session(config('session.Admin'))['username'],
                    'url'       =>  '/admin/order_bnb/editprice',
                    'title'     =>  '修改订单价格',
                    'content'   =>  $order_sn.' 订单价格由 ￥'.$orderInfo['order_total'].' 更改为 '.$order_money,
                    'ip'        =>  $_SERVER['REMOTE_ADDR'],
                    'useragent' =>  $_SERVER['HTTP_USER_AGENT'],
                    'createtime'    =>  time()
                ];
                $log = (new \app\admin\model\AdminLog())->save($logData);
                $this->model->commit();
                $return = true;
            } catch (Exception $e) {
                $this->model->rollback();
                $return = false;
            }

            if(!$return){
                $this->error('修改订单价格失败');
            }
            $this->success('修改订单价格成功');
        }
    }

    public function cancelorder(){
        if($this->request->isAjax()) {
            $params = Request::instance()->param();
            $order_sn = $params['order_sn'] ?? "";
            $adminId = session(config('session.Admin'))['id'];
            if ($order_sn) {
                $e = (new BnbOrderLogic())->onCancel($order_sn, 0, $adminId);
                if ($e->checkResult()) {
                    return $this->success("取消订单成功");
                } else {
                    return $this->error("取消订单失败 " . $e->getText());
                }
            } else {
                return $this->error("未选择订单");
            }
        }else{
            $this->error("异常请求");
        }
    }
    //订单附加保洁订单信息
    public function addonClean(){
        $order_sn = Request::instance()->param('order_sn');
        //增加的保洁员订单
        $orderAddClean = $this->orderAddonCleanModel->getCleanOrderByOrderSn($order_sn);
        if(!$orderAddClean){
            $this->error('该民宿订单无附加保洁订单信息');
        }
        $this->assign('row',$orderAddClean);
        return $this->fetch();
    }
    //用户预定订单
    public function orderClean(){
        $order_sn = Request::instance()->param('order_sn');
        //增加的保洁员订单
        $orderClean = $this->orderUserCleanModel->getOrderByBnbOrderSn($order_sn);
        if(!$orderClean){
            $this->error('该用户未购买保洁订单');
        }
        $this->assign('row',$orderClean);
        return $this->fetch();
    }
    //生成代下单订单号
    private function genOrderSn($admin_id, $bnb_id, $city_code)
    {
        $begin_string = "BD"; // 1
        $userid_string = str_pad($admin_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $bnb_string = str_pad($bnb_id, 7, "0", STR_PAD_LEFT); // 7
        $random_string = rand(10000, 99999); //5

        $ordersn = $begin_string . $time_string . $city_code . $userid_string . $bnb_string . $random_string; //42
        return $ordersn;
    }
    //收款信息
    public function savePay(){
        $order_sn = Request::instance()->param('order_sn');
        if($this->request->isPost()){
            $param = $this->request->post('row/a');
            if(!$param['trade_no'] || !$param['pay_sn'] || !$param['pay_time']){
                $this->error('收款信息不完整');
            }
            $param['pay_time'] = strtotime($param['pay_time']);

            //创建保洁订单信息
            $orderData = $this->model->getOrderBySn($param['order_sn']);
            $resClean = (new BnbCleanLogic())->onBnbOrderPaid($orderData);
            if($resClean->getCode() > 0){
                $this->error($resClean->getText());
            }
            $result = $this->model->saveOrderBnbPayInfo($param['order_sn'],$param['pay_sn'],$param['trade_no'],$param['pay_time']);
            if(!$result){
                $this->error('保存收款信息失败');
            }
            $this->success('保存收款信息成功');
        }
        $this->assign('order_sn',$order_sn);
        return $this->fetch();
    }
    private function commonData($provinceCode=null){

        $resourceList = (new ReplacedSource())->where('status',config('state.state_ok'))->column('name','id');
        $provinces = (new Area())-> getProvinceList();
        $provinceCode = array_keys($provinces)[0];
        //市
        $citys = (new Area())->getCitys($provinceCode);
        $list = ['provinces'=>$provinces,'citys'=>$citys,'resourceList'=>$resourceList];
        $this->assign($list);
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
                        case 'in_date':
                        case 'out_date':
                        case 'contact_name':
                        case 'contact_mobile':
                        case 'source_name':
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