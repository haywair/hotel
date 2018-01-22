<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/1 0001
 * Time: 16:31
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use app\admin\model\BnbSpecialprice as Bsmodel;
use app\admin\model\Landlordinfo as Landlord;
use app\admin\model\Bnb;
use app\admin\model\Area;
use app\common\model\Users;
use app\common\model\OrderBnb;
use app\common\model\BillLandlord;
use think\Request;
class Landlordinfo extends Backend
{
    /**
     * Landlord模型对象
     */
    protected $model = null;
    protected $bnbModel = null;
    protected $orderBnbModel = null;
    protected $usersModel = null;
    protected $billLandlordModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Landlord();
        $this->bnbModel = new Bnb();
        $this->usersModel = new Users();
        $this->orderBnbModel = new OrderBnb();
        $this->billLandlordModel = new BillLandlord();
    }
    /**
     * 房东列表
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
            $filterData['is_landlord'] = [
                    'key' => '=',
                    'value' => 1
            ];

            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,null,$filterData);
            $total = $this->usersModel
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->usersModel
                ->where($where)
                ->field(['password', 'salt', 'token'], true)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
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
        //房东民宿订单数量
        if($row['is_landlord'] == 1){
            //房东所拥有的民宿
            $bnbIds = $this->bnbModel->getLandlordBnb($row['id']);
            if($bnbIds) {
                $conditionBnb = [
                    'status' => array('gt', config('state.state_disable')),
                    'bnb_id' => array('IN', implode(',', $bnbIds))
                ];
                $orderBnbNum = $this->orderBnbModel->getOrderBnbNum($conditionBnb);
                if ($orderBnbNum > 0) {
                    $this->assign('orderBnbNum', $orderBnbNum);
                }
                //房东结算订单
                $whereBill = [
                    'status'    =>  config('state.state_ok'),
                    'landlord_id'  =>  $row['id']
                ];
                $billLandlordNum = $this->billLandlordModel->getBillLandlordNum($whereBill);
                if($billLandlordNum > 0){
                    $this->assign('billLandlordNum',$billLandlordNum);
                }
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
     * 查看房东信息
     */
    public function preview($uid=null){
        if($uid) {
            //房东信息
            $data = $this->model->getLandloardByUID($uid);
            //房东所属民宿信息
            $condition = [
                'a.landlord_user' => $uid,
                'a.status' => ['IN',[config('state.state_ok'),config('state.state_disable'),config('state.state_mark')]]
            ];
            $dataBnb = $this->bnbModel->getBnbList($condition);
            if($data){
                $this->assign('row',$data);
            }
            if($dataBnb){
                $this->assign('dataBnb',$dataBnb);
            }
            $this->assign('uid',$uid);
        }else{
            $this->error('无有效用户信息');
        }
        return $this->fetch();
    }
    /**
     * 添加房东信息
     */
    public function add($uid=null){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $result = $this->model->save($params);
            if($result){
                $this->success('添加房东信息成功');
            }else{
                $this->error('添加房东信息失败！');
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
     * 设置房东信息
     */
    public function edit($uid=null){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            $result = $this->model->where(['id'=>$params['id']])->update($params);
            if($result){
                $this->success('修改房东信息成功');
            }else{
                $this->error('修改房东信息失败！');
            }
        }else{
            if($uid) {
                $data = $this->model->getLandloardByUID($uid);
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
     * 未绑定的房间列表
     */
    public function bindBnb($uid=null){
        if($this->request->isPost()){
            $params = $this->request->post();
            $upData = [];
            if(isset($params['ids']) && $params['ids']){
                foreach($params['ids'] as $k=>$v){
                    if($v) {
                        $upData[$k] = [
                            'id' => $v,
                            'landlord_user' => $params['uid']
                        ];
                    }
                }
                $result = $this->bnbModel->saveAll($upData);
                if($result){
                    $this->success('民宿绑定成功');
                }else{
                    $this->error('民宿绑定失败');
                }
            }else{
                $this->error('未选择民宿');
            }

        }else{

            if(!$uid){
                $this->error('未选定有效房东');
            }
            $condition = [
                'a.landlord_user' => 0,
                'a.status' => ['IN',[config('state.state_ok'),config('state.state_disable'),config('state.state_mark')]]
            ];
            $params = $this->request->request();
            $result = [];
            if(isset($params['name']) && $params['name']){
                $condition['a.name'] = array('LIKE','%'.$params['name'].'%');
                $result['name'] = $params['name'];
            }
            if(isset($params['province']) && $params['province']){
                $condition['a.area_province_code'] = $params['province'];
                $result['province'] = $params['province'];
            }
            if(isset($params['city']) && $params['city']){
                $condition['a.area_city_code'] = $params['city'];
                $result['city'] = $params['city'];
            }

            $data = $this->bnbModel-> getBnbPageList($condition);
            $page = $data->render();
            foreach($data as $k=>$v){
                switch($v['status']){
                    case config('state.state_ok'):
                        $data[$k]['status'] = '正常';
                        break;
                    case config('state.state_disable'):
                        $data[$k]['status'] = '下架';
                        break;
                    case config('state.state_delete'):
                        $data[$k]['status'] = '删除';
                        break;
                    case config('state.state_mark'):
                        $data[$k]['status'] = '推荐';
                        break;
                }
                $data[$k]['bnb_image'] = '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/'.$v['bnb_image'];
            }
            if($this->request->isAjax()){
                $result['page'] = $page;
                $result['rows'] = $data;
                return json($result);
            }else{
                $provinces = (new Area())-> getProvinceList();
                $citys = (new Area())->getCitys(array_keys($provinces)[0]);
                $this->assign('provinces',$provinces);
                $this->assign('citys',$citys);
                $this->assign('uid',$uid);
                $this->assign('data',$data);
                $this->assign('page',$page);
                return $this->fetch();
            }
        }
    }
    /**
     * 移除房东绑定的房间
     */
    public function removeBnb(){
        if($this->request->isAjax()){
            $params = $this->request->request();
            $condition = [
                'id' => $params['id'],
                'landlord_user' => $params['landlord_user']
            ];
            $count = $this->bnbModel->where($condition)->count();
            if($count > 0){
                $result = $this->bnbModel->updateBnbByBID($params['id'],['landlord_user'=>0]);
                if($result){
                    $this->success('解绑成功');
                }else{
                    $this->error('解绑失败');
                }
            }else{
                $this->error('暂未绑定该民宿');
            }

        }else{
            $this->error('异常请求');
        }
    }
    /**
     * 房东结算信息
     * @param null $cleaner_id
     * @return mixed|\think\response\Json
     */
    public function billLandlordPreview($landlord=null){
        if(!$landlord){
            $this->error('未选定房东');
        }
        $condition = [
            'a.landlord_id' => $landlord,
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
        $data = $this->billLandlordModel-> getListData($condition);
        $page = $data->render();
        if($this->request->isAjax()){
            $result['page'] = $page;
            $result['rows'] = $data;
            return json($result);
        }else{
            $this->assign('uid',$landlord);
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