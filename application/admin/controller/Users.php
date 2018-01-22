<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/31 0031
 * Time: 17:25
 */

namespace app\admin\controller;
use app\common\controller\Backend;
use app\common\model\Users as User;
use app\common\model\OrderClean;
use app\common\model\OrderBnb;
use app\common\model\Bnb;
use think\Controller;
use think\Request;
class Users extends Backend
{
    protected $model = null;
    protected $orderCleanModel = null;
    protected $orderBnbModel = null;
    protected $bnbModel = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new User();
        $this->orderCleanModel = new OrderClean();
        $this->orderBnbModel = new OrderBnb();
        $this->bnbModel = new Bnb();
    }
    /**
     * 查看
     */
    public function index($type=null){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加订单查询条件
            $filterData = [];
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            $filterData = $this->getSearchParams($params);

            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,null,$filterData);
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->field(['password', 'salt', 'token'], true)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach($list as $k=>$v){
                $isUploadAvatar = strstr($v['user_avatar'],'http');
                if(!$isUploadAvatar){
                    $list[$k]['user_avatar'] = '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/'.$v['user_avatar'];
                }
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        if($type){
            $this->assign('type',$type);
        }
        return $this->view->fetch();

    }
    /**
     * 详情*
     */
    public function more($ids=NULL){
        $row = $this->model->getUsersInfo($ids);
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
        }
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
     * 认证用户
     */
    public function setUserclass(){
        if($this->request->isAjax()){
            $param = $this->request->request('row/a');
            if(!$param['id'] || !$param['user_idcard_number']){
                $this->error('未填写认证的证件号码');
            }
            if($param['user_idcard_image_up'] && $param['user_idcard_image_down']){
                $param['user_idcard_image'] = $param['user_idcard_image_up'].','.$param['user_idcard_image_down'];
                unset( $param['user_idcard_image_up']);
                unset($param['user_idcard_image_down']);
            }else{
                $this->error('请上传身份证正反两面照片');
            }
            $userId = $param['id'];
            $row = $this->model->getUsersInfo($userId);
            if($row){
                if($row['user_class'] == 1){
                    $param['user_class'] = 2;
                    $result = $this->model->updateUserById($userId,$param);
                    if($result){
                        $this->success('认证成功');
                    }
                    $this->error('认证失败');
                }
                $this->error('该用户已认证');
            }
            $this->error('该用户不存在');
        }else{
            $user_id = Request::instance()->param('ids');
            if(!$user_id){
                $this->error('未选择需要认证用户的id');
            }
            $usersInfo = $this->model->getUserById($user_id);
            $this->assign('row',$usersInfo);
            return $this->fetch();
        }
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