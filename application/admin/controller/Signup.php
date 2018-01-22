<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3 0003
 * Time: 14:14
 */

namespace app\admin\controller;
use app\common\controller\Backend;
use think\Controller;
use think\Request;
use app\admin\model\Admin;
use think\Db;
use app\admin\model\Area;
use app\common\model\Users;
class Signup extends Backend
{
    /**
     * Signup模型对象
     */
    protected $model = null;
    protected $usersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Signup');
        $this->usersModel = new Users();
    }
    public function landlord(){
        $type = config('users.landlord_type');
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index($type);
            return $json;
        }
        $this->assign('type',$type);
        return $this->view->fetch('index');
    }
    public function cleaner(){
        $type = config('users.cleaner_type');
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index($type);
            return $json;
        }
        $this->assign('type',$type);
        return $this->view->fetch('index');
    }
    //查看列表
    public function index($type=null)
    {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加查询条件
            $filterData = [];
            //$type = $this->request->request('type');
            if($type){
                $filterData['type'] = [
                        'key' => '=',
                        'value' => $type
                 ];

            }
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.username as admin,d.province_name,e.city_name')
                ->join('__USERS__ b','a.users_id = b.id','left')
                ->join('__ADMIN__ c','a.admin_id = c.id','left')
                ->join('__AREA__ d','a.province_code = d.id','left')
                ->join('__AREA__ e','a.city_code = e.id','left')
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.username,d.province_name,e.city_name')
                ->join('__USERS__ b','a.users_id = b.id','left')
                ->join('__ADMIN__ c','a.admin_id = c.id','left')
                ->join('__AREA__ d','a.province_code = d.id','left')
                ->join('__AREA__ e','a.city_code = e.id','left')
                ->where($where)
                ->order($sort,$order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);

    }
    /**
     * 详情
     */
    public function more($ids=null){
        if(!$ids){
            $this->error('异常请求');
        }
        $row = $this->model->getSignupInfo($ids);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 审核
     */
    public function verify(){
        $params = $this->request->post();
        $dataUser = $this->usersModel->getUserById($params['user_id']);
        $dataSignup = $this->model->getSignupInfo($params['id']);
        if(!$dataUser){
            $this->error('无此用户信息');
        }
        if($params['type'] == 1){//保洁
            $upData = ['is_cleaner' => '1'];
        }else{//房东
            $upData = ['is_landlord' => '1'];
        }
        $upData['user_idcard_number'] = $dataSignup['idcard_number'];
        //申请表数据
        $signupData = [
            'status' => 2,
            'admin_id' => session('admin.id'),
            'finish_time' => time()
        ];
        Db::startTrans();
        try{
            $resUsers = $this->usersModel->updateUserById($params['user_id'],$upData);
            $resSignup = $this->model->updateUserById($params['id'],$signupData);
            Db::commit();
            $state = 'success';
        } catch (\Exception $e) {
            Db::rollback();
            $state = 'error';
        }
        if($state == 'success'){
            $this->success('审核成功');
        }else{
            $this->error('审核失败');
        }

    }
}