<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2 0002
 * Time: 17:45
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use app\common\model\Cleaninfo as Clean;
use app\admin\model\Area;
use app\common\model\Users;
use app\common\model\OrderClean;
use app\common\model\BillClean;
use think\Request;
class Cleaninfo extends Backend
{

    /**
     * Cleaninfo模型对象
     */
    protected $model = null;
    protected $usersModel = null;
    protected $orderCleanModel = null;
    protected $billCleanModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Clean();
        $this->usersModel = new Users();
        $this->orderCleanModel = new OrderClean();
        $this->billCleanModel = new BillClean();
    }
    /**
     * 保洁员列表
     */
    public function index(){
        //设置过滤方法
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
            $filterData['is_cleaner'] = [
                    'key' => '=',
                    'value' => 1
            ];

            //指定关联查询主表users
            $relationModel = config('database.prefix').'users';
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData,$relationModel);
            $total = $this->usersModel
                    -> alias('a')
                    -> field('a.*,b.is_order,b.is_order_time,b.id as bid')
                    -> join('__CLEANINFO__ b','a.id = b.users_id','right')
                    ->where($where)->order($sort, $order)->count();

            $list = $this->usersModel
                -> alias('a')
                -> field('a.*,b.is_order,b.is_order_time,b.id as bid')
                -> join('__CLEANINFO__ b','a.id = b.users_id','left')
                -> where($where)->order($sort, $order)->limit($offset,$limit)->select();
            foreach($list as $k=>$v){
                $list[$k]['user_avatar'] = config('upload.avatar')['thumb']['avatar']['dir'].'/'.$v['user_avatar'];
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }
    /**
     * 详情*
     */
    public function more($ids=NULL){
        $row = $this->usersModel->getUsersInfo($ids);
        //保洁订单数量
        if($row['is_cleaner'] == 1){
            $conditionClean = [
                'status' => array('gt',config('state.state_disable')),
                'cleaner_id' => $row['id']
            ];
            $orderCleanNum = $this->orderCleanModel->getOrderCleanNum($conditionClean);
            if($orderCleanNum > 0){
                $this->assign('orderCleanNum',$orderCleanNum);
            }
            $whereBill = [
                'status'    =>  config('state.state_ok'),
                'cleaner_id'  =>  $row['id']
            ];
            $billCleanNum = $this->billCleanModel->getBillCleanNum($whereBill);
            if($billCleanNum > 0){
                $this->assign('billCleanNum',$billCleanNum);
            }
        }
        $idcardImg = [];
        $imgPath = '/'.config('upload.idcard')['thumb']['idcard']['dir'].'/';
        if($row['user_idcard_image']){
            $idcardImg = explode(',',$row['user_idcard_image']);
        }
        $list = ['row'=>$row,'idcardImg'=>$idcardImg,'imgPath'=>$imgPath];
        $this->view->assign($list);
        return $this->view->fetch();
    }
    /**
     * 查看保洁员信息
     */
    public function preview($uid=null){
        if($uid) {
            //房东信息
            $data = $this->model->getCleanerByUID($uid);
            if($data){
                $this->assign('row',$data);
            }
            $this->assign('data',$data);
            $this->assign('uid',$uid);
        }else{
            $this->error('无有效用户信息');
        }
        return $this->fetch();
    }
    /**
     * 添加保洁员信息
     */
    public function add($uid=null){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $result = $this->model->save($params);
            if($result){
                $this->success('添加保洁员信息成功');
            }else{
                $this->error('添加保洁员信息失败！');
            }
        }else{
            if(!$uid){
                $this->error('无有效用户信息');
            }
            $this->commonData();
            $this->assign('uid',$uid);
            return $this->fetch('setInfo');
        }
    }
    /**
     * @param null $uid
     * @return mixed
     * 修改保洁员信息
     */
    public function edit($uid=null){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $result = $this->model->where(['id'=>$params['id']])->update($params);
            if($result){
                $this->success('修改保洁员信息成功');
            }else{
                $this->error('修改保洁员信息失败！');
            }
        }else{
            if($uid) {
                $data = $this->model->getCleanerByUID($uid);
                if($data){
                    $this->assign('row',$data);
                }
                $this->commonData($data['province_code']);
                $this->assign('uid',$uid);
            }else{
                $this->error('无有效用户信息');
            }
            return $this->fetch('setInfo');
        }

    }
    /**
     * 设置是否接单
     */
    public function setOrder(){
        if($this->request->isAjax()){
            $params = $this->request->request();
            if($params){
                $data = [];
                switch($params['state']){
                    case 'success':
                        $data['is_order'] = '1';
                        break;
                    case 'fail':
                        $data['is_order'] = '0';
                        break;
                }
                $data['is_order_time'] = time();
                $result = $this->model->updateCleaninfoByUId($params['id'],$data);
                if($result){
                    $this->success('操作成功');
                }
                $this->error('操作失败');
            }
            $this->error('无有效请求参数');
        }
        $this->error('异常请求');
    }

    /**
     * 保洁结算信息
     * @param null $cleaner_id
     * @return mixed|\think\response\Json
     */
    public function billCleanPreview($cleaner_id=null){
        if(!$cleaner_id){
            $this->error('未选定保洁员');
        }
        $condition = [
            'a.cleaner_id' => $cleaner_id,
            'a.status' => config('state.state_ok')
        ];
        $params = $this->request->request();
        $result = [];
        if(isset($params['name']) && $params['name']){
            $condition['c.name'] = array('LIKE','%'.$params['name'].'%');
            $result['name'] = $params['name'];
        }
        if(isset($params['order_sn']) && $params['order_sn']){
            $condition['b.order_sn'] = array('LIKE','%'.$params['order_sn'].'%');
            $result['order_sn'] = $params['order_sn'];
        }
        if(isset($params['bill_date']) && $params['bill_date']){
            $condition['a.bill_date'] = str_replace('/','-',$params['bill_date']);
            $result['bill_date'] = $params['bill_date'];
        }
        $data = $this->billCleanModel-> getListData($condition);
        $page = $data->render();
        if($this->request->isAjax()){
            $result['page'] = $page;
            $result['rows'] = $data;
            return json($result);
        }else{
            $this->assign('uid',$cleaner_id);
            $this->assign('data',$data);
            $this->assign('page',$page);
            return $this->fetch();
        }
    }
    /**
     * 获取房间省、市、管理员、房东公共信息
     */
    private function commonData($provinceCode=Null){
        //省
        $provinces = (new Area())-> getProvinceList();
        //市
        if(!$provinceCode){
            $provinceCode = array_keys($provinces)[0];
        }
        $citys = (new Area())->getCitys($provinceCode);
        $this->assign('provinces',$provinces);
        $this->assign('citys',$citys);
    }
    private function getSearchParams($params){
        $filterData = [];
        if($params){
            foreach($params as $k=>$v){
                if($v) {
                    $relation = '=';
                    switch($k){
                        case 'user_truename':
                        case 'user_nickname':
                        case 'user_idcard_number':
                        case 'user_mobile':
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