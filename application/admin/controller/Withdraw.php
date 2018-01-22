<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11 0011
 * Time: 13:12
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\base\UserWithdraw;
use app\common\model\Landlordinfo;
use app\common\model\Cleaninfo;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as WxConfigService;
use think\Controller;
use think\Request;
class Withdraw extends Backend
{
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Withdraw');

    }
    /**
     * 查看用户提现
     */
    public function cleaner(){
        //设置过滤方法
        $type = 'cleaner';
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index($type);
            return $json;
        }
        $this->assign('type',$type);
        return $this->view->fetch('index');
    }
    public function landlord(){
        //设置过滤方法
        $type = 'landlord';
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index();
            return $json;
        }
        $this->assign('type',$type);
        return $this->view->fetch('index');
    }
    public function index($type=null){

        //如果发送的来源是Selectpage，则转发到Selectpage
        if ($this->request->request('pkey_name')) {
            return $this->selectpage();
        }

        //增加查询条件
        $filter = Request::instance()->request('filter');
        $params = json_decode($filter,true);
        $filterData = $this->getSearchParams($params);
        if($type){
            $filterData['type'] = [
                    'key' => '=',
                    'value' => ($type=='cleaner'?config('users.cleaner_type'):config('users.landlord_type'))
            ];

        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
        $total = $this->model
            -> alias('a')
            -> field('a.*,b.user_nickname,c.nickname')
            -> join('__USERS__ b','a.user_id = b.id','left')
            -> join('__ADMIN__ c','a.admin_id = c.id','left')
            -> where($where)
            -> order($sort, $order)
            -> count();

        $list = $this->model
            -> alias('a')
            -> field('a.*,b.user_nickname,c.nickname')
            -> join('__USERS__ b','a.user_id = b.id','left')
            -> join('__ADMIN__ c','a.admin_id = c.id','left')
            -> where($where)
            -> order($sort, $order)
            -> limit($offset, $limit)
            -> select();
        foreach($list as $k=>$v){
            $userInfo = [];
            switch($v['type']){
                case config('users.cleaner_type'):
                    $userInfo = (new Cleaninfo())->getCleanerByUID($v['user_id']);
                    break;
                case config('users.landlord_type'):
                    $userInfo = (new Landlordinfo())->getLandloardByUID($v['user_id']);
                    break;
            }
            $list[$k]['money_total'] = $userInfo['money_total']?$userInfo['money_total']:0;
        }
        $result = array("total" => $total, "rows" => $list);

        return json($result);

    }
    /**
     *  提现详情
     */
    public function more($ids){
        if($ids){
            $data = $this->model->getWithdrawById($ids);
            if($data){
                $this->assign('row',$data);
                $this->assign('withdrawStatus',config('state.withdraw_state'));
                $this->assign('stateList',config('state.state'));
                return $this->fetch();
            }
            $this->error('未找到提现记录');
        }
        $this->error('异常请求');
    }

    /**
     * 审核提现申请
     */
    public function verify(){
        if($this->request->isAjax()){
            $params = $this->request->request();
            if($params){
                $data = [];
                $msg = '操作失败';
                $result = '';
                switch($params['state']){
                    case 'success':
                        $user_withdraw = new UserWithdraw();
                        $wxapp = new Application(WxConfigService::load());
                        $withdraw = $user_withdraw->doWithdraw($wxapp,$params['id'],session(config('session.Admin'))['id']);
                        if($withdraw->getCode() > 0){
                            $msg = $withdraw->getText();
                            $result = '';
                        }else{
                            $result = true;
                        }
                        break;
                    case 'fail':
                        $data['withdraw_status'] = config('state.withdraw_state_fail');
                        $result = $this->model->updateWithdraw($params['id'],$data);
                        break;
                }
                if($result){
                    $this->success('操作成功');
                }
                $this->error($msg);
            }
            $this->error('无有效请求参数');
        }
        $this->error('异常请求');
    }
    /**
     * 审核通过列表
     */
    public function withdraw($type=null){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }

            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            //增加查询条件
            $filterData = $this->getSearchParams($params);
            $filterData['withdraw_status'] = [
                    'key' => '=',
                    'value' => strval(config('state.withdraw_state_success'))
                ];

            //print_r($filterData);die();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                -> alias('a')
                -> field('a.*,b.user_nickname,c.nickname')
                -> join('__USERS__ b','a.user_id = b.id','left')
                -> join('__ADMIN__ c','a.admin_id = c.id','left')
                -> where($where)
                -> order($sort, $order)
                -> count();

            $list = $this->model
                -> alias('a')
                -> field('a.*,b.user_nickname,c.nickname')
                -> join('__USERS__ b','a.user_id = b.id','left')
                -> join('__ADMIN__ c','a.admin_id = c.id','left')
                -> where($where)
                -> order($sort, $order)
                -> limit($offset, $limit)
                -> select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        if($type){
            $this->assign('type',$type);
        }
        return $this->view->fetch();
    }
    public function pay(){
        $withdrawID = Request::instance()->param('id');
        if(!$withdrawID){
            $this->error('未提供需提现的申请信息');
        }
        $wxapp = new Application(WxConfigService::load());
        $admin_id = session(config('session.Admin'))['id'];
        $res = (new UserWithdraw())->doWithdraw($wxapp,$withdrawID,$admin_id);
        if($res->getCode() > 0){
            $this->error($res->getText());
        }
        $this->success('转账成功');
    }
    private function getSearchParams($params){
        $filterData = [];
        if($params){
            foreach($params as $k=>$v){
                if($v) {
                    switch($k){
                        case 'user_nickname':
                            $k = 'b.user_nickname';
                            break;

                    }
                    $filterData[$k] =  [
                        'key' => 'LIKE',
                        'value' => $v
                    ];
                }
            }
        }
        return $filterData;
    }
}